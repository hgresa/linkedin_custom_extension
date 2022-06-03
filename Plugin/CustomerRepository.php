<?php

namespace Tomadevall\Developers\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Tomadevall\Developers\Model\Config\Config as LinkedinConfig;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerRepository
{
    protected LinkedinConfig $config;

    protected Http $request;

    /**
     * @param LinkedinConfig $config
     * @param Http $request
     */
    public function __construct(
        LinkedinConfig $config,
        Http           $request
    )
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @param $passwordHash
     * @return array|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface           $customer,
                                    $passwordHash
    ): ?array
    {
        if (
            $this->config->getLinkedinFieldOption() === 'invisible' &&
            $this->request->getFullActionName() === 'customer_account_editPost'
        ) {
            $customerObj = $subject->getById($customer->getId());
            $linkedinAttrCode = LinkedinConfig::getLinkedinProfileAttributeCode();
            $linkedinAttr = $customerObj->getCustomAttribute($linkedinAttrCode);

            if ($linkedinAttr) {
                $customer->getCustomAttribute($linkedinAttrCode)->setValue($linkedinAttr->getValue());

                return [$customer, $passwordHash];
            }
        }

        return null;
    }
}
