<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPSetGlobalVariableActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'GlobalVariableValue' => null,
		];
	}

	public function execute(): int
	{
		$variableValue = $this->getRawProperty('GlobalVariableValue');
		if (!$variableValue)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		foreach ($variableValue as $varId => $value)
		{
			$var = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getById($varId);
			if ($var === null)
			{
				continue;
			}
			$var['Default'] = $this->parseValue($value, $var['Type']);
			\Bitrix\Bizproc\Workflow\Type\GlobalVar::upsert($varId, $var);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters = [],
		$arWorkflowVariables = [],
		$arCurrentValues = [],
		$formName = ''
	): \Bitrix\Bizproc\Activity\PropertiesDialog
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService('DocumentService');
		$fieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		$variables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll();

		$javascriptFunctions = $documentService->GetJSFunctionsForFields(
			$documentType,
			'objFieldsGlobalVar',
			$variables,
			$fieldTypes
		);

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(
			__FILE__,
			[
				'documentType' => $documentType,
				'activityName' => $activityName,
				'workflowTemplate' => $arWorkflowTemplate,
				'formName' => $formName,
			]
		);

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$variableValues =
			is_array($currentActivity['Properties']['GlobalVariableValue'])
				? $currentActivity['Properties']['GlobalVariableValue']
				: []
		;

		$dialog->setRuntimeData([
			'arCurrentValues' => $variableValues,
			'arVariables' => $variables,
			'javascriptFunctions' => $javascriptFunctions,
			'isAdmin' => static::checkAdminPermission(),
		]);

		return $dialog;
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	): bool
	{
		$arErrors = [];
		$arProperties = ['GlobalVariableValue' => []];

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService('DocumentService');

		$allVariables = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAll();
		if (!$allVariables)
		{
			$arErrors[] = [
				'code' => 'EmptyGlobalVariables',
				'parameter' => '',
				'message' => GetMessage('BPSGCA_EMPTY_VARIABLES'),
			];

			return false;
		}

		$htmlVariableCode = 'global_variable_field_';
		$lenHtmlVariableCode = mb_strlen($htmlVariableCode);
		foreach ($arCurrentValues as $htmlCode => $variableId)
		{
			if (mb_strpos($htmlCode, $htmlVariableCode) !== 0)
			{
				continue;
			}

			$index = mb_substr($htmlCode, $lenHtmlVariableCode);
			if ($index . '!' === intval($index) . '!')
			{
				if (array_key_exists($variableId, $allVariables))
				{
					$arProperties['GlobalVariableValue'][$variableId] = $documentService->GetFieldInputValue(
						$documentType,
						$allVariables[$variableId],
						$variableId,
						$arCurrentValues,
						$arErrors
					);
				}
			}
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$arErrors = self::validateProperties($arProperties, $user);
		if ($arErrors)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;

		return true;
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null): array
	{
		$errors = [];

		if (
			!is_array($arTestProperties)
			|| !array_key_exists('GlobalVariableValue', $arTestProperties)
			|| !is_array($arTestProperties['GlobalVariableValue'])
			|| count($arTestProperties['GlobalVariableValue']) <= 0
		)
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'GlobalVariableValue',
				'message' => GetMessage('BPSGCA_EMPTY_VARIABLES'),
			];
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}

	public function collectUsages(): array
	{
		$usages = parent::collectUsages();
		if (is_array($this->arProperties['GlobalVariableValue']))
		{
			foreach (array_keys($this->arProperties['GlobalVariableValue']) as $value)
			{
				$usages[] = $this->getObjectSourceType('GlobalVar', $value);
			}
		}

		return $usages;
	}

	private static function checkAdminPermission(): bool
	{
		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		return $user->isAdmin();
	}
}
