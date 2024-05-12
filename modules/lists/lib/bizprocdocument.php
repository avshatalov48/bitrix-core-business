<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('bizproc') || !Loader::includeModule('iblock'))
{
	return;
}

class BizprocDocument extends CIBlockDocument
{
	const DOCUMENT_TYPE_PREFIX = 'iblock_';
	private static $cachedTasks;
	private static $elements = [];

	public static function getEntityName()
	{
		return Loc::getMessage('LISTS_BIZPROC_ENTITY_NAME');
	}

	/**
	 * @param $iblockId
	 * @return string
	 */
	public static function generateDocumentType($iblockId)
	{
		$iblockId = (int)$iblockId;
		return self::DOCUMENT_TYPE_PREFIX . $iblockId;
	}

	/**
	 * @param $iblockType
	 * @param $iblockId
	 * @return array
	 */
	public static function generateDocumentComplexType($iblockType, $iblockId)
	{
		if($iblockType == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
			return array('lists', get_called_class(), self::generateDocumentType($iblockId));
		else
			return array('lists', 'Bitrix\Lists\BizprocDocumentLists', self::generateDocumentType($iblockId));
	}

	/**
	 * @param $iblockType
	 * @param $documentId
	 * @return array
	 */
	public static function getDocumentComplexId($iblockType, $documentId)
	{
		if($iblockType == COption::getOptionString("lists", "livefeed_iblock_type_id"))
			return array('lists', get_called_class(), $documentId);
		else
			return array('lists', 'Bitrix\Lists\BizprocDocumentLists', $documentId);
	}

	/**
	 * @param $iblockId
	 */
	public static function deleteDataIblock($iblockId)
	{
		$iblockId = intval($iblockId);
		$documentType = array('lists', get_called_class(), self::generateDocumentType($iblockId));
		$errors = array();
		$templateObject = CBPWorkflowTemplateLoader::getList(
			array('ID' => 'DESC'),
			array('DOCUMENT_TYPE' => $documentType),
			false,
			false,
			array('ID')
		);
		while($template = $templateObject->fetch())
		{
			CBPDocument::deleteWorkflowTemplate($template['ID'], $documentType, $errors);
		}
	}

	/**
	 * Method returns document icon (image source path)
	 * @param $documentId
	 * @return null|string
	 * @throws CBPArgumentNullException
	 */
	public static function getDocumentIcon($documentId)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException('documentId');

		$db = CIBlockElement::getList(
			array(),
			array('ID' => $documentId, 'SHOW_NEW'=>'Y', 'SHOW_HISTORY' => 'Y'),
			false,
			false,
			array('ID', 'IBLOCK_ID')
		);
		if ($element = $db->fetch())
		{
			$iblockPicture = CIBlock::getArrayByID($element['IBLOCK_ID'], 'PICTURE');
			$imageFile = CFile::getFileArray($iblockPicture);
			if(!empty($imageFile['SRC']))
				return $imageFile['SRC'];
		}

		return null;
	}

	/**
	 * @param $documentId
	 * @return array
	 * @throws CBPArgumentNullException
	 * @throws CBPArgumentOutOfRangeException
	 * @throws Exception
	 */
	public static function getDocument($documentId)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
		{
			throw new CBPArgumentNullException('documentId');
		}

		$result = [];
		$element = [];
		$elementProperty = [];

		$queryElement = CIBlockElement::getList(
			[],
			['ID' => $documentId, 'SHOW_NEW' => 'Y', 'SHOW_HISTORY' => 'Y']
		);
		while ($queryResult = $queryElement->fetch())
		{
			$element = $queryResult;
			$queryProperty = CIBlockElement::getProperty(
				$queryResult['IBLOCK_ID'],
				$queryResult['ID'],
				['sort' => 'asc', 'id' => 'asc', 'enum_sort' => 'asc', 'value_id' => 'asc'],
				['ACTIVE' => 'Y', 'EMPTY' => 'N']
			);
			while ($property = $queryProperty->fetch())
			{
				$propertyKey = 'PROPERTY_' . $property['ID'];
				if ($property['MULTIPLE'] == 'Y')
				{
					if (!array_key_exists($propertyKey, $elementProperty))
					{
						$elementProperty[$propertyKey] = $property;
						$elementProperty[$propertyKey]['VALUE'] = [];
					}
					$elementProperty[$propertyKey]['VALUE'][] = $property['VALUE'];
				}
				else
				{
					$elementProperty[$propertyKey] = $property;
				}
			}
		}

		foreach ($element as $fieldId => $fieldValue)
		{
			$result[$fieldId] = $fieldValue;
			if (in_array($fieldId, ['MODIFIED_BY', 'CREATED_BY']))
			{
				$result[$fieldId] = 'user_' . $fieldValue;
				$result[$fieldId . '_PRINTABLE'] =
					$element[($fieldId == 'MODIFIED_BY')
						? 'USER_NAME'
						: 'CREATED_USER_NAME']
				;
			}
			elseif (in_array($fieldId, ['PREVIEW_TEXT', 'DETAIL_TEXT']))
			{
				if ($element[$fieldId . '_TYPE'] == 'html')
				{
					$result[$fieldId] = HTMLToTxt($fieldValue);
				}
			}
		}
		foreach ($elementProperty as $propertyId => $property)
		{
			if (trim($property['CODE']) <> '')
			{
				$propertyId = $property['CODE'];
			}
			else
			{
				$propertyId = $property['ID'];
			}

			if (!empty($property['USER_TYPE']))
			{
				if (
					$property['USER_TYPE'] == 'UserID'
					|| $property['USER_TYPE'] == 'employee'
					&& (COption::getOptionString('bizproc', 'employee_compatible_mode', 'N') != 'Y')
				)
				{
					if (empty($property['VALUE']))
					{
						continue;
					}
					if (!is_array($property['VALUE']))
					{
						$property['VALUE'] = [$property['VALUE']];
					}

					$listUsers = implode(' | ', $property['VALUE']);
					$userQuery = CUser::getList(
						'ID',
						'ASC',
						['ID' => $listUsers],
						[
							'FIELDS' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME'],
						]
					);
					while ($user = $userQuery->fetch())
					{
						if ($property['MULTIPLE'] == 'Y')
						{
							$result = self::setArray($result, 'PROPERTY_' . $propertyId);
							$result = self::setArray($result, 'PROPERTY_' . $propertyId . '_PRINTABLE');
							$result['PROPERTY_' . $propertyId][] = 'user_' . intval($user['ID']);
							$result['PROPERTY_' . $propertyId . '_PRINTABLE'][] = '(' . $user['LOGIN'] . ')' .
								(($user['NAME'] <> '' || $user['LAST_NAME'] <> '') ? ' ' : '') . $user['NAME'] .
								(($user['NAME'] <> '' && $user['LAST_NAME'] <> '') ? ' ' : '') . $user['LAST_NAME'];
						}
						else
						{
							$result['PROPERTY_' . $propertyId] = 'user_' . intval($user['ID']);
							$result['PROPERTY_' . $propertyId . '_PRINTABLE'] = '(' . $user['LOGIN'] . ')' .
								(($user['NAME'] <> '' || $user['LAST_NAME'] <> '') ? ' ' : '') . $user['NAME'] .
								(($user['NAME'] <> '' && $user['LAST_NAME'] <> '') ? ' ' : '') . $user['LAST_NAME'];
						}
					}
				}
				elseif ($property['USER_TYPE'] == 'DiskFile')
				{
					$diskValues = current($property['VALUE']);
					$userType = \CIBlockProperty::getUserType($property['USER_TYPE']);
					if (is_array($diskValues))
					{
						$result = self::setArray($result, 'PROPERTY_' . $propertyId);
						$result = self::setArray($result, 'PROPERTY_' . $propertyId . '_PRINTABLE');
						foreach ($diskValues as $attachedId)
						{
							$fileId = null;
							if (array_key_exists('GetObjectId', $userType))
							{
								$fileId = call_user_func_array($userType['GetObjectId'], [$attachedId]);
							}
							if (!$fileId)
							{
								continue;
							}
							$printableUrl = '';
							if (array_key_exists('GetUrlAttachedFileElement', $userType))
							{
								$printableUrl = call_user_func_array(
									$userType['GetUrlAttachedFileElement'],
									[$documentId, $fileId]
								);
							}

							$result['PROPERTY_' . $propertyId][$attachedId] = $fileId;
							$result['PROPERTY_' . $propertyId . '_PRINTABLE'][$attachedId] = $printableUrl;
						}
					}
					else
					{
						continue;
					}
				}
				elseif ($property['USER_TYPE'] == 'HTML')
				{
					if (\CBPHelper::isAssociativeArray($property['VALUE']))
					{
						if ($property['VALUE']['TYPE'] == 'HTML')
						{
							$result['PROPERTY_' . $propertyId] = HTMLToTxt($property['VALUE']['TEXT']);
						}
						else
						{
							$result['PROPERTY_' . $propertyId] = $property['VALUE']['TEXT'];
						}
					}
					else
					{
						$result = self::setArray($result, 'PROPERTY_' . $propertyId);
						foreach ($property['VALUE'] as $htmlValue)
						{
							if ($htmlValue['TYPE'] == 'HTML')
							{
								$result['PROPERTY_' . $propertyId][] = HTMLToTxt($htmlValue['TEXT']);
							}
							else
							{
								$result['PROPERTY_' . $propertyId][] = $htmlValue['TEXT'];
							}
						}
					}
				}
				elseif ($property['USER_TYPE'] == 'Money')
				{
					$userType = \CIBlockProperty::getUserType($property['USER_TYPE']);
					if (is_array($property['VALUE']))
					{
						$result = self::setArray($result, 'PROPERTY_' . $propertyId);
						$result = self::setArray($result, 'PROPERTY_' . $propertyId . '_PRINTABLE');
						foreach ($property['VALUE'] as $moneyValue)
						{
							$result['PROPERTY_' . $propertyId][] = $moneyValue;
							if (array_key_exists('GetPublicViewHTML', $userType))
							{
								$result['PROPERTY_' . $propertyId . '_PRINTABLE'][] = call_user_func_array(
									$userType['GetPublicViewHTML'],
									[$property, ['VALUE' => $moneyValue], []]
								);
							}
						}
					}
					else
					{
						$result['PROPERTY_' . $propertyId] = $property['VALUE'];
						if (array_key_exists('GetPublicViewHTML', $userType))
						{
							$result['PROPERTY_' . $propertyId . '_PRINTABLE'] = call_user_func_array(
								$userType['GetPublicViewHTML'],
								[$property, ['VALUE' => $property['VALUE']], []]
							);
						}
					}
				}
				else
				{
					$result['PROPERTY_' . $propertyId] = $property['VALUE'];
				}
			}
			elseif ($property['PROPERTY_TYPE'] == 'L')
			{
				$result = self::setArray($result, 'PROPERTY_' . $propertyId);
				//$result = self::setArray($result, 'PROPERTY_'.$propertyId.'_PRINTABLE');
				$propertyArray = [];
				$propertyKeyArray = [];
				if (!is_array($property['VALUE']))
				{
					$property['VALUE'] = [$property['VALUE']];
				}
				foreach ($property['VALUE'] as $enumId)
				{
					$enumsObject = CIBlockProperty::getPropertyEnum(
						$property['ID'],
						['SORT' => 'asc'],
						['ID' => $enumId]
					);
					while ($enums = $enumsObject->fetch())
					{
						$propertyArray[] = $enums['VALUE'];
						$propertyKeyArray[] = $enums['XML_ID'];
					}
				}
				for ($i = 0, $cnt = count($propertyArray); $i < $cnt; $i++)
				{
					$result['PROPERTY_' . $propertyId][$propertyKeyArray[$i]] = $propertyArray[$i];
				}
			}
			elseif ($property['PROPERTY_TYPE'] == 'F')
			{
				$result = self::setArray($result, 'PROPERTY_' . $propertyId);
				$result = self::setArray($result, 'PROPERTY_' . $propertyId . '_PRINTABLE');
				$propertyArray = $property['VALUE'];
				if (!is_array($propertyArray))
				{
					$propertyArray = [$propertyArray];
				}

				foreach ($propertyArray as $v)
				{
					$fileArray = \CFile::getFileArray($v);
					if ($fileArray)
					{
						$result['PROPERTY_' . $propertyId][] = intval($v);
						$result['PROPERTY_' . $propertyId . '_PRINTABLE'][] =
							"[url=/bitrix/tools/bizproc_show_file.php?f=" .
							urlencode($fileArray["FILE_NAME"]) . "&i=" . $v . "&h=" . md5($fileArray["SUBDIR"]) . "]" .
							htmlspecialcharsbx($fileArray["ORIGINAL_NAME"]) . "[/url]";
					}
				}
			}
			else
			{
				$result['PROPERTY_' . $propertyId] = $property['VALUE'];
			}
		}

		if (!empty($result))
		{
			$documentFields = static::getDocumentFields(static::getDocumentType($documentId));
			foreach ($documentFields as $fieldKey => $field)
			{
				if (!array_key_exists($fieldKey, $result))
				{
					$result[$fieldKey] = null;
				}
			}
		}

