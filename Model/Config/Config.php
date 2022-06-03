<?php

namespace Tomadevall\Developers\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Tomadevall\Developers\Setup\Patch\Data\LinkedinAttributeCreatePatch;

class Config
{
    public const XML_PATH_LINKEDIN_PROFILE_FIELD_OPTION = 'customer/create_account/linkedin_profile_field_option';

    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getLinkedinFieldOption()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LINKEDIN_PROFILE_FIELD_OPTION
        );
    }

    /**
     * @return string
     */
    public static function getLinkedinProfileAttributeCode(): string
    {
        return LinkedinAttributeCreatePatch::LINKEDIN_PROFILE_ATTRIBUTE_CODE;
    }
}
