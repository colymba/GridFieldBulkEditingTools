<?php
/**
 * Base Component for all 'GridFieldBulkEditingTools'
 * defines the common HTML fragment
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkEditingTools implements GridField_HTMLProvider
{
    public function getHTMLFragments($gridField)
		{			
			Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkEditingTools.css');
			
			return array(
					"after" => "<div id=\"bulkEditTools\">\$DefineFragment(bulk-edit-tools)</div>"
			);
    }
}