		return $result;
	}

	protected static function setArray(array $result, $value)
	{
		if (!isset($result[$value]) || !is_array($result[$value]))
		{
			$result[$value] = array();
		}
		return $result;
	}

	protected static function getSystemIblockFields()
	{
		$result = array(
			"ID" => array(
				"Name" => GetMessage("IBD_FIELD_ID"),
				"Type" => "int",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"TIMESTAMP_X" => array(
				"Name" => GetMessage("IBD_FIELD_TIMESTAMP_X"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"MODIFIED_BY" => array(
				"Name" => GetMessage("IBD_FIELD_MODYFIED"),
				"Type" => "user",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"MODIFIED_BY_PRINTABLE" => array(
				"Name" => GetMessage("IBD_FIELD_MODIFIED_BY_USER_PRINTABLE"),
				"Type" => "string",
				"Filterable" => false,
				"Editable" => false,
				"Required" => false,
			),
			"DATE_CREATE" => array(
				"Name" => GetMessage("IBD_FIELD_DATE_CREATE"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"CREATED_BY" => array(
				"Name" => GetMessage("IBD_FIELD_CREATED"),
				"Type" => "user",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"CREATED_BY_PRINTABLE" => array(
				"Name" => GetMessage("IBD_FIELD_CREATED_BY_USER_PRINTABLE"),
				"Type" => "string",
				"Filterable" => false,
				"Editable" => false,
				"Required" => false,
			),
			"IBLOCK_ID" => array(
				"Name" => GetMessage("IBD_FIELD_IBLOCK_ID"),
				"Type" => "int",
				"Filterable" => true,
				"Editable" => true,
				"Required" => true,
			),
			"ACTIVE" => array(
				"Name" => GetMessage("IBD_FIELD_ACTIVE"),
				"Type" => "bool",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"BP_PUBLISHED" => array(
				"Name" => GetMessage("IBD_FIELD_BP_PUBLISHED"),
				"Type" => "bool",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"ACTIVE_FROM" => array(
				"Name" => GetMessage("IBD_FIELD_DATE_ACTIVE_FROM"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"ACTIVE_TO" => array(
				"Name" => GetMessage("IBD_FIELD_DATE_ACTIVE_TO"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"SORT" => array(
				"Name" => GetMessage("IBD_FIELD_SORT"),
				"Type" => "int",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"NAME" => array(
				"Name" => GetMessage("IBD_FIELD_NAME"),
				"Type" => "string",
				"Filterable" => true,
				"Editable" => true,
				"Required" => true,
			),
			"PREVIEW_PICTURE" => array(
				"Name" => GetMessage("IBD_FIELD_PREVIEW_PICTURE"),
				"Type" => "file",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"PREVIEW_TEXT" => array(
				"Name" => GetMessage("IBD_FIELD_PREVIEW_TEXT"),
				"Type" => "text",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"PREVIEW_TEXT_TYPE" => array(
				"Name" => GetMessage("IBD_FIELD_PREVIEW_TEXT_TYPE"),
				"Type" => "select",
				"Options" => array(
					"text" => GetMessage("IBD_DESC_TYPE_TEXT"),
					"html" => "Html",
				),
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"DETAIL_PICTURE" => array(
				"Name" => GetMessage("IBD_FIELD_DETAIL_PICTURE"),
				"Type" => "file",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"DETAIL_TEXT" => array(
				"Name" => GetMessage("IBD_FIELD_DETAIL_TEXT"),
				"Type" => "text",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"DETAIL_TEXT_TYPE" => array(
				"Name" => GetMessage("IBD_FIELD_DETAIL_TEXT_TYPE"),
				"Type" => "select",
				"Options" => array(
					"text" => GetMessage("IBD_DESC_TYPE_TEXT"),
					"html" => "Html",
				),
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"CODE" => array(
				"Name" => GetMessage("IBD_FIELD_CODE"),
				"Type" => "string",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"XML_ID" => array(
				"Name" => GetMessage("IBD_FIELD_XML_ID"),
				"Type" => "string",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
		);

		$keys = array_keys($result);
		foreach ($keys as $key)
		{
			$result[$key]["Multiple"] = false;
			$result[$key]["active"] = false;
		}

		return $result;
	}

	/**
	 * @param string $documentType
	 * @return array
	 * @throws CBPArgumentOutOfRangeException
	 */
	public static function getDocumentFields($documentType)
	{
		$iblockId = intval(mb_substr($documentType, mb_strlen("iblock_")));
		if ($iblockId <= 0)
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);

		$documentFieldTypes = self::getDocumentFieldTypes($documentType);

		$result = self::getSystemIblockFields();

		$dbProperties = CIBlockProperty::getList(
			array("sort" => "asc", "name" => "asc"),
			array("IBLOCK_ID" => $iblockId, 'ACTIVE' => 'Y')
		);
		$ignoreProperty = array();
		while ($property = $dbProperties->fetch())
		{
			if (trim($property["CODE"]) <> '')
			{
				$key = "PROPERTY_".$property["CODE"];
				$ignoreProperty["PROPERTY_".$property["ID"]] = "PROPERTY_".$property["CODE"];
			}
			else
			{
				$key = "PROPERTY_".$property["ID"];
				$ignoreProperty["PROPERTY_".$property["ID"]] = 0;
			}

			$result[$key] = array(
				"Name" => $property["NAME"],
				"Filterable" => ($property["FILTRABLE"] == "Y"),
				"Editable" => true,
				"Required" => ($property["IS_REQUIRED"] == "Y"),
				"Multiple" => ($property["MULTIPLE"] == "Y"),
				"TypeReal" => $property["PROPERTY_TYPE"],
				"UserTypeSettings" => $property["USER_TYPE_SETTINGS"]
			);

			if(trim($property["CODE"]) <> '')
				$result[$key]["Alias"] = "PROPERTY_".$property["ID"];

			if ($property["USER_TYPE"] <> '')
			{
				$result[$key]["TypeReal"] = $property["PROPERTY_TYPE"].":".$property["USER_TYPE"];

				if ($property["USER_TYPE"] == "UserID"
					|| $property["USER_TYPE"] == "employee" && (COption::getOptionString("bizproc", "employee_compatible_mode", "N") != "Y"))
				{
					$result[$key]["Type"] = "user";
					$result[$key."_PRINTABLE"] = array(
						"Name" => $property["NAME"].GetMessage("IBD_FIELD_USERNAME_PROPERTY"),
						"Filterable" => false,
						"Editable" => false,
						"Required" => false,
						"Multiple" => ($property["MULTIPLE"] == "Y"),
						"Type" => "string",
					);
					$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
				}
				elseif ($property["USER_TYPE"] == "DateTime")
				{
					$result[$key]["Type"] = "datetime";
					$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
				}
				elseif ($property["USER_TYPE"] == "Date")
				{
					$result[$key]["Type"] = "date";
					$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
				}
				elseif ($property["USER_TYPE"] == "EList")
				{
					$result[$key]["Type"] = "E:EList";
					$result[$key]["Options"] = $property["LINK_IBLOCK_ID"];
				}
				elseif ($property["USER_TYPE"] == "ECrm")
				{
					$result[$key]["Type"] = "E:ECrm";
					$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
					$result[$key]["Options"] = $property["USER_TYPE_SETTINGS"];
				}
				elseif ($property["USER_TYPE"] == "Money")
				{
					$result[$key]["Type"] = "S:Money";
					$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
					$result[$key."_PRINTABLE"] = array(
						"Name" => $property["NAME"].GetMessage("IBD_FIELD_USERNAME_PROPERTY"),
						"Filterable" => false,
						"Editable" => false,
						"Required" => false,
						"Multiple" => ($property["MULTIPLE"] == "Y"),
						"Type" => "string",
					);
				}
				elseif ($property["USER_TYPE"] == "Sequence")
				{
					$result[$key]["Type"] = "N:Sequence";
					$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
					$result[$key]["Options"] = $property["USER_TYPE_SETTINGS"];
				}
				elseif ($property["USER_TYPE"] == "DiskFile")
				{
					$result[$key]["Type"] = "S:DiskFile";
					$result[$key."_PRINTABLE"] = array(
						"Name" => $property["NAME"].GetMessage("IBD_FIELD_USERNAME_PROPERTY"),
						"Filterable" => false,
						"Editable" => false,
						"Required" => false,
						"Multiple" => ($property["MULTIPLE"] == "Y"),
						"Type" => "int",
					);
				}
				elseif ($property["USER_TYPE"] == "HTML")
				{
					$result[$key]["Type"] = "S:HTML";
					$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
				}
				else
				{
					$result[$key]["Type"] = "string";
					$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
				}
			}
			elseif ($property["PROPERTY_TYPE"] == "L")
			{
				$result[$key]["Type"] = "select";

				$result[$key]["Options"] = array();
				$dbPropertyEnums = CIBlockProperty::getPropertyEnum($property["ID"]);
				while ($propertyEnum = $dbPropertyEnums->getNext())
				{
					$result[$key]["Options"][$propertyEnum["XML_ID"]] = $propertyEnum["~VALUE"];
					if($propertyEnum["DEF"] == "Y")
						$result[$key]["DefaultValue"] = $propertyEnum["~VALUE"];
				}
			}
			elseif ($property["PROPERTY_TYPE"] == "N")
			{
				$result[$key]["Type"] = "double";
				$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
			}
			elseif ($property["PROPERTY_TYPE"] == "F")
			{
				$result[$key]["Type"] = "file";
				$result[$key."_PRINTABLE"] = array(
					"Name" => $property["NAME"].GetMessage("IBD_FIELD_USERNAME_PROPERTY"),
					"Filterable" => false,
					"Editable" => false,
					"Required" => false,
					"Multiple" => ($property["MULTIPLE"] == "Y"),
					"Type" => "string",
				);
			}
			elseif ($property["PROPERTY_TYPE"] == "S")
			{
				$result[$key]["Type"] = "string";
				$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
			}
			elseif ($property["PROPERTY_TYPE"] == "E")
			{
				$result[$key]["Type"] = "E:EList";
				$result[$key]["Options"] = $property["LINK_IBLOCK_ID"];
				$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
			}
			else
			{
				$result[$key]["Type"] = "string";
				$result[$key]["DefaultValue"] = $property["DEFAULT_VALUE"];
			}
		}

		$list = new CList($iblockId);
		$fields = $list->getFields();
		foreach($fields as $fieldId => $field)
		{
			if(empty($field["SETTINGS"]))
				$field["SETTINGS"] = array("SHOW_ADD_FORM" => 'Y', "SHOW_EDIT_FORM"=>'Y');

			if(array_key_exists($fieldId, $ignoreProperty))
			{
				$ignoreProperty[$fieldId] ? $key = $ignoreProperty[$fieldId] : $key = $fieldId;
				$result[$key]["sort"] =  $field["SORT"];
				$result[$key]["settings"] = $field["SETTINGS"];
				$result[$key]["active"] = true;
				$result[$key]["DefaultValue"] = $field["DEFAULT_VALUE"];
				if($field["ROW_COUNT"] && $field["COL_COUNT"])
				{
					$result[$key]["row_count"] = $field["ROW_COUNT"];
					$result[$key]["col_count"] = $field["COL_COUNT"];
				}
			}
			else
			{
				$result[$fieldId] = array(
					"Name" => $field['NAME'],
					"Filterable" => !empty($result[$fieldId]['Filterable']) ? $result[$fieldId]['Filterable'] : false,
					"Editable" => !empty($result[$fieldId]['Editable']) ? $result[$fieldId]['Editable'] : true,
					"Required" => ($field['IS_REQUIRED'] == 'Y'),
					"Multiple" => ($field['MULTIPLE'] == 'Y'),
					"Type" => !empty($result[$fieldId]['Type']) ? $result[$fieldId]['Type'] : $field['TYPE'],
					"sort" => $field["SORT"],
					"settings" => $field["SETTINGS"],
					"active" => true,
					"active_type" => $field['TYPE'],
					"DefaultValue" => $field["DEFAULT_VALUE"],
				);
				if(isset($field['ROW_COUNT'], $field['COL_COUNT']) && $field["ROW_COUNT"] && $field["COL_COUNT"])
				{
					$result[$fieldId]["row_count"] = $field["ROW_COUNT"];
					$result[$fieldId]["col_count"] = $field["COL_COUNT"];
				}
			}
		}

		$keys = array_keys($result);
		foreach ($keys as $k)
		{
			$result[$k]["BaseType"] = $documentFieldTypes[$result[$k]["Type"]]["BaseType"];
			$result[$k]["Complex"] = $documentFieldTypes[$result[$k]["Type"]]["Complex"] ?? null;
		}

		return $result;
	}

	/**
	 * @param int $integerCode
	 * @return string
	 */
	public static function generateMnemonicCode($integerCode = 0)
	{
		$code = '';
		for ($i = 1; $integerCode >= 0 && $i < 10; $i++)
		{
			$code = chr(0x41 + ($integerCode % pow(26, $i) / pow(26, $i - 1))) . $code;
			$integerCode -= pow(26, $i);
		}
		return $code;
	}

	/**
	 * @param $documentType
	 * @param $fields
	 * @return bool|string
	 * @throws CBPArgumentOutOfRangeException
	 */
	public static function addDocumentField($documentType, $fields)
	{
		$iblockId = intval(mb_substr($documentType, mb_strlen("iblock_")));
		if ($iblockId <= 0)
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);

		if (mb_substr($fields["code"], 0, mb_strlen("PROPERTY_")) == "PROPERTY_")
			$fields["code"] = mb_substr($fields["code"], mb_strlen("PROPERTY_"));

		if(!empty($fields["active_type"]))
			$fields["type"] = $fields["active_type"];

		$fieldsTemporary = array(
			"NAME" => $fields["name"],
			"ACTIVE" => "Y",
			"SORT" => $fields["sort"] ? $fields["sort"] : 900,
			"CODE" => $fields["code"],
			'MULTIPLE' => $fields['multiple'] == 'Y' || (string)$fields['multiple'] === '1' ? 'Y' : 'N',
			'IS_REQUIRED' => $fields['required'] == 'Y' || (string)$fields['required'] === '1' ? 'Y' : 'N',
			"IBLOCK_ID" => $iblockId,
			"FILTRABLE" => "Y",
			"SETTINGS" => $fields["settings"] ? $fields["settings"] : array("SHOW_ADD_FORM" => 'Y', "SHOW_EDIT_FORM"=>'Y'),
			"DEFAULT_VALUE" => $fields['DefaultValue']
		);

		if (mb_strpos("0123456789", mb_substr($fieldsTemporary["CODE"], 0, 1)) !== false)
			$fieldsTemporary["CODE"] = self::generatePropertyCode($fields["name"], $fields["code"], $iblockId);

		if (array_key_exists("additional_type_info", $fields))
			$fieldsTemporary["LINK_IBLOCK_ID"] = intval($fields["additional_type_info"]);

		if(!empty($fields["UserTypeSettings"]))
			$fieldsTemporary["USER_TYPE_SETTINGS"] = $fields["UserTypeSettings"];

		if(mb_strstr($fields["type"], ":") !== false)
		{
			list($fieldsTemporary["TYPE"], $fieldsTemporary["USER_TYPE"]) = explode(":", $fields["type"], 2);
			if($fields["type"] == "E:EList")
			{
				$fieldsTemporary["LINK_IBLOCK_ID"] = $fields["options"] ?? null;
			}
			elseif($fields["type"] == "E:ECrm")
			{
				$fieldsTemporary["TYPE"] = "S:ECrm";
			}
		}
		elseif ($fields["type"] == "user")
		{
			$fieldsTemporary["TYPE"] = "S:employee";
			$fieldsTemporary["USER_TYPE"]= "UserID";
		}
		elseif ($fields["type"] == "date")
		{
			$fieldsTemporary["TYPE"] = "S:Date";
			$fieldsTemporary["USER_TYPE"]= "Date";
		}
		elseif ($fields["type"] == "datetime")
		{
			$fieldsTemporary["TYPE"] = "S:DateTime";
			$fieldsTemporary["USER_TYPE"]= "DateTime";
		}
		elseif ($fields["type"] == "file")
		{
			$fieldsTemporary["TYPE"] = "F";
			$fieldsTemporary["USER_TYPE"]= "";
		}
		elseif ($fields["type"] == "select")
		{
			$fieldsTemporary["TYPE"] = "L";
			$fieldsTemporary["USER_TYPE"]= false;

			if (is_array($fields["options"]))
			{
				$i = 10;
				foreach ($fields["options"] as $k => $v)
				{
					$def = "N";
					if($fields['DefaultValue'] == $v)
						$def = "Y";
					$fieldsTemporary["VALUES"][] = array("XML_ID" => $k, "VALUE" => $v, "DEF" => $def, "SORT" => $i);
					$i = $i + 10;
				}
			}
			elseif (is_string($fields["options"]) && ($fields["options"] <> ''))
			{
				$a = explode("\n", $fields["options"]);
				$i = 10;
				foreach ($a as $v)
				{
					$v = trim(trim($v), "\r\n");
					if (!$v)
						continue;
					$v1 = $v2 = $v;
					if (mb_substr($v, 0, 1) == "[" && mb_strpos($v, "]") !== false)
					{
						$v1 = mb_substr($v, 1, mb_strpos($v, "]") - 1);
						$v2 = trim(mb_substr($v, mb_strpos($v, "]") + 1));
					}
					$def = "N";
					if($fields['DefaultValue'] == $v2)
						$def = "Y";
					$fieldsTemporary["VALUES"][] = array("XML_ID" => $v1, "VALUE" => $v2, "DEF" => $def, "SORT" => $i);
					$i = $i + 10;
				}
			}
		}
		elseif($fields["type"] == "string")
		{
			$fieldsTemporary["TYPE"] = "S";

			if($fields["row_count"] && $fields["col_count"])
			{
				$fieldsTemporary["ROW_COUNT"] = $fields["row_count"];
				$fieldsTemporary["COL_COUNT"] = $fields["col_count"];
			}
			else
			{
				$fieldsTemporary["ROW_COUNT"] = 1;
				$fieldsTemporary["COL_COUNT"] = 30;
			}
		}
		elseif($fields["type"] == "text")
		{
			$fieldsTemporary["TYPE"] = "S";
			if($fields["row_count"] && $fields["col_count"])
			{
				$fieldsTemporary["ROW_COUNT"] = $fields["row_count"];
				$fieldsTemporary["COL_COUNT"] = $fields["col_count"];
			}
			else
			{
				$fieldsTemporary["ROW_COUNT"] = 4;
				$fieldsTemporary["COL_COUNT"] = 30;
			}
		}
		elseif($fields["type"] == "int" || $fields["type"] == "double")
		{
			$fieldsTemporary["TYPE"] = "N";
		}
		elseif($fields["type"] == "bool")
		{
			$fieldsTemporary["TYPE"] = "L";
			$fieldsTemporary["VALUES"][] = array(
				"XML_ID" => 'Y',
				"VALUE" => GetMessage("BPVDX_YES"),
				"DEF" => "N",
				"SORT" => 10
			);
			$fieldsTemporary["VALUES"][] = array(
				"XML_ID" => 'N',
				"VALUE" => GetMessage("BPVDX_NO"),
				"DEF" => "N",
				"SORT" => 20
			);
		}
		else
		{
			$fieldsTemporary["TYPE"] = $fields["type"];
			$fieldsTemporary["USER_TYPE"] = false;
		}

		$idField = false;
		$properties = CIBlockProperty::getList(
			array(),
			array("IBLOCK_ID" => $fieldsTemporary["IBLOCK_ID"], "CODE" => $fieldsTemporary["CODE"])
		);
		if(!$properties->fetch())
		{
			$listObject = new CList($iblockId);
			$idField = $listObject->addField($fieldsTemporary);
		}

		if($idField)
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->clearByTag("lists_list_".$iblockId);
			if(!empty($fieldsTemporary["CODE"]))
			{
				$idField = mb_substr($idField, 0, mb_strlen("PROPERTY_")).$fieldsTemporary["CODE"];
			}
			return $idField;
		}
		return false;
	}

	/**
	 * @param string $documentType
	 * @param array $fields
	 * @return bool|string
	 * @throws CBPArgumentOutOfRangeException
	 */
	public static function updateDocumentField($documentType, $fields)
	{
		if(!isset($fields['settings'])) // check field on the activity
			return false;

		if(!empty($fields["active_type"]))
			$fields["type"] = $fields["active_type"];

		$iblockId = intval(mb_substr($documentType, mb_strlen("iblock_")));
		if ($iblockId <= 0)
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);

		$fieldId = false;
		if (mb_substr($fields["code"], 0, mb_strlen("PROPERTY_")) == "PROPERTY_")
		{
			$fields["code"] = mb_substr($fields["code"], mb_strlen("PROPERTY_"));
			$propertyObject = CIBlockProperty::getList(
				array(),
				array("IBLOCK_ID" => $iblockId, "CODE" => $fields["code"])
			);
			if($property = $propertyObject->fetch())
			{
				$fieldId = "PROPERTY_".$property["ID"];
			}
		}
		else
		{
			if(empty($fields["code"]))
			{
				return false;
			}

			$fieldId = $fields["code"];
		}

		if($fieldId)
		{
			$fieldData = array(
				"NAME" => $fields["name"],
				"ACTIVE" => "Y",
				"SORT" => $fields["sort"] ? $fields["sort"] : 900,
				"CODE" => $fields["code"],
				'MULTIPLE' => $fields['multiple'] == 'Y' || (string)$fields['multiple'] === '1' ? 'Y' : 'N',
				'IS_REQUIRED' => $fields['required'] == 'Y' || (string)$fields['required'] === '1' ? 'Y' : 'N',
				"IBLOCK_ID" => $iblockId,
				"FILTRABLE" => "Y",
				"SETTINGS" => $fields["settings"] ? $fields["settings"] :
					array("SHOW_ADD_FORM" => 'Y', "SHOW_EDIT_FORM"=>'Y'),
				"DEFAULT_VALUE" => $fields['DefaultValue']
			);

			if (array_key_exists("additional_type_info", $fields))
				$fieldData["LINK_IBLOCK_ID"] = intval($fields["additional_type_info"]);

			if(!empty($fields["UserTypeSettings"]))
				$fieldData["USER_TYPE_SETTINGS"] = $fields["UserTypeSettings"];

			if(mb_strstr($fields["type"], ":") !== false)
			{
				list($fieldData["TYPE"], $fieldData["USER_TYPE"]) = explode(":", $fields["type"], 2);
				if($fields["type"] == "E:EList")
				{
					$fieldData["LINK_IBLOCK_ID"] = $fields["options"] ?? null;
				}
				elseif($fields["type"] == "E:ECrm")
				{
					$fieldData["TYPE"] = "S:ECrm";
				}
			}
			elseif ($fields["type"] == "user")
			{
				$fieldData["TYPE"] = "S:employee";
				$fieldData["USER_TYPE"]= "UserID";
			}
			elseif ($fields["type"] == "date")
			{
				$fieldData["TYPE"] = "S:Date";
				$fieldData["USER_TYPE"]= "Date";
			}
			elseif ($fields["type"] == "datetime")
			{
				$fieldData["TYPE"] = "S:DateTime";
				$fieldData["USER_TYPE"]= "DateTime";
			}
			elseif ($fields["type"] == "file")
			{
				$fieldData["TYPE"] = "F";
				$fieldData["USER_TYPE"]= "";
			}
			elseif ($fields["type"] == "select")
			{
				$fieldData["TYPE"] = "L";
				$fieldData["USER_TYPE"]= false;

				if (is_array($fields["options"]))
				{
					$i = 10;
					foreach ($fields["options"] as $k => $v)
					{
						$def = "N";
						if($fields['DefaultValue'] == $v)
							$def = "Y";
						$fieldData["VALUES"][] = array("XML_ID" => $k, "VALUE" => $v, "DEF" => $def, "SORT" => $i);
						$i = $i + 10;
					}
				}
				elseif (is_string($fields["options"]) && ($fields["options"] <> ''))
				{
					$a = explode("\n", $fields["options"]);
					$i = 10;
					foreach ($a as $v)
					{
						$v = trim(trim($v), "\r\n");
						if (!$v)
							continue;
						$v1 = $v2 = $v;
						if (mb_substr($v, 0, 1) == "[" && mb_strpos($v, "]") !== false)
						{
							$v1 = mb_substr($v, 1, mb_strpos($v, "]") - 1);
							$v2 = trim(mb_substr($v, mb_strpos($v, "]") + 1));
						}
						$def = "N";
						if($fields['DefaultValue'] == $v2)
							$def = "Y";
						$fieldData["VALUES"][] = array("XML_ID" => $v1, "VALUE" => $v2, "DEF" => $def, "SORT" => $i);
						$i = $i + 10;
					}
				}
			}
			elseif($fields["type"] == "string")
			{
				$fieldData["TYPE"] = "S";

				if($fields["row_count"] && $fields["col_count"])
				{
					$fieldData["ROW_COUNT"] = $fields["row_count"];
					$fieldData["COL_COUNT"] = $fields["col_count"];
				}
				else
				{
					$fieldData["ROW_COUNT"] = 1;
					$fieldData["COL_COUNT"] = 30;
				}
			}
			elseif($fields["type"] == "text")
			{
				$fieldData["TYPE"] = "S";
				if($fields["row_count"] && $fields["col_count"])
				{
					$fieldData["ROW_COUNT"] = $fields["row_count"];
					$fieldData["COL_COUNT"] = $fields["col_count"];
				}
				else
				{
					$fieldData["ROW_COUNT"] = 4;
					$fieldData["COL_COUNT"] = 30;
				}
			}
			elseif($fields["type"] == "int" || $fields["type"] == "double")
			{
				$fieldData["TYPE"] = "N";
			}
			elseif($fields["type"] == "bool")
			{
				$fieldData["TYPE"] = "L";
				$fieldData["VALUES"][] = array(
					"XML_ID" => 'Y',
					"VALUE" => GetMessage("BPVDX_YES"),
					"DEF" => "N",
					"SORT" => 10
				);
				$fieldData["VALUES"][] = array(
					"XML_ID" => 'N',
					"VALUE" => GetMessage("BPVDX_NO"),
					"DEF" => "N",
					"SORT" => 20
				);
			}
			else
			{
				$fieldData["TYPE"] = $fields["type"];
				$fieldData["USER_TYPE"] = false;
			}

			$list = new CList($iblockId);
			$oldFields = $list->getFields();
			if(array_key_exists($fieldId, $oldFields))
			{
				if($oldFields[$fieldId]["TYPE"] != $fieldData["TYPE"])
					$fieldData["TYPE"] = $oldFields[$fieldId]["TYPE"];
				$fieldId = $list->updateField($fieldId, $fieldData);
			}
			else
			{
				$fieldId = $list->addField($fieldData);
			}

			if($fieldId)
			{
				global $CACHE_MANAGER;
				$CACHE_MANAGER->clearByTag("lists_list_".$iblockId);
				return $fieldId;
			}
		}

		return false;
	}

	public static function updateDocument($documentId, $arFields)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
		{
			throw new CBPArgumentNullException('documentId');
		}

		CIBlockElement::WF_CleanUpHistoryCopies($documentId, 0);

		$arFieldsPropertyValues = [];

		$dbResult = CIBlockElement::GetList(
			[],
			['ID' => $documentId, 'SHOW_NEW' => 'Y', 'SHOW_HISTORY' => 'Y'],
			false, false,
			['ID', 'IBLOCK_ID']
		);
		$arResult = $dbResult->Fetch();
		if (!$arResult)
		{
			throw new Exception('Element is not found');
		}

		$complexDocumentId = ['lists', get_called_class(), $documentId];
		$arDocumentFields = self::GetDocumentFields('iblock_' . $arResult['IBLOCK_ID']);

		$arKeys = array_keys($arFields);
		foreach ($arKeys as $key)
		{
			if (!array_key_exists($key, $arDocumentFields))
			{
				continue;
			}

			$arFields[$key] =
				(is_array($arFields[$key]) && !CBPHelper::IsAssociativeArray($arFields[$key]))
					? $arFields[$key]
					: [$arFields[$key]];
			$realKey = (
			(mb_substr($key, 0, mb_strlen('PROPERTY_')) == 'PROPERTY_')
				? mb_substr($key, mb_strlen('PROPERTY_'))
				: $key
			);

			if ($arDocumentFields[$key]['Type'] == 'user')
			{
				$arFields[$key] = \CBPHelper::extractUsers($arFields[$key], $complexDocumentId);
			}
			elseif ($arDocumentFields[$key]['Type'] == 'select')
			{
				$arV = [];
				$db = CIBlockProperty::GetPropertyEnum(
					$realKey,
					false,
					['IBLOCK_ID' => $arResult['IBLOCK_ID']]
				);
				while ($ar = $db->GetNext())
				{
					$arV[$ar['XML_ID']] = $ar['ID'];
				}

				$listValue = [];
				foreach ($arFields[$key] as &$value)
				{
					if (is_array($value) && CBPHelper::isAssociativeArray($value))
					{
						$listXmlId = array_keys($value);
						foreach ($listXmlId as $xmlId)
						{
							$listValue[] = $arV[$xmlId];
						}
					}
					else
					{
						if (array_key_exists($value, $arV))
						{
							$value = $arV[$value];
						}
					}
				}
				if (!empty($listValue))
				{
					$arFields[$key] = $listValue;
				}
			}
			elseif ($arDocumentFields[$key]['Type'] == 'file')
			{
				$files = [];
				foreach ($arFields[$key] as $value)
				{
					if (is_array($value))
					{
						foreach ($value as $file)
						{
							$makeFileArray = CFile::MakeFileArray($file);
							if ($makeFileArray)
							{
								$files[] = $makeFileArray;
							}
						}
					}
					else
					{
						$makeFileArray = CFile::MakeFileArray($value);
						if ($makeFileArray)
						{
							$files[] = $makeFileArray;
						}
					}
				}
				if ($files)
				{
					$arFields[$key] = $files;
				}
				else
				{
					$arFields[$key] = [['del' => 'Y']];
				}
			}
			elseif ($arDocumentFields[$key]['Type'] == 'S:DiskFile')
			{
				foreach ($arFields[$key] as &$value)
				{
					if (!empty($value))
					{
						$value = 'n' . $value;
					}
				}
				$arFields[$key] = ['VALUE' => $arFields[$key], 'DESCRIPTION' => 'workflow'];
			}
			elseif ($arDocumentFields[$key]['Type'] == 'S:HTML')
			{
				foreach ($arFields[$key] as &$value)
				{
					$value = ['VALUE' => $value];
				}
			}

			unset($value);

			if (!$arDocumentFields[$key]["Multiple"] && is_array($arFields[$key]))
			{
				if (count($arFields[$key]) > 0)
				{
					$a = array_values($arFields[$key]);
					$arFields[$key] = $a[0];
				}
				else
				{
					$arFields[$key] = null;
				}
			}

			if (mb_substr($key, 0, mb_strlen("PROPERTY_")) == "PROPERTY_")
			{
				$realKey = mb_substr($key, mb_strlen("PROPERTY_"));
				$arFieldsPropertyValues[$realKey] = (is_array($arFields[$key])
					&& !CBPHelper::IsAssociativeArray($arFields[$key])) ? $arFields[$key] : [$arFields[$key]];
				if (empty($arFieldsPropertyValues[$realKey]))
					$arFieldsPropertyValues[$realKey] = [null];
				unset($arFields[$key]);
			}
		}

		if (count($arFieldsPropertyValues) > 0)
		{
			$arFields['PROPERTY_VALUES'] = $arFieldsPropertyValues;
		}

		$iblockElement = new CIBlockElement();
		if (isset($arFields['PROPERTY_VALUES']) && count($arFields['PROPERTY_VALUES']) > 0)
		{
			$iblockElement->SetPropertyValuesEx($documentId, $arResult['IBLOCK_ID'], $arFields['PROPERTY_VALUES']);
		}

		unset($arFields['PROPERTY_VALUES']);
		$res = $iblockElement->Update($documentId, $arFields, false, true, true);
		if (!$res)
		{
			throw new Exception($iblockElement->LAST_ERROR);
		}

		if (isset($arFields['BP_PUBLISHED']) && $arFields['BP_PUBLISHED'] === 'Y')
		{
			self::publishDocument($documentId);
		}
		elseif (isset($arFields['BP_PUBLISHED']) &&$arFields['BP_PUBLISHED'] === 'N')
		{
			self::unpublishDocument($documentId);
		}

		if (CModule::includeModule('lists'))
		{
			CLists::rebuildSeachableContentForElement($arResult['IBLOCK_ID'], $documentId);
		}
	}

	public static function onTaskChange($documentId, $taskId, $taskData, $status)
	{
		CListsLiveFeed::setMessageLiveFeed($taskData['USERS'] ?? null, $documentId, $taskData['WORKFLOW_ID'], false);
		if ($status == CBPTaskChangedStatus::Delegate)
		{
			$runtime = CBPRuntime::getRuntime();
			/**
			 * @var CBPAllStateService $stateService
			 */
			$stateService = $runtime->getService('StateService');
			$stateService->setStatePermissions(
				$taskData['WORKFLOW_ID'],
				array('R' => array('user_'.$taskData['USERS'][0])),
				array('setMode' => CBPSetPermissionsMode::Hold, 'setScope' => CBPSetPermissionsMode::ScopeDocument)
			);
		}
	}

	/**
	 * @param string $documentId
	 * @param string $workflowId
	 * @param int $status
	 * @param null|CBPActivity $rootActivity
	 */
	public static function onWorkflowStatusChange($documentId, $workflowId, $status, $rootActivity)
	{
		if ($status == CBPWorkflowStatus::Completed)
		{
			CListsLiveFeed::setMessageLiveFeed(array(), $documentId, $workflowId, true);
		}

		if($status == CBPWorkflowStatus::Terminated)
		{
			CLists::deleteSocnetLog(array($workflowId));
		}

		if (
			$rootActivity
			&& $status === \CBPWorkflowStatus::Running
			&& !$rootActivity->workflow->isNew()
		)
		{
			$iblockTypeId = 'lists';

			$elementQuery = CIBlockElement::getList(
				[],
				['ID' => $documentId],
				false,
				false,
				['IBLOCK_TYPE_ID']
			);
			if ($element = $elementQuery->fetch())
			{
				$iblockTypeId = $element['IBLOCK_TYPE_ID'];
			}

			if (!\CLists::isBpFeatureEnabled($iblockTypeId))
			{
				throw new \Exception(Loc::getMessage('LISTS_BIZPROC_RESUME_RESTRICTED'));
			}
		}
	}

	/**
	 * @param int $documentId
	 * @return null|string
	 * @throws CBPArgumentNullException
	 */
	public static function getDocumentAdminPage($documentId)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		$elementQuery = CIBlockElement::getList(
			array(),
			array("ID" => $documentId, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y"),
			false,
			false,
			array("ID", "IBLOCK_ID", "IBLOCK_TYPE_ID", "DETAIL_PAGE_URL")
		);
		if ($element = $elementQuery->fetch())
		{
			return COption::getOptionString('lists', 'livefeed_url').'?livefeed=y&list_id='.$element["IBLOCK_ID"].'&element_id='.$documentId;
		}

		return null;
	}

	protected static function getRightsTasks()
	{
		if (self::$cachedTasks === null)
		{
			$iterator = CTask::getList(
				array("LETTER"=>"asc"),
				array(
					"MODULE_ID" => "iblock",
					"BINDING" => "iblock"
				)
			);

			while($ar = $iterator->fetch())
			{
				if ($ar['LETTER'] === '')
				{
					$ar['LETTER'] = $ar['ID'];
				}
				self::$cachedTasks[$ar["LETTER"]] = $ar;
			}
		}
		return self::$cachedTasks;
	}

	/**
	 * @param string $documentType
	 * @return array
	 * @throws CBPArgumentOutOfRangeException
	 */
	public static function getAllowableOperations($documentType)
	{
		$iblockId = intval(mb_substr($documentType, mb_strlen("iblock_")));
		if ($iblockId <= 0)
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);

		if (CIBlock::getArrayByID($iblockId, "RIGHTS_MODE") === "E")
		{
			$operations = array();
			$tasks = self::getRightsTasks();

			foreach($tasks as $ar)
			{
				$key = empty($ar['LETTER']) ? $ar['ID'] : $ar['LETTER'];
				$operations[$key] = $ar['TITLE'];
			}

			return $operations;
		}
		return parent::getAllowableOperations($documentType);
	}

	/**
	 * @param $documentType
	 * @param array $permissions
	 * @return array
	 */
	public static function toInternalOperations($documentType, $permissions)
	{
		$permissions = (array) $permissions;
		$tasks = self::getRightsTasks();

		$normalized = array();
		foreach ($permissions as $key => $value)
		{
			if (isset($tasks[$key]))
				$key = $tasks[$key]['ID'];
			$normalized[$key] = $value;
		}

		return $normalized;
	}

	/**
	 * @param $documentType
	 * @param array $permissions
	 * @return array
	 */
	public static function toExternalOperations($documentType, $permissions)
	{
		$permissions = (array) $permissions;
		$tasks = self::getRightsTasks();
		$letters = array();
		foreach ($tasks as $k => $t)
		{
			$letters[$t['ID']] = $k;
		}
		unset($tasks);

		$normalized = array();
		foreach ($permissions as $key => $value)
		{
			if (isset($letters[$key]))
				$key = $letters[$key];
			$normalized[$key] = $value;
		}

		return $normalized;
	}

	public static function CanUserOperateDocument($operation, $userId, $documentId, $parameters = array())
	{
		$documentId = trim($documentId);
		if ($documentId == '')
			return false;

		if (self::isAdmin())
		{
			return true;
		}

		if (!array_key_exists("IBlockId", $parameters)
			&& (
				!array_key_exists("IBlockPermission", $parameters)
				|| !array_key_exists("DocumentStates", $parameters)
				|| !array_key_exists("IBlockRightsMode", $parameters)
				|| array_key_exists("IBlockRightsMode", $parameters) && ($parameters["IBlockRightsMode"] === "E")
			)
			|| !array_key_exists("CreatedBy", $parameters) && !array_key_exists("AllUserGroups", $parameters))
		{
			if (empty(self::$elements[$documentId]))
			{
				$elementListQuery = CIBlockElement::getList(
					array(),
					array("ID" => $documentId, "SHOW_NEW" => "Y", "SHOW_HISTORY" => "Y"),
					false,
					false,
					array("ID", "IBLOCK_ID", "CREATED_BY")
				);
				self::$elements[$documentId] = $elementListQuery->fetch();
			}

			if (empty(self::$elements[$documentId]))
				return false;

			$element = self::$elements[$documentId];

			$parameters["IBlockId"] = $element["IBLOCK_ID"];
			$parameters["CreatedBy"] = $element["CREATED_BY"];
		}

		if (!array_key_exists("IBlockRightsMode", $parameters))
			$parameters["IBlockRightsMode"] = CIBlock::getArrayByID($parameters["IBlockId"], "RIGHTS_MODE");

		if ($parameters["IBlockRightsMode"] === "E")
		{
			if (
				$operation === CBPCanUserOperateOperation::ReadDocument ||
				$operation === CBPCanUserOperateOperation::ViewWorkflow
			)
				return CIBlockElementRights::userHasRightTo($parameters["IBlockId"], $documentId, "element_read");
			elseif ($operation === CBPCanUserOperateOperation::WriteDocument)
				return CIBlockElementRights::userHasRightTo($parameters["IBlockId"], $documentId, "element_edit");
			elseif ($operation === CBPCanUserOperateOperation::StartWorkflow)
			{
				if (CIBlockElementRights::userHasRightTo($parameters["IBlockId"], $documentId, "element_edit"))
					return true;

				if (!array_key_exists("WorkflowId", $parameters))
					return false;

				if (!CIBlockElementRights::userHasRightTo($parameters["IBlockId"], $documentId, "element_read"))
					return false;

				$userId = intval($userId);
				if (!array_key_exists("AllUserGroups", $parameters))
				{
					if (!array_key_exists("UserGroups", $parameters))
						$parameters["UserGroups"] = CUser::getUserGroup($userId);

					$parameters["AllUserGroups"] = $parameters["UserGroups"];
					if ($userId == $parameters["CreatedBy"])
						$parameters["AllUserGroups"][] = "Author";
				}

				if (!array_key_exists("DocumentStates", $parameters))
				{
					if ($operation === CBPCanUserOperateOperation::StartWorkflow)
						$parameters["DocumentStates"] = CBPWorkflowTemplateLoader::getDocumentTypeStates(array('lists', get_called_class(), self::generateDocumentType($parameters["IBlockId"])));
					else
						$parameters["DocumentStates"] = CBPDocument::getDocumentStates(
							array('lists', get_called_class(), self::generateDocumentType($parameters["IBlockId"])),
							array('lists', get_called_class(), $documentId)
						);
				}

				if (array_key_exists($parameters["WorkflowId"], $parameters["DocumentStates"]))
					$parameters["DocumentStates"] = array($parameters["WorkflowId"] => $parameters["DocumentStates"][$parameters["WorkflowId"]]);
				else
					return false;

				$allowableOperations = CBPDocument::getAllowableOperations(
					$userId,
					$parameters["AllUserGroups"],
					$parameters["DocumentStates"],
					true
				);

				if (!is_array($allowableOperations))
					return false;

				if (($operation === CBPCanUserOperateOperation::ViewWorkflow) && in_array("read", $allowableOperations)
					|| ($operation === CBPCanUserOperateOperation::StartWorkflow) && in_array("write", $allowableOperations))
					return true;

				$chop = ($operation === CBPCanUserOperateOperation::ViewWorkflow) ? "element_read" : "element_edit";

				$tasks = self::getRightsTasks();
				foreach ($allowableOperations as $op)
				{
					if (isset($tasks[$op]))
						$op = $tasks[$op]['ID'];
					$ar = CTask::getOperations($op, true);
					if (in_array($chop, $ar))
						return true;
				}
			}
			elseif ($operation === CBPCanUserOperateOperation::CreateWorkflow)
			{
				return CBPDocument::canUserOperateDocumentType(
					CBPCanUserOperateOperation::CreateWorkflow,
					$userId,
					array('lists', get_called_class(), $parameters['IBlockId']),
					$parameters
				);
			}

			return false;
		}

		if (!array_key_exists("IBlockPermission", $parameters))
		{
			if (CModule::includeModule('lists'))
				$parameters["IBlockPermission"] = CLists::getIBlockPermission($parameters["IBlockId"], $userId);
			else
				$parameters["IBlockPermission"] = CIBlock::getPermission($parameters["IBlockId"], $userId);
		}

		if ($parameters["IBlockPermission"] <= "R")
			return false;
		elseif ($parameters["IBlockPermission"] >= "W")
			return true;

		$userId = intval($userId);
		if (!array_key_exists("AllUserGroups", $parameters))
		{
			if (!array_key_exists("UserGroups", $parameters))
				$parameters["UserGroups"] = CUser::getUserGroup($userId);

			$parameters["AllUserGroups"] = $parameters["UserGroups"];
			if ($userId == $parameters["CreatedBy"])
				$parameters["AllUserGroups"][] = "Author";
		}

		if (!array_key_exists("DocumentStates", $parameters))
		{
			$parameters["DocumentStates"] = CBPDocument::getDocumentStates(
				array("lists", get_called_class(), "iblock_".$parameters["IBlockId"]),
				array('lists', get_called_class(), $documentId)
			);
		}

		if (array_key_exists("WorkflowId", $parameters))
		{
			if (array_key_exists($parameters["WorkflowId"], $parameters["DocumentStates"]))
				$parameters["DocumentStates"] = array($parameters["WorkflowId"] => $parameters["DocumentStates"][$parameters["WorkflowId"]]);
			else
				return false;
		}

		$allowableOperations = CBPDocument::getAllowableOperations(
			$userId,
			$parameters["AllUserGroups"],
			$parameters["DocumentStates"]
		);

		if (!is_array($allowableOperations))
			return false;

		$r = false;
		switch ($operation)
		{
			case CBPCanUserOperateOperation::ViewWorkflow:
				$r = in_array("read", $allowableOperations);
				break;
			case CBPCanUserOperateOperation::StartWorkflow:
				$r = in_array("write", $allowableOperations);
				break;
			case CBPCanUserOperateOperation::CreateWorkflow:
				$r = false;
				break;
			case CBPCanUserOperateOperation::WriteDocument:
				$r = in_array("write", $allowableOperations);
				break;
			case CBPCanUserOperateOperation::ReadDocument:
				$r = in_array("read", $allowableOperations) || in_array("write", $allowableOperations);
				break;
			default:
				$r = false;
		}

		return $r;
	}

	public static function CanUserOperateDocumentType($operation, $userId, $documentType, $parameters = array())
	{
		$documentType = trim($documentType);
		if ($documentType == '')
			return false;

		if (self::isAdmin())
		{
			return true;
		}

		if(is_numeric($documentType))
			$parameters["IBlockId"] = intval($documentType);
		else
			$parameters["IBlockId"] = intval(mb_substr($documentType, mb_strlen("iblock_")));
		$parameters['sectionId'] = !empty($parameters['sectionId']) ? (int)$parameters['sectionId'] : 0;

		if (!array_key_exists("IBlockRightsMode", $parameters))
			$parameters["IBlockRightsMode"] = CIBlock::getArrayByID($parameters["IBlockId"], "RIGHTS_MODE");

		if ($parameters["IBlockRightsMode"] === "E")
		{
			if ($operation === CBPCanUserOperateOperation::CreateWorkflow)
				return CIBlockRights::userHasRightTo($parameters["IBlockId"], $parameters["IBlockId"], "iblock_rights_edit");
			elseif ($operation === CBPCanUserOperateOperation::WriteDocument)
				return CIBlockSectionRights::userHasRightTo($parameters["IBlockId"], $parameters["sectionId"], "section_element_bind");
			elseif ($operation === CBPCanUserOperateOperation::ViewWorkflow
				|| $operation === CBPCanUserOperateOperation::StartWorkflow)
			{
				if ($operation === CBPCanUserOperateOperation::ViewWorkflow)
				{
					return (
						CIBlockRights::userHasRightTo($parameters["IBlockId"], 0, "element_read")
						|| CIBlockRights::userHasRightTo($parameters["IBlockId"], $parameters["IBlockId"], "iblock_rights_edit")
					);
				}

				if ($operation === CBPCanUserOperateOperation::StartWorkflow)
					return CIBlockSectionRights::userHasRightTo($parameters["IBlockId"], $parameters['sectionId'], "section_element_bind");


				$userId = intval($userId);
				if (!array_key_exists("AllUserGroups", $parameters))
				{
					if (!array_key_exists("UserGroups", $parameters))
						$parameters["UserGroups"] = CUser::getUserGroup($userId);

					$parameters["AllUserGroups"] = $parameters["UserGroups"];
					$parameters["AllUserGroups"][] = "Author";
				}

				if (!array_key_exists("DocumentStates", $parameters))
				{
					if ($operation === CBPCanUserOperateOperation::StartWorkflow)
						$parameters["DocumentStates"] = CBPWorkflowTemplateLoader::getDocumentTypeStates(array("lists", get_called_class(), "iblock_".$parameters["IBlockId"]));
					else
						$parameters["DocumentStates"] = CBPDocument::getDocumentStates(
							array("lists", get_called_class(), "iblock_".$parameters["IBlockId"]),
							null
						);
				}

				if (array_key_exists($parameters["WorkflowId"], $parameters["DocumentStates"]))
					$parameters["DocumentStates"] = array($parameters["WorkflowId"] => $parameters["DocumentStates"][$parameters["WorkflowId"]]);
				else
					return false;

				$allowableOperations = CBPDocument::getAllowableOperations(
					$userId,
					$parameters["AllUserGroups"],
					$parameters["DocumentStates"],
					true
				);

				if (!is_array($allowableOperations))
					return false;

				if (($operation === CBPCanUserOperateOperation::ViewWorkflow) && in_array("read", $allowableOperations)
					|| ($operation === CBPCanUserOperateOperation::StartWorkflow) && in_array("write", $allowableOperations))
					return true;

				$chop = ($operation === CBPCanUserOperateOperation::ViewWorkflow) ? "element_read" : "section_element_bind";

				$tasks  = self::getRightsTasks();
				foreach ($allowableOperations as $op)
				{
					if (isset($tasks[$op]))
						$op = $tasks[$op]['ID'];
					$ar = CTask::getOperations($op, true);
					if (in_array($chop, $ar))
						return true;
				}
			}

			return false;
		}

		if (!array_key_exists("IBlockPermission", $parameters))
		{
			if(CModule::includeModule('lists'))
				$parameters["IBlockPermission"] = CLists::getIBlockPermission($parameters["IBlockId"], $userId);
			else
				$parameters["IBlockPermission"] = CIBlock::getPermission($parameters["IBlockId"], $userId);
		}

		if ($parameters["IBlockPermission"] <= "R")
			return false;
		elseif ($parameters["IBlockPermission"] >= "W")
			return true;

		$userId = intval($userId);
		if (!array_key_exists("AllUserGroups", $parameters))
		{
			if (!array_key_exists("UserGroups", $parameters))
				$parameters["UserGroups"] = CUser::getUserGroup($userId);

			$parameters["AllUserGroups"] = $parameters["UserGroups"];
			$parameters["AllUserGroups"][] = "Author";
		}

		if (!array_key_exists("DocumentStates", $parameters))
		{
			$parameters["DocumentStates"] = CBPDocument::getDocumentStates(
				array("lists", get_called_class(), "iblock_".$parameters["IBlockId"]),
				null
			);
		}

		if (array_key_exists("WorkflowId", $parameters))
		{
			if (array_key_exists($parameters["WorkflowId"], $parameters["DocumentStates"]))
				$parameters["DocumentStates"] = array($parameters["WorkflowId"] => $parameters["DocumentStates"][$parameters["WorkflowId"]]);
			else
				return false;
		}

		$allowableOperations = CBPDocument::getAllowableOperations(
			$userId,
			$parameters["AllUserGroups"],
			$parameters["DocumentStates"]
		);

		if (!is_array($allowableOperations))
			return false;

		$r = false;
		switch ($operation)
		{
			case CBPCanUserOperateOperation::ViewWorkflow:
				$r = in_array("read", $allowableOperations);
				break;
			case CBPCanUserOperateOperation::StartWorkflow:
				$r = in_array("write", $allowableOperations);
				break;
			case CBPCanUserOperateOperation::CreateWorkflow:
				$r = in_array("write", $allowableOperations);
				break;
			case CBPCanUserOperateOperation::WriteDocument:
				$r = in_array("write", $allowableOperations);
				break;
			case CBPCanUserOperateOperation::ReadDocument:
				$r = false;
				break;
			default:
				$r = false;
		}

		return $r;
	}

	protected static function isAdmin()
	{
		global $USER;
		if (is_object($USER) && $USER->IsAuthorized())
		{
			if ($USER->IsAdmin() || CModule::IncludeModule("bitrix24") && CBitrix24::IsPortalAdmin($USER->GetID()))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $documentType
	 * @param bool $withExtended
	 * @return array|bool
	 */
	public static function GetAllowableUserGroups($documentType, $withExtended = false)
	{
		$documentType = trim($documentType);
		if ($documentType == '')
			return false;

		$iblockId = intval(mb_substr($documentType, mb_strlen("iblock_")));

		$result = array("Author" => GetMessage("IBD_DOCUMENT_AUTHOR"));

		$groupsId = array(1);
		$extendedGroupsCode = array();
		if(CIBlock::getArrayByID($iblockId, "RIGHTS_MODE") === "E")
		{
			$rights = new CIBlockRights($iblockId);
			foreach($rights->getGroups(/*"element_bizproc_start"*/) as $iblockGroupCode)
				if(preg_match("/^G(\\d+)\$/", $iblockGroupCode, $match))
					$groupsId[] = $match[1];
				else
					$extendedGroupsCode[] = $iblockGroupCode;
		}
		else
		{
			foreach(CIBlock::getGroupPermissions($iblockId) as $groupId => $perm)
			{
				if ($perm > "R")
					$groupsId[] = $groupId;
			}
		}

		$groupsIterator = CGroup::getListEx(array("NAME" => "ASC"), array("ID" => $groupsId));
		while ($group = $groupsIterator->fetch())
			$result[$group["ID"]] = $group["NAME"];

		if ($withExtended && $extendedGroupsCode)
		{
			foreach ($extendedGroupsCode as $groupCode)
			{
				$result['group_'.$groupCode] = CBPHelper::getExtendedGroupName($groupCode);
			}
		}

		return $result;
	}

	public static function SetPermissions($documentId, $workflowId, $permissions, $rewrite = true)
	{
		$permissions = self::toInternalOperations(null, $permissions);
		parent::setPermissions($documentId, $workflowId, $permissions, $rewrite);
	}

	public static function GetFieldInputControl($documentType, $fieldType, $fieldName, $fieldValue, $allowSelection = false, $publicMode = false)
	{
		$iblockId = intval(mb_substr($documentType, mb_strlen("iblock_")));
		if ($iblockId <= 0)
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);

		static $documentFieldTypes = array();
		if (!array_key_exists($documentType, $documentFieldTypes))
			$documentFieldTypes[$documentType] = self::getDocumentFieldTypes($documentType);

		$fieldType["BaseType"] = "string";
		$fieldType["Complex"] = false;
		if (array_key_exists($fieldType["Type"], $documentFieldTypes[$documentType]))
		{
			$fieldType["BaseType"] = $documentFieldTypes[$documentType][$fieldType["Type"]]["BaseType"];
			$fieldType["Complex"] = $documentFieldTypes[$documentType][$fieldType["Type"]]["Complex"];
		}

		if (!is_array($fieldValue) || is_array($fieldValue) && CBPHelper::isAssociativeArray($fieldValue))
			$fieldValue = array($fieldValue);

		$customMethodName = "";
		$customMethodNameMulty = "";
		if (mb_strpos($fieldType["Type"], ":") !== false)
		{
			$ar = CIBlockProperty::getUserType(mb_substr($fieldType["Type"], 2));
			if (array_key_exists("GetPublicEditHTML", $ar))
				$customMethodName = $ar["GetPublicEditHTML"];
			if (array_key_exists("GetPublicEditHTMLMulty", $ar))
				$customMethodNameMulty = $ar["GetPublicEditHTMLMulty"];
		}

		ob_start();

		if ($fieldType["Type"] == "select")
		{
			$fieldValueTmp = $fieldValue;
			?>
			<select id="id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>" name="<?= htmlspecialcharsbx($fieldName["Field"]).($fieldType["Multiple"] ? "[]" : "") ?>"<?= ($fieldType["Multiple"] ? ' size="5" multiple' : '') ?>>
				<?
				if (!$fieldType["Required"])
					echo '<option value="">['.GetMessage("BPCGHLP_NOT_SET").']</option>';
				foreach ($fieldType["Options"] as $k => $v)
				{
					if (is_array($v) && count($v) == 2)
					{
						$v1 = array_values($v);
						$k = $v1[0];
						$v = $v1[1];
					}

					$ind = array_search($k, $fieldValueTmp);
					echo '<option value="'.htmlspecialcharsbx($k).'"'.($ind !== false ? ' selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
					if ($ind !== false)
						unset($fieldValueTmp[$ind]);
				}
				?>
			</select>
			<?
			if ($allowSelection)
			{
				?>
				<br /><input type="text" id="id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text" name="<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text" value="<?
			if (count($fieldValueTmp) > 0)
			{
				$a = array_values($fieldValueTmp);
				echo htmlspecialcharsbx($a[0]);
			}
			?>">
				<input type="button" value="..." onclick="BPAShowSelector('id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text', 'select');">
			<?
			}
		}
		elseif ($fieldType["Type"] == "user")
		{
			$fieldValue = CBPHelper::usersArrayToString($fieldValue, null, array("lists", get_called_class(), $documentType));
			?><input type="text" size="40" id="id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>" name="<?= htmlspecialcharsbx($fieldName["Field"]) ?>" value="<?= htmlspecialcharsbx($fieldValue) ?>"><input type="button" value="..." onclick="BPAShowSelector('id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>', 'user');"><?
		}
		elseif ((mb_strpos($fieldType["Type"], ":") !== false)
			&& $fieldType["Multiple"]
			&& (
				is_array($customMethodNameMulty) && count($customMethodNameMulty) > 0
				|| !is_array($customMethodNameMulty) && $customMethodNameMulty <> ''
			)
		)
		{
			if (!is_array($fieldValue))
				$fieldValue = array();

			if ($allowSelection)
			{
				$fieldValueTmp1 = array();
				$fieldValueTmp2 = array();
				foreach ($fieldValue as $v)
				{
					$vTrim = trim($v);
					if (\CBPDocument::IsExpression($vTrim))
						$fieldValueTmp1[] = $vTrim;
					else
						$fieldValueTmp2[] = $v;
				}
			}
			else
			{
				$fieldValueTmp1 = array();
				$fieldValueTmp2 = $fieldValue;
			}

			if (($fieldType["Type"] == "S:employee") && COption::getOptionString("bizproc", "employee_compatible_mode", "N") != "Y")
				$fieldValueTmp2 = CBPHelper::stripUserPrefix($fieldValueTmp2);

			foreach ($fieldValueTmp2 as &$fld)
				if (!isset($fld['VALUE']))
					$fld = array("VALUE" => $fld);

			if ($fieldType["Type"] == "E:EList")
			{
				static $fl = true;
				if ($fl)
				{
					if (!empty($_SERVER['HTTP_BX_AJAX']))
						$GLOBALS["APPLICATION"]->showAjaxHead();
					$GLOBALS["APPLICATION"]->addHeadScript('/bitrix/js/iblock/iblock_edit.js');
				}
				$fl = false;
			}
			echo call_user_func_array(
				$customMethodNameMulty,
				array(
					array("LINK_IBLOCK_ID" => $fieldType["Options"]),
					$fieldValueTmp2,
					array(
						"FORM_NAME" => $fieldName["Form"],
						"VALUE" => htmlspecialcharsbx($fieldName["Field"])
					),
					true
				)
			);

			if ($allowSelection)
			{
				?>
				<br /><input type="text" id="id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text" name="<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text" value="<?
			if (count($fieldValueTmp1) > 0)
			{
				$a = array_values($fieldValueTmp1);
				echo htmlspecialcharsbx($a[0]);
			}
			?>">
				<input type="button" value="..." onclick="BPAShowSelector('id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text', 'user', '<?= $fieldType["Type"] == 'S:employee'? 'employee' : '' ?>');">
			<?
			}
		}
		else
		{
			if (!array_key_exists("CBPVirtualDocumentCloneRowPrinted", $GLOBALS) && $fieldType["Multiple"])
			{
				$GLOBALS["CBPVirtualDocumentCloneRowPrinted"] = 1;
				?>
				<script>
					function CBPVirtualDocumentCloneRow(tableID)
					{
						var tbl = document.getElementById(tableID);
						var cnt = tbl.rows.length;
						var oRow = tbl.insertRow(cnt);
						var oCell = oRow.insertCell(0);
						var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
						var p = 0;
						while (true)
						{
							var s = sHTML.indexOf('[n', p);
							if (s < 0)
								break;
							var e = sHTML.indexOf(']', s);
							if (e < 0)
								break;
							var n = parseInt(sHTML.substr(s + 2, e - s));
							sHTML = sHTML.substr(0, s) + '[n' + (++n) + ']' + sHTML.substr(e + 1);
							p = s + 1;
						}
						var p = 0;
						while (true)
						{
							var s = sHTML.indexOf('__n', p);
							if (s < 0)
								break;
							var e = sHTML.indexOf('_', s + 2);
							if (e < 0)
								break;
							var n = parseInt(sHTML.substr(s + 3, e - s));
							sHTML = sHTML.substr(0, s) + '__n' + (++n) + '_' + sHTML.substr(e + 1);
							p = e + 1;
						}
						oCell.innerHTML = sHTML;
						var patt = new RegExp('<' + 'script' + '>[^\000]*?<' + '\/' + 'script' + '>', 'ig');
						var code = sHTML.match(patt);
						if (code)
						{
							for (var i = 0; i < code.length; i++)
							{
								if (code[i] != '')
								{
									var s = code[i].substring(8, code[i].length - 9);
									jsUtils.EvalGlobal(s);
								}
							}
						}
					}
					function createAdditionalHtmlEditor(tableId)
					{
						var tbl = document.getElementById(tableId);
						var cnt = tbl.rows.length-1;
						var name = tableId.replace(/(?:CBPVirtualDocument_)(.*)(?:_Table)/, '$1')
						var idEditor = 'id_'+name+'__n'+cnt+'_';
						var inputNameEditor = name+'[n'+cnt+']';
						window.BXHtmlEditor.Show(
							{
								'id':idEditor,
								'inputName':inputNameEditor,
								'content':'',
								'useFileDialogs':false,
								'width':'100%',
								'height':'200',
								'allowPhp':false,
								'limitPhpAccess':false,
								'templates':[],
								'templateId':'',
								'templateParams':[],
								'componentFilter':'',
								'snippets':[],
								'placeholder':'Text here...',
								'actionUrl':'/bitrix/tools/html_editor_action.php',
								'cssIframePath':'/bitrix/js/fileman/html_editor/iframe-style.css?1412693817',
								'bodyClass':'',
								'bodyId':'',
								'spellcheck_path':'/bitrix/js/fileman/html_editor/html-spell.js?v=1412693817',
								'usePspell':'N',
								'useCustomSpell':'Y',
								'bbCode':false,
								'askBeforeUnloadPage':true,
								'settingsKey':'user_settings_1',
								'showComponents':true,
								'showSnippets':true,
								'view':'wysiwyg',
								'splitVertical':false,
								'splitRatio':'1',
								'taskbarShown':false,
								'taskbarWidth':'250',
								'lastSpecialchars':false,
								'cleanEmptySpans':true,
								'lazyLoad':false,
								'showTaskbars':false,
								'showNodeNavi':false,
								'controlsMap':[
									{'id':'Bold','compact':true,'sort':'80'},
									{'id':'Italic','compact':true,'sort':'90'},
									{'id':'Underline','compact':true,'sort':'100'},
									{'id':'Strikeout','compact':true,'sort':'110'},
									{'id':'RemoveFormat','compact':true,'sort':'120'},
									{'id':'Color','compact':true,'sort':'130'},
									{'id':'FontSelector','compact':false,'sort':'135'},
									{'id':'FontSize','compact':false,'sort':'140'},
									{'separator':true,'compact':false,'sort':'145'},
									{'id':'OrderedList','compact':true,'sort':'150'},
									{'id':'UnorderedList','compact':true,'sort':'160'},
									{'id':'AlignList','compact':false,'sort':'190'},
									{'separator':true,'compact':false,'sort':'200'},
									{'id':'InsertLink','compact':true,'sort':'210'},
									{'id':'InsertImage','compact':false,'sort':'220'},
									{'id':'InsertVideo','compact':true,'sort':'230'},
									{'id':'InsertTable','compact':false,'sort':'250'},
									{'id':'Smile','compact':false,'sort':'280'},
									{'separator':true,'compact':false,'sort':'290'},
									{'id':'Fullscreen','compact':false,'sort':'310'},
									{'id':'More','compact':true,'sort':'400'}],
								'autoResize':true,
								'autoResizeOffset':'40',
								'minBodyWidth':'350',
								'normalBodyWidth':'555'
							});
						var htmlEditor = BX.findChildrenByClassName(BX(tableId), 'bx-html-editor');
						for(var k in htmlEditor)
						{
							var editorId = htmlEditor[k].getAttribute('id');
							var frameArray = BX.findChildrenByClassName(BX(editorId), 'bx-editor-iframe');
							if(frameArray.length > 1)
							{
								for(var i = 0; i < frameArray.length - 1; i++)
								{
									frameArray[i].parentNode.removeChild(frameArray[i]);
								}
							}

						}
					}
				</script>
			<?
			}

			if ($fieldType["Multiple"])
				echo '<table width="100%" border="0" cellpadding="2" cellspacing="2" id="CBPVirtualDocument_'.htmlspecialcharsbx($fieldName["Field"]).'_Table">';

			$fieldValueTmp = $fieldValue;

			if (sizeof($fieldValue) == 0)
				$fieldValue[] = null;

			$ind = -1;
			foreach ($fieldValue as $key => $value)
			{
				$ind++;
				$fieldNameId = 'id_'.htmlspecialcharsbx($fieldName["Field"]).'__n'.$ind.'_';
				$fieldNameName = htmlspecialcharsbx($fieldName["Field"]).($fieldType["Multiple"] ? "[n".$ind."]" : "");

				if ($fieldType["Multiple"])
					echo '<tr><td>';

				if (is_array($customMethodName) && count($customMethodName) > 0 || !is_array($customMethodName) && $customMethodName <> '')
				{
					if($fieldType["Type"] == "S:HTML")
					{
						if (Loader::includeModule("fileman"))
						{
							$editor = new CHTMLEditor;
							$res = array_merge(
								array(
									'useFileDialogs' => false,
									'height' => 200,
									'useFileDialogs' => false,
									'minBodyWidth' => 350,
									'normalBodyWidth' => 555,
									'bAllowPhp' => false,
									'limitPhpAccess' => false,
									'showTaskbars' => false,
									'showNodeNavi' => false,
									'askBeforeUnloadPage' => true,
									'bbCode' => false,
									'siteId' => SITE_ID,
									'autoResize' => true,
									'autoResizeOffset' => 40,
									'saveOnBlur' => true,
									'actionUrl' => '/bitrix/tools/html_editor_action.php',
									'controlsMap' => array(
										array('id' => 'Bold',  'compact' => true, 'sort' => 80),
										array('id' => 'Italic',  'compact' => true, 'sort' => 90),
										array('id' => 'Underline',  'compact' => true, 'sort' => 100),
										array('id' => 'Strikeout',  'compact' => true, 'sort' => 110),
										array('id' => 'RemoveFormat',  'compact' => true, 'sort' => 120),
										array('id' => 'Color',  'compact' => true, 'sort' => 130),
										array('id' => 'FontSelector',  'compact' => false, 'sort' => 135),
										array('id' => 'FontSize',  'compact' => false, 'sort' => 140),
										array('separator' => true, 'compact' => false, 'sort' => 145),
										array('id' => 'OrderedList',  'compact' => true, 'sort' => 150),
										array('id' => 'UnorderedList',  'compact' => true, 'sort' => 160),
										array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
										array('separator' => true, 'compact' => false, 'sort' => 200),
										array('id' => 'InsertLink',  'compact' => true, 'sort' => 210, 'wrap' => 'bx-b-link-'.$fieldNameId),
										array('id' => 'InsertImage',  'compact' => false, 'sort' => 220),
										array('id' => 'InsertVideo',  'compact' => true, 'sort' => 230, 'wrap' => 'bx-b-video-'.$fieldNameId),
										array('id' => 'InsertTable',  'compact' => false, 'sort' => 250),
										array('id' => 'Code',  'compact' => true, 'sort' => 260),
										array('id' => 'Quote',  'compact' => true, 'sort' => 270, 'wrap' => 'bx-b-quote-'.$fieldNameId),
										array('id' => 'Smile',  'compact' => false, 'sort' => 280),
										array('separator' => true, 'compact' => false, 'sort' => 290),
										array('id' => 'Fullscreen',  'compact' => false, 'sort' => 310),
										array('id' => 'BbCode',  'compact' => true, 'sort' => 340),
										array('id' => 'More',  'compact' => true, 'sort' => 400)
									)
								),
								array(
									'name' => $fieldNameName,
									'inputName' => $fieldNameName,
									'id' => $fieldNameId,
									'width' => '100%',
									'content' => htmlspecialcharsBack($value),
								)
							);
							$editor->show($res);
						}
						else
						{
							?><textarea rows="5" cols="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?= htmlspecialcharsbx($value) ?></textarea><?
						}
					}
					else
					{
						$value1 = $value;
						if ($allowSelection && \CBPDocument::IsExpression(trim($value1)))
							$value1 = null;
						else
							unset($fieldValueTmp[$key]);

						if (($fieldType["Type"] == "S:employee") && COption::getOptionString("bizproc", "employee_compatible_mode", "N") != "Y")
							$value1 = CBPHelper::stripUserPrefix($value1);

						echo call_user_func_array(
							$customMethodName,
							array(
								array("LINK_IBLOCK_ID" => $fieldType["Options"]),
								array("VALUE" => $value1),
								array(
									"FORM_NAME" => $fieldName["Form"],
									"VALUE" => $fieldNameName
								),
								true
							)
						);
					}
				}
				else
				{
					switch ($fieldType["Type"])
					{
						case "int":
						case "double":
							unset($fieldValueTmp[$key]);
							?><input type="text" size="10" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>" value="<?= htmlspecialcharsbx($value) ?>"><?
							break;
						case "file":
							if ($publicMode)
							{
								//unset($fieldValueTmp[$key]);
								?><input type="file" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?
							}
							break;
						case "bool":
							if (in_array($value, array("Y", "N")))
								unset($fieldValueTmp[$key]);
							?>
							<select id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>">
								<?
								if (!$fieldType["Required"])
									echo '<option value="">['.GetMessage("BPCGHLP_NOT_SET").']</option>';
								?>
								<option value="Y"<?= (in_array("Y", $fieldValue) ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_YES") ?></option>
								<option value="N"<?= (in_array("N", $fieldValue) ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_NO") ?></option>
							</select>
							<?
							break;
						case "text":
							unset($fieldValueTmp[$key]);
							?><textarea rows="5" cols="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?= htmlspecialcharsbx($value) ?></textarea><?
							break;
						case "date":
						case "datetime":
							if (defined("ADMIN_SECTION") && ADMIN_SECTION)
							{
								$v = "";
								if (!\CBPDocument::IsExpression(trim($value)))
								{
									$v = $value;
									unset($fieldValueTmp[$key]);
								}
								require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/init_admin.php");
								echo CAdminCalendar::calendarDate($fieldNameName, $v, 19, ($fieldType["Type"] != "date"));
							}
							else
							{
								$value1 = $value;
								if ($allowSelection && \CBPDocument::IsExpression(trim($value1)))
									$value1 = null;
								else
									unset($fieldValueTmp[$key]);

								if($fieldType["Type"] == "date")
									$type = "Date";
								else
									$type = "DateTime";
								$ar = CIBlockProperty::getUserType($type);
								echo call_user_func_array(
									$ar["GetPublicEditHTML"],
									array(
										array("LINK_IBLOCK_ID" => $fieldType["Options"]),
										array("VALUE" => $value1),
										array(
											"FORM_NAME" => $fieldName["Form"],
											"VALUE" => $fieldNameName
										),
										true
									)
								);
							}

							break;
						default:
							unset($fieldValueTmp[$key]);
							?><input type="text" size="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>" value="<?= htmlspecialcharsbx($value) ?>"><?
					}
				}

				if ($allowSelection)
				{
					if (!in_array($fieldType["Type"], array("file", "bool", "date", "datetime")) && (is_array($customMethodName) && count($customMethodName) <= 0 || !is_array($customMethodName) && $customMethodName == ''))
					{
						?><input type="button" value="..." onclick="BPAShowSelector('<?= $fieldNameId ?>', '<?= htmlspecialcharsbx($fieldType["BaseType"]) ?>');"><?
					}
				}

				if ($fieldType["Multiple"])
					echo '</td></tr>';
			}

			if ($fieldType["Multiple"])
				echo "</table>";

			if ($fieldType["Multiple"] && $fieldType["Type"] != "S:HTML" && (($fieldType["Type"] != "file") || $publicMode))
			{
				echo '<input type="button" value="'.GetMessage("BPCGHLP_ADD").'" onclick="CBPVirtualDocumentCloneRow(\'CBPVirtualDocument_'.$fieldName["Field"].'_Table\')"/><br />';
			}
			elseif($fieldType["Multiple"] && $fieldType["Type"] == "S:HTML")
			{
				$functionOnclick = 'CBPVirtualDocumentCloneRow(\'CBPVirtualDocument_'.$fieldName["Field"].'_Table\');createAdditionalHtmlEditor(\'CBPVirtualDocument_'.$fieldName["Field"].'_Table\');';
				echo '<input type="button" value="'.GetMessage("BPCGHLP_ADD").'" onclick="'.$functionOnclick.'"/><br />';
			}

			if ($allowSelection)
			{
				if (in_array($fieldType["Type"], array("file", "bool", "date", "datetime")) || (is_array($customMethodName) && count($customMethodName) > 0 || !is_array($customMethodName) && $customMethodName <> ''))
				{
					?>
					<input type="text" id="id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text" name="<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text" value="<?
					if (count($fieldValueTmp) > 0)
					{
						$a = array_values($fieldValueTmp);
						echo htmlspecialcharsbx($a[0]);
					}
					?>">
					<input type="button" value="..." onclick="BPAShowSelector('id_<?= htmlspecialcharsbx($fieldName["Field"]) ?>_text', '<?= htmlspecialcharsbx($fieldType["BaseType"]) ?>', '<?= $fieldType["Type"] == 'S:employee'? 'employee' : '' ?>');">
				<?
				}
			}
		}

		$s = ob_get_contents();
		ob_end_clean();

		return $s;
	}

	public static function GetFieldInputValue($documentType, $fieldType, $fieldName, $request, &$errors)
	{
		$iblockId = intval(mb_substr($documentType, mb_strlen("iblock_")));
		if ($iblockId <= 0)
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);

		$result = array();

		if ($fieldType["Type"] == "user")
		{
			$value = $request[$fieldName["Field"]];
			if ($value <> '')
			{
				$result = CBPHelper::usersStringToArray($value, array("lists", get_called_class(), $documentType), $errors);
				if (count($errors) > 0)
				{
					foreach ($errors as $e)
						$errors[] = $e;
				}
			}
			else
				$result = null;
		}
		elseif (array_key_exists($fieldName["Field"], $request) || array_key_exists($fieldName["Field"]."_text", $request))
		{
			$valueArray = array();
			if (array_key_exists($fieldName["Field"], $request))
			{
				$valueArray = $request[$fieldName["Field"]];
				if (!is_array($valueArray) || is_array($valueArray) && CBPHelper::isAssociativeArray($valueArray))
					$valueArray = array($valueArray);
			}
			if (array_key_exists($fieldName["Field"]."_text", $request))
				$valueArray[] = $request[$fieldName["Field"]."_text"];

			foreach ($valueArray as $value)
			{
				if (is_array($value) || !is_array($value) && !\CBPDocument::IsExpression(trim($value)))
				{
					if ($fieldType["Type"] == "int")
					{
						if ($value <> '')
						{
							$value = str_replace(" ", "", str_replace(",", ".", $value));
							if (is_numeric($value))
							{
								$value = doubleval($value);
							}
							else
							{
								$value = null;
								$errors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("LISTS_BIZPROC_INVALID_INT"),
									"parameter" => $fieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif ($fieldType["Type"] == "double")
					{
						if ($value <> '')
						{
							$value = str_replace(" ", "", str_replace(",", ".", $value));
							if (is_numeric($value))
							{
								$value = doubleval($value);
							}
							else
							{
								$value = null;
								$errors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("LISTS_BIZPROC_INVALID_INT"),
									"parameter" => $fieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif ($fieldType["Type"] == "select")
					{
						if (!is_array($fieldType["Options"]) || count($fieldType["Options"]) <= 0 || $value == '')
						{
							$value = null;
						}
						else
						{
							$ar = array_values($fieldType["Options"]);
							if (is_array($ar[0]))
							{
								$b = false;
								foreach ($ar as $a)
								{
									if ($a[0] == $value)
									{
										$b = true;
										break;
									}
								}
								if (!$b)
								{
									$value = null;
									$errors[] = array(
										"code" => "ErrorValue",
										"message" => GetMessage("LISTS_BIZPROC_INVALID_SELECT"),
										"parameter" => $fieldName["Field"],
									);
								}
							}
							else
							{
								if (!array_key_exists($value, $fieldType["Options"]))
								{
									$value = null;
									$errors[] = array(
										"code" => "ErrorValue",
										"message" => GetMessage("LISTS_BIZPROC_INVALID_SELECT"),
										"parameter" => $fieldName["Field"],
									);
								}
							}
						}
					}
					elseif ($fieldType["Type"] == "bool")
					{
						if ($value !== "Y" && $value !== "N")
						{
							if ($value === true)
							{
								$value = "Y";
							}
							elseif ($value === false)
							{
								$value = "N";
							}
							elseif ($value <> '')
							{
								$value = mb_strtolower($value);
								if (in_array($value, array("y", "yes", "true", "1")))
								{
									$value = "Y";
								}
								elseif (in_array($value, array("n", "no", "false", "0")))
								{
									$value = "N";
								}
								else
								{
									$value = null;
									$errors[] = array(
										"code" => "ErrorValue",
										"message" => GetMessage("BPCGWTL_INVALID45"),
										"parameter" => $fieldName["Field"],
									);
								}
							}
							else
							{
								$value = null;
							}
						}
					}
					elseif ($fieldType["Type"] == "file")
					{
						if (is_array($value) && array_key_exists("name", $value) && $value["name"] <> '')
						{
							if (!array_key_exists("MODULE_ID", $value) || $value["MODULE_ID"] == '')
								$value["MODULE_ID"] = "bizproc";

							$value = CFile::saveFile($value, "bizproc_wf", true, true);
							if (!$value)
							{
								$value = null;
								$errors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("BPCGWTL_INVALID915"),
									"parameter" => $fieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif ($fieldType["Type"] == "date")
					{
						if ($value <> '')
						{
							if(!CheckDateTime($value, FORMAT_DATE))
							{
								$value = null;
								$errors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("LISTS_BIZPROC_INVALID_DATE"),
									"parameter" => $fieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}

					}
					elseif ($fieldType["Type"] == "datetime")
					{
						if ($value <> '')
						{
							$valueTemporary = array();
							$valueTemporary["VALUE"] = $value;
							$result = CIBlockPropertyDateTime::checkFields('', $valueTemporary);
							if (!empty($result))
							{
								$message = '';
								foreach ($result as $error)
									$message .= $error;

								$value = null;
								$errors[] = array(
									"code" => "ErrorValue",
									"message" => $message,
									"parameter" => $fieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif (mb_strpos($fieldType["Type"], ":") !== false && $fieldType["Type"] != "S:HTML")
					{
						$customType = CIBlockProperty::getUserType(mb_substr($fieldType["Type"], 2));
						if (array_key_exists("GetLength", $customType))
						{
							if (call_user_func_array(
									$customType["GetLength"],
									array(
										array("LINK_IBLOCK_ID" => $fieldType["Options"]),
										array("VALUE" => $value)
									)
								) <= 0)
							{
								$value = null;
							}
						}

						if (($value != null) && array_key_exists("CheckFields", $customType))
						{
							$errorsTemporary = call_user_func_array(
								$customType["CheckFields"],
								array(
									array("LINK_IBLOCK_ID" => $fieldType["Options"]),
									array("VALUE" => $value)
								)
							);
							if (count($errorsTemporary) > 0)
							{
								$value = null;
								foreach ($errorsTemporary as $e)
									$errors[] = array(
										"code" => "ErrorValue",
										"message" => $e,
										"parameter" => $fieldName["Field"],
									);
							}
						}
						elseif (!array_key_exists("GetLength", $customType) && $value === '')
							$value = null;

						if (
							$value !== null &&
							$fieldType["Type"] == "S:employee" &&
							COption::getOptionString("bizproc", "employee_compatible_mode", "N") != "Y"
						)
						{
							$value = "user_".$value;
						}
					}
					else
					{
						if (!is_array($value) && $value == '')
							$value = null;
					}
				}

				if ($value !== null)
					$result[] = $value;
			}
		}

		if (!$fieldType["Multiple"])
		{
			if (is_array($result) && count($result) > 0)
				$result = $result[0];
			else
				$result = null;
		}

		return $result;
	}

	public static function GetFieldInputValuePrintable($documentType, $fieldType, $fieldValue)
	{
		$result = $fieldValue;

		switch ($fieldType['Type'])
		{
			case "user":
				if (!is_array($fieldValue))
					$fieldValue = array($fieldValue);

				$result = CBPHelper::usersArrayToString($fieldValue, null, array("lists", get_called_class(), $documentType));
				break;

			case "bool":
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
						$result[] = ((mb_strtoupper($r) != "N" && !empty($r)) ? GetMessage("BPVDX_YES") : GetMessage("BPVDX_NO"));
				}
				else
				{
					$result = ((mb_strtoupper($fieldValue) != "N" && !empty($fieldValue)) ? GetMessage("BPVDX_YES") : GetMessage("BPVDX_NO"));
				}
				break;

			case "file":
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
					{
						$r = intval($r);
						$imgQuery = CFile::getByID($r);
						if ($img = $imgQuery->fetch())
							$result[] = "[url=/bitrix/tools/bizproc_show_file.php?f=".urlencode($img["FILE_NAME"])."&i=".$r."&h=".md5($img["SUBDIR"])."]".htmlspecialcharsbx($img["ORIGINAL_NAME"])."[/url]";
					}
				}
				else
				{
					$fieldValue = intval($fieldValue);
					$imgQuery = CFile::getByID($fieldValue);
					if ($img = $imgQuery->fetch())
						$result = "[url=/bitrix/tools/bizproc_show_file.php?f=".urlencode($img["FILE_NAME"])."&i=".$fieldValue."&h=".md5($img["SUBDIR"])."]".htmlspecialcharsbx($img["ORIGINAL_NAME"])."[/url]";
				}
				break;

			case "select":
				if (is_array($fieldType["Options"]))
				{
					if (is_array($fieldValue))
					{
						$result = array();
						foreach ($fieldValue as $r)
						{
							if (array_key_exists($r, $fieldType["Options"]))
								$result[] = $fieldType["Options"][$r];
						}
					}
					else
					{
						if (array_key_exists($fieldValue, $fieldType["Options"]))
							$result = $fieldType["Options"][$fieldValue];
					}
				}
				break;
		}

		if (mb_strpos($fieldType['Type'], ":") !== false)
		{
			if ($fieldType["Type"] == "S:employee")
				$fieldValue = CBPHelper::stripUserPrefix($fieldValue);

			$customType = CIBlockProperty::getUserType(mb_substr($fieldType['Type'], 2));
			if (array_key_exists("GetPublicViewHTML", $customType))
			{
				if (is_array($fieldValue) && !CBPHelper::isAssociativeArray($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $value)
					{
						$r = call_user_func_array(
							$customType["GetPublicViewHTML"],
							array(
								array("LINK_IBLOCK_ID" => $fieldType["Options"]),
								array("VALUE" => $value),
								""
							)
						);

						$result[] = HTMLToTxt($r);
					}
				}
				else
				{
					$result = call_user_func_array(
						$customType["GetPublicViewHTML"],
						array(
							array("LINK_IBLOCK_ID" => $fieldType["Options"]),
							array("VALUE" => $fieldValue),
							""
						)
					);

					$result = HTMLToTxt($result);
				}
			}
		}

		return $result;
	}

	public static function UnlockDocument($documentId, $workflowId)
	{
		global $DB;

		$strSql = "
			SELECT * FROM b_iblock_element_lock
			WHERE IBLOCK_ELEMENT_ID = ".intval($documentId)."
		";
		$query = $DB->query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
		if($query->fetch())
		{
			$strSql = "
				DELETE FROM b_iblock_element_lock
				WHERE IBLOCK_ELEMENT_ID = ".intval($documentId)."
				AND (LOCKED_BY = '".$DB->forSQL($workflowId, 32)."' OR '".$DB->forSQL($workflowId, 32)."' = '')
			";
			$query = $DB->query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			$result = $query->affectedRowsCount();
		}
		else
		{//Success unlock when there is no locks at all
			$result = 1;
		}

		if ($result > 0)
		{
			foreach (GetModuleEvents("iblock", "CIBlockDocument_OnUnlockDocument", true) as $event)
			{
				ExecuteModuleEventEx($event, array(array("lists", get_called_class(), $documentId)));
			}
		}

		return $result > 0;
	}

	/**
	 * The method of publishing the document. That is making it available in the public section.
	 * @param string $documentId
	 * @return bool|int
	 */
	public static function PublishDocument($documentId)
	{
		global $DB;
		$ID = intval($documentId);

		$elementQuery = CIBlockElement::getList(array(), array("ID"=>$ID, "SHOW_HISTORY"=>"Y"), false, false,
			array(
				"ID",
				"TIMESTAMP_X",
				"MODIFIED_BY",
				"DATE_CREATE",
				"CREATED_BY",
				"IBLOCK_ID",
				"ACTIVE",
				"ACTIVE_FROM",
				"ACTIVE_TO",
				"SORT",
				"NAME",
				"PREVIEW_PICTURE",
				"PREVIEW_TEXT",
				"PREVIEW_TEXT_TYPE",
				"DETAIL_PICTURE",
				"DETAIL_TEXT",
				"DETAIL_TEXT_TYPE",
				"WF_STATUS_ID",
				"WF_PARENT_ELEMENT_ID",
				"WF_NEW",
				"WF_COMMENTS",
				"IN_SECTIONS",
				"CODE",
				"TAGS",
				"XML_ID",
				"TMP_ID",
			)
		);
		if($element = $elementQuery->fetch())
		{
			$parentId = intval($element["WF_PARENT_ELEMENT_ID"]);
			if($parentId)
			{
				$elementObject = new CIBlockElement;
				$element["WF_PARENT_ELEMENT_ID"] = false;

				if($element["PREVIEW_PICTURE"])
					$element["PREVIEW_PICTURE"] = CFile::makeFileArray($element["PREVIEW_PICTURE"]);
				else
					$element["PREVIEW_PICTURE"] = array("tmp_name" => "", "del" => "Y");

				if($element["DETAIL_PICTURE"])
					$element["DETAIL_PICTURE"] = CFile::makeFileArray($element["DETAIL_PICTURE"]);
				else
					$element["DETAIL_PICTURE"] = array("tmp_name" => "", "del" => "Y");

				$element["IBLOCK_SECTION"] = array();
				if($element["IN_SECTIONS"] == "Y")
				{
					$sectionsQuery = CIBlockElement::getElementGroups($element["ID"], true, array('ID', 'IBLOCK_ELEMENT_ID'));
					while($section = $sectionsQuery->fetch())
						$element["IBLOCK_SECTION"][] = $section["ID"];
				}

				$element["PROPERTY_VALUES"] = array();
				$props = &$element["PROPERTY_VALUES"];

				//Delete old files
				$propsQuery = CIBlockElement::getProperty($element["IBLOCK_ID"], $parentId, array("value_id" => "asc"), array("PROPERTY_TYPE" => "F", "EMPTY" => "N"));
				while($prop = $propsQuery->fetch())
				{
					if(!array_key_exists($prop["ID"], $props))
						$props[$prop["ID"]] = array();
					$props[$prop["ID"]][$prop["PROPERTY_VALUE_ID"]] = array(
						"VALUE" => array("tmp_name" => "", "del" => "Y"),
						"DESCRIPTION" => false,
					);
				}

				//Add new proiperty values
				$propsQuery = CIBlockElement::getProperty($element["IBLOCK_ID"], $element["ID"], array("value_id" => "asc"));
				$i = 0;
				while($prop = $propsQuery->fetch())
				{
					$i++;
					if(!array_key_exists($prop["ID"], $props))
						$props[$prop["ID"]] = array();

					if($prop["PROPERTY_VALUE_ID"])
					{
						if($prop["PROPERTY_TYPE"] == "F")
							$props[$prop["ID"]]["n".$i] = array(
								"VALUE" => CFile::makeFileArray($prop["VALUE"]),
								"DESCRIPTION" => $prop["DESCRIPTION"],
							);
						else
							$props[$prop["ID"]]["n".$i] = array(
								"VALUE" => $prop["VALUE"],
								"DESCRIPTION" => $prop["DESCRIPTION"],
							);
					}
				}

				$elementObject->update($parentId, $element);
				CBPDocument::mergeDocuments(
					array("lists", get_called_class(), $parentId),
					array("lists", get_called_class(), $documentId)
				);
				CIBlockElement::delete($ID);
				CIBlockElement::wF_CleanUpHistoryCopies($parentId, 0);
				$strSql = "update b_iblock_element set WF_STATUS_ID='1', WF_NEW=NULL WHERE ID=".$parentId." AND WF_PARENT_ELEMENT_ID IS NULL";
				$DB->Query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
				CIBlockElement::updateSearch($parentId);
				return $parentId;
			}
			else
			{
				CIBlockElement::wF_CleanUpHistoryCopies($ID, 0);
				$strSql = "update b_iblock_element set WF_STATUS_ID='1', WF_NEW=NULL WHERE ID=".$ID." AND WF_PARENT_ELEMENT_ID IS NULL";
				$DB->Query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
				CIBlockElement::updateSearch($ID);
				return $ID;
			}
		}
		return false;
	}

	/**
	 * Method return array with all information about document. Array used for method RecoverDocumentFromHistory.
	 * @param string $documentId
	 * @param $historyIndex
	 * @return null
	 * @throws CBPArgumentNullException
	 */
	public static function GetDocumentForHistory($documentId, $historyIndex)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		$result = null;

		$dbDocumentList = CIBlockElement::getList(
			array(),
			array("ID" => $documentId, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y")
		);
		if ($objDocument = $dbDocumentList->getNextElement())
		{
			$fields = $objDocument->getFields();
			$properties = $objDocument->getProperties();

			$result["NAME"] = $fields["~NAME"];

			$result["FIELDS"] = array();
			foreach ($fields as $fieldKey => $fieldValue)
			{
				if ($fieldKey == "~PREVIEW_PICTURE" || $fieldKey == "~DETAIL_PICTURE")
				{
					$result["FIELDS"][mb_substr($fieldKey, 1)] = CBPDocument::prepareFileForHistory(
						array("lists", get_called_class(), $documentId),
						$fieldValue,
						$historyIndex
					);
				}
				elseif (mb_substr($fieldKey, 0, 1) == "~")
				{
					$result["FIELDS"][mb_substr($fieldKey, 1)] = $fieldValue;
				}
			}

			$result["PROPERTIES"] = array();
			foreach ($properties as $propertyKey => $propertyValue)
			{
				if ($propertyValue["USER_TYPE"] <> '')
				{
					$result["PROPERTIES"][$propertyKey] = array(
						"VALUE" => $propertyValue["VALUE"],
						"DESCRIPTION" => $propertyValue["DESCRIPTION"]
					);
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "L")
				{
					$result["PROPERTIES"][$propertyKey] = array(
						"VALUE" => $propertyValue["VALUE_ENUM_ID"],
						"DESCRIPTION" => $propertyValue["DESCRIPTION"]
					);
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "F")
				{
					$result["PROPERTIES"][$propertyKey] = array(
						"VALUE" => CBPDocument::prepareFileForHistory(
							array("lists", get_called_class(), $documentId),
							$propertyValue["VALUE"],
							$historyIndex
						),
						"DESCRIPTION" => $propertyValue["DESCRIPTION"]
					);
				}
				else
				{
					$result["PROPERTIES"][$propertyKey] = array(
						"VALUE" => $propertyValue["VALUE"],
						"DESCRIPTION" => $propertyValue["DESCRIPTION"]
					);
				}
			}
		}

		return $result;
	}

	public static function isFeatureEnabled($documentType, $feature)
	{
		return in_array($feature, array(\CBPDocumentService::FEATURE_MARK_MODIFIED_FIELDS));
	}
}
