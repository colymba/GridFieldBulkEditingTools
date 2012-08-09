<?php
/**
 * Generic helper class for the various bulk editing component
 * contains common functions
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkEditingHelper {
	//put your code here
	
	public static function getModelCMSDataFields ( $config, $modelClass )
	{
		$cmsFields = singleton($modelClass)->getCMSFields();
		$cmsDataFields = $cmsFields->dataFields();		
		$cmsDataFields = GridFieldBulkEditingHelper::filterNonEditableRecordsFields($config, $cmsDataFields);
		
		return $cmsDataFields;
	}
	
	
	public static function filterNonEditableRecordsFields ( $config, $dataFields )
	{
		if ( isset($config['editableFields']) )
		{
			if ( $config['editableFields'] != null )
			{
				foreach ($dataFields as $name => $field)
				{
					if ( !in_array($name, $config['editableFields']) )
					{
						unset( $dataFields[$name] );
					}
				}
			}
		}
		
		return $dataFields;
	}

	/**
	 * Filters out all unwanted fields from the config settings
	 * 
	 * @param array $config
	 * @param array $dataFields
	 * @return array 
	 */
	public static function getModelFilteredDataFields ( $config, $dataFields )
	{
		//remove the image field - for bulk image upload
		if ( isset($config['imageFieldName']) )
		{
			if ( $config['imageFieldName'] != null )
			{
				unset( $dataFields[ substr($config['imageFieldName'], 0, -2) ] );
			}
		}
		
		//if class blacklist filter
		if ( count($config['fieldsClassBlacklist']) > 0 )
		{
			foreach ($dataFields as $fieldName => $field)
			{
				if ( in_array(get_class($field), $config['fieldsClassBlacklist']) )
				{
					array_push($config['fieldsNameBlacklist'], $fieldName);
				}
			}
		}
		
		//if name blacklist filter
		if ( count($config['fieldsNameBlacklist']) > 0 )
		{
			foreach ( $config['fieldsNameBlacklist'] as $badFieldName )
			{
				unset( $dataFields[ $badFieldName ] );
			}
		}
		
		return $dataFields;
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
		$fieldsHTML = array();
		
		foreach ( $dataFields as $key => $field )
		{
			//@TODO: FieldHolder() does not seem to exist on UploadField
			$fieldsHTML[$key] = $field->FieldHolder();
		}
		
		return $fieldsHTML;
	}
	
	public static function escapeFormFieldsHTML ( $formFieldsHTML, $unique )
	{
		$prefix = 'record_'.$unique.'_';
		
		foreach ( $formFieldsHTML as $name => $html )
		{
			$formFieldsHTML[$name] = str_ireplace ( array('id="', 'for="', 'name="'),
																							array('id="'.$prefix, 'for="'.$prefix, 'name="'.$prefix), 
																							$html);
		}
		
		return $formFieldsHTML;
	}
	
	public static function unescapeFormFieldsPOSTData ( $requestVars )
	{
		//@todo
	}
	
	
	
	
}