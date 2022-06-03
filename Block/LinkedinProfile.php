<?php

namespace Tomadevall\Developers\Block;

use Tomadevall\Developers\Model\Config\Config;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Api\Data\AttributeValidationRuleInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Customer\Block\Account\Dashboard;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\App\Request\Http;

class LinkedinProfile extends Dashboard
{
    private array $fieldValidationRules;

    protected Config $config;

    protected DataPersistorInterface $dataPersistor;

    protected AttributeRepositoryInterface $attributeRepository;

    protected Http $request;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param Config $config
     * @param DataPersistorInterface $dataPersistor
     * @param AttributeRepositoryInterface $attributeRepository
     * @param Http $request
     * @param array $data
     */
    public function __construct(
        Context                      $context,
        Session                      $customerSession,
        SubscriberFactory            $subscriberFactory,
        CustomerRepositoryInterface  $customerRepository,
        AccountManagementInterface   $customerAccountManagement,
        Config                       $config,
        DataPersistorInterface       $dataPersistor,
        AttributeRepositoryInterface $attributeRepository,
        Http                         $request,
        array                        $data = []
    )
    {
        parent::__construct($context, $customerSession, $subscriberFactory, $customerRepository, $customerAccountManagement, $data);
        $this->config = $config;
        $this->dataPersistor = $dataPersistor;
        $this->attributeRepository = $attributeRepository;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function fieldIsInvisible(): bool
    {
        return $this->config->getLinkedinFieldOption() === 'invisible';
    }

    /**
     * @return bool
     */
    public function fieldIsRequired(): bool
    {
        return $this->config->getLinkedinFieldOption() === 'required';
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    private function getDataPersistorItem(string $key)
    {
        $value = $this->dataPersistor->get($key);

        if ($value) {
            $this->dataPersistor->clear($key);

            return $value;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getAttributeCode(): string
    {
        return $this->config::getLinkedinProfileAttributeCode();
    }

    /**
     * @return mixed
     */
    public function getFieldValue()
    {
        if ($this->request->getFullActionName() === 'customer_account_edit') {
            $attribute = $this->getCustomer()->getCustomAttribute($this->getAttributeCode());

            return $attribute ? $attribute->getValue() : null;
        }

        return $this->getDataPersistorItem($this->getAttributeCode());
    }

    /**
     * @return array|AttributeValidationRuleInterface[]|null
     * @throws NoSuchEntityException
     */
    private function getFieldValidationRules(): ?array
    {
        if (!isset($this->fieldValidationRules)) {
            $attribute = $this->attributeRepository->get(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $this->getAttributeCode()
            );
            $this->fieldValidationRules = $attribute->getValidationRules();
        }

        return $this->fieldValidationRules;
    }

    /**
     * @return AttributeValidationRuleInterface|mixed
     * @throws NoSuchEntityException
     */
    public function getFieldMinLength()
    {
        return $this->getFieldValidationRules()['min_text_length'];
    }

    /**
     * @return AttributeValidationRuleInterface|mixed
     * @throws NoSuchEntityException
     */
    public function getFieldMaxLength()
    {
        return $this->getFieldValidationRules()['max_text_length'];
    }
}
