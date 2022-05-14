<?
namespace Bitrix\Rest;
use Bitrix\Main;
use Bitrix\Rest\Api\UserFieldType;
use Bitrix\Rest\UserField\Callback;

abstract class UserFieldProxy
{
	protected $entityID = '';
	/** @var \CUser @ */
	protected $user = null;
	protected $isAdminUser = null;
	protected $isAuthorizedUser = null;
	protected $namePrefix = '';
	private static $langs = null;
	private static $fieldsInfo = null;
	private static $langIncluded = false;

	function __construct($entityID, \CUser $user = null)
	{
		if(!is_string($entityID))
		{
			throw new Main\ArgumentTypeException('entityID', 'string');
		}

		$this->entityID = $entityID;
		$this->user = $user !== null ? $user : $this->getCurrentUser();
	}
	public function getEntityID()
	{
		return $this->entityID;
	}

	public function getNamePrefix()
	{
		return $this->namePrefix;
	}
	public function setNamePrefix($prefix)
	{
		if(!is_string($prefix))
		{
			throw new Main\ArgumentTypeException('prefix', 'string');
		}

		$this->namePrefix = $prefix !== ''? mb_strtoupper($prefix) : '';
	}
	public static function getFields()
	{
		if(self::$fieldsInfo === null)
		{
			//isReadOnly - Can not be defined by user. Read only access.
			//isImmutable - Can be defined by user during creation. It can not be changed.
			//isMultiple - It is multiple (array will be returned).

			self::includeLangFile();
			self::$fieldsInfo = array(
				'ID' => array('type' => 'int', 'title' => GetMessage('REST_UF_ID'), 'isReadOnly'=> true),
				'ENTITY_ID' => array('type' => 'string', 'title' => GetMessage('REST_UF_ENTITY_ID'), 'isImmutable' => true),
				'FIELD_NAME' => array('type' => 'string', 'title' => GetMessage('REST_UF_FIELD_NAME'), 'isImmutable' => true),
				'USER_TYPE_ID' => array('type' => 'string', 'title' => GetMessage('REST_UF_USER_TYPE_ID'), 'isImmutable' => true),
				'XML_ID' => array('type' => 'string', 'title' => GetMessage('REST_UF_XML_ID')),
				'SORT' => array('type' => 'int', 'title' => GetMessage('REST_UF_SORT')),
				'MULTIPLE' => array('type' => 'char', 'title' => GetMessage('REST_UF_MULTIPLE')),
				'MANDATORY' => array('type' => 'char', 'title' => GetMessage('REST_UF_MANDATORY')),
				'SHOW_FILTER' => array('type' => 'char', 'title' => GetMessage('REST_UF_SHOW_FILTER')),
				'SHOW_IN_LIST' => array('type' => 'char', 'title' => GetMessage('REST_UF_SHOW_IN_LIST')),
				'EDIT_IN_LIST' => array('type' => 'char', 'title' => GetMessage('REST_UF_EDIT_IN_LIST')),
				'IS_SEARCHABLE' => array('type' => 'char', 'title' => GetMessage('REST_UF_IS_SEARCHABLE')),
				'EDIT_FORM_LABEL' => array('type' => 'string', 'title' => GetMessage('REST_UF_EDIT_FORM_LABEL')),
				'LIST_COLUMN_LABEL' => array('type' => 'string', 'title' => GetMessage('REST_UF_LIST_COLUMN_LABEL')),
				'LIST_FILTER_LABEL' => array('type' => 'string', 'title' => GetMessage('REST_UF_LIST_FILTER_LABEL')),
				'ERROR_MESSAGE' => array('type' => 'string', 'title' => GetMessage('REST_UF_ERROR_MESSAGE')),
				'HELP_MESSAGE' => array('type' => 'string', 'title' => GetMessage('REST_UF_HELP_MESSAGE')),
				'LIST' => array('type' => 'uf_enum_element', 'title' => GetMessage('REST_UF_LIST'), 'isMultiple'=> true),
				'SETTINGS' => array('type' => 'object', 'title' => GetMessage('REST_UF_SETTINGS'))
			);
		}

		return self::$fieldsInfo;
	}
	public static function getEnumerationElementFields()
	{
		self::includeLangFile();
		return array(
			'ID' => array('type' => 'int', 'title' => GetMessage('REST_UF_ID'), 'isReadOnly'=> true),
			'SORT' => array('type' => 'int', 'title' => GetMessage('REST_UF_SORT')),
			'VALUE' => array('type' => 'string', 'title' => GetMessage('REST_UF_VALUE')),
			'DEF' => array('type' => 'string', 'title' => GetMessage('REST_UF_IS_DEF')),
			'DEL' => array('type' => 'string', 'title' => GetMessage('REST_UF_DEL')),
		);
	}
	public static function getSettingsFields($typeID)
	{
		if(!is_string($typeID))
		{
			throw new Main\ArgumentTypeException('typeID', 'string');
		}

		if($typeID === '')
		{
			throw new Main\ArgumentException('Empty string is specified', 'typeID');
		}


		self::includeLangFile();
		switch($typeID)
		{
			case 'string':
			{
				return array(
					'DEFAULT_VALUE' => array('type' => 'string', 'title' => GetMessage('REST_UF_DEFAULT_VALUE')),
					'ROWS' => array('type' => 'int', 'title' => GetMessage('REST_UF_ROWS'))
				);
			}
			case 'integer':
			{
				return array(
					'DEFAULT_VALUE' => array('type' => 'int', 'title' => GetMessage('REST_UF_DEFAULT_VALUE'))
				);
			}
			case 'double':
			{
				return array(
					'DEFAULT_VALUE' => array('type' => 'double', 'title' => GetMessage('REST_UF_DEFAULT_VALUE')),
					'PRECISION' => array('type' => 'int', 'title' => GetMessage('REST_UF_PRECISION'))
				);
			}
			case 'boolean':
			{
				return array(
					'DEFAULT_VALUE' => array('type' => 'int', 'title' => GetMessage('REST_UF_DEFAULT_VALUE')),
					'DISPLAY' => array('type' => 'string', 'title' => GetMessage('REST_UF_DISPLAY'))
				);
			}
			case 'datetime':
			{
				return array(
					'DEFAULT_VALUE' => array('type' => 'datetime', 'title' => GetMessage('REST_UF_DEFAULT_VALUE'))
				);
			}
			case 'enumeration':
			{
				return array(
					'DISPLAY' => array('type' => 'string', 'title' => GetMessage('REST_UF_DISPLAY')),
					'LIST_HEIGHT' => array('type' => 'int', 'title' => GetMessage('REST_UF_LIST_HEIGHT')),
				);
			}
			case 'iblock_section':
			case 'iblock_element':
			{
				return array(
					'DEFAULT_VALUE' => array('type' => 'int', 'title' => GetMessage('REST_UF_DEFAULT_VALUE')),
					'IBLOCK_ID' => array('type' => 'int', 'title' => GetMessage('REST_UF_IBLOCK_ID')),
					'IBLOCK_TYPE_ID' => array('type' => 'string', 'title' => GetMessage('REST_UF_IBLOCK_TYPE_ID')),
					'DISPLAY' => array('type' => 'string', 'title' => GetMessage('REST_UF_DISPLAY')),
					'LIST_HEIGHT' => array('type' => 'int', 'title' => GetMessage('REST_UF_LIST_HEIGHT')),
					'ACTIVE_FILTER' => array('type' => 'char', 'title' => GetMessage('REST_UF_ACTIVE_FILTER'))
				);
			}
			case 'crm_status':
			{
				return array(
					'ENTITY_TYPE' => array('type' => 'string', 'title' => GetMessage('REST_UF_ENTITY_TYPE'))
				);
			}
			case 'crm':
			{
				return array(
					'LEAD' => array('type' => 'char', 'title' => GetMessage('REST_UF_CRM_LEAD')),
					'CONTACT' => array('type' => 'char', 'title' => GetMessage('REST_UF_CRM_CONTACT')),
					'COMPANY' => array('type' => 'char', 'title' => GetMessage('REST_UF_CRM_COMPANY')),
					'DEAL' => array('type' => 'char', 'title' => GetMessage('REST_UF_CRM_DEAL'))
				);
			}
			default:
			{
				return array();
			}
		}
	}
	public static function getTypes(\CRestServer $server = null)
	{
		self::includeLangFile();
		$result = array(
			array('ID' => 'string', 'title' => GetMessage('REST_UF_TYPE_STRING')),
			array('ID' => 'integer', 'title' => GetMessage('REST_UF_TYPE_INTEGER')),
			array('ID' => 'double', 'title' => GetMessage('REST_UF_TYPE_DOUBLE')),
			array('ID' => 'boolean', 'title' => GetMessage('REST_UF_TYPE_BOOLEAN')),
			array('ID' => 'enumeration', 'title' => GetMessage('REST_UF_TYPE_ENUMERATION')),
			array('ID' => 'datetime', 'title' => GetMessage('REST_UF_TYPE_DATETIME')),
			array('ID' => 'date', 'title' => GetMessage('REST_UF_TYPE_DATE')),
			array('ID' => 'money', 'title' => GetMessage('REST_UF_TYPE_MONEY')),
			array('ID' => 'url', 'title' => GetMessage('REST_UF_TYPE_URL')),
			array('ID' => 'address', 'title' => GetMessage('REST_UF_TYPE_ADDRESS')),
			array('ID' => 'file', 'title' => GetMessage('REST_UF_TYPE_FILE')),
			array('ID' => 'employee', 'title' => GetMessage('REST_UF_TYPE_EMPLOYEE')),
			array('ID' => 'crm_status', 'title' => GetMessage('REST_UF_TYPE_CRM_STATUS')),
			array('ID' => 'iblock_section', 'title' => GetMessage('REST_UF_TYPE_IBLOCK_SECTION')),
			array('ID' => 'iblock_element', 'title' => GetMessage('REST_UF_TYPE_IBLOCK_ELEMENT')),
			array('ID' => 'crm', 'title' => GetMessage('REST_UF_TYPE_CRM'))
		);

		if($server !== null && $server->getAuthType() === OAuth\Auth::AUTH_TYPE)
		{
			$clientInfo = AppTable::getByClientId($server->getClientId());
			$placementHandlerList = PlacementTable::getHandlersList(UserFieldType::PLACEMENT_UF_TYPE);

			foreach($placementHandlerList as $handler)
			{
				if($handler['APP_ID'] === $clientInfo['ID'])
				{
					$result[] = array(
						'ID' => $handler['ADDITIONAL'],
						'title' => $handler['TITLE']
					);
				}
			}
		}

		return $result;
	}
	public function add(array $fields)
	{
		global $APPLICATION;
		if(!$this->checkCreatePermission())
		{
			throw new RestException('Access denied.');
		}

		if($this->entityID === '')
		{
			throw new RestException('Operation is not allowed. Entity ID is not defined.');
		}

		//Try get default field label
		$defaultLabel = isset($fields['LABEL']) ? trim($fields['LABEL']) : '';

		self::sanitizeFields($fields);
		$fields['ENTITY_ID'] = $this->entityID;
		$errors = array();

		$userTypeID = isset($fields['USER_TYPE_ID']) ? trim($fields['USER_TYPE_ID']) : '';
		if($userTypeID === '')
		{
			$errors[] = "The 'USER_TYPE_ID' field is not found.";
		}
		$fields['USER_TYPE_ID'] = $userTypeID;

		$fieldName = isset($fields['FIELD_NAME']) ? trim($fields['FIELD_NAME']) : '';
		if($fieldName === '')
		{
			$errors[] = "The 'FIELD_NAME' field is not found.";
		}

		$fieldName = mb_strtoupper($fieldName);
		$prefix = $this->namePrefix;
		if($prefix !== '')
		{
			$fullPrefix = 'UF_'.$prefix.'_';
			$fullPrefixLen = mb_strlen($fullPrefix);
			if(strncmp($fieldName, $fullPrefix, $fullPrefixLen) !== 0)
			{
				$fieldName = strncmp($fieldName, 'UF_', 3) === 0
					? $fullPrefix.mb_substr($fieldName, 3)
					: $fullPrefix.$fieldName;
			}
		}
		else
		{
			$fullPrefix = 'UF_';
			$fullPrefixLen = 3;
			if(strncmp($fieldName, $fullPrefix, $fullPrefixLen) !== 0)
			{
				$fieldName = 'UF_'. $fieldName;
			}
		}

		$fields['FIELD_NAME'] = $fieldName;

		if(!empty($errors))
		{
			throw new RestException(implode("\n", $errors));
		}

		if($defaultLabel === '')
		{
			$defaultLabel = $fieldName;
		}

		self::prepareLabels($fields, 'LIST_FILTER_LABEL', $defaultLabel);
		self::prepareLabels($fields, 'LIST_COLUMN_LABEL', $defaultLabel);
		self::prepareLabels($fields, 'EDIT_FORM_LABEL', $defaultLabel);
		self::prepareLabels($fields, 'ERROR_MESSAGE', $defaultLabel);
		self::prepareLabels($fields, 'HELP_MESSAGE', $defaultLabel);

		$fields['MULTIPLE'] = isset($fields['MULTIPLE']) && mb_strtoupper($fields['MULTIPLE']) === 'Y' ? 'Y' : 'N';
		$fields['MANDATORY'] = isset($fields['MANDATORY']) && mb_strtoupper($fields['MANDATORY']) === 'Y' ? 'Y' : 'N';
		$fields['SHOW_FILTER'] = isset($fields['SHOW_FILTER']) && mb_strtoupper($fields['SHOW_FILTER']) === 'Y' ? 'E' : 'N'; // E - 'By mask' is default

		$isMultiple = isset($fields['MULTIPLE']) && $fields['MULTIPLE'] === 'Y';

		$settings = isset($fields['SETTINGS']) && is_array($fields['SETTINGS']) ? $fields['SETTINGS'] : array();
		$effectiveSettings = array();
		switch ($userTypeID)
		{
			case 'string':
			{
				$effectiveSettings['DEFAULT_VALUE'] = isset($settings['DEFAULT_VALUE'])
					? $settings['DEFAULT_VALUE'] : '';

				$effectiveSettings['ROWS'] = $settings['ROWS'] > 0
					? $settings['ROWS'] : 1;
				break;
			}
			case 'integer':
			{
				$effectiveSettings['DEFAULT_VALUE'] = isset($settings['DEFAULT_VALUE'])
					? $settings['DEFAULT_VALUE'] : '';
				break;
			}
			case 'double':
			{
				$effectiveSettings['DEFAULT_VALUE'] = isset($settings['DEFAULT_VALUE'])
					? $settings['DEFAULT_VALUE'] : '';

				$effectiveSettings['PRECISION'] = $settings['PRECISION'] >= 0
					? $settings['PRECISION'] : 2;
				break;
			}
			case 'boolean':
			{
				$effectiveSettings['DEFAULT_VALUE'] = isset($settings['DEFAULT_VALUE'])
					&& $settings['DEFAULT_VALUE'] > 0 ? 1 : 0;

				$display = isset($settings['DISPLAY']) ? $settings['DISPLAY'] : '';
				$effectiveSettings['DISPLAY'] = $display !== ''? mb_strtoupper($display) : 'CHECKBOX';

				$fields['MULTIPLE'] = 'N';
				break;
			}
			case 'date':
			case 'datetime':
			{
				$defaultValue = isset($settings['DEFAULT_VALUE']) ? $settings['DEFAULT_VALUE'] : array();
				if(!is_array($defaultValue))
				{
					$defaultValue = array('VALUE' => $defaultValue, 'TYPE' => 'NONE');
				}

				$effectiveSettings['DEFAULT_VALUE'] = array(
					'VALUE' => isset($defaultValue['VALUE'])
						? \CRestUtil::unConvertDateTime($defaultValue['VALUE']) : '',
					'TYPE' => isset($defaultValue['TYPE']) && $defaultValue['TYPE'] !== ''
						? mb_strtoupper($defaultValue['TYPE']) : 'NONE'
				);
				break;
			}
			case 'enumeration':
			{
				$display = isset($settings['DISPLAY']) ? $settings['DISPLAY'] : '';
				$effectiveSettings['DISPLAY'] = $display !== ''? mb_strtoupper($display) : 'LIST';

				$height = isset($settings['LIST_HEIGHT']) ? (int)$settings['LIST_HEIGHT'] : 0;
				$effectiveSettings['LIST_HEIGHT'] = $height > 0 ? $height : 1;

				$listItems = isset($fields['LIST']) && is_array($fields['LIST']) ? $fields['LIST'] : array();
				$effectiveListItems = array();

				$counter = 0;
				$defaultItemKey = '';
				foreach($listItems as $item)
				{
					$itemValue = isset($item['VALUE']) ? trim($item['VALUE'], " \t\n\r") : '';
					if($itemValue === '')
					{
						continue;
					}

					$effectiveItem = array('VALUE' => $itemValue);
					$itemSort = isset($item['SORT']) && is_numeric($item['SORT']) ? (int)$item['SORT'] : 0;
					if($itemSort > 0)
					{
						$effectiveItem['SORT'] = $itemSort;
					}

					if($itemSort > 0)
					{
						$effectiveItem['SORT'] = $itemSort;
					}

					$itemKey = "n{$counter}";
					$counter++;

					if(isset($item['DEF']))
					{
						$isDefault = mb_strtoupper($item['DEF']) === 'Y';
						if($isMultiple)
						{
							$effectiveItem['DEF'] = $isDefault ? 'Y' : 'N';
						}
						elseif($isDefault && $defaultItemKey === '')
						{
							$defaultItemKey = $itemKey;
						}
					}

					$effectiveListItems[$itemKey] = &$effectiveItem;
					unset($effectiveItem);
				}

				if(!$isMultiple && $defaultItemKey !== '')
				{
					foreach($effectiveListItems as $key => &$item)
					{
						$item['DEF'] = $key === $defaultItemKey ? 'Y' : 'N';
					}
					unset($item);
				}
				$fields['LIST'] = $effectiveListItems;
				break;
			}
			case 'iblock_section':
			case 'iblock_element':
			{
				$effectiveSettings['IBLOCK_TYPE_ID'] = isset($settings['IBLOCK_TYPE_ID']) ? $settings['IBLOCK_TYPE_ID'] : '';
				$effectiveSettings['IBLOCK_ID'] = isset($settings['IBLOCK_ID']) ? (int)$settings['IBLOCK_ID'] : 0;
				$effectiveSettings['DEFAULT_VALUE'] = isset($settings['DEFAULT_VALUE']) ? $settings['DEFAULT_VALUE'] : '';

				$display = isset($settings['DISPLAY']) ? $settings['DISPLAY'] : '';
				$effectiveSettings['DISPLAY'] = $display !== ''? mb_strtoupper($display) : 'LIST';

				$height = isset($settings['LIST_HEIGHT']) ? (int)$settings['LIST_HEIGHT'] : 0;
				$effectiveSettings['LIST_HEIGHT'] = $height > 0 ? $height : 1;
				$effectiveSettings['ACTIVE_FILTER'] = isset($settings['ACTIVE_FILTER'])
					&& mb_strtoupper($settings['ACTIVE_FILTER']) === 'Y' ? 'Y' : 'N';
				break;
			}
			case 'crm_status':
			{
				$effectiveSettings['ENTITY_TYPE'] = isset($settings['ENTITY_TYPE']) ? $settings['ENTITY_TYPE'] : '';
				break;
			}
			case 'crm':
			{
				$effectiveSettings['LEAD'] = isset($settings['LEAD']) && mb_strtoupper($settings['LEAD']) === 'Y' ? 'Y' : 'N';
				$effectiveSettings['CONTACT'] = isset($settings['CONTACT']) && mb_strtoupper($settings['CONTACT']) === 'Y' ? 'Y' : 'N';
				$effectiveSettings['COMPANY'] = isset($settings['COMPANY']) && mb_strtoupper($settings['COMPANY']) === 'Y' ? 'Y' : 'N';
				$effectiveSettings['DEAL'] = isset($settings['DEAL']) && mb_strtoupper($settings['DEAL']) === 'Y' ? 'Y' : 'N';
				break;
			}
			case 'employee':
			{
				if($fields['SHOW_FILTER'] !== 'N')
				{
					$fields['SHOW_FILTER'] = 'I'; // Force exact match for 'USER' field type
				}
				break;
			}
			default:
			{
				$userTypeList = PlacementTable::getHandlersList(UserFieldType::PLACEMENT_UF_TYPE);

				foreach($userTypeList as $userType)
				{
					if($userType['ADDITIONAL'] === $userTypeID)
					{
						$fields['USER_TYPE_ID'] = UserField\Callback::getUserTypeId($userType);
					}
				}

				$fields['SHOW_FILTER'] = 'N';
			}
		}

		$fields['SETTINGS'] = $effectiveSettings;

		$entity = new \CUserTypeEntity();
		$ID = $entity->Add($fields);
		if($ID <= 0)
		{
			$exc = $APPLICATION->GetException();
			$errors[] = $exc !== false ? $exc->GetString() : 'Fail to create new user field.';
		}
		elseif ($userTypeID === 'enumeration' && isset($fields['LIST']) && is_array($fields['LIST']))
		{
			$enum = new \CUserFieldEnum();
			if(!$enum->SetEnumValues($ID, $fields['LIST']))
			{
				$exc = $APPLICATION->GetException();
				$errors[] = $exc !== false ? $exc->GetString() : 'Fail to save enumumeration field values.';
			}
		}

		if(!empty($errors))
		{
			throw new RestException(implode("\n", $errors), RestException::ERROR_CORE);
		}

		return $ID;
	}
	public function get($ID)
	{
		if(!is_int($ID))
		{
			$ID = (int)$ID;
		}

		if($ID <= 0)
		{
			throw new RestException('ID is not defined or invalid.');
		}

		if(!$this->checkReadPermission())
		{
			throw new RestException('Access denied.');
		}

		if($this->entityID === '')
		{
			throw new RestException('Operation is not allowed. Entity ID is not defined.');
		}

		$entity = new \CUserTypeEntity();
		$result = $entity->GetByID($ID);
		if(!is_array($result))
		{
			throw new RestException("The entity with ID '{$ID}' is not found.", RestException::ERROR_NOT_FOUND);
		}

		$entityID = isset($result['ENTITY_ID']) ? $result['ENTITY_ID'] : '';
		if($entityID !== $this->entityID)
		{
			throw new RestException('Access denied.');
		}

		if($result['USER_TYPE_ID'] === 'enumeration')
		{
			$result['LIST'] = array();

			$enumEntity = new \CUserFieldEnum();
			$dbResultEnum = $enumEntity->GetList(array('SORT' => 'ASC'), array('USER_FIELD_ID' => $ID));
			while($enum = $dbResultEnum->Fetch())
			{
				$result['LIST'][] = array(
					'ID' => $enum['ID'],
					'SORT' => $enum['SORT'],
					'VALUE' => $enum['VALUE'],
					'DEF' => $enum['DEF']
				);
			}
		}
		elseif(preg_match("/^".UserField\Callback::USER_TYPE_ID_PREFIX."_([\d]+)_/", $result['USER_TYPE_ID'], $matches))
		{
			$result['USER_TYPE_ID'] = str_replace($matches[0], '', $result['USER_TYPE_ID']);

			$appInfo = AppTable::getByClientId($matches[1]);
			$result['USER_TYPE_OWNER'] = $appInfo['CLIENT_ID'];
		}

		return $result;
	}
	public function getList(array $order, array $filter)
	{
		if(!$this->checkReadPermission())
		{
			throw new RestException('Access denied.');
		}

		if($this->entityID === '')
		{
			throw new RestException('Operation is not allowed. Entity ID is not defined.');
		}
		$filter['ENTITY_ID'] = $this->entityID;

		if(isset($filter['USER_TYPE_ID']))
		{
			$handlerList = PlacementTable::getHandlersList(UserFieldType::PLACEMENT_UF_TYPE);
			foreach($handlerList as $handler)
			{
				if($handler['ADDITIONAL'] === $filter['USER_TYPE_ID'])
				{
					$filter['USER_TYPE_ID'] = Callback::getUserTypeId($handler);
				}
			}
		}

		$entity = new \CUserTypeEntity();
		$dbResult = $entity->GetList($order, $filter);
		$result = array();
		while($fields = $dbResult->Fetch())
		{
			$userTypeID = isset($fields['USER_TYPE_ID']) ? $fields['USER_TYPE_ID'] : '';
			if($userTypeID === 'datetime'
				&& isset($fields['SETTINGS'])
				&& isset($fields['SETTINGS']['DEFAULT_VALUE'])
				&& isset($fields['SETTINGS']['DEFAULT_VALUE']['VALUE'])
				&& $fields['SETTINGS']['DEFAULT_VALUE']['VALUE'] !== '')
			{
				$fields['SETTINGS']['DEFAULT_VALUE']['VALUE'] = \CRestUtil::ConvertDateTime($fields['SETTINGS']['DEFAULT_VALUE']['VALUE']);
			}

			if($userTypeID === 'enumeration')
			{
				$fields['LIST'] = array();

				$enumEntity = new \CUserFieldEnum();
				$dbResultEnum = $enumEntity->GetList(array('SORT' => 'ASC'), array('USER_FIELD_ID' => $fields['ID']));
				while($enum = $dbResultEnum->Fetch())
				{
					$fields['LIST'][] = array(
						'ID' => $enum['ID'],
						'SORT' => $enum['SORT'],
						'VALUE' => $enum['VALUE'],
						'DEF' => $enum['DEF']
					);
				}
			}
			elseif(preg_match("/^".UserField\Callback::USER_TYPE_ID_PREFIX."_([\d]+)_/", $userTypeID, $matches))
			{
				$fields['USER_TYPE_ID'] = str_replace($matches[0], '', $fields['USER_TYPE_ID']);

				$appInfo = AppTable::getByClientId($matches[1]);
				$fields['USER_TYPE_OWNER'] = $appInfo['CLIENT_ID'];
			}

			$result[] = $fields;
		}

		$result['total'] = count($result);
		return $result;
	}
	public function update($ID, array $fields)
	{
		global $APPLICATION;

		if(!is_int($ID))
		{
			$ID = (int)$ID;
		}

		if($ID <= 0)
		{
			throw new RestException('ID is not defined or invalid.');
		}

		if(!$this->checkUpdatePermission())
		{
			throw new RestException('Access denied.');
		}

		if($this->entityID === '')
		{
			throw new RestException('Operation is not allowed. Entity ID is not defined.');
		}

		$entity = new \CUserTypeEntity();

		$persistedFields = $entity->GetByID($ID);
		if(!is_array($persistedFields))
		{
			throw new RestException("The entity with ID '{$ID}' is not found.", RestException::ERROR_NOT_FOUND);
		}

		$entityID = isset($persistedFields['ENTITY_ID']) ? $persistedFields['ENTITY_ID'] : '';
		if($entityID !== $this->entityID)
		{
			throw new RestException('Access denied.');
		}

		//User type ID can't be changed.
		$userTypeID = isset($persistedFields['USER_TYPE_ID']) ? $persistedFields['USER_TYPE_ID'] : '';
		if($userTypeID === '')
		{
			throw new RestException("Could not find 'USER_TYPE_ID' in persisted entity with ID '{$ID}'.");
		}

		$isMultiple = isset($persistedFields['MULTIPLE']) && $persistedFields['MULTIPLE'] === 'Y';

		self::sanitizeFields($fields);

		if(isset($fields['LIST_FILTER_LABEL']))
		{
			self::prepareLabels($fields, 'LIST_FILTER_LABEL', '');
		}
		elseif(isset($persistedFields['LIST_FILTER_LABEL']))
		{
			$fields['LIST_FILTER_LABEL'] = $persistedFields['LIST_FILTER_LABEL'];
		}

		if(isset($fields['LIST_COLUMN_LABEL']))
		{
			self::prepareLabels($fields, 'LIST_COLUMN_LABEL', '');
		}
		elseif(isset($persistedFields['LIST_COLUMN_LABEL']))
		{
			$fields['LIST_COLUMN_LABEL'] = $persistedFields['LIST_COLUMN_LABEL'];
		}

		if(isset($fields['EDIT_FORM_LABEL']))
		{
			self::prepareLabels($fields, 'EDIT_FORM_LABEL', '');
		}
		elseif(isset($persistedFields['EDIT_FORM_LABEL']))
		{
			$fields['EDIT_FORM_LABEL'] = $persistedFields['EDIT_FORM_LABEL'];
		}

		if(isset($fields['ERROR_MESSAGE']))
		{
			self::prepareLabels($fields, 'ERROR_MESSAGE', '');
		}
		elseif(isset($persistedFields['ERROR_MESSAGE']))
		{
			$fields['ERROR_MESSAGE'] = $persistedFields['ERROR_MESSAGE'];
		}

		if(isset($fields['HELP_MESSAGE']))
		{
			self::prepareLabels($fields, 'HELP_MESSAGE', '');
		}
		elseif(isset($persistedFields['HELP_MESSAGE']))
		{
			$fields['HELP_MESSAGE'] = $persistedFields['HELP_MESSAGE'];
		}

		$settings = isset($fields['SETTINGS']) && is_array($fields['SETTINGS']) ? $fields['SETTINGS'] : array();
		$effectiveSettings = isset($persistedFields['SETTINGS']) && is_array($persistedFields['SETTINGS'])
			? $persistedFields['SETTINGS'] : array();

		if(isset($fields['SHOW_FILTER']))
		{
			$fields['SHOW_FILTER'] = mb_strtoupper($fields['SHOW_FILTER']) === 'Y' ? 'E' : 'N'; // E - 'By mask' is default
		}

		switch ($userTypeID)
		{
			case 'string':
			{
				if(isset($settings['DEFAULT_VALUE']))
				{
					$effectiveSettings['DEFAULT_VALUE'] = $settings['DEFAULT_VALUE'];
				}

				if(isset($settings['ROWS']))
				{
					$effectiveSettings['ROWS'] = min(max($settings['ROWS'], 1), 50);
				}
				break;
			}
			case 'integer':
			{
				if(isset($settings['DEFAULT_VALUE']))
				{
					$effectiveSettings['DEFAULT_VALUE'] = $settings['DEFAULT_VALUE'];
				}
				break;
			}
			case 'double':
			{
				if(isset($settings['DEFAULT_VALUE']))
				{
					$effectiveSettings['DEFAULT_VALUE'] = $settings['DEFAULT_VALUE'];
				}

				if(isset($settings['PRECISION']))
				{
					$effectiveSettings['PRECISION'] = $settings['PRECISION'] >= 0
						? $settings['PRECISION'] : 2;
				}
				break;
			}
			case 'boolean':
			{
				if(isset($settings['DEFAULT_VALUE']))
				{
					$effectiveSettings['DEFAULT_VALUE'] = $settings['DEFAULT_VALUE'] > 0 ? 1 : 0;
				}

				if(isset($settings['DISPLAY']))
				{
					$effectiveSettings['DISPLAY'] = $settings['DISPLAY'] !== ''
						? mb_strtoupper($settings['DISPLAY']) : 'CHECKBOX';
				}

				unset($fields['MULTIPLE']);
				break;
			}
			case 'datetime':
			{
				if(isset($settings['DEFAULT_VALUE']))
				{
					$defaultValue = $settings['DEFAULT_VALUE'];
					if(!is_array($defaultValue))
					{
						$defaultValue = array('VALUE' => $defaultValue, 'TYPE' => 'NONE');
					}

					$effectiveSettings['DEFAULT_VALUE'] = array(
						'VALUE' => isset($defaultValue['VALUE'])
							? \CRestUtil::unConvertDateTime($defaultValue['VALUE']) : '',
						'TYPE' => isset($defaultValue['TYPE']) && $defaultValue['TYPE'] !== ''
							? mb_strtoupper($defaultValue['TYPE']) : 'NONE'
					);
				}
				break;
			}
			case 'enumeration':
			{
				if(isset($settings['DISPLAY']))
				{
					$effectiveSettings['DISPLAY'] = $settings['DISPLAY'] !== ''
						? mb_strtoupper($settings['DISPLAY']) : 'LIST';
				}

				if(isset($settings['LIST_HEIGHT']))
				{
					$effectiveSettings['LIST_HEIGHT'] = $settings['LIST_HEIGHT'] > 0 ? $settings['LIST_HEIGHT'] : 1;
				}

				if(isset($fields['LIST']))
				{
					$listItems = is_array($fields['LIST']) ? $fields['LIST'] : array();
					$effectiveListItems = array();

					$counter = 0;
					$defaultItemKey = '';
					foreach($listItems as $item)
					{
						$itemValue = isset($item['VALUE']) ? trim($item['VALUE'], " \t\n\r") : '';

						$effectiveItem = array('VALUE' => $itemValue);
						$itemXmlID = isset($item['XML_ID']) ? $item['XML_ID'] : '';
						if($itemXmlID !== '')
						{
							$effectiveItem['XML_ID'] = $itemXmlID;
						}
						$itemSort = isset($item['SORT']) && is_numeric($item['SORT']) ? (int)$item['SORT'] : 0;
						if($itemSort > 0)
						{
							$effectiveItem['SORT'] = $itemSort;
						}

						$itemID = isset($item['ID']) && is_numeric($item['ID']) ? (int)$item['ID'] : 0;
						if($itemID > 0)
						{
							$itemKey = strval($itemID);
							if(isset($item['DEL']) && mb_strtoupper($item['DEL']) === 'Y')
							{
								$effectiveItem['DEL'] = 'Y';
							}
						}
						else
						{
							$itemKey = "n{$counter}";
							$counter++;
						}

						if(isset($item['DEF']))
						{
							$isDefault = mb_strtoupper($item['DEF']) === 'Y';
							if($isMultiple)
							{
								$effectiveItem['DEF'] = $isDefault ? 'Y' : 'N';
							}
							elseif($isDefault && $defaultItemKey === '')
							{
								$defaultItemKey = $itemKey;
							}
						}

						if(!empty($item))
						{
							$effectiveListItems[$itemKey] = &$effectiveItem;
						}
						unset($effectiveItem);
					}

					if(!$isMultiple && $defaultItemKey !== '')
					{
						foreach($effectiveListItems as $key => &$item)
						{
							$item['DEF'] = $key === $defaultItemKey ? 'Y' : 'N';
						}
						unset($item);
					}
					$fields['LIST'] = $effectiveListItems;
				}

				break;
			}
			case 'iblock_section':
			case 'iblock_element':
			{
				if(isset($settings['IBLOCK_TYPE_ID']))
				{
					$effectiveSettings['IBLOCK_TYPE_ID'] = $settings['IBLOCK_TYPE_ID'];
				}

				if(isset($settings['IBLOCK_ID']))
				{
					$effectiveSettings['IBLOCK_ID'] = $settings['IBLOCK_ID'] > 0 ? $settings['IBLOCK_ID'] : 0;
				}

				if(isset($settings['DEFAULT_VALUE']))
				{
					$effectiveSettings['DEFAULT_VALUE'] = $settings['DEFAULT_VALUE'];
				}

				if(isset($settings['DISPLAY']))
				{
					$effectiveSettings['DISPLAY'] = $settings['DISPLAY'] !== ''
						? mb_strtoupper($settings['DISPLAY']) : 'LIST';
				}

				if(isset($settings['LIST_HEIGHT']))
				{
					$effectiveSettings['LIST_HEIGHT'] = $settings['LIST_HEIGHT'] > 0 ? $settings['LIST_HEIGHT'] : 1;
				}

				if(isset($settings['ACTIVE_FILTER']))
				{
					$effectiveSettings['ACTIVE_FILTER'] = mb_strtoupper($settings['ACTIVE_FILTER']) === 'Y' ? 'Y' : 'N';
				}

				break;
			}
			case 'crm_status':
			{
				if(isset($settings['ENTITY_TYPE']))
				{
					$effectiveSettings['ENTITY_TYPE'] = $settings['ENTITY_TYPE'];
				}
				break;
			}
			case 'crm':
			{
				if(isset($settings['LEAD']))
				{
					$effectiveSettings['LEAD'] = mb_strtoupper($settings['LEAD']) === 'Y' ? 'Y' : 'N';
				}

				if(isset($settings['CONTACT']))
				{
					$effectiveSettings['CONTACT'] = mb_strtoupper($settings['CONTACT']) === 'Y' ? 'Y' : 'N';
				}

				if(isset($settings['COMPANY']))
				{
					$effectiveSettings['COMPANY'] = mb_strtoupper($settings['COMPANY']) === 'Y' ? 'Y' : 'N';
				}

				if(isset($settings['DEAL']))
				{
					$effectiveSettings['DEAL'] = mb_strtoupper($settings['DEAL']) === 'Y' ? 'Y' : 'N';
				}
				break;
			}
			case 'employee':
			{
				if(isset($fields['SHOW_FILTER']) && $fields['SHOW_FILTER'] !== 'N')
				{
					$fields['SHOW_FILTER'] = 'I'; // Force exact match for 'USER' field type
				}
				break;
			}
		}

		$fields['SETTINGS'] = $effectiveSettings;

		if($entity->Update($ID, $fields) === false)
		{
			$exc = $APPLICATION->GetException();
			throw new RestException(
				$exc !== false ? $exc->GetString() : 'Fail to update user field.',
				RestException::ERROR_CORE
			);
		}
		elseif($userTypeID === 'enumeration' && isset($fields['LIST']) && is_array($fields['LIST']))
		{
			$enum = new \CUserFieldEnum();
			if(!$enum->SetEnumValues($ID, $fields['LIST']))
			{
				$exc = $APPLICATION->GetException();
				throw new RestException(
					$exc !== false ? $exc->GetString() : 'Fail to save enumumeration field values.',
					RestException::ERROR_CORE
				);
			}
		}
		return true;
	}
	public function delete($ID)
	{
		global $APPLICATION;

		if(!is_int($ID))
		{
			$ID = (int)$ID;
		}

		if($ID <= 0)
		{
			throw new RestException('ID is not defined or invalid.');
		}

		if(!$this->checkDeletePermission())
		{
			throw new RestException('Access denied.');
		}

		$entity = new \CUserTypeEntity();
		$dbResult = $entity->GetList(array(), array('ID' => $ID));
		$persistedFields = is_object($dbResult) ? $dbResult->Fetch() : null;
		if(!is_array($persistedFields))
		{
			throw new RestException("The entity with ID '{$ID}' is not found.", RestException::ERROR_NOT_FOUND);
		}

		$entityID = isset($persistedFields['ENTITY_ID']) ? $persistedFields['ENTITY_ID'] : '';
		if($entityID !== $this->entityID)
		{
			throw new RestException('Access denied.');
		}

		if($entity->Delete($ID) === false)
		{
			$exc = $APPLICATION->GetException();
			throw new RestException(
				$exc !== false ? $exc->GetString() : 'Fail to delete user field.',
				RestException::ERROR_CORE
			);
		}
		return true;
	}

