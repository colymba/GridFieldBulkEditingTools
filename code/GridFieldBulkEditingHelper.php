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
	//put your code here
	
	public static function getModelCMSDataFields ( $config, $modelClass, $recordID = null )
	{
		$cmsFields = singleton($modelClass)->getCMSFields();
				
		$cmsDataFields = $cmsFields->dataFields();		
		$cmsDataFields = GridFieldBulkEditingHelper::filterNonEditableRecordsFields($config, $cmsDataFields);
		
		return $cmsDataFields;
	}
	
	/**
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