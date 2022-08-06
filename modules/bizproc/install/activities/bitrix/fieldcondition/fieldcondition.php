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

	public function Evaluate(CBPActivity $ownerActivity)
	{
		if (!$this->isConditionGroupExist())
		{
			return true;
		}

		$this->conditionGroupToArray();

		$rootActivity = $ownerActivity->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();
		$documentType = $rootActivity->GetDocumentType();

		$documentService = $ownerActivity->workflow->GetService("DocumentService");
		$document = $documentService->GetDocument($documentId, $documentType);
		$documentFields = $documentService->GetDocumentFields($documentType);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($documentFields);

		$items = [];
		foreach ($this->condition as $cond)
		{
			if (!isset($document[$cond[0]]) && mb_substr($cond[0], -mb_strlen('_PRINTABLE')) == '_PRINTABLE')
			{
				$cond[0] = mb_substr($cond[0], 0, mb_strlen($cond[0]) - mb_strlen('_PRINTABLE'));
			}

			if (!isset($document[$cond[0]]) && isset($documentFieldsAliasesMap[$cond[0]]))
			{
				$cond[0] = $documentFieldsAliasesMap[$cond[0]];
			}

			$fld = $document[$cond[0] . "_XML_ID"] ?? $document[$cond[0]];
			$baseType = $documentFields[$cond[0]]["BaseType"];
			$type = $documentFields[$cond[0]]["Type"];
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
			$arCurrentValues = array();
			if (is_array($defaultValue))
			{
				$i = 0;
				foreach ($defaultValue as $value)
				{
					if (!isset($arDocumentFieldsTmp[$value[0]]) && isset($documentFieldsAliasesMap[$value[0]]))
					{
						$value[0] = $documentFieldsAliasesMap[$value[0]];
					}

					if ($arCurrentValues["field_condition_count"] <> '')
					{
						$arCurrentValues["field_condition_count"] .= ",";
					}
					$arCurrentValues["field_condition_count"] .= $i;

					$arCurrentValues["field_condition_field_".$i] = $value[0];
					$arCurrentValues["field_condition_condition_".$i] = $value[1];
					$arCurrentValues["field_condition_value_".$i] = $value[2];
					$arCurrentValues["field_condition_joiner_".$i] = $value[3];

					$i++;
				}
			}
		}
		else
		{
			$arFieldConditionCount = explode(",", $arCurrentValues["field_condition_count"]);
			foreach ($arFieldConditionCount as $i)
			{
				if (intval($i)."!" != $i."!")
				{
					continue;
				}

				$i = intval($i);

				if (
					!array_key_exists("field_condition_field_" . $i, $arCurrentValues)
					|| $arCurrentValues["field_condition_field_" . $i] == ''
				)
				{
					continue;
				}

				$arErrors = [];
				$arCurrentValues["field_condition_value_" . $i] = $documentService->GetFieldInputValue(
					$documentType,
					$arDocumentFieldsTmp[$arCurrentValues["field_condition_field_" . $i]],
					"field_condition_value_" . $i,
					$arCurrentValues,
					$arErrors
				);
			}
		}

		$arDocumentFields = array();
		foreach ($arDocumentFieldsTmp as $key => $value)
		{
			//if (!$value["Filterable"])
			//	continue;
			$arDocumentFields[$key] = $value;
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields(
			$documentType,
			"objFieldsFC",
			$arDocumentFields,
			$arFieldTypes
		);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arDocumentFields" => $arDocumentFields,
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
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
		$arErrors = array();

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

		$arFieldConditionCount = explode(",", $arCurrentValues["field_condition_count"]);
		foreach ($arFieldConditionCount as $i)
		{
			if (intval($i) . "!" != $i . "!")
			{
				continue;
			}
			$i = intval($i);

			if (
				!array_key_exists("field_condition_field_".$i, $arCurrentValues)
				|| $arCurrentValues["field_condition_field_".$i] == ''
			)
			{
				continue;
			}

			$arErrors = [];
			$arCurrentValues["field_condition_value_" . $i] = $documentService->GetFieldInputValue(
				$documentType,
				$arDocumentFieldsTmp[$arCurrentValues["field_condition_field_" . $i]],
				"field_condition_value_" . $i,
				$arCurrentValues,
				$arErrors
			);

			$arResult[] = [
				$arCurrentValues["field_condition_field_" . $i],
				htmlspecialcharsback($arCurrentValues["field_condition_condition_" . $i]),
				$arCurrentValues["field_condition_value_" . $i],
				(int) $arCurrentValues["field_condition_joiner_" . $i],
			];
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
