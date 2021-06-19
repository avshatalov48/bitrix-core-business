<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetUserInfoActivity
	extends CBPActivity
{
	private $userFields;

	protected static function getUserFields()
	{
		return [
			'USER_ACTIVE' => [
				'Name' => GetMessage('BPGUIA_USER_ACTIVE'),
				'Type' => 'bool',
			],
			'USER_EMAIL' => [
				'Name' => GetMessage('BPGUIA_USER_EMAIL'),
				'Type' => 'string',
			],
			'USER_WORK_PHONE' => [
				'Name' => GetMessage('BPGUIA_USER_WORK_PHONE'),
				'Type' => 'string',
			],
			'USER_PERSONAL_MOBILE' => [
				'Name' => GetMessage('BPGUIA_USER_PERSONAL_MOBILE'),
				'Type' => 'string',
			],
			'USER_UF_PHONE_INNER' => [
				'Name' => GetMessage('BPGUIA_USER_UF_PHONE_INNER'),
				'Type' => 'string',
			],
			'USER_LOGIN' => [
				'Name' => GetMessage('BPGUIA_USER_LOGIN'),
				'Type' => 'string',
			],
			'USER_LAST_NAME' => [
				'Name' => GetMessage('BPGUIA_USER_LAST_NAME'),
				'Type' => 'string',
			],
			'USER_NAME' => [
				'Name' => GetMessage('BPGUIA_USER_NAME'),
				'Type' => 'string',
			],
			'USER_SECOND_NAME' => [
				'Name' => GetMessage('BPGUIA_USER_SECOND_NAME'),
				'Type' => 'string',
			],
			'USER_WORK_POSITION' => [
				'Name' => GetMessage('BPGUIA_USER_WORK_POSITION'),
				'Type' => 'string',
			],
			'USER_PERSONAL_BIRTHDAY' => [
				'Name' => GetMessage('BPGUIA_USER_PERSONAL_BIRTHDAY'),
				'Type' => 'date',
			],
			'USER_PERSONAL_WWW' => [
				'Name' => GetMessage('BPGUIA_USER_PERSONAL_WWW'),
				'Type' => 'string',
			],
			'USER_PERSONAL_CITY' => [
				'Name' => GetMessage('BPGUIA_USER_PERSONAL_CITY'),
				'Type' => 'string',
			],
			'USER_UF_SKYPE' => [
				'Name' => GetMessage('BPGUIA_USER_UF_SKYPE'),
				'Type' => 'string',
			],
			'USER_UF_TWITTER' => [
				'Name' => GetMessage('BPGUIA_USER_UF_TWITTER'),
				'Type' => 'string',
			],
			'USER_UF_FACEBOOK' => [
				'Name' => GetMessage('BPGUIA_USER_UF_FACEBOOK'),
				'Type' => 'string',
			],
			'USER_UF_LINKEDIN' => [
				'Name' => GetMessage('BPGUIA_USER_UF_LINKEDIN'),
				'Type' => 'string',
			],
			'USER_UF_XING' => [
				'Name' => GetMessage('BPGUIA_USER_UF_XING'),
				'Type' => 'string',
			],
			'USER_UF_WEB_SITES' => [
				'Name' => GetMessage('BPGUIA_USER_UF_WEB_SITES'),
				'Type' => 'string',
			],
			'USER_UF_DEPARTMENT' => [
				'Name' => GetMessage('BPGUIA_USER_UF_DEPARTMENT'),
				'Type' => 'int',
				'Multiple' => true
			],
			'IS_ABSENT' => [
				'Name' => GetMessage('BPGUIA_IS_ABSENT'),
				'Type' => 'bool',
			],
			'TIMEMAN_STATUS' => [
				'Name' => GetMessage('BPGUIA_TIMEMAN_STATUS'),
				'Type' => 'string',
			],
		];
	}

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"GetUser" => null,
			'UserFields' => null
		];

		$this->userFields = array_merge(self::getUserFields(), self::getFieldsCreatedByUser());

		foreach (array_keys($this->userFields) as $uf)
		{
			$this->arProperties[$uf] = null;
		}

		$this->SetPropertiesTypes($this->userFields);
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		foreach (array_keys($this->userFields) as $uf)
		{
			$this->arProperties[$uf] = null;
		}
	}

	public function Execute()
	{
		$userId = CBPHelper::ExtractUsers($this->GetUser, $this->GetDocumentId(), true);

		if (!$userId)
		{
			$this->WriteToTrackingService(GetMessage('BPGUIA_ERROR_1'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$defaultUserFields = [
			'EMAIL',
			'UF_SKYPE',
			'UF_TWITTER',
			'UF_FACEBOOK',
			'UF_LINKEDIN',
			'UF_XING',
			'UF_WEB_SITES',
			'UF_PHONE_INNER',
			'UF_DEPARTMENT'
		];

		$dbUsers = CUser::GetList(
			'id', 'asc',
			array('ID' => $userId),
			array('SELECT' => array_merge($defaultUserFields, array_keys(self::getFieldsCreatedByUser())))
		);

		$user = $dbUsers ? $dbUsers->Fetch() : null;

		if (!$user)
		{
			$this->WriteToTrackingService(GetMessage('BPGUIA_ERROR_USER_NOT_FOUND', ['#ID#' => $userId]), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		foreach ($this->userFields as $uf => $fieldMap)
		{
			if (mb_strpos($uf, 'USER_') !== 0 && $this->isCreatedByUser($uf) === false)
			{
				continue;
			}

			$ufMap = str_replace('USER_', '', $uf);
			if($fieldMap['Type'] === 'bool')
			{
				$user[$ufMap] = CBPHelper::getBool($user[$ufMap]) ? 'Y' : 'N';
			}
			else if($fieldMap['Type'] === 'select')
			{
				$user[$ufMap] = $this->convertSelectValue($user[$ufMap], $fieldMap);
			}
			$this->__set($uf, $user[$ufMap]);
		}

		if (CModule::IncludeModule('intranet'))
		{
			$this->__set('IS_ABSENT', CIntranetUtils::IsUserAbsent($userId) ? 'Y' : 'N');
		}

		if (CModule::IncludeModule('timeman'))
		{
			$tmUser = new CTimeManUser($userId);
			$this->__set('TIMEMAN_STATUS', $tmUser->State());
		}

		return CBPActivityExecutionStatus::Closed;
	}

	protected function convertSelectValue($value, $fieldMap)
	{
		if(is_array($value))
		{
			$xmlIds = array();
			foreach ($value as $i => $val)
			{
				$xmlIds[$i] = $this->convertSelectValue($val, $fieldMap);
			}
			return $xmlIds;
		}

		foreach ($fieldMap['Settings']['ENUM'] as $enum)
		{
			if((int) $enum['ID'] === (int) $value)
			{
				return $enum['XML_ID'];
			}
		}
	}

	protected function isCreatedByUser($fieldName)
	{
		return array_key_exists($fieldName, self::getFieldsCreatedByUser());
	}

	protected static function getFieldsCreatedByUser()
	{
		static $fieldsCreatedByUser = null;
		if(isset($fieldsCreatedByUser))
		{
			return $fieldsCreatedByUser;
		}
		$fieldsCreatedByUser = [];

		$userFieldIds = \Bitrix\Main\UserFieldTable::getList(array(
			'select' => array('ID'),
			'filter' => [
				'ENTITY_ID' => 'USER',
				'%=FIELD_NAME' => 'UF_USR_%',
		   ]
	   ))->fetchAll();

		foreach ($userFieldIds as $fieldId)
		{
			$field = \Bitrix\Main\UserFieldTable::getFieldData($fieldId['ID']);
			$fieldName = $field['FIELD_NAME'];

			$name = in_array(LANGUAGE_ID, $field['LANGUAGE_ID']) ? $field['LIST_COLUMN_LABEL'][LANGUAGE_ID] : $field['FIELD_NAME'];

			$fieldsCreatedByUser[$fieldName] = array(
				'Name' => $name,
				'Type' => self::resolveUserFieldType($field['USER_TYPE_ID']),
				'Multiple' => $field['MULTIPLE'] === 'Y'
			);
			if($fieldsCreatedByUser[$fieldName]['Type'] === 'select')
			{
				$fieldsCreatedByUser[$fieldName]['Options'] = self::getOptionsFromFieldEnum($field);
				$fieldsCreatedByUser[$fieldName]['Settings'] = isset($field['ENUM']) ? ['ENUM' => $field['ENUM']] : array();
			}
		}

		return $fieldsCreatedByUser;
	}

	protected static function resolveUserFieldType(string $type): ?string
	{
		$bpType = null;
		switch ($type)
		{
			case 'string':
			case 'datetime':
			case 'date':
			case 'double':
			case 'file':
				$bpType = $type;
				break;
			case 'integer':
				$bpType = 'int';
				break;
			case 'boolean':
				$bpType = 'bool';
				break;
			case 'employee':
				$bpType = 'user';
				break;
			case 'enumeration':
				$bpType = 'select';
				break;
			case 'money':
			case 'url':
			case 'address':
			case 'resourcebooking':
			case 'crm_status':
			case 'iblock_section':
			case 'iblock_element':
			case 'crm':
				$bpType = "UF:{$type}";
				break;
		}
		return $bpType;
	}

	protected static function getOptionsFromFieldEnum($field)
	{
		$options = [];
		if(isset($field['ENUM']))
		{
			foreach ($field['ENUM'] as $enum)
			{
				$options[$enum['XML_ID']] = $enum['VALUE'];
			}
		}
		return $options;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues
		]);

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$dialog->setMap([
			'GetUser' => [
				'Name' => GetMessage('BPGUIA_TARGET_USER_NAME'),
				'FieldName' => 'get_user',
				'Type' => 'user',
				'Default' => $user->getBizprocId()
			],
		]);

		$dialog->setRuntimeData([
			'user' => $user
		]);

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];
		$properties = ['GetUser' => null];

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		if ($user->isAdmin())
		{
			$properties["GetUser"] = CBPHelper::UsersStringToArray(
				$arCurrentValues["get_user"], $documentType, $errors
			);
			if (count($errors) > 0)
			{
				return false;
			}
		}
		else
		{
			$properties["GetUser"] = $user->getBizprocId();
		}
		$properties['UserFields'] = array_merge(self::getUserFields(), self::getFieldsCreatedByUser());

		$errors = self::ValidateProperties($properties, $user);
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		if (empty($arTestProperties["GetUser"]))
		{
			$errors[] = ["code" => "NotExist", "parameter" => "GetUser", "message" => GetMessage("BPGUIA_ERROR_1")];
		}
		else
		{
			if ($user && $arTestProperties["GetUser"] !== $user->getBizprocId() && !$user->isAdmin())
			{
				$errors[] = ["code" => "NotExist", "parameter" => "GetUser", "message" => GetMessage("BPGUIA_ERROR_2")];
			}
		}

		return array_merge($errors, parent::ValidateProperties($arTestProperties, $user));
	}
}