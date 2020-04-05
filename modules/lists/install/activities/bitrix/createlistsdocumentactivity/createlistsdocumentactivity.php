<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPCreateListsDocumentActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"DocumentType" => null,
			"Fields" => null,

			//return properties
			'ElementId' => null,
		);

		//return properties mapping
		$this->SetPropertiesTypes(array(
			'ElementId' => array(
				'Type' => 'int',
			),
		));
	}

	public function ReInitialize()
	{
		parent::ReInitialize();
		$this->ElementId = null;
	}

	public function Execute()
	{
		$documentType = $this->DocumentType;
		$fields = $this->Fields;
		$fields['IBLOCK_ID'] = substr($documentType[2], 7); // strlen('iblock_') == 7

		if (!isset($fields["CREATED_BY"]))
		{
			$stateInfo = CBPStateService::getWorkflowStateInfo($this->getWorkflowInstanceId());
			if (intval($stateInfo["STARTED_BY"]) > 0)
				$fields["CREATED_BY"] = $stateInfo["STARTED_BY"];
		}

		$documentService = $this->workflow->GetService("DocumentService");
		$this->ElementId = $documentService->CreateDocument($documentType, $fields);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($testProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = array();

		try
		{
			CBPHelper::ParseDocumentId($testProperties['DocumentType']);
		}
		catch (Exception $e)
		{
			$errors[] = array("code" => "NotExist", "parameter" => "DocumentType", "message" => GetMessage("BPCLDA_ERROR_DT"));
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	public static function GetPropertiesDialog($paramDocumentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null)
	{
		if (!CModule::IncludeModule('lists'))
			return null;

		$documentType = null;
		if (!empty($arCurrentValues['lists_document_type']))
		{
			$documentType = explode('@', $arCurrentValues['lists_document_type']);
		}

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (!empty($arCurrentActivity["Properties"]['Fields']) && is_array($arCurrentActivity["Properties"]["Fields"]))
			{
				foreach ($arCurrentActivity["Properties"]["Fields"] as $k => $v)
				{
					$arCurrentValues[$k] = $v;
				}
			}
			if (!empty($arCurrentActivity["Properties"]['DocumentType']))
			{
				$documentType = $arCurrentActivity["Properties"]['DocumentType'];
			}
		}
		elseif ($documentType)
		{
			$fields = $documentService->GetDocumentFields($documentType);
			foreach ($fields as $key => $value)
			{
				if (!$value["Editable"])
					continue;

				$arErrors = array();
				$arCurrentValues[$key] = $documentService->GetFieldInputValue($documentType, $value, $key, $arCurrentValues, $arErrors);
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"formName" => $formName,
				"documentFieldsRender" => static::renderDocumentFields($documentType, $formName, $arCurrentValues),
				'documentType' => $documentType? implode('@', $documentType) : null,
				"paramDocumentType" => $paramDocumentType,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$documentType = null;
		if (!empty($arCurrentValues['lists_document_type']))
		{
			$documentType = explode('@', $arCurrentValues['lists_document_type']);
		}

		$arProperties = array("Fields" => array(), 'DocumentType' => $documentType);

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFields = $documentType ? $documentService->GetDocumentFields($documentType) : array();

		$iblockId = $documentType? substr($documentType[2], 7) : null;
		$listFields = $iblockId? static::getVisibleFieldsList($iblockId) : array();

		foreach ($arDocumentFields as $fieldKey => $fieldValue)
		{
			if (!$fieldValue["Editable"] || !in_array($fieldKey, $listFields))
				continue;

			if ($fieldKey == 'IBLOCK_ID')
			{
				$arProperties["Fields"][$fieldKey] = $iblockId;
				continue;
			}

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
					"message" => str_replace("#FIELD#", $fieldValue["Name"], GetMessage("BPCLDA_FIELD_REQUIED")),
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

	public static function getAjaxResponse($request)
	{
		$result = '';

		if (!empty($request['lists_document_type']) && !empty($request['form_name']))
		{
			$documentType = explode('@', $request['lists_document_type']);
			$result = self::renderDocumentFields($documentType, $request['form_name']);
		}

		return $result;
	}

	private static function renderDocumentFields($documentType, $formName, array $currentValues = array())
	{
		$result = '';
		if (!$documentType)
			return $result;

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$fields = $documentService->GetDocumentFields($documentType);

		$listFields = static::getVisibleFieldsList(substr($documentType[2], 7));

		foreach ($fields as $fieldKey => $fieldValue)
		{
			if ($fieldKey !== "CREATED_BY")
			{
				if (!$fieldValue["Editable"] || $fieldKey == 'IBLOCK_ID' || !in_array($fieldKey, $listFields))
					continue;
			}

			$result .='
			<tr>
				<td align="right" width="40%" class="adm-detail-content-cell-l">'
				.($fieldValue["Required"] ? "<span class=\"adm-required-field\">".htmlspecialcharsbx($fieldValue["Name"]).":</span>"
					: htmlspecialcharsbx($fieldValue["Name"]).":" ).'</td>
				<td width="60%" class="adm-detail-content-cell-r">'.$documentService->GetFieldInputControl(
						$documentType,
						$fieldValue,
						array($formName, $fieldKey),
						$currentValues[$fieldKey],
						true,
						false
					).'</td>
			</tr>';
		}

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);
		$result .= $documentService->GetJSFunctionsForFields($documentType, "objFieldsCD", $fields, $arFieldTypes);

		return $result;
	}

	private static function getVisibleFieldsList($iblockId)
	{
		$list = new CList($iblockId);
		$listFields = $list->getFields();
		$result = array();
		foreach ($listFields as $key => $field)
		{
			if (strpos($key, 'PROPERTY_') === 0)
			{
				if (!empty($field['CODE']))
					$key = 'PROPERTY_'.$field['CODE'];
			}
			$result[] = $key;
		}
		return $result;
	}
}
?>
