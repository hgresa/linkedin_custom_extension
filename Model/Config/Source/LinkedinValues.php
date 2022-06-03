<?php

namespace Tomadevall\Developers\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class LinkedinValues implements ArrayInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'invisible', 'label' => __('Invisible')],
            ['value' => 'optional', 'label' => __('Optional')],
            ['value' => 'required', 'label' => __('Required')],

        ];
    }
}
