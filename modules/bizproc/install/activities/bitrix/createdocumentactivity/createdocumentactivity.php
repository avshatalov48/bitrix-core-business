<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPCreateDocumentActivity
	extends CBPActivity
{
	const EXECUTION_MAX_DEPTH = 1;
	private static $executionDepth = array();

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Fields" => null,
		);
	}

	public function Execute()
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();
		$fieldValue = $this->Fields;

		$documentService = $this->workflow->GetService("DocumentService");

		$documentFields = $documentService->GetDocumentFields($documentService->GetDocumentType($documentId));
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($documentFields);
		if ($documentFieldsAliasesMap)
		{
			$fixedFields = array();
			foreach ($fieldValue as $key => $value)
			{
				if (!isset($documentFields[$key]) && isset($documentFieldsAliasesMap[$key]))
				{
					$fixedFields[$documentFieldsAliasesMap[$key]] = $value;
					continue;
				}
				$fixedFields[$key] = $value;
			}
			$fieldValue = $fixedFields;
		}

		$executionKey = $rootActivity->GetWorkflowTemplateId();

		self::increaseExecutionDepth($executionKey);
		$documentService->CreateDocument($documentId, $fieldValue);
		self::resetExecutionDepth($executionKey);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null)
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFieldsTmp = $documentService->GetDocumentFields($documentType);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($arDocumentFieldsTmp);

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"])
				&& array_key_exists("Fields", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["Fields"]))
			{
				foreach ($arCurrentActivity["Properties"]["Fields"] as $k => $v)
				{
					if (!isset($arDocumentFieldsTmp[$k]) && isset($documentFieldsAliasesMap[$k]))
						$k = $documentFieldsAliasesMap[$k];

					$arCurrentValues[$k] = $v;

					if ($arDocumentFieldsTmp[$k]["BaseType"] == "user")
					{
						if (!is_array($arCurrentValues[$k]))
							$arCurrentValues[$k] = array($arCurrentValues[$k]);

						$ar = array();
						foreach ($arCurrentValues[$k] as $v)
						{
							if (intval($v)."!" == $v."!")
								$v = "user_".$v;
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
				if (!$value["Editable"])
					continue;

				$arErrors = array();
				$arCurrentValues[$key] = $documentService->GetFieldInputValue($documentType, $value, $key, $arCurrentValues, $arErrors);
			}
		}

		$arDocumentFields = array();
		$defaultFieldValue = "";
		foreach ($arDocumentFieldsTmp as $key => $value)
		{
			if (!$value["Editable"])
				continue;

			$arDocumentFields[$key] = $value;
			if (strlen($defaultFieldValue) <= 0)
				$defaultFieldValue = $key;

			/*if ($value["BaseType"] == "select" || $value["BaseType"] == "bool")
			{
				if (array_key_exists($key."_text", $arCurrentValues)
					&& ($value["Multiple"] && count($arCurrentValues[$key."_text"]) > 0
						|| !$value["Multiple"] && strlen($arCurrentValues[$key."_text"]) > 0)
					)
				{
					$arCurrentValues[$key] = $arCurrentValues[$key."_text"];
				}
			}*/
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFieldsCD", $arDocumentFields, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arDocumentFields" => $arDocumentFields,
				"formName" => $formName,
				"defaultFieldValue" => $defaultFieldValue,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
				"documentType" => $documentType,
				"popupWindow" => &$popupWindow,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = array("Fields" => array());

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		foreach ($arDocumentFields as $fieldKey => $fieldValue)
		{
			if (!$fieldValue["Editable"])
				continue;

			$arFieldErrors = array();
			$r = $documentService->GetFieldInputValue($documentType, $fieldValue, $fieldKey, $arCurrentValues, $arFieldErrors);

			if(is_array($arFieldErrors) && !empty($arFieldErrors))
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

			if ($fieldValue["Required"] && ($r == null))
			{
				$arErrors[] = array(
					"code" => "emptyRequiredField",
					"message" => str_replace("#FIELD#", $fieldValue["Name"], GetMessage("BPCDA_FIELD_REQUIED")),
				);
			}

			if ($r != null)
				$arProperties["Fields"][$fieldKey] = $r;
		}

		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	private static function increaseExecutionDepth($key)
	{
		if (!isset(self::$executionDepth[$key]))
		{
			self::$executionDepth[$key] = 0;
		}
		self::$executionDepth[$key]++;

		if (self::$executionDepth[$key] > self::EXECUTION_MAX_DEPTH)
			throw new Exception(GetMessage('BPCDA_RECURSION_ERROR'));
	}
	private static function resetExecutionDepth($key)
	{
		self::$executionDepth[$key] = 0;
	}
}