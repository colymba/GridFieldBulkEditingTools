<?php
/**
 * 
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkEditingTools implements GridField_HTMLProvider
{
    public function getHTMLFragments($gridField)
		{			
			Requirements::css('GridFieldBulkImageUpload/css/GridFieldBulkEditingTools.css');
			
			return array(
					//"footer" => '<tr><td colspan="'.$columnsCount.'">\$DefineFragment(bulk-edit-tools)</td></tr>'
					"after" => "<div id=\"bulkEditTools\">\$DefineFragment(bulk-edit-tools)</div>"
			);
    }
}