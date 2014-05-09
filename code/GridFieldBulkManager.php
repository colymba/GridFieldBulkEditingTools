<?php
/**
 * GridField component for editing attached models in bulk
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkManager implements GridField_HTMLProvider, GridField_ColumnProvider, GridField_URLHandler
{	
	/**
	 * component configuration
	 * 
	 * 'imageFieldName' => field name of the $has_one Model Image relation
	 * 'editableFields' => fields editable on the Model
	 * 'fieldsClassBlacklist' => field types that will be removed from the automatic form generation
	 * 'fieldsNameBlacklist' => fields that will be removed from the automatic form generation
	 * 'actions' => maps of action name and configuration
	 * 
	 * @var array 
	 */
	protected $config = array(
		'editableFields' => null,
		'fieldsClassBlacklist' => array(),
		'fieldsNameBlacklist' => array(),
		'actions' => array()
	);
	
	/**
	 * Holds any class that should not be used as they break the component
	 * These cannot be removed from the blacklist
	 */
	protected $forbiddenFieldsClasses = array( 'GridField', 'UploadField' );
	
	
	public function __construct($editableFields = null, $defaultActions = true)
	{				
		if ( $editableFields != null ) $this->setConfig ( 'editableFields', $editableFields );
		$this->config['fieldsClassBlacklist'] = $this->forbiddenFieldsClasses;

		if ( $defaultActions )
		{
			$this->config['actions'] = array(
	      'bulkedit'   => array(
	      	'label' => _t('GridFieldBulkTools.EDIT_SELECT_LABEL', 'Edit'),
	      	'handler' => 'GridFieldBulkActionEditHandler',
	      	'config' => array(
						'isAjax' => false,
						'icon' => 'pencil',
						'isDestructive' => false
					)
	      ),
	      'unlink' => array(
	      	'label' => _t('GridFieldBulkTools.UNLINK_SELECT_LABEL', 'UnLink'),
	      	'handler' => 'GridFieldBulkActionUnlinkHandler',
	      	'config' => array(
						'isAjax' => true,
						'icon' => 'chain--minus',
						'isDestructive' => false
					)
	      ),
	      'delete' => array(
	      	'label' => _t('GridFieldBulkTools.DELETE_SELECT_LABEL', 'Delete'),
	      	'handler' => 'GridFieldBulkActionDeleteHandler',
	      	'config' => array(
						'isAjax' => true,
						'icon' => 'decline',
						'isDestructive' => true
					)
	      )
			);
		}
	}
	
	/**
	 * Set a component configuration parameter
	 * 
	 * @param string $reference
	 * @param mixed $value 
	 */
	function setConfig ( $reference, $value )
	{
		if (!key_exists($reference, $this->config) )
		{
			user_error("Unknown option reference: $reference", E_USER_ERROR);
		}

		if ( $reference == 'actions' )
		{
			user_error("Bulk actions must be edited via addBulkAction() and removeBulkAction()", E_USER_ERROR);
		}
		
		if ( ($reference == 'fieldsClassBlacklist' || $reference == 'fieldsClassBlacklist' || $reference == 'editableFields') && !is_array($value) )
		{
			$value = array($value);
		}

		//makes sure $forbiddenFieldsClasses are in no matter what
		if ( $reference == 'fieldsClassBlacklist' )
		{
			$value = array_unique( array_merge($value, $this->forbiddenFieldsClasses) );
		}

		$this->config[$reference] = $value;

		return $this;
	}
	
	/**
	 * Returns one $config parameter of the full $config
	 * 
	 * @param string $reference $congif parameter to return
	 * @return mixed 
	 */
	function getConfig ( $reference = false )
	{
		if ( $reference ) return $this->config[$reference];
		else return $this->config;
	}
	
	/**
	 * Add a field to the editable fields blacklist
	 * 
	 * @param string $fieldName
	 * @return boolean 
	 */
	function addFieldNameToBlacklist ( $fieldName )
	{
		return array_push( $this->config['fieldsNameBlacklist'], $fieldName);
	}
	
	/**
	 * Add a class to the editable fields blacklist
	 * 
	 * @param string $className
	 * @return boolean 
	 */
	function addClassToBlacklist ( $className )
	{
		return array_push( $this->config['fieldsClassBlacklist'], $className);
	}
	
	/**
	 * Remove a field to the editable fields blacklist
	 * 
	 * @param string $fieldName
	 * @return boolean 
	 */
	function removeFieldNameFromBlacklist ( $fieldName )
	{
		if (key_exists($fieldName, $this->config['fieldsNameBlacklist'])) {
			return delete( $this->config['fieldsNameBlacklist'][$fieldName] );
		}else{
			return false;
		}
	}
	
	/**
	 * Remove a class to the editable fields blacklist
	 * 
	 * @param string $className
	 * @return boolean 
	 */
	function removeClassFromBlacklist ( $className )
	{
		if (key_exists($className, $this->config['fieldsNameBlacklist']) && !in_array($className, $this->forbiddenFieldsClasses)) {
			return delete( $this->config['fieldsNameBlacklist'][$className] );
		}else{
			return false;
		}
	}


	/**
	 * Lets you add custom bulk actions to the bulk manager interface
	 *
	 * @todo  add config options for front-end: isAjax, icon
	 * 
	 * @param  string                $name     Bulk action's name. Used by RequestHandler.
	 * @param  string                $label    Dropdown menu action's label. Default to ucfirst($name).
	 * @param  string                $handler  RequestHandler class name for this action. Default to 'GridFieldBulkAction'.ucfirst($name).'Handler'
	 * @param  array 								 $config   Front-end configuration array( 'isAjax' => true, 'icon' => 'accept', 'isDestructive' => false )
	 * @return GridFieldBulkManager            Current GridFieldBulkManager instance
	 */
	function addBulkAction($name, $label = null, $handler = null, $config = null)
	{
		if ( array_key_exists($name, $this->config['actions']) )
		{
			user_error("Bulk action $name already exists.", E_USER_ERROR);
		}

		$name = strtolower($name);

		if ( !$label )
		{
			$label = ucfirst($name);
		}

		if ( !$handler )
		{
			$handler = 'GridFieldBulkAction'.ucfirst($name).'Handler';
		}

		if ( !ClassInfo::exists( $handler ) )
		{
			user_error("Bulk action handler for $name not found: $handler", E_USER_ERROR);
		}

		if ( $config && !is_array($config) )
		{
			user_error("Bulk action front-end config should be an array of key => value pairs.", E_USER_ERROR);
		}
		else{
			$config = array(
				'isAjax'        => isset($config['isAjax']) ? $config['isAjax'] : true,
				'icon'          => isset($config['icon']) ? $config['icon'] : 'accept',
				'isDestructive' => isset($config['isDestructive']) ? $config['isDestructive'] : false
			);
		}

		$this->config['actions'][$name] = array(
      'label'   => $label,
      'handler' => $handler,
      'config'  => $config
    );

		return $this;
	}


	/**
	 * Removes a bulk actions from the bulk manager interface
	 * 
	 * @param  string 								$name  Bulk action's name
	 * @return GridFieldBulkManager          Current GridFieldBulkManager instance
	 */
	function removeBulkAction($name)
	{
		if ( !array_key_exists($name, $this->config['actions']) )
		{
			user_error("Bulk action $name doesn't exists.", E_USER_ERROR);
		}

		unset( $this->config['actions'][$name] );

		return $this;
	}

	
	/* GridField_ColumnProvider */
	
	function augmentColumns($gridField, &$columns)
	{
		if(!in_array('BulkSelect', $columns)) $columns[] = 'BulkSelect';
	}
	
	function getColumnsHandled($gridField)
	{
		return array('BulkSelect');
	}
	
	function getColumnContent($gridField, $record, $columnName)
	{
		$cb = CheckboxField::create('bulkSelect_'.$record->ID)
			    ->addExtraClass('bulkSelect no-change-track')
			    ->setAttribute('data-record', $record->ID);
		return $cb->Field();
	}
	
	function getColumnAttributes($gridField, $record, $columnName)
	{
		return array('class' => 'col-bulkSelect');
	}
	
	function getColumnMetadata($gridField, $columnName)
	{
		if($columnName == 'BulkSelect') {
			return array('title' => 'Select');
		}
	}
		
	/* // GridField_ColumnProvider */
	
	/**
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getHTMLFragments($gridField)
	{				
		Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkManager.css');
		Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkManager.js');
		Requirements::add_i18n_javascript(BULK_EDIT_TOOLS_PATH . '/javascript/lang');
		
		if ( !count($this->config['actions']) )
		{
			user_error("Trying to use GridFieldBulkManager without any bulk action.", E_USER_ERROR);
		}

		$actionsListSource = array();
		$actionsConfig = array();

		foreach ($this->config['actions'] as $action => $actionData)
		{
      $actionsListSource[$action] = $actionData['label'];
      $actionsConfig[$action]     = $actionData['config'];
		}

		reset($this->config['actions']);
		$firstAction = key($this->config['actions']);

		$dropDownActionsList = DropdownField::create('bulkActionName', '')
			->setSource( $actionsListSource )
			->setAttribute('class', 'bulkActionName no-change-track')
			->setAttribute('id', '');

    $templateData = array(
    	'Menu' => $dropDownActionsList->FieldHolder(),
    	'Button' => array(
        'Label'      => _t('GridFieldBulkTools.ACTION_BTN_LABEL', 'Go'),
        'DataURL'    => $gridField->Link('bulkaction'),
        'Icon'       => $this->config['actions'][$firstAction]['config']['icon'],
        'DataConfig' => htmlspecialchars(json_encode($actionsConfig), ENT_QUOTES, 'UTF-8')
    	),
    	'Select' => array(
    		'Label' => _t('GridFieldBulkTools.SELECT_ALL_LABEL', 'Select all')
    	),
    	'Colspan' => (count($gridField->getColumns()) - 1)
    );

		$templateData = new ArrayData($templateData);

		return array(
			'header' => $templateData->renderWith('BulkManagerButtons')
		);
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getURLHandlers($gridField) {
			return array(
				'bulkaction' => 'handlebulkaction'
			);
	}
	
	/**
	 * Pass control over to the RequestHandler
	 * loop through the handlers provided in config['actions']
	 * and find matching url_handlers.
	 *
	 * $url_handlers rule should not use wildcards like '$Action' => '$Action'
	 * but have more specific path defined
	 * 
	 * @param GridField $gridField
	 * @param SS_HTTPRequest $request
	 * @return mixed 
	 */
	public function handlebulkaction($gridField, $request)
	{
		$controller = $gridField->getForm()->Controller();

		foreach ($this->config['actions'] as $name => $data)
		{
			$handlerClass = $data['handler'];
			$urlHandlers = Config::inst()->get($handlerClass, 'url_handlers', Config::UNINHERITED);

			if($urlHandlers) foreach($urlHandlers as $rule => $action)
			{
				if($request->match($rule, false))
				{
					//print_r('matched ' . $handlerClass . ' to ' . $rule);
					$handler = Injector::inst()->create($handlerClass, $gridField, $this, $controller);
					return $handler->handleRequest($request, DataModel::inst());
				}
			}
		}

		user_error("Unable to find matching bulk action handler for ".$request->remaining().'.', E_USER_ERROR);
	}
}
