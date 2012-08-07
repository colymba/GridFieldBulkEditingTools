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
				//@TODO: this wont work if the fieldname has ID in it: i.e. TheIDImageID -> remove last 2 char only
				unset( $dataFields[str_ireplace('ID', '', $config['imageFieldName']) ] );
			}
		}
				
		//if class blacklist filter
		if ( count($config['fieldsClassBlacklist']) > 0 )
		{
			foreach ($dataFields as $fieldName => $field)
			{
				//@TODO find PHP function that return the classname
				if ( in_array($field->ClassName, $config['fieldsClassBlacklist']) )
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