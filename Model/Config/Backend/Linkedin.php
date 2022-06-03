<?php

namespace Tomadevall\Developers\Model\Config\Backend;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Tomadevall\Developers\Model\Config\Config;

class Linkedin extends Value
{
    protected CustomerSetup $customerSetup;

    protected LoggerInterface $logger;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param CustomerSetup $customerSetup
     * @param LoggerInterface $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context              $context,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        CustomerSetup        $customerSetup,
        LoggerInterface      $logger,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null, array $data = []
    )
    {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->customerSetup = $customerSetup;
        $this->logger = $logger;
    }

    /**
     * @param bool $isRequired
     * @param string $attributeCode
     * @return void
     * @throws LocalizedException
     */
    public function updateAttributeRequirement(bool $isRequired, string $attributeCode): void
    {
        $attribute = $this->customerSetup->getEavConfig()->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributeCode);
        if ($attribute->getIsRequired() !== $isRequired) {
            $this->customerSetup->updateAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $attributeCode,
                'is_required',
                $isRequired
            );
        }
    }

    /**
     * @return Linkedin
     */
    public function afterSave(): Linkedin
    {
        try {
            $value = $this->getValue();

            switch ($value) {
                case 'invisible':
                case 'optional':
                    $this->updateAttributeRequirement(false, Config::getLinkedinProfileAttributeCode());
                    break;
                case 'required':
                    $this->updateAttributeRequirement(true, Config::getLinkedinProfileAttributeCode());
                    break;
            }
        } catch (AlreadyExistsException|LocalizedException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return parent::afterSave();
    }
}
