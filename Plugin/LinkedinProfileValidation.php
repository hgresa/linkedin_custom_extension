<?php

namespace Tomadevall\Developers\Plugin;

use Magento\Eav\Model\Attribute\Data\Text;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\FilterBuilder;
use Tomadevall\Developers\Model\Config\Config as LinkedinConfig;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session;

class LinkedinProfileValidation
{
    protected CustomerRepositoryInterface $customerRepository;

    protected SearchCriteriaInterface $searchCriteria;

    protected FilterGroup $filterGroup;

    protected FilterBuilder $filterBuilder;

    protected DataPersistorInterface $dataPersistor;

    protected LinkedinConfig $linkedinConfig;

    protected Http $request;

    protected Session $session;

    private array $errors;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param FilterGroup $filterGroup
     * @param FilterBuilder $filterBuilder
     * @param DataPersistorInterface $dataPersistor
     * @param LinkedinConfig $linkedinConfig
     * @param Http $request
     * @param Session $session
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaInterface     $searchCriteria,
        FilterGroup                 $filterGroup,
        FilterBuilder               $filterBuilder,
        DataPersistorInterface      $dataPersistor,
        LinkedinConfig              $linkedinConfig,
        Http                        $request,
        Session                     $session
    )
    {
        $this->customerRepository = $customerRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filterGroup = $filterGroup;
        $this->filterBuilder = $filterBuilder;
        $this->dataPersistor = $dataPersistor;
        $this->linkedinConfig = $linkedinConfig;
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * @return bool
     */
    private function isAccountEditPage(): bool
    {
        return in_array($this->request->getFullActionName(), ['customer_account_edit', 'customer_account_editPost']);
    }

    /**
     * @param string $profileUrl
     * @return bool
     */
    private function urlIsValid(string $profileUrl): bool
    {
        $pattern = '/^(http(s)?:\/\/)?([\w]+\.)?linkedin\.com\/(pub|in|profile)/m';
        return preg_match($pattern, $profileUrl);
    }

    /**
     * @param string $profileUrl
     * @return bool
     * @throws LocalizedException
     */
    private function urlIsUnique(string $profileUrl): bool
    {
        $linkedinAttrCode = LinkedinConfig::getLinkedinProfileAttributeCode();

        if (
            $this->isAccountEditPage() &&
            $this->session->getCustomer()->getData($linkedinAttrCode) === $profileUrl
        )
        {
            return true;
        }

        $filter = $this->filterBuilder
            ->setField($linkedinAttrCode)
            ->setConditionType('eq')
            ->setValue($profileUrl)
            ->create();

        $this->filterGroup->setFilters([$filter]);

        $this->searchCriteria->setFilterGroups([$this->filterGroup]);

        return !$this->customerRepository->getList($this->searchCriteria)->getTotalCount();
    }

    /**
     * @param string $url
     * @return string
     * @throws LocalizedException
     */
    public function validateUrl(string $url): string
    {
        if (!$this->urlIsValid($url)) {
            $this->errors[] = __('"%1" is not a valid linkedin profile url.', $url);
        }

        if (!$this->urlIsUnique($url)) {
            $this->errors[] = __('User with "%1" linkedin profile link already exists', $url);
        }

        if (isset($this->errors)) {
            return false;
        }

        return true;
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
        return $this->errors ?? null;
    }

    /**
     * @param Text $textEav
     * @param $result
     * @return array|bool
     * @throws LocalizedException
     */
    public function afterValidateValue(
        Text $textEav,
             $result
    )
    {
        $attribute = $textEav->getAttribute();
        $attributeCode = $attribute->getAttributeCode();

        if ($attributeCode === LinkedinConfig::getLinkedinProfileAttributeCode()) {
            $value = $textEav->getEntity()->getDataUsingMethod($attributeCode);

            if (!$attribute->getIsRequired() && ($value === false || $value === '')) {
                return $result;
            }

            if ($this->validateUrl($value)) {
                return $result;
            }

            $this->dataPersistor->set($attributeCode, $value);

            if (is_array($result)) {
                return array_merge($result, $this->getErrors());
            }

            return $this->getErrors();
        }

        return $result;
    }
}