	protected static function sanitizeFields(array &$fields)
	{
		$fieldsInfo = self::getFields();
		foreach($fields as $k => $v)
		{
			if(!isset($fieldsInfo[$k]))
			{
				unset($fields[$k]);
			}
		}
	}
	protected function isAuthorizedUser()
	{
		if($this->isAuthorizedUser === null)
		{
			$this->isAuthorizedUser = $this->user->IsAuthorized();
		}
		return $this->isAuthorizedUser;
	}
	protected function isAdminUser()
	{
		if($this->isAdminUser !== null)
		{
			return $this->isAdminUser;
		}

		$this->isAdminUser = $this->user->IsAdmin();

		if(!$this->isAdminUser
			&& \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')
			&& \Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			try
			{
				$this->isAdminUser = \CBitrix24::IsPortalAdmin($this->user->GetID());
			}
			catch(\Exception $exc)
			{
			}
		}

		return $this->isAdminUser;
	}
	protected function getCurrentUser()
	{
		return isset($USER) && ((get_class($USER) === 'CUser') || ($USER instanceof \CUser))
			? $USER : (new \CUser());
	}
	protected static function getAllLanguages()
	{
		if(self::$langs !== null)
		{
			return self::$langs;
		}

		self::$langs = array();
		$entity = new \CLanguage();
		$dbResult = $entity->GetList();
		while($lang = $dbResult->Fetch())
		{
			self::$langs[$lang['LID']] = array('LID' => $lang['LID'], 'NAME' => $lang['NAME']);
		}
		return self::$langs;
	}
	protected static function prepareLabels(array &$fields, $name, $defaultLabel)
	{
		$label = isset($fields[$name]) ? $fields[$name] : null;
		if(is_string($label) && $label !== '')
		{
			$labels = array();
			$default = $label;
		}
		else
		{
			$labels = is_array($label) ? $label : array();
			$default = $defaultLabel;
		}

		$langIDs = array_keys(self::getAllLanguages());
		$fields[$name] = array();
		foreach($langIDs as $lid)
		{
			$fields[$name][$lid] = isset($labels[$lid]) && is_string($labels[$lid]) && $labels[$lid] !== ''
				? $labels[$lid] : $default;
		}
	}
	protected function checkCreatePermission()
	{
		return $this->isAdminUser();
	}
	protected function checkReadPermission()
	{
		return $this->isAdminUser();
	}
	protected function checkUpdatePermission()
	{
		return $this->isAdminUser();
	}
	protected function checkDeletePermission()
	{
		return $this->isAdminUser();
	}

	private static function includeLangFile()
	{
		if(!self::$langIncluded)
		{
			self::$langIncluded = IncludeModuleLangFile(__FILE__);
		}
	}
}
