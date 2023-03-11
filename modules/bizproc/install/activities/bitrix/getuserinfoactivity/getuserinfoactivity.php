<?php

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPGetUserInfoActivity extends CBPActivity
{
	private $userFields;

	protected static function getUserFields()
	{
		$userService = CBPRuntime::getRuntime()->getUserService();
		$fieldsFromService = $userService->getUserBaseFields();
		$fields = [];
		foreach ($fieldsFromService as $key => $property)
		{
			$fields['USER_' . $key] = $property;
		}

		// compatibility
		$fields['IS_ABSENT'] = [
			'Name' => Loc::getMessage('BPGUIA_IS_ABSENT'),
			'Type' => 'bool',
		];
		$fields['TIMEMAN_STATUS'] = [
			'Name' => Loc::getMessage('BPGUIA_TIMEMAN_STATUS'),
			'Type' => 'string',
		];

		return $fields;
	}

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"GetUser" => null,
			'UserFields' => null
		];

		$this->userFields = array_merge(self::getUserFields(), self::getUserExtendedFields());

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

		$this->writeDebugInfo($this->getDebugInfo(['GetUser' => $userId ? 'user_' . $userId : '']));
		if (!$userId)
		{
			$this->WriteToTrackingService(GetMessage('BPGUIA_ERROR_1'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$userService = $this->workflow->getRuntime()->getUserService();
		$user = $userService->getUserInfo($userId);

		if (!$user)
		{
			$this->WriteToTrackingService(GetMessage('BPGUIA_ERROR_USER_NOT_FOUND', ['#ID#' => $userId]), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		foreach (array_keys($this->userFields) as $key)
		{
			$fieldId = str_replace('USER_', '', $key);

			if (isset($user[$fieldId]))
			{
				$this->__set($key, $user[$fieldId]);
			}
		}

		//compatible, without new b24 editions checking
		if (!isset($user['TIMEMAN_STATUS']) && CModule::IncludeModule('timeman'))
		{
			$tmUser = new CTimeManUser($userId);
			$this->__set('TIMEMAN_STATUS', $tmUser->State());
		}
		$this->logUserFields();

		return CBPActivityExecutionStatus::Closed;
	}

	private function logUserFields(): void
	{
		$map = array_filter(
			array_merge(self::getUserFields(), self::getUserExtendedFields()),
			fn ($fieldId) => !CBPHelper::isEmptyValue($this->__get($fieldId)),
			ARRAY_FILTER_USE_KEY,
		);
		$debugInfo = $this->getDebugInfo([], $map);

		$this->writeDebugInfo($debugInfo);
	}

	protected static function getFieldsCreatedByUser()
	{
		$userService = CBPRuntime::getRuntime()->getUserService();

		return $userService->getUserUserFields();
	}

	protected static function getUserExtendedFields(): array
	{
		$userService = CBPRuntime::getRuntime()->getUserService();

		return $userService->getUserExtendedFields();
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

		$dialog->setMap(static::getPropertiesMap($documentType, ['user' => $user]));

		$dialog->setRuntimeData([
			'user' => $user
		]);

		return $dialog;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		return [
			'GetUser' => [
				'Name' => GetMessage('BPGUIA_TARGET_USER_NAME'),
				'FieldName' => 'get_user',
				'Type' => 'user',
				'Default' => isset($context['user']) ? $context['user']->getBizprocId() : null,
			],
		];
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
		$properties['UserFields'] = array_merge(self::getUserFields(), self::getUserExtendedFields());

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