<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetUserInfoActivity
	extends CBPActivity
{
	private $userFields = [
		'USER_ACTIVE' => [
			'Type' => 'bool',
		],
		'USER_EMAIL' => [
			'Type' => 'string',
		],
		'USER_WORK_PHONE' => [
			'Type' => 'string',
		],
		'USER_PERSONAL_MOBILE' => [
			'Type' => 'string',
		],
		'USER_UF_PHONE_INNER' => [
			'Type' => 'string',
		],
		'USER_LOGIN' => [
			'Type' => 'string',
		],
		'USER_LAST_NAME' => [
			'Type' => 'string',
		],
		'USER_NAME' => [
			'Type' => 'string',
		],
		'USER_SECOND_NAME' => [
			'Type' => 'string',
		],
		'USER_WORK_POSITION' => [
			'Type' => 'string',
		],
		'USER_PERSONAL_WWW' => [
			'Type' => 'string',
		],
		'USER_PERSONAL_CITY' => [
			'Type' => 'string',
		],
		'USER_UF_SKYPE' => [
			'Type' => 'string',
		],
		'USER_UF_TWITTER' => [
			'Type' => 'string',
		],
		'USER_UF_FACEBOOK' => [
			'Type' => 'string',
		],
		'USER_UF_LINKEDIN' => [
			'Type' => 'string',
		],
		'USER_UF_XING' => [
			'Type' => 'string',
		],
		'USER_UF_WEB_SITES' => [
			'Type' => 'string',
		],
		'USER_UF_DEPARTMENT' => [
			'Type' => 'int',
			'Multiple' => true
		],
		'IS_ABSENT' => [
			'Type' => 'bool'
		],
		'TIMEMAN_STATUS' => [
			'Type' => 'string'
		],
	];

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"GetUser" => null,
		];

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

		$dbUsers = CUser::GetList(
			($sortBy = 'id'), ($sortOrder = 'asc'),
			array('ID' => $userId),
			array('SELECT' => [
				'EMAIL',
				'UF_SKYPE',
				'UF_TWITTER',
				'UF_FACEBOOK',
				'UF_LINKEDIN',
				'UF_XING',
				'UF_WEB_SITES',
				'UF_PHONE_INNER',
				'UF_DEPARTMENT'
			]
			)
		);

		$user = $dbUsers ? $dbUsers->Fetch() : null;

		if (!$user)
		{
			$this->WriteToTrackingService(GetMessage('BPGUIA_ERROR_USER_NOT_FOUND', ['#ID#' => $userId]), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		foreach (array_keys($this->userFields) as $uf)
		{
			if (strpos($uf, 'USER_') !== 0)
			{
				continue;
			}

			$ufMap = str_replace('USER_', '', $uf);
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