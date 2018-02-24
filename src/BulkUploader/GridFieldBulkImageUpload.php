<?php

namespace Colymba\BulkUpload;

use Colymba\BulkUpload\BulkUploader;
use SilverStripe\Dev\Deprecation;

/**
 * Legacy GridFieldBulkImageUpload component.
 *
 * @deprecated 2.0 "GridFieldBulkImageUpload" is deprecated, use {@link BulkUploader} class instead.
 *
 * @author colymba
 */
class GridFieldBulkImageUpload extends BulkUploader
{
    /**
     * Component constructor.
     *
     * @deprecated 2.0 "GridFieldBulkImageUpload" is deprecated, use {@link BulkUploader} class instead.
     *
     * @param string $fileRelationName
     */
    public function __construct($fileRelationName = null)
    {
        Deprecation::notice(
            '2.0',
            '"GridFieldBulkImageUpload" is deprecated, use "BulkUploader" class instead.'
        );

        return new BulkUploader($fileRelationName);
    }
}
