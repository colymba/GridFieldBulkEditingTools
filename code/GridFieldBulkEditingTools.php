<?php
/**
 * Generic helper class for the various bulk editing component
 * contains common functions
 *
 * @author colymba
 */
class GridFieldBulkEditingTools {
	//put your code here
	
	public static function getModelDataFields ( $gridfield )
	{
		$modelClass = $gridfield->list->dataClass;
		$cmsFields = singleton($modelClass)->getCMSFields();
		$fields = $cmsFields->dataFields();
			
		return $fields->dataFields();
	}
	
	public static function getModelFilteredDataFields ( $config, $dataFields )
	{
		//@todo
	}
	
	
	
	
	
	
}