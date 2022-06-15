<?
namespace Bitrix\Lists\Entity;

use Bitrix\Lists\Service\Param;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\ErrorableImplementation;

class Element implements Controllable, Errorable
{
	use ErrorableImplementation;

	const ERROR_ADD_ELEMENT = "ERROR_ADD_ELEMENT";
	const ERROR_UPDATE_ELEMENT = "ERROR_UPDATE_ELEMENT";
	const ERROR_DELETE_ELEMENT = "ERROR_DELETE_ELEMENT";
	const ERROR_ELEMENT_ALREADY_EXISTS = "ERROR_ELEMENT_ALREADY_EXISTS";
	const ERROR_ELEMENT_NOT_FOUND = "ERROR_ELEMENT_NOT_FOUND";
	const ERROR_ELEMENT_FIELD_VALUE = "ERROR_ELEMENT_FIELD_VALUE";

	private $param;
	private $params = [];

	private $iblockId;
	private $elementId;
	private $listObject;

	public $resultSanitizeFilter = [];

	public function __construct(Param $param)
	{
		$this->param = $param;
		$this->params = $param->getParams();

		$this->iblockId = Utils::getIblockId($this->params);
		$this->elementId = Utils::getElementId($this->params);

		$this->listObject = new \CList($this->iblockId);

		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * Checks whether an element exists.
	 *
	 * @return bool
	 */
	public function isExist()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_CODE", "IBLOCK_ID", "ELEMENT_CODE", "ELEMENT_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return false;
		}

