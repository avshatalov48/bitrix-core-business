<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

CBPRuntime::getRuntime()->includeActivityFile('CreateDocumentActivity');

/**
 * @property int| null $ElementId
 * @property-write string|null ErrorMessage
 */
class CBPCreateListsDocumentActivity extends CBPCreateDocumentActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties['DocumentType'] = null;
		// return properties
		$this->arProperties['ElementId'] = null;
		$this->arProperties['ErrorMessage'] = null;

		//return properties mapping
		$this->SetPropertiesTypes([
			'ElementId' => [
				'Type' => 'int',
			],
			'ErrorMessage' => [
				'Type' => 'string',
			],
		]);
	}

	public function reInitialize()
	{
		parent::reInitialize();
		$this->ElementId = null;
		$this->ErrorMessage = null;
	}

	public function execute()
	{
		if (!\Bitrix\Main\Loader::includeModule('lists'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$documentType = $this->DocumentType;
		if (!$documentType)
		{
			$this->writeToTrackingService(
				Loc::getMessage('BPCLDA_ERROR_DT_1'),
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		if ($this->workflow->isDebug())
		{
			$this->logDebugType($documentType);
		}

		$fields = $this->Fields;
		if (!is_array($fields))
		{
			$fields = [];
		}

		$fields['IBLOCK_ID'] = mb_substr($documentType[2], 7); // strlen('iblock_') == 7
		if (!isset($fields['CREATED_BY']))
		{
			$stateInfo = CBPStateService::getWorkflowStateInfo($this->getWorkflowInstanceId());
			if ($stateInfo && (int)$stateInfo['STARTED_BY'] > 0)
			{
				$fields['CREATED_BY'] = 'user_' . $stateInfo['STARTED_BY'];
			}
		}

		$documentService = $this->workflow->getRuntime()->getDocumentService();
		$documentFields = $documentService->getDocumentFields($documentType);
		$resultFields = $this->prepareFieldsValues($documentType, $fields);
		try
		{
			$this->ElementId = $documentService->createDocument($documentType, $resultFields);
		}
		catch (Exception $e)
		{
			$this->writeToTrackingService($e->getMessage(), 0, CBPTrackingType::Error);
			$this->ErrorMessage = $e->getMessage();
		}

		if ($this->workflow->isDebug())
		{
			$this->logDebugId($this->ElementId);

			$fieldsMap = [];
			foreach ($resultFields as $key => $field)
			{
				$fieldsMap[$key] = $documentFields[$key];
			}

			$this->logDebugFields($fieldsMap, $resultFields);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function validateProperties($testProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		try
		{
			CBPHelper::ParseDocumentId($testProperties['DocumentType']);
		}
		catch (Exception $e)
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'DocumentType',
				'message' => Loc::getMessage('BPCLDA_ERROR_DT_1'),
			];
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	public static function getPropertiesDialog(
		$paramDocumentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = '',
		$popupWindow = null
	)
	{
		if (!CModule::IncludeModule('lists'))
		{
			return null;
		}

		$documentType = null;
		if (!empty($arCurrentValues['lists_document_type']))
		{
			$documentType = explode('@', $arCurrentValues['lists_document_type']);
		}

		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (
				!empty($arCurrentActivity['Properties']['Fields'])
				&& is_array($arCurrentActivity['Properties']['Fields'])
			)
			{
				foreach ($arCurrentActivity['Properties']['Fields'] as $k => $v)
				{
					$arCurrentValues[$k] = $v;
				}
			}
			if (!empty($arCurrentActivity['Properties']['DocumentType']))
			{
				$documentType = $arCurrentActivity['Properties']['DocumentType'];
				$arCurrentValues['lists_document_type'] = implode('@', $documentType);
			}
		}
		elseif ($documentType)
		{
			$fields = $documentService->GetDocumentFields($documentType);
			foreach ($fields as $key => $value)
			{
				if (!$value['Editable'])
				{
					continue;
				}

				$arErrors = [];
				$arCurrentValues[$key] = $documentService->GetFieldInputValue(
					$documentType,
					$value,
					$key,
					$arCurrentValues,
					$arErrors
				);
			}
		}

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $paramDocumentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
		]);

		$dialog->setMap(static::getPropertiesMap($paramDocumentType));

		$dialog->setRuntimeData([
			'documentFields' => $documentType ? self::getDocumentFields($documentType) : [],
			'documentService' => $documentService,
			'listsDocumentType' => $documentType,
		]);

		return $dialog;
	}

	public static function getPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$errors
	)
	{
		$errors = [];

		$realDocumentType = null;
		if (!empty($arCurrentValues['lists_document_type']))
		{
			$realDocumentType = explode('@', $arCurrentValues['lists_document_type']);
		}

		$arProperties = ['Fields' => [], 'DocumentType' => $realDocumentType];

		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		$arDocumentFields = $realDocumentType ? $documentService->GetDocumentFields($realDocumentType) : [];

		$iblockId = $realDocumentType ? mb_substr($realDocumentType[2], 7) : null;
		$listFields = $iblockId ? static::getVisibleFieldsList($iblockId) : [];

		foreach ($arDocumentFields as $fieldKey => $fieldValue)
		{
			if ($fieldKey == 'IBLOCK_ID')
			{
				$arProperties["Fields"][$fieldKey] = $iblockId;
				continue;
			}

			if ($fieldKey !== "CREATED_BY")
			{
				if (!$fieldValue["Editable"] || !in_array($fieldKey, $listFields))
				{
					continue;
				}
			}

			$arFieldErrors = [];
			$r = $documentService->GetFieldInputValue(
				$realDocumentType,
				$fieldValue,
				$fieldKey,
				$arCurrentValues,
				$arFieldErrors
			);

			if (is_array($arFieldErrors) && !empty($arFieldErrors))
			{
				$errors = array_merge($errors, $arFieldErrors);
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
				$errors[] = [
					"code" => "emptyRequiredField",
					"message" => str_replace("#FIELD#", $fieldValue["Name"], GetMessage("BPCLDA_FIELD_REQUIED")),
				];
			}

			if ($r != null)
			{
				$arProperties["Fields"][$fieldKey] = $r;
			}
		}

		if (count($errors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;

		return true;
	}

	public static function getAjaxResponse($request)
	{
		$result = '';

		if (!empty($request['lists_document_type']) && !empty($request['form_name']))
		{
			$documentType = explode('@', $request['lists_document_type']);

			if (isset($request['public_mode']) && $request['public_mode'] === 'Y')
			{
				$fields = self::getDocumentFields($documentType);

				foreach ($fields as $key => $field)
				{
					$field = \Bitrix\Bizproc\FieldType::normalizeProperty($field);
					$field['Id'] = $key;
					$fields[$key] = $field;
				}
				return array_values($fields);
			}

			$result = self::renderDocumentFields($documentType, $request['form_name']);
		}

		return $result;
	}

	private static function renderDocumentFields($documentType, $formName, array $currentValues = [])
	{
		$result = '';
		if (!$documentType)
		{
			return $result;
		}

		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		$fields = self::getDocumentFields($documentType);

		foreach ($fields as $fieldKey => $fieldValue)
		{
			$fieldHtml = $documentService->GetFieldInputControl(
				$documentType,
				$fieldValue,
				[$formName, $fieldKey],
				$currentValues[$fieldKey] ?? null,
				true,
				false
			);

			$result .= '<tr><td align="right" width="40%" class="adm-detail-content-cell-l">'
				. ($fieldValue["Required"] ?
					"<span class=\"adm-required-field\">" . htmlspecialcharsbx($fieldValue["Name"]) . ":</span>"
					: htmlspecialcharsbx($fieldValue["Name"]) . ":"
				) . '</td><td width="60%" class="adm-detail-content-cell-r">' . $fieldHtml . '</td></tr>';
		}

		return $result;
	}

	private static function getDocumentFields(array $documentType)
	{
		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		$fields = $documentService->GetDocumentFields($documentType);

		$listFields = static::getVisibleFieldsList(mb_substr($documentType[2], 7));

		foreach ($fields as $fieldKey => $fieldValue)
		{
			if (
				$fieldKey !== 'CREATED_BY'
				&& (!$fieldValue['Editable'] || $fieldKey == 'IBLOCK_ID' || !in_array($fieldKey, $listFields))
			)
			{
				unset($fields[$fieldKey]);
			}
		}

		return $fields;
	}

	private static function getVisibleFieldsList($iblockId)
	{
		$list = new CList($iblockId);
		$listFields = $list->getFields();
		$result = [];
		foreach ($listFields as $key => $field)
		{
			if (mb_strpos($key, 'PROPERTY_') === 0)
			{
				if (!empty($field['CODE']))
				{
					$key = 'PROPERTY_' . $field['CODE'];
				}
			}
			$result[] = $key;
		}
		return $result;
	}

	private static function getDocumentTypeField()
	{
		$field = [
			'Name' => Loc::getMessage('BPCLDA_DOC_TYPE_1'),
			'FieldName' => 'lists_document_type',
			'Type' => 'select',
			'Required' => true,
		];

		$options = [];
		$groups = [];

		$processesType = COption::getOptionString('lists', 'livefeed_iblock_type_id', 'bitrix_processes');
		$groups = [
			'lists' => ['name' => Loc::getMessage('BPCLDA_DT_LISTS'), 'items' => []],
			$processesType => ['name' => Loc::getMessage('BPCLDA_DT_PROCESSES'), 'items' => []],
			'lists_socnet' => ['name' => Loc::getMessage('BPCLDA_DT_LISTS_SOCNET_1'), 'items' => []],
		];
		// other lists
		$typesResult = CLists::GetIBlockTypes();
		while ($typeRow = $typesResult->fetch())
		{
			$groups[$typeRow['IBLOCK_TYPE_ID']] = ['name' => $typeRow['NAME'], 'items' => []];
		}

		$iterator = CIBlock::GetList(['SORT' => 'ASC', 'NAME' => 'ASC'], [
			'ACTIVE' => 'Y',
			'TYPE' => array_keys($groups),
			'CHECK_PERMISSIONS' => 'N',
		]);

		while ($row = $iterator->fetch())
		{
			$value =
				'lists@'
				. ($row['IBLOCK_TYPE_ID'] === $processesType ? 'BizprocDocument' : 'Bitrix\Lists\BizprocDocumentLists')
				. '@iblock_'
				. $row['ID']
			;
			$name = '[' . $row['LID'] . '] ' . $row['NAME'];

			$options[$value] = $name;
			$groups[$row['IBLOCK_TYPE_ID']]['items'][$value] = $name;
		}

		$field['Options'] = $options;
		$field['Settings'] = ['Groups' => $groups];

		return $field;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		return [
			'DocumentType' => self::getDocumentTypeField(),
		];
	}

	private function logDebugType($type)
	{
		$debugInfo = $this->getDebugInfo([
			'DocumentType' => implode('@', $type),
		]);
		$this->writeDebugInfo($debugInfo);
	}

	private function logDebugId($id)
	{
		$debugInfo = $this->getDebugInfo(
			['ElementId' => $id],
			['ElementId' => Loc::getMessage('BPCLDA_CREATED_ELEMENT_ID')],
		);
		$this->writeDebugInfo($debugInfo);
	}

	private function logDebugFields(array $fields, array $values)
	{
		$debugInfo = $this->getDebugInfo($values, $fields);
		$this->writeDebugInfo($debugInfo);
	}
}
