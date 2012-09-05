<?php
/**
 * Generic helper class for the various bulk editing component
 * contains common functions
 *
 * @todo clean up functions names: makes them consistent and more explicit
 * 
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkEditingHelper {
	
	/**
	 * Returns all or allowed Form Fields for editing
	 * 
	 * @param type $config
	 * @param type $modelClass
	 * @param type $recordID
	 * @return type 
	 */
	public static function getModelCMSDataFields ( $config, $modelClass )
	{
		$cmsFields = singleton($modelClass)->getCMSFields();
				
		$cmsDataFields = $cmsFields->dataFields();		
		$cmsDataFields = GridFieldBulkEditingHelper::filterNonEditableRecordsFields($config, $cmsDataFields);
		
		return $cmsDataFields;
	}
	
	/**
	 * Populate the FomFields with a given record's value
	 * 
	 * @TODO: UploadField get populated OK, however, file recovery and controllers URL are all wrong and should be updated manually
	 * UploadField url should point to GridFieldBulkManager_Request appropriate method
	 * 
	 * @param type $cmsDataFields
	 * @param type $modelClass
	 * @param type $recordID
	 * @return type 
	 */
	public static function populateCMSDataFields ( $cmsDataFields, $modelClass, $recordID )
	{
		$record = DataObject::get_by_id($modelClass, $recordID);
				
		$recordComponents = array(
			'one' => $record->has_one(),
			'many' => $record->has_many(),
			'manymany' => $record->many_many()
		);
		
		foreach ( $cmsDataFields as $name => $f )
		{			
			if ( array_key_exists($name, $recordComponents['one']) )
			{
				$obj = $record->{$name}();				
				switch ( get_class($f) )
				{
					case 'UploadField':											
						$cmsDataFields[$name]->setRecord($record);
						$cmsDataFields[$name]->setItems( DataList::create($obj->ClassName)->byID($obj->ID) );
						print_r($cmsDataFields[$name]);
						break;
					
					default:
						$cmsDataFields[$name]->setValue( $obj->ID );
						break;
				}
				
			}
			else if ( array_key_exists($name, $recordComponents['many']) || array_key_exists($name, $recordComponents['manymany']) )
			{				
				$list = $record->{$name}();				
				switch ( get_class($f) )
				{
					case 'UploadField':
						$cmsDataFields[$name]->setRecord($record);
						$cmsDataFields[$name]->setItems($list);
						break;
					
					case 'DropdownField':
					case 'ListboxField':
						$cmsDataFields[$name]->setValue( array_values($list->getIDList()) );
						break;
					
					default:
						break;
				}
				
			}else{
				$cmsDataFields[$name]->setValue( $record->getField($name) );
			}
		}
		
		return $cmsDataFields;
	}
	
	/**
	 * Remove all the fields that were not explicitly specified as editable via the $config
	 * 
	 * @param type $config
	 * @param type $dataFields
	 * @return type 
	 */
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
	
	/**
	 * Convert a list of DataFields into a list of their repective HTML
	 * 
	 * @param type $dataFields
	 * @return type 
	 */
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
	
	/**
	 * Escape form fields name with a $unique token
	 * avoid having an ID URLParams sent through and cought as a pageID
	 * 
	 * @param type $formFields
	 * @param type $unique
	 * @return type 
	 */
	public static function escapeFormFieldsName ( $formFields, $unique )
	{
		$prefix = 'record_'.$unique.'_';
		
		foreach ( $formFields as $name => $f )
		{
			$f->Name = $prefix . $f->Name;
			$formFields[$name] = $f;
		}
		
		return $formFields;
	}
	
	/**
	 * Escape HTML form node names with a $unique token
	 * avoid having an ID URLParams sent through and cought as a pageID
	 * 
	 * @param type $formFieldsHTML
	 * @param type $unique
	 * @return type 
	 */
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
	
  /**
	 * Simple function that replace the 'record_XX_' off of the ID field name
	 * prefix needed since it was taken for a pageID if sent as is as well as fixing other things
	 * 
	 * @param array $data
	 * @return array 
	 */
	public static function unescapeFormFieldsPOSTData ( $requestVars )
	{
		$return = array();
		
		foreach( $requestVars as $key => $val)
		{			
			$return[ preg_replace( '/record_(\d+)_(\w+)/i', '$2', $key) ] = $val;
		}
		
		if ( isset($return['url']) ) unset($return['url']);
		if ( isset($return['cacheBuster']) ) unset($return['cacheBuster']);
		
		return $return;
	}
	
	
}