<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc;

class CBPFieldCondition extends CBPActivityCondition
{
	public $condition = null;

	public function __construct($condition)
	{
		$this->condition = $condition;
	}

	public function evaluate(CBPActivity $ownerActivity)
	{
		if (!$this->isConditionGroupExist())
		{
			return true;
		}

		$this->conditionGroupToArray();

		$rootActivity = $ownerActivity->getRootActivity();
		$documentId = $rootActivity->GetDocumentId();
		$documentType = $rootActivity->getDocumentType();

		$documentService = $ownerActivity->workflow->getRuntime()->getDocumentService();
		$document = $documentService->getDocument($documentId, $documentType);
		$documentFields = $documentService->getDocumentFields($documentType);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($documentFields);

		$items = [];
		foreach ($this->condition as $cond)
		{
			if (!isset($document[$cond[0]]) && mb_substr($cond[0], -mb_strlen('_PRINTABLE')) === '_PRINTABLE')
			{
				$cond[0] = mb_substr($cond[0], 0, mb_strlen($cond[0]) - mb_strlen('_PRINTABLE'));
			}

			if (!isset($document[$cond[0]]) && isset($documentFieldsAliasesMap[$cond[0]]))
			{
				$cond[0] = $documentFieldsAliasesMap[$cond[0]];
			}

			$fld = null;
			if (isset($document[$cond[0] . '_XML_ID']))
			{
				$fld = $document[$cond[0] . '_XML_ID'];
			}
			elseif(isset($document[$cond[0]]))
			{
				$fld = $document[$cond[0]];
			}

			$baseType = isset($documentFields[$cond[0]]) ? $documentFields[$cond[0]]['BaseType'] : null;
			$type = isset($documentFields[$cond[0]]) ? $documentFields[$cond[0]]['Type'] : null;
			if ($type === 'UF:boolean')
			{
				$baseType = 'bool';
			}

			$items[] = [
				'joiner' => $this->getJoiner($cond),
				'operator' => $cond[1],
				'valueToCheck' => ($cond[1] === 'modified') ? $cond[0] : $fld,
				'fieldType' => $this->getFieldTypeObject($rootActivity, ['Type' => $type ?: $baseType]),
				'value' =>
					($cond[1] === 'modified')
						? $rootActivity->{CBPDocument::PARAM_MODIFIED_DOCUMENT_FIELDS}
						: $rootActivity->parseValue($cond[2], $baseType)
				,
				'fieldName' => $documentFields[$cond[0]]['Name'] ?? $cond[0],
			];
		}

		$conditionGroup = new Bizproc\Activity\ConditionGroup([
			'items' => $items,
			'parameterDocumentId' => $rootActivity->getDocumentId()
		]);

		return $conditionGroup->evaluate();
	}

	public function collectUsages(CBPActivity $ownerActivity)
	{
		$usages = [];
		foreach ($this->condition as $cond)
		{
			$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::DocumentField, $cond[0]];
			if (is_string($cond[2]))
			{
				$parsed = $ownerActivity::parseExpression($cond[2]);
				if ($parsed)
				{
					$usages[] = \Bitrix\Bizproc\Workflow\Template\SourceType::getObjectSourceType(
						$parsed['object'],
						$parsed['field']
					);
				}
			}
		}

		return $usages;
	}

	public static function GetPropertiesDialog(
		$documentType,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$defaultValue,
		$arCurrentValues = null,
		$formName = ""
	)
	{
		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFieldsTmp = $documentService->GetDocumentFields($documentType);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($arDocumentFieldsTmp);

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [];
			if (is_array($defaultValue))
			{
				$i = 0;
				foreach ($defaultValue as $value)
				{
					if (!isset($arDocumentFieldsTmp[$value[0]]) && isset($documentFieldsAliasesMap[$value[0]]))
					{
						$value[0] = $documentFieldsAliasesMap[$value[0]];
					}

					if (isset($arCurrentValues["field_condition_count"]) && $arCurrentValues["field_condition_count"] !== '')
					{
						$arCurrentValues["field_condition_count"] .= ",";
					}
					else
					{
						$arCurrentValues["field_condition_count"] = '';
					}

					$arCurrentValues["field_condition_count"] .= $i;

					$arCurrentValues["field_condition_field_" . $i] = $value[0];
					$arCurrentValues["field_condition_condition_" . $i] = $value[1];
					$arCurrentValues["field_condition_value_" . $i] = $value[2];
					$arCurrentValues["field_condition_joiner_" . $i] = $value[3];

					$i++;
				}
			}
		}
		else
		{
			$arFieldConditionCount = explode(",", (string)$arCurrentValues["field_condition_count"]);
			foreach ($arFieldConditionCount as $i)
			{
				if (!is_numeric($i))
				{
					continue;
				}
				$i = (int)$i;

				$fieldId = $arCurrentValues["field_condition_field_" . $i] ?? null;
				if (CBPHelper::isEmptyValue($fieldId))
				{
					continue;
				}

				$operator = $arCurrentValues['field_condition_condition_' . $i] ?? '=';
				$property = $arDocumentFieldsTmp[$fieldId] ?? [];

				$value =
					static::getConditionFieldInputValue(
						(string)$operator,
						$documentType,
						$property,
						'field_condition_value_' . $i,
						$arCurrentValues,
					)
						->getData()['value']
				;

				$arCurrentValues['field_condition_value_' . $i] = $value ?? '';
			}
		}

		$arDocumentFields = [];
		foreach ($arDocumentFieldsTmp as $key => $value)
		{
			$arDocumentFields[$key] = $value;
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arDocumentFields" => $arDocumentFields,
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				"arFieldTypes" => $arFieldTypes,
				'documentService' => $documentService,
				'documentType' => $documentType,
			]
		);
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	)
	{
		$runtime = CBPRuntime::GetRuntime();
		$arErrors = [];

		if (!array_key_exists("field_condition_count", $arCurrentValues) || $arCurrentValues["field_condition_count"] == '')
		{
			$arErrors[] = [
				"code" => "",
				"message" => GetMessage("BPFC_NO_WHERE"),
			];

			return null;
		}

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFieldsTmp = $documentService->GetDocumentFields($documentType);

		$arResult = [];

		$arFieldConditionCount = explode(",", (string)$arCurrentValues["field_condition_count"]);
		foreach ($arFieldConditionCount as $i)
		{
			if (!is_numeric($i))
			{
				continue;
			}
			$i = (int)$i;

			$fieldId = $arCurrentValues['field_condition_field_' . $i];
			if (CBPHelper::isEmptyValue($fieldId))
			{
				continue;
			}

			$operator = htmlspecialcharsback((string)$arCurrentValues['field_condition_condition_' . $i]);
			$property = $arDocumentFieldsTmp[$fieldId] ?? [];
			$inputResult = static::getConditionFieldInputValue(
				$operator,
				$documentType,
				$property,
				"field_condition_value_" . $i,
				$arCurrentValues,
			);
			if (!$inputResult->isSuccess())
			{
				foreach ($inputResult->getErrors() as $error)
				{
					$arErrors[] = [
						'message' => $error->getMessage(),
						'code' => $error->getCode(),
					];
				}
			}

			$joiner = isset($arCurrentValues['field_condition_joiner_' . $i]) ? (int)$arCurrentValues['field_condition_joiner_' . $i] : 0;

			$arResult[] = [$fieldId, $operator, $inputResult->getData()['value'] ?? '', $joiner];
		}

		if (count($arResult) <= 0)
		{
			$arErrors[] = [
				"code" => "",
				"message" => GetMessage("BPFC_NO_WHERE"),
			];

			return null;
		}

		return $arResult;
	}
}
