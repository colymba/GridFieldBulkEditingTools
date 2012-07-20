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
	
	public static function filterDatafieldsByClass ( $config, $dataFields )
	{
		//@todo
	}
	
	public static function filterDataFieldsByName ( $config, $dataFields )
	{
		//@todo
	}
	
	public static function dataFieldsToHTML ( $dataFields )
	{
		//@todo
	}
	
	public static function escapeFormFieldsHTML ( $formFieldsHTML )
	{
		//@todo
	}
	
	public static function unescapeFormFieldsPOSTData ( $requestVars )
	{
		//@todo
	}
	
	
	
	
}