<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @property-write string|null ErrorMessage */
class CBPCreateDocumentActivity extends CBPActivity
{
	const EXECUTION_MAX_DEPTH = 1;
	private static $executionDepth = [];

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"Fields" => null,
			//return
			'ErrorMessage' => null,
		];

		$this->setPropertiesTypes([
			'ErrorMessage' => ['Type' => 'string'],
		]);
	}

	public function Execute()
	{
		$documentId = $this->GetDocumentId();
		$documentType = $this->GetDocumentType();

		$fieldValue = $this->Fields;
		if (!is_array($fieldValue))
		{
			$fieldValue = [];
		}

		$documentService = $this->workflow->GetService("DocumentService");
		$resultFields = $this->prepareFieldsValues($documentType, $fieldValue);

		$executionKey = $this->GetWorkflowTemplateId();

		self::increaseExecutionDepth($executionKey);
		try
		{
			$documentService->CreateDocument($documentId, $resultFields);
		}
		catch (Exception $e)
		{
			$this->WriteToTrackingService($e->getMessage(), 0, CBPTrackingType::Error);
			$this->ErrorMessage = $e->getMessage();
		}
		self::resetExecutionDepth($executionKey);

		return CBPActivityExecutionStatus::Closed;
	}

	protected function reInitialize()
	{
		parent::reInitialize();
		$this->ErrorMessage = null;
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = "",
		$popupWindow = null
	)
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arWorkflowParameters))
		{
			$arWorkflowParameters = [];
		}
		if (!is_array($arWorkflowVariables))
		{
			$arWorkflowVariables = [];
		}

		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFieldsTmp = $documentService->GetDocumentFields($documentType);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($arDocumentFieldsTmp);

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (
				is_array($arCurrentActivity["Properties"])
				&& array_key_exists("Fields", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["Fields"])
			)
			{
				foreach ($arCurrentActivity["Properties"]["Fields"] as $k => $v)
				{
					if (!isset($arDocumentFieldsTmp[$k]) && isset($documentFieldsAliasesMap[$k]))
					{
						$k = $documentFieldsAliasesMap[$k];
					}

					$arCurrentValues[$k] = $v;

					if ($arDocumentFieldsTmp[$k]["BaseType"] == "user")
					{
						if (!is_array($arCurrentValues[$k]))
						{
							$arCurrentValues[$k] = [$arCurrentValues[$k]];
						}

						$ar = [];
						foreach ($arCurrentValues[$k] as $v)
						{
							if (intval($v) . "!" == $v . "!")
							{
								$v = "user_" . $v;
							}
							$ar[] = $v;
						}

						$arCurrentValues[$k] = CBPHelper::UsersArrayToString($ar, $arWorkflowTemplate, $documentType);
					}
				}
			}
		}
		else
		{
			foreach ($arDocumentFieldsTmp as $key => $value)
			{
				if (empty($value["Editable"]))
				{
					continue;
				}

				$arErrors = [];
				$arCurrentValues[$key] = $documentService->GetFieldInputValue($documentType, $value, $key,
					$arCurrentValues, $arErrors);
			}
		}

		$arDocumentFields = [];
		$defaultFieldValue = "";
		foreach ($arDocumentFieldsTmp as $key => $value)
		{
			if (empty($value["Editable"]))
			{
				continue;
			}

			$arDocumentFields[$key] = $value;
			if ($defaultFieldValue == '')
			{
				$defaultFieldValue = $key;
			}
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFieldsCD",
			$arDocumentFields, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arCurrentValues" => $arCurrentValues,
				"arDocumentFields" => $arDocumentFields,
				"formName" => $formName,
				"defaultFieldValue" => $defaultFieldValue,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
				"documentType" => $documentType,
				"popupWindow" => &$popupWindow,
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

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = ["Fields" => []];

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		foreach ($arDocumentFields as $fieldKey => $fieldValue)
		{
			if (empty($fieldValue["Editable"]))
			{
				continue;
			}

			$arFieldErrors = [];
			$r = $documentService->GetFieldInputValue($documentType, $fieldValue, $fieldKey, $arCurrentValues,
				$arFieldErrors);

			if (is_array($arFieldErrors) && !empty($arFieldErrors))
			{
				$arErrors = array_merge($arErrors, $arFieldErrors);
			}

			if ($fieldValue["BaseType"] == "user")
			{
				if ($r === "author")
				{
					//HACK: We can't resolve author for new document - setup target user as author.
					$r = "{=Template:TargetUser}";
				}
				elseif (is_array($r))
				{
					$qty = count($r);
					if ($qty == 0)
					{
						$r = null;
					}
					elseif ($qty == 1)
					{
						$r = $r[0];
					}
				}
			}

			if (!empty($fieldValue["Required"]) && ($r == null))
			{
				$arErrors[] = [
					"code" => "emptyRequiredField",
					"message" => GetMessage("BPCDA_FIELD_REQUIED", ["#FIELD#" => $fieldValue["Name"]]),
				];
			}

			if ($r != null)
			{
				$arProperties["Fields"][$fieldKey] = $r;
			}
		}

		if (count($arErrors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	protected function prepareFieldsValues(array $documentType, array $values): array
	{
		$documentService = $this->workflow->getRuntime()->getDocumentService();

		$documentFields = $documentService->GetDocumentFields($documentType);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($documentFields);

		$resultFields = [];
		foreach ($values as $key => $value)
		{
			if (!isset($documentFields[$key]) && isset($documentFieldsAliasesMap[$key]))
			{
				$key = $documentFieldsAliasesMap[$key];
			}

			$property = $documentFields[$key] ?? null;

			if ($property && $value)
			{
				$fieldTypeObject = $documentService->getFieldTypeObject($documentType, $property);
				if ($fieldTypeObject)
				{
					$fieldTypeObject->setValue($value);
					$value = $fieldTypeObject->externalizeValue('Document', $fieldTypeObject->getValue());
				}
			}

			if (is_null($value))
			{
				$value = '';
			}

			$resultFields[$key] = $value;
		}

		return $resultFields;
	}

	private static function increaseExecutionDepth($key)
	{
		if (!isset(self::$executionDepth[$key]))
		{
			self::$executionDepth[$key] = 0;
		}
		self::$executionDepth[$key]++;

		if (self::$executionDepth[$key] > self::EXECUTION_MAX_DEPTH)
		{
			throw new Exception(GetMessage('BPCDA_RECURSION_ERROR_1'));
		}
	}

	private static function resetExecutionDepth($key)
	{
		self::$executionDepth[$key] = 0;
	}
}