		$filter = [
			"ID" => $this->params["ELEMENT_ID"] ? $this->params["ELEMENT_ID"] : "",
			"IBLOCK_ID" => $this->iblockId,
			"=CODE" => $this->params["ELEMENT_CODE"] ? $this->params["ELEMENT_CODE"] : "",
			"CHECK_PERMISSIONS" => "N",
		];
		$queryObject = \CIBlockElement::getList([], $filter, false, false, ["ID"]);
		return (bool) $queryObject->fetch();
	}

	/**
	 * Adds an element.
	 *
	 * @return int|bool
	 */
	public function add()
	{
		$this->param->checkRequiredInputParams(
			[
				"IBLOCK_TYPE_ID",
				"IBLOCK_CODE",
				"IBLOCK_ID",
				"ELEMENT_CODE",
				[
					"FIELDS" => ["NAME"]
				],
			]
		);

		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());

			return false;
		}

		$this->setUrlTemplate();

		$this->validateFields();

		$isEnabledBp = $this->isEnabledBizproc($this->params["IBLOCK_TYPE_ID"]);
		$hasBpTemplatesWithAutoStart = false;
		if ($isEnabledBp)
		{
			$hasBpTemplatesWithAutoStart = $this->hasBpTemplatesWithAutoStart(\CBPDocumentEventType::Create);
		}

		if ($this->hasErrors())
		{
			return false;
		}

		$elementFields = $this->getElementFields($this->elementId, $this->params["FIELDS"]);

		$elementObject = new \CIBlockElement;
		$this->elementId = $elementObject->add($elementFields, false, true, true);
		if ($this->elementId)
		{
			if ($isEnabledBp && $hasBpTemplatesWithAutoStart)
			{
				$this->startBp($this->elementId, \CBPDocumentEventType::Create);
			}

			return $this->elementId;
		}
		else
		{
			if ($elementObject->LAST_ERROR)
			{
				$this->errorCollection->setError(
					new Error($elementObject->LAST_ERROR, self::ERROR_ADD_ELEMENT)
				);
			}
			else
			{
				$this->errorCollection->setError(
					new Error("Unknown error", self::ERROR_ADD_ELEMENT)
				);
			}

			return false;
		}
	}

	/**
	 * Returns a list of element data.
	 *
	 * @param array $navData Navigation data.
	 *
	 * @return array
	 */
	public function get(array $navData = [])
	{
		$this->param->checkRequiredInputParams(["IBLOCK_TYPE_ID", "IBLOCK_CODE", "IBLOCK_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return [];
		}

		return $this->getElements($navData);
	}

	/**
	 * Updates an element.
	 *
	 * @return bool
	 */
	public function update()
	{
		$this->param->checkRequiredInputParams(
			[
				"IBLOCK_TYPE_ID",
				"IBLOCK_CODE",
				"IBLOCK_ID",
				"ELEMENT_CODE",
				"ELEMENT_ID",
			]
		);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());

			return false;
		}

		$this->validateFields();

		$isEnabledBp = $this->isEnabledBizproc($this->params["IBLOCK_TYPE_ID"]);
		$hasBpTemplatesWithAutoStart = false;
		if ($isEnabledBp)
		{
			$hasBpTemplatesWithAutoStart = $this->hasBpTemplatesWithAutoStart(\CBPDocumentEventType::Edit);
		}

		if ($this->hasErrors())
		{
			return false;
		}

		list($elementSelect, $elementFields, $elementProperty) = $this->getElementData();

		$fields = $this->getElementFields($this->elementId, $this->params["FIELDS"]);

		$elementObject = new \CIBlockElement;
		$updateResult = $elementObject->update($this->elementId, $fields, false, true, true);
		if ($updateResult)
		{
			if ($isEnabledBp && $hasBpTemplatesWithAutoStart)
			{
				$changedElementFields = \CLists::checkChangedFields(
					$this->iblockId,
					$this->elementId,
					$elementSelect,
					$elementFields,
					$elementProperty
				);

				$this->startBp($this->elementId, \CBPDocumentEventType::Edit, $changedElementFields);

				if ($this->hasErrors())
				{
					return false;
				}
			}

			return true;
		}
		else
		{
			if ($elementObject->LAST_ERROR)
			{
				$this->errorCollection->setError(
					new Error($elementObject->LAST_ERROR, self::ERROR_UPDATE_ELEMENT)
				);
			}
			else
			{
				$this->errorCollection->setError(
					new Error("Unknown error", self::ERROR_UPDATE_ELEMENT)
				);
			}

			return false;
		}
	}

	/**
	 * Deletes an element.
	 *
	 * @return bool
	 */
	public function delete()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_TYPE_ID", "IBLOCK_CODE", "IBLOCK_ID",
			"ELEMENT_CODE", "ELEMENT_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return false;
		}

		$elementObject = new \CIBlockElement;

		global $DB, $APPLICATION;
		$DB->startTransaction();
		$APPLICATION->resetException();

		if ($elementObject->delete($this->elementId))
		{
			$DB->commit();
			return true;
		}
		else
		{
			$DB->rollback();
			if ($exception = $APPLICATION->getException())
				$this->errorCollection->setError(new Error($exception->getString(), self::ERROR_UPDATE_ELEMENT));
			else
				$this->errorCollection->setError(new Error("Unknown error", self::ERROR_UPDATE_ELEMENT));

			return false;
		}
	}

	/**
	 * Returns the path to the file.
	 *
	 * @return array An array with a list of url for the field of type "File" or "File (Disk)".
	 */
	public function getFileUrl()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_CODE", "IBLOCK_ID", "ELEMENT_CODE", "ELEMENT_ID", "FIELD_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return [];
		}

		$urls = [];

		$sefFolder = $this->getSefFolder();

		$queryProperty = \CIBlockElement::getProperty($this->iblockId, $this->elementId,
			"SORT", "ASC", array("ACTIVE"=>"Y", "EMPTY"=>"N", "ID" => $this->params["FIELD_ID"])
		);
		while ($property = $queryProperty->fetch())
		{
			if ($property["PROPERTY_TYPE"] == "F")
			{
				$file = new \CListFile($this->iblockId, 0, $this->elementId,
					"PROPERTY_".$this->params["FIELD_ID"], $property["VALUE"]);
				$file->SetSocnetGroup($this->params["SOCNET_GROUP_ID"]);
				$urls[] = $file->GetImgSrc(["url_template" => $sefFolder.
					"#list_id#/file/#section_id#/#element_id#/#field_id#/#file_id#/"]);
			}
			elseif ($property["USER_TYPE"] == "DiskFile")
			{
				if (is_array($property["VALUE"]))
				{
					foreach ($property["VALUE"] as $attacheId)
					{
						$driver = \Bitrix\Disk\Driver::getInstance();
						$urls[] = $driver->getUrlManager()->getUrlUfController(
							"download", array("attachedId" => $attacheId));
					}
				}
			}
		}
		return $urls;
	}

	/**
	 * Returns a list of available fields for filtering and a list of custom handlers.
	 *
	 * @return array
	 */
	public function getAvailableFields()
	{
		$availableFields = array("ID", "ACTIVE", "NAME", "TAGS", "XML_ID", "EXTERNAL_ID", "PREVIEW_TEXT",
			"PREVIEW_TEXT_TYPE", "PREVIEW_PICTURE", "DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DETAIL_PICTURE",
			"CHECK_PERMISSIONS", "PERMISSIONS_BY", "CATALOG_TYPE", "MIN_PERMISSION", "SEARCHABLE_CONTENT",
			"SORT", "TIMESTAMP_X", "DATE_MODIFY_FROM", "DATE_MODIFY_TO", "MODIFIED_USER_ID", "MODIFIED_BY",
			"DATE_CREATE", "CREATED_USER_ID", "CREATED_BY", "DATE_ACTIVE_FROM", "DATE_ACTIVE_TO", "ACTIVE_DATE",
			"ACTIVE_FROM", "ACTIVE_TO", "SECTION_ID");

		$listCustomFields = [];

		$fields = $this->listObject->getFields();

		foreach ($fields as $field)
		{
			if ($field["CODE"] <> '')
			{
				$availableFields[] = "PROPERTY_".$field["CODE"];
			}

			if ($this->isFieldDateType($field["TYPE"]))
			{
				$callback = $field["PROPERTY_USER_TYPE"]["ConvertToDB"];
				$listCustomFields[$field["FIELD_ID"]] = function ($value) use ($callback) {
					$regexDetectsIso8601 = '/^([\+-]?\d{4}(?!\d{2}\b))'
						. '((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?'
						. '|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d'
						. '|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])'
						. '((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d'
						. '([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
					if (preg_match($regexDetectsIso8601, $value) === 1)
					{
						return \CRestUtil::unConvertDateTime($value);
					}
					elseif (is_callable($callback))
					{
						return call_user_func_array($callback, [[], ["VALUE" => $value]]);
					}
					else
					{
						return $value;
					}
				};
			}
		}

		$availableFields = array_merge($availableFields, array_keys($fields));

		return array($availableFields, $listCustomFields);
	}

	private function isEnabledBizproc(string $iblockTypeId): bool
	{
		return (Loader::includeModule("bizproc") && \CLists::isBpFeatureEnabled($iblockTypeId));
	}

	private function setUrlTemplate()
	{
		if (!empty($this->params["LIST_ELEMENT_URL"]))
		{
			$this->listObject->actualizeDocumentAdminPage(str_replace(
				["#list_id#", "#group_id#"],
				[$this->iblockId, $this->params["SOCNET_GROUP_ID"]],
				$this->params["LIST_ELEMENT_URL"])
			);
		}
	}

	private function validateFields()
	{
		$fields = $this->listObject->getFields();
		foreach ($fields as $fieldId => $fieldData)
		{
			$fieldValue = $this->params["FIELDS"][$fieldId];

			if (
				empty($this->params["FIELDS"][$fieldId])
				&& $fieldData["IS_REQUIRED"] === "Y"
				&& !is_numeric($this->params["FIELDS"][$fieldId])
			)
			{
				$this->errorCollection->setError(
					new Error(
						"The field \"".$fieldData["NAME"]."\" is required",
						self::ERROR_ELEMENT_FIELD_VALUE
					)
				);
			}

			if (!$this->listObject->is_field($fieldId))
			{
				if (!is_array($fieldValue))
				{
					$fieldValue = [$fieldValue];
				}

				switch ($fieldData["TYPE"])
				{
					case "N":
						foreach($fieldValue as $key => $value)
						{
							$value = str_replace(" ", "", str_replace(",", ".", $value));
							if ($value && !is_numeric($value))
							{
								$this->errorCollection->setError(new Error(
									"Value of the \"".$fieldData["NAME"]."\" field is not correct",
									self::ERROR_ELEMENT_FIELD_VALUE)
								);
							}
						}
						break;
				}
			}
		}
	}

	private function getElementFields($elementId, array $values)
	{
		$elementFields = [
			"IBLOCK_ID" => $this->iblockId,
			"CODE" => $this->params["ELEMENT_CODE"],
			"ID" => $elementId,
			"PROPERTY_VALUES" => []
		];

		$fields = $this->listObject->getFields();
		foreach ($fields as $fieldId => $fieldData)
		{
			$fieldValue = $values[$fieldId];

			if ($this->listObject->is_field($fieldId))
			{
				if ($fieldId == "PREVIEW_PICTURE" || $fieldId == "DETAIL_PICTURE")
				{
					$this->setPictureValue($elementFields, $fieldId, $fieldValue, $values);
				}
				elseif ($fieldId == "PREVIEW_TEXT" || $fieldId == "DETAIL_TEXT")
				{
					$this->setTextValue($elementFields, $fieldId, $fieldValue, $fieldData);
				}
				else
				{
					$this->setBaseValue($elementFields, $fieldId, $fieldValue);
				}
			}
			else
			{
				if (!is_array($fieldValue))
				{
					$fieldValue = [$fieldValue];
				}

				switch ($fieldData["TYPE"])
				{
					case "F":
						$this->setFileValue($elementFields, $fieldId, $fieldValue, $fieldData, $values);
						break;
					case "N":
						$this->setIntegerValue($elementFields, $fieldValue, $fieldData);
						break;
					case "S:DiskFile":
						$this->setFileDiskValue($elementFields, $fieldValue, $fieldData);
						break;
					case "S:Date":
						$this->setDateValue($elementFields, $fieldValue, $fieldData);
						break;
					case "S:DateTime":
						$this->setDateTimeValue($elementFields, $fieldValue, $fieldData);
						break;
					case "S:HTML":
						$this->setHtmlValue($elementFields, $fieldValue, $fieldData);
						break;
					default:
						$this->setPropertyValue($elementFields, $fieldValue, $fieldData);
				}
			}
		}

		global $USER;
		if (empty($values["MODIFIED_BY"]) && isset($USER) && is_object($USER))
		{
			$userId = $USER->getID();
			$elementFields["MODIFIED_BY"] = $userId;
		}
		unset($elementFields["TIMESTAMP_X"]);

		$elementFields["IBLOCK_SECTION_ID"] = (
			is_numeric($values['IBLOCK_SECTION_ID'])
			? (int) $values['IBLOCK_SECTION_ID'] : 0
		);

		return $elementFields;
	}

	private function setPictureValue(&$elementFields, $fieldId, $fieldValue, array $values)
	{
		if (intval($fieldValue))
		{
			$elementFields[$fieldId] = \CFile::makeFileArray($fieldValue);
		}
		else
		{
			$elementFields[$fieldId] = \CRestUtil::saveFile($fieldValue);
		}

		if (!empty($values[$fieldId."_DEL"]))
		{
			$elementFields[$fieldId]["del"] = "Y";
		}
	}

	private function setTextValue(&$elementFields, $fieldId, $fieldValue, $fieldData)
	{
		if (is_array($fieldValue))
		{
			$fieldValue = current($fieldValue);
		}

		if (!empty($fieldData["SETTINGS"]["USE_EDITOR"]) && $fieldData["SETTINGS"]["USE_EDITOR"] == "Y")
		{
			$elementFields[$fieldId."_TYPE"] = "html";
		}
		else
		{
			$elementFields[$fieldId."_TYPE"] = "text";
		}

		$elementFields[$fieldId] = $fieldValue;
	}

	private function setBaseValue(&$elementFields, $fieldId, $fieldValue)
	{
		if (is_array($fieldValue))
		{
			$fieldValue = current($fieldValue);
		}

		$elementFields[$fieldId] = $fieldValue;
	}

	private function setFileValue(&$elementFields, $fieldId, array $fieldValue, array $fieldData, array $values)
	{
		if (!empty($values[$fieldId."_DEL"]))
			$delete = $values[$fieldId."_DEL"];
		else
			$delete = [];

		if (!Loader::includeModule("rest"))
		{
			return;
		}

		foreach ($fieldValue as $key => $value)
		{
			if (is_array($value))
			{
				$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$key]["VALUE"] = \CRestUtil::saveFile($value);
			}
			else
			{
				if (is_numeric($value))
				{
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$key]["VALUE"] = \CFile::makeFileArray($value);
				}
				else
				{
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$key]["VALUE"] = \CRestUtil::saveFile($fieldValue);

					break;
				}
			}
		}

		foreach ($delete as $elementPropertyId => $mark)
		{
			if (isset($elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$elementPropertyId]["VALUE"]))
				$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$elementPropertyId]["VALUE"]["del"] = "Y";
			else
				$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$elementPropertyId]["del"] = "Y";

		}
	}

	private function setIntegerValue(&$elementFields, array $fieldValue, $fieldData)
	{
		foreach ($fieldValue as $key => $value)
		{
			$value = str_replace(" ", "", str_replace(",", ".", $value));
			$value = is_numeric($value) ? doubleval($value) : '';
			$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$key]["VALUE"] = $value;
		}
	}

	private function setFileDiskValue(&$elementFields, array $fieldValue, $fieldData)
	{
		foreach ($fieldValue as $key => $value)
		{
			if (is_array($value))
			{
				$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$key]["VALUE"] = $value;
			}
			else
			{
				if (!is_array($elementFields["PROPERTY_VALUES"][$fieldData["ID"]]["VALUE"]))
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]]["VALUE"] = [];
				$elementFields["PROPERTY_VALUES"][$fieldData["ID"]]["VALUE"][] = $value;
			}
		}
	}

	private function setDateValue(&$elementFields, array $fieldValue, $fieldData)
	{
		if (!Loader::includeModule("rest"))
		{
			return;
		}

		foreach ($fieldValue as $key => $value)
		{
			if (is_array($value))
			{
				foreach($value as $k => $v)
				{
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$k]["VALUE"] = \CRestUtil::unConvertDate($v);
				}
			}
			else
			{
				$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$key]["VALUE"] = \CRestUtil::unConvertDate($value);
			}
		}
	}

	private function setDateTimeValue(&$elementFields, array $fieldValue, $fieldData)
	{
		if (!Loader::includeModule("rest"))
		{
			return;
		}

		foreach ($fieldValue as $key => $value)
		{
			if (is_array($value))
			{
				foreach($value as $k => $v)
				{
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$k]["VALUE"] =
						\CRestUtil::unConvertDateTime($v);
				}
			}
			else
			{
				$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$key]["VALUE"] =
					\CRestUtil::unConvertDateTime($value);
			}
		}
	}

	private function setHtmlValue(&$elementFields, array $fieldValue, $fieldData)
	{
		foreach($fieldValue as $key => $value)
		{
			if (!is_array($value))
			{
				$value = [$key => $value];
			}

			foreach($value as $k => $v)
			{
				if (CheckSerializedData($v) && @unserialize($v, ['allowed_classes' => false]))
				{
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$k]["VALUE"] = unserialize($v, ['allowed_classes' => false]);
				}
				else
				{
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$k]["VALUE"]["TYPE"] = "html";
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$k]["VALUE"]["TEXT"] = $v;
				}
			}
		}
	}

	private function setPropertyValue(&$elementFields, array $fieldValue, $fieldData)
	{
		foreach($fieldValue as $key => $value)
		{
			if(is_array($value))
			{
				foreach($value as $k => $v)
					$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$k]["VALUE"] = $v;
			}
			else
			{
				$elementFields["PROPERTY_VALUES"][$fieldData["ID"]][$key]["VALUE"] = $value;
			}
		}
	}

	private function hasBpTemplatesWithAutoStart(int $execType): bool
	{
		$documentType = \BizProcDocument::generateDocumentComplexType(
			$this->params["IBLOCK_TYPE_ID"],
			$this->iblockId
		);

		$bpTemplatesWithAutoStart = \CBPWorkflowTemplateLoader::searchTemplatesByDocumentType(
			$documentType,
			$execType
		);

		foreach ($bpTemplatesWithAutoStart as $template)
		{
			if (!\CBPWorkflowTemplateLoader::isConstantsTuned($template["ID"]))
			{
				$this->errorCollection->setError(
					new Error("Workflow constants need to be configured", self::ERROR_ADD_ELEMENT)
				);
			}
		}

		return !empty($bpTemplatesWithAutoStart);
	}

	private function startBp(int $elementId, int $execType, $changedElementFields = []): void
	{
		$documentType = \BizprocDocument::generateDocumentComplexType(
			$this->params["IBLOCK_TYPE_ID"],
			$this->iblockId
		);
		$documentId = \BizProcDocument::getDocumentComplexId(
			$this->params["IBLOCK_TYPE_ID"],
			$elementId
		);
		$documentStates = \CBPWorkflowTemplateLoader::getDocumentTypeStates(
			$documentType,
			$execType
		);

		if (is_array($documentStates) && !empty($documentStates))
		{
			global $USER;

			$userId = $USER->getID();

			$currentUserGroups = $USER->getUserGroupArray();
			if ($this->params["CREATED_BY"] == $userId)
			{
				$currentUserGroups[] = "author";
			}

			if ($execType === \CBPDocumentEventType::Create)
			{
				$canWrite = \CBPDocument::canUserOperateDocumentType(
					\CBPCanUserOperateOperation::WriteDocument,
					$userId,
					$documentType,
					[
						"AllUserGroups" => $currentUserGroups,
						"DocumentStates" => $documentStates
					]
				);
			}
			else
			{
				$canWrite = \CBPDocument::canUserOperateDocument(
					\CBPCanUserOperateOperation::WriteDocument,
					$userId,
					$documentId,
					[
						"AllUserGroups" => $currentUserGroups,
						"DocumentStates" => $documentStates
					]
				);
			}

			if (!$canWrite)
			{
				$this->errorCollection->setError(
					new Error("You do not have enough permissions to edit this record in its current bizproc state")
				);

				return;
			}

			$errors = [];

			foreach ($documentStates as $documentState)
			{
				$parameters = \CBPDocument::startWorkflowParametersValidate(
					$documentState["TEMPLATE_ID"],
					$documentState["TEMPLATE_PARAMETERS"],
					$documentType,
					$errors
				);

				\CBPDocument::startWorkflow(
					$documentState["TEMPLATE_ID"],
					\BizProcDocument::getDocumentComplexId($this->params["IBLOCK_TYPE_ID"], $elementId),
					array_merge(
						$parameters,
						[
							\CBPDocument::PARAM_TAGRET_USER => "user_" . intval($userId),
							\CBPDocument::PARAM_MODIFIED_DOCUMENT_FIELDS => $changedElementFields,
						]
					),
					$errors
				);
			}

			foreach($errors as $message)
			{
				$this->errorCollection->setError(new Error($message));
			}
		}
	}

	private function getElementData()
	{
		$elementSelect = ["ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "CREATED_BY", "BP_PUBLISHED", "CODE"];
		$elementFields = [];
		$elementProperty = [];

		$fields = $this->listObject->getFields();
		$propertyFields = [];
		foreach ($fields as $fieldId => $field)
		{
			if ($this->listObject->is_field($fieldId))
				$elementSelect[] = $fieldId;
			else
				$propertyFields[] = $fieldId;

			if ($fieldId == "CREATED_BY")
				$elementSelect[] = "CREATED_USER_NAME";
			if ($fieldId == "MODIFIED_BY")
				$elementSelect[] = "USER_NAME";
		}

		$filter = [
			"IBLOCK_TYPE" => $this->params["IBLOCK_TYPE_ID"],
			"IBLOCK_ID" => $this->iblockId,
			"ID" => $this->elementId,
			"CHECK_PERMISSIONS" => "N"
		];
		$queryObject = \CIBlockElement::getList([], $filter, false, false, $elementSelect);
		if ($result = $queryObject->fetch())
		{
			$elementFields = $result;

			if (!empty($propertyFields))
			{
				$queryProperty = \CIBlockElement::getProperty(
					$this->iblockId,
					$result["ID"], "SORT", "ASC",
					array("ACTIVE"=>"Y", "EMPTY"=>"N")
				);
				while ($property = $queryProperty->fetch())
				{
					$propertyId = $property["ID"];
					if (!array_key_exists($propertyId, $elementProperty))
					{
						$elementProperty[$propertyId] = $property;
						unset($elementProperty[$propertyId]["DESCRIPTION"]);
						unset($elementProperty[$propertyId]["VALUE_ENUM_ID"]);
						unset($elementProperty[$propertyId]["VALUE_ENUM"]);
						unset($elementProperty[$propertyId]["VALUE_XML_ID"]);
						$elementProperty[$propertyId]["FULL_VALUES"] = [];
						$elementProperty[$propertyId]["VALUES_LIST"] = [];
					}
					$elementProperty[$propertyId]["FULL_VALUES"][$property["PROPERTY_VALUE_ID"]] = [
						"VALUE" => $property["VALUE"],
						"DESCRIPTION" => $property["DESCRIPTION"],
					];
					$elementProperty[$propertyId]["VALUES_LIST"][$property["PROPERTY_VALUE_ID"]] = $property["VALUE"];
				}
			}
		}

		return [$elementSelect, $elementFields, $elementProperty];
	}

	private function getElements($navData)
	{
		$elements = [];

		$fields = $this->listObject->getFields();

		$elementSelect = ["ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "CREATED_BY", "BP_PUBLISHED", "CODE"];
		$propertyFields = [];
		$ignoreSortFields = ["S:Money", "PREVIEW_TEXT", "DETAIL_TEXT", "S:ECrm", "S:map_yandex", "PREVIEW_PICTURE",
			"DETAIL_PICTURE", "S:DiskFile", "IBLOCK_SECTION_ID", "BIZPROC", "COMMENTS"];

		$availableFieldsIdForSort = ["ID"];
		foreach ($fields as $fieldId => $field)
		{
			if ($this->listObject->is_field($fieldId))
				$elementSelect[] = $fieldId;
			else
				$propertyFields[] = $fieldId;

			if ($fieldId == "CREATED_BY")
				$elementSelect[] = "CREATED_USER_NAME";
			if ($fieldId == "MODIFIED_BY")
				$elementSelect[] = "USER_NAME";

			if (!($field["MULTIPLE"] == "Y" || in_array($field["TYPE"], $ignoreSortFields)))
			{
				$availableFieldsIdForSort[] = $fieldId;
			}
		}

		$order = $this->getOrder($availableFieldsIdForSort);

		$filter = [
			"=IBLOCK_TYPE" => $this->params["IBLOCK_TYPE_ID"],
			"IBLOCK_ID" => $this->iblockId,
			"ID" => $this->params["ELEMENT_ID"] ? $this->params["ELEMENT_ID"] : "",
			"=CODE" => $this->params["ELEMENT_CODE"] ? $this->params["ELEMENT_CODE"] : "",
			"SHOW_NEW" => (!empty($this->params["CAN_FULL_EDIT"]) && $this->params["CAN_FULL_EDIT"] == "Y" ? "Y" : "N"),
			"CHECK_PERMISSIONS" => "Y"
		];
		$filter = $this->getInputFilter($filter);
		$queryObject = \CIBlockElement::getList($order, $filter, false, $navData, $elementSelect);
		while ($result = $queryObject->fetch())
		{
			$elements[$result["ID"]] = $result;

			if (!empty($propertyFields))
			{
				$queryProperty = \CIBlockElement::getProperty(
					$this->iblockId,
					$result["ID"], "SORT", "ASC",
					array("ACTIVE" => "Y", "EMPTY" => "N")
				);
				while ($property = $queryProperty->fetch())
				{
					$propertyId = $property["ID"];
					$elements[$result["ID"]]["PROPERTY_".$propertyId][
						$property["PROPERTY_VALUE_ID"]] = $property["VALUE"];
				}
			}
		}

		return array($elements, $queryObject);
	}

	private function getOrder($availableFieldsIdForSort)
	{
		$order = [];

		if (is_array($this->params["ELEMENT_ORDER"]))
		{

			$orderList = ["nulls,asc", "asc,nulls", "nulls,desc", "desc,nulls", "asc", "desc"];
			foreach ($this->params["ELEMENT_ORDER"] as $fieldId => $orderParam)
			{
				$orderParam = mb_strtolower($orderParam);
				if (!in_array($orderParam, $orderList) || !in_array($fieldId, $availableFieldsIdForSort))
				{
					continue;
				}
				$order[$fieldId] = $orderParam;
			}
		}

		if (empty($order))
		{
			$order = ["ID" => "asc"];
		}

		return $order;
	}

	private function getInputFilter(array $filter)
	{
		if (is_array($this->params["FILTER"]))
		{
			foreach ($this->resultSanitizeFilter as $key => $value)
			{
				$key = str_replace(["ACTIVE_FROM", "ACTIVE_TO"], ["DATE_ACTIVE_FROM", "DATE_ACTIVE_TO"], $key);
				$filter[$key] = $value;
			}
		}

		return $filter;
	}

	private function isFieldDateType($type)
	{
		return (in_array($type, ["DATE_CREATE", "TIMESTAMP_X", "DATE_MODIFY_FROM", "DATE_MODIFY_TO", "ACTIVE_DATE",
			"S:Date", "S:DateTime", "DATE_ACTIVE_FROM", "DATE_ACTIVE_TO", "ACTIVE_FROM", "ACTIVE_TO"]));
	}

	private function getSefFolder()
	{
		$defaultSefFolder = [
			"lists" => "/company/lists/",
			"lists_socnet" => "/workgroups/group/#group_id#/lists/",
			"bitrix_processes" => "/bizproc/processes/",
		];

		if (!empty($this->params["SEF_FOLDER"]))
		{
			$sefFolder = $this->params["SEF_FOLDER"];
		}
		elseif (!empty($defaultSefFolder[$this->params["IBLOCK_TYPE_ID"]]))
		{
			$sefFolder = $defaultSefFolder[$this->params["IBLOCK_TYPE_ID"]];
		}
		else
		{
			$sefFolder = $defaultSefFolder["lists"];
		}

		return $sefFolder;
	}
}