<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPSetVariableActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'VariableValue' => null,
		];
	}

	public function execute()
	{
		$variables = $this->getRawProperty('VariableValue');
		if (!is_array($variables) || count($variables) <= 0)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$documentService = $this->workflow->getRuntime()->getDocumentService();

		foreach ($variables as $name => $value)
		{
			if ($value !== '')
			{
				$property = $this->getVariableType($name);
				$value = $this->parseValue($value, $property['Type'] ?? null);

				if (is_array($property))
				{
					$fieldType = $documentService->getFieldTypeObject($this->getDocumentType(), $property);
					if ($fieldType)
					{
						$fieldType->setValue($value);
						$value = $fieldType->getValue();
					}
				}
			}

			$this->setVariable($name, $value);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		if (!is_array($arTestProperties)
			|| !array_key_exists('VariableValue', $arTestProperties)
			|| !is_array($arTestProperties['VariableValue'])
			|| count($arTestProperties['VariableValue']) <= 0)
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'VariableValue',
				'message' => Bitrix\Main\Localization\Loc::getMessage('BPSVA_EMPTY_VARS'),
			];
		}

		return array_merge($arErrors, parent::validateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = ''
	)
	{
		$runtime = CBPRuntime::getRuntime();

		$documentService = $runtime->getDocumentService();
		$arFieldTypes = $documentService->getDocumentFieldTypes($documentType);

		if (!is_array($arWorkflowParameters))
		{
			$arWorkflowParameters = [];
		}
		if (!is_array($arWorkflowVariables))
		{
			$arWorkflowVariables = [];
		}

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity['Properties'])
				&& array_key_exists('VariableValue', $arCurrentActivity['Properties'])
				&& is_array($arCurrentActivity['Properties']['VariableValue']))
			{
				foreach ($arCurrentActivity['Properties']['VariableValue'] as $k => $v)
				{
					$arCurrentValues[$k] = $v;

					/*if ($arFieldTypes[$arWorkflowVariables[$k]["Type"]]["BaseType"] == "user")
					{
						if (!is_array($arCurrentValues[$k]))
							$arCurrentValues[$k] = array($arCurrentValues[$k]);

						$arCurrentValues[$k] = CBPHelper::UsersArrayToString($arCurrentValues[$k], $arWorkflowTemplate, $documentType);
					}*/
				}
			}
		}
		else
		{
			$ind = 0;
			while (array_key_exists('variable_field_' .$ind, $arCurrentValues))
			{
				if (array_key_exists($arCurrentValues['variable_field_' .$ind], $arWorkflowVariables))
				{
					$varCode = $arCurrentValues['variable_field_' .$ind];

					$arErrors = [];
					$arCurrentValues[$varCode] = $documentService->getFieldInputValue(
						$documentType,
						$arWorkflowVariables[$varCode],
						$varCode,
						$arCurrentValues,
						$arErrors
					);
				}
				$ind++;
			}
		}

		$javascriptFunctions = $documentService->getJSFunctionsForFields(
			$documentType,
			'objFieldsVars',
			$arWorkflowVariables,
			$arFieldTypes
		);

		return $runtime->executeResourceFile(
			__FILE__,
			'properties_dialog.php',
			[
				'arCurrentValues' => $arCurrentValues,
				'arVariables' => $arWorkflowVariables,
				'formName' => $formName,
				'javascriptFunctions' => $javascriptFunctions,
			]
		);
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	)
	{
		$arErrors = [];

		$runtime = CBPRuntime::getRuntime();
		$documentService = $runtime->getDocumentService();

		$arProperties = ['VariableValue' => []];

		if (!is_array($arWorkflowVariables))
		{
			$arWorkflowVariables = [];
		}

		if (count($arWorkflowVariables) <= 0)
		{
			$arErrors[] = [
				'code' => 'EmptyVariables',
				'parameter' => '',
				'message' => Bitrix\Main\Localization\Loc::getMessage('BPSVA_EMPTY_VARS'),
			];

			return false;
		}

		$l = mb_strlen('variable_field_');
		foreach ($arCurrentValues as $key => $varCode)
		{
			if (mb_strpos($key, 'variable_field_') === 0)
			{
				$ind = mb_substr($key, $l);
				if (($ind . '!' === intval($ind) . '!') && array_key_exists($varCode, $arWorkflowVariables))
				{
					$arProperties['VariableValue'][$varCode] = $documentService->getFieldInputValue(
						$documentType,
						$arWorkflowVariables[$varCode],
						$varCode,
						$arCurrentValues,
						$arErrors
					);
				}
			}
		}

		$arErrors = self::validateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;

		return true;
	}

	public function collectUsages()
	{
		$usages = parent::collectUsages();
		if (is_array($this->arProperties['VariableValue']))
		{
			foreach (array_keys($this->arProperties['VariableValue']) as $v)
			{
				$usages[] = $this->getObjectSourceType('Variable', $v);
			}
		}

		return $usages;
	}
}