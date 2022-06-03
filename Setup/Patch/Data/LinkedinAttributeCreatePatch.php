<?php

namespace Tomadevall\Developers\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\ResourceModel\Attribute as AttributeResource;
use Exception;
use Psr\Log\LoggerInterface;

class LinkedinAttributeCreatePatch implements DataPatchInterface, PatchRevertableInterface
{
    public const LINKEDIN_PROFILE_ATTRIBUTE_CODE = 'linkedin_profile_4';

    protected ModuleDataSetupInterface $moduleDataSetup;

    protected CustomerSetupFactory $customerSetupFactory;

    protected AttributeResource $attributeResource;

    protected LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeResource $attributeResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory     $customerSetupFactory,
        AttributeResource        $attributeResource,
        LoggerInterface          $logger
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetup = $customerSetupFactory->create(['setup' => $moduleDataSetup]);
        $this->attributeResource = $attributeResource;
        $this->logger = $logger;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        try {
            $this->moduleDataSetup->getConnection()->startSetup();

            $this->customerSetup->addAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                self::LINKEDIN_PROFILE_ATTRIBUTE_CODE,
                [
                    'label' => 'Linkedin Profile',
                    'required' => 1,
                    'unique' => 1,
                    'position' => 200,
                    'system' => 0,
                    'user_defined' => 1,
                    'is_used_in_grid' => 1,
                    'is_visible_in_grid' => 1,
                    'is_filterable_in_grid' => 1,
                    'is_searchable_in_grid' => 1
                ]
            );

            $this->customerSetup->addAttributeToSet(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                self::LINKEDIN_PROFILE_ATTRIBUTE_CODE
            );

            $attribute = $this->customerSetup->getEavConfig()
                ->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, self::LINKEDIN_PROFILE_ATTRIBUTE_CODE);

            $attribute->setData('used_in_forms', [
                'adminhtml_checkout',
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit',
                'checkout_register'
            ]);

            $attribute->setData('validate_rules', [
                'input_validation' => 1,
                'min_text_length' => 1,
                'max_text_length' => 250
            ]);

            $this->attributeResource->save($attribute);

            $this->moduleDataSetup->getConnection()->endSetup();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @return void
     */
    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->customerSetup->removeAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            self::LINKEDIN_PROFILE_ATTRIBUTE_CODE
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }
}
