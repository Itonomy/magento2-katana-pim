<?php

namespace Itonomy\Katanapim\Model\Source;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Statuses implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $availableOptions = $this->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * Prepare import statuses.
     *
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return [
            KatanaImportInterface::STATUS_PENDING => __('Pending'),
            KatanaImportInterface::STATUS_RUNNING => __('Running'),
            KatanaImportInterface::STATUS_ERROR => __('Error'),
            KatanaImportInterface::STATUS_COMPLETE => __('Complete')
        ];
    }
}
