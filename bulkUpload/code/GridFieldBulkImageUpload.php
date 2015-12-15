<?php
/**
 * Legacy GridFieldBulkImageUpload component.
 *
 * @deprecated 2.0 "GridFieldBulkImageUpload" is deprecated, use {@link GridFieldBulkUpload} class instead.
 *
 * @author colymba
 */
class GridFieldBulkImageUpload extends GridFieldBulkUpload
{
    /**
   * Component constructor.
   *
   * @deprecated 2.0 "GridFieldBulkImageUpload" is deprecated, use {@link GridFieldBulkUpload} class instead.
   * 
   * @param string $fileRelationName
   */
  public function __construct($fileRelationName = null)
  {
      Deprecation::notice('2.0', '"GridFieldBulkImageUpload" is deprecated, use "GridFieldBulkUpload" class instead.');

      return new GridFieldBulkUpload($fileRelationName);
  }
}
