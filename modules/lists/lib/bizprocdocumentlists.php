<?php

namespace Bitrix\Lists;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('bizproc'))
{
	return;
}

class BizprocDocumentLists extends \BizprocDocument
{
	public static function getEntityName()
	{
		return Loc::getMessage('LISTS_BIZPROC_ENTITY_LISTS_NAME');
	}

	/**
	 * @param $documentId
	 * @return array
	 * @throws \CBPArgumentNullException
	 * @throws \CBPArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public static function getDocument($documentId)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new \CBPArgumentNullException('documentId');

		$result = array();
		$element = array();
		$elementProperty = array();

		$queryElement = \CIBlockElement::getList(array(),
			array('ID' => $documentId, 'SHOW_NEW'=>'Y', 'SHOW_HISTORY' => 'Y'));
		while($queryResult= $queryElement->fetch())
		{
			$element = $queryResult;
			$queryProperty = \CIBlockElement::getProperty(
				$queryResult['IBLOCK_ID'],
				$queryResult['ID'],
				array('sort'=>'asc', 'id'=>'asc', 'enum_sort'=>'asc', 'value_id'=>'asc'),
				array('ACTIVE'=>'Y', 'EMPTY'=>'N')
			);
			while($property = $queryProperty->fetch())
			{
				$propertyKey = 'PROPERTY_'.$property['ID'];
				if($property['MULTIPLE'] == 'Y')
				{
					if(!array_key_exists($propertyKey, $elementProperty))
					{
						$elementProperty[$propertyKey] = $property;
						$elementProperty[$propertyKey]['VALUE'] = array();
					}
					$elementProperty[$propertyKey]['VALUE'][] = $property['VALUE'];
				}
				else
				{
					$elementProperty[$propertyKey] = $property;
				}
			}
		}

		foreach($element as $fieldId => $fieldValue)
		{
			$result[$fieldId] = $fieldValue;
			if (in_array($fieldId, array('MODIFIED_BY', 'CREATED_BY')))
			{
				$result[$fieldId] = 'user_'.$fieldValue;
				$result[$fieldId.'_PRINTABLE'] = $element[($fieldId == 'MODIFIED_BY')
					? 'USER_NAME' : 'CREATED_USER_NAME'];
			}
			elseif (in_array($fieldId, array('PREVIEW_TEXT', 'DETAIL_TEXT')))
			{
				if ($element[$fieldId.'_TYPE'] == 'html')
					$result[$fieldId] = HTMLToTxt($fieldValue);
			}
		}
		foreach($elementProperty as $propertyId => $property)
		{
			if(trim($property['CODE']) <> '')
				$propertyId = $property['CODE'];
			else
				$propertyId = $property['ID'];

			if(!empty($property['USER_TYPE']))
			{
				if ($property['USER_TYPE'] == 'UserID' || $property['USER_TYPE'] == 'employee' &&
					(\COption::getOptionString('bizproc', 'employee_compatible_mode', 'N') != 'Y'))
				{
					if(empty($property['VALUE']))
						continue;
					if(!is_array($property['VALUE']))
						$property['VALUE'] = array($property['VALUE']);

					$listUsers = implode(' | ', $property['VALUE']);
					$userQuery = \CUser::getList('ID', 'ASC',
						array('ID' => $listUsers) ,
						array('FIELDS' => array('ID' ,'LOGIN', 'NAME', 'LAST_NAME')));
					while($user = $userQuery->fetch())
					{
						if($property['MULTIPLE'] == 'Y')
						{
							$result = self::setArray($result, 'PROPERTY_'.$propertyId);
							$result = self::setArray($result, 'PROPERTY_'.$propertyId.'_PRINTABLE');
							$result['PROPERTY_'.$propertyId][] = 'user_'.intval($user['ID']);
							$result['PROPERTY_'.$propertyId.'_PRINTABLE'][] = '('.$user['LOGIN'].')'.
								(($user['NAME'] <> '' || $user['LAST_NAME'] <> '') ? ' ' : '').$user['NAME'].
								(($user['NAME'] <> '' && $user['LAST_NAME'] <> '') ? ' ' : '').$user['LAST_NAME'];
						}
						else
						{
							$result['PROPERTY_'.$propertyId] = 'user_'.intval($user['ID']);
							$result['PROPERTY_'.$propertyId.'_PRINTABLE'] = '('.$user['LOGIN'].')'.
								(($user['NAME'] <> '' || $user['LAST_NAME'] <> '') ? ' ' : '').$user['NAME'].
								(($user['NAME'] <> '' && $user['LAST_NAME'] <> '') ? ' ' : '').$user['LAST_NAME'];
						}
					}
				}
				elseif($property['USER_TYPE'] == 'DiskFile')
				{
					if (is_array($property['VALUE']))
					{
						$diskValues = current($property['VALUE']);
						$userType = \CIBlockProperty::getUserType($property['USER_TYPE']);
						$result = self::setArray($result, 'PROPERTY_'.$propertyId);
						$result = self::setArray($result, 'PROPERTY_'.$propertyId.'_PRINTABLE');
						foreach($diskValues as $attachedId)
						{
							$fileId = null;
							if (array_key_exists('GetObjectId', $userType))
								$fileId = call_user_func_array($userType['GetObjectId'], array($attachedId));
							if(!$fileId)
								continue;
							$printableUrl = '';
							if (array_key_exists('GetUrlAttachedFileElement', $userType))
								$printableUrl = call_user_func_array($userType['GetUrlAttachedFileElement'],
									array($documentId, $fileId));

							$result['PROPERTY_'.$propertyId][$attachedId] = $fileId;
							$result['PROPERTY_'.$propertyId.'_PRINTABLE'][$attachedId] = $printableUrl;
						}
					}
					else
					{
						continue;
					}
				}
				elseif($property['USER_TYPE'] == 'HTML')
				{
					if(\CBPHelper::isAssociativeArray($property['VALUE']))
					{
						if($property['VALUE']['TYPE'] == 'HTML')
						{
							$result['PROPERTY_'.$propertyId] = HTMLToTxt($property['VALUE']['TEXT']);
						}
						else
						{
							$result['PROPERTY_'.$propertyId] = $property['VALUE']['TEXT'];
						}
					}
					else
					{
						$result = self::setArray($result, 'PROPERTY_'.$propertyId);
						foreach($property['VALUE'] as $htmlValue)
						{
							if($htmlValue['TYPE'] == 'HTML')
							{
								$result['PROPERTY_'.$propertyId][] = HTMLToTxt($htmlValue['TEXT']);
							}
							else
							{
								$result['PROPERTY_'.$propertyId][] = $htmlValue['TEXT'];
							}
						}
					}
				}
				elseif($property['USER_TYPE'] == 'Money')
				{
					$userType = \CIBlockProperty::getUserType($property['USER_TYPE']);
					if(is_array($property['VALUE']))
					{
						$result = self::setArray($result, 'PROPERTY_'.$propertyId);
						$result = self::setArray($result, 'PROPERTY_'.$propertyId.'_PRINTABLE');
						foreach($property['VALUE'] as $moneyValue)
						{
							$result['PROPERTY_'.$propertyId][] = $moneyValue;
							if(array_key_exists('GetPublicViewHTML', $userType))
							{
								$result['PROPERTY_'.$propertyId.'_PRINTABLE'][] = call_user_func_array(
									$userType['GetPublicViewHTML'],
									array($property, array('VALUE' => $moneyValue), array())
								);
							}
						}
					}
					else
					{
						$result['PROPERTY_'.$propertyId] = $property['VALUE'];
						if(array_key_exists('GetPublicViewHTML', $userType))
						{
							$result['PROPERTY_'.$propertyId.'_PRINTABLE'] = call_user_func_array(
								$userType['GetPublicViewHTML'],
								array($property, array('VALUE' => $property['VALUE']), array())
							);
						}
					}
				}
				else
				{
					$result['PROPERTY_'.$propertyId] = $property['VALUE'];
				}
			}
			elseif ($property['PROPERTY_TYPE'] == 'L')
			{
				$result = self::setArray($result, 'PROPERTY_'.$propertyId);
				//$result = self::setArray($result, 'PROPERTY_'.$propertyId.'_PRINTABLE');
				$propertyArray = array();
				$propertyKeyArray = array();
				if(!is_array($property['VALUE']))
					$property['VALUE'] = array($property['VALUE']);
				foreach($property['VALUE'] as $enumId)
				{
					$enumsObject = \CIBlockProperty::getPropertyEnum(
						$property['ID'],
						array('SORT' => 'asc'),
						array('ID' => $enumId)
					);
					while($enums = $enumsObject->fetch())
					{
						$propertyArray[] = $enums['VALUE'];
						$propertyKeyArray[] = (self::getVersion() > 1) ? $enums['XML_ID'] : $enums['ID'];
					}
				}
				for ($i = 0, $cnt = count($propertyArray); $i < $cnt; $i++)
					$result['PROPERTY_'.$propertyId][$propertyKeyArray[$i]] = $propertyArray[$i];
			}
			elseif ($property['PROPERTY_TYPE'] == 'F')
			{
				$result = self::setArray($result, 'PROPERTY_'.$propertyId);
				$result = self::setArray($result, 'PROPERTY_'.$propertyId.'_PRINTABLE');
				$propertyArray = $property['VALUE'];
				if (!is_array($propertyArray))
					$propertyArray = array($propertyArray);

				foreach ($propertyArray as $v)
				{
					$fileArray = \CFile::getFileArray($v);
					if ($fileArray)
					{
						$result['PROPERTY_'.$propertyId][] = intval($v);
						$result['PROPERTY_'.$propertyId.'_PRINTABLE'][] =
							"[url=/bitrix/tools/bizproc_show_file.php?f=".
							urlencode($fileArray["FILE_NAME"])."&i=".$v."&h=".md5($fileArray["SUBDIR"])."]".
							htmlspecialcharsbx($fileArray["ORIGINAL_NAME"])."[/url]";
					}
				}
			}
			else
			{
				$result['PROPERTY_'.$propertyId] = $property['VALUE'];
			}
		}

		if(!empty($result))
		{
			$documentFields = static::getDocumentFields(static::getDocumentType($documentId));
			foreach ($documentFields as $fieldKey => $field)
			{
				if (!array_key_exists($fieldKey, $result))
					$result[$fieldKey] = null;
			}
		}

		return $result;
	}

	/**
	 * @param string $documentType
	 * @return array
	 * @throws \CBPArgumentOutOfRangeException
	 */
	public static function getDocumentFields($documentType)
	{
		$iblockId = intval(mb_substr($documentType, mb_strlen("iblock_")));
		if ($iblockId <= 0)
			throw new \CBPArgumentOutOfRangeException("documentType", $documentType);

		$documentFieldTypes = self::getDocumentFieldTypes($documentType);

		$result = self::getSystemIblockFields();

		$propertyObject = \CIBlockProperty::getList(
			array("sort" => "asc", "name" => "asc"),
			array("IBLOCK_ID" => $iblockId, 'ACTIVE' => 'Y')
		);
		$ignoreProperty = array();
		while ($property = $propertyObject->fetch())
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
					|| $property["USER_TYPE"] == "employee" && (\COption::getOptionString("bizproc", "employee_compatible_mode", "N") != "Y"))
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
				}
				elseif ($property["USER_TYPE"] == "DateTime")
				{
					$result[$key]["Type"] = "datetime";
				}
				elseif ($property["USER_TYPE"] == "Date")
				{
					$result[$key]["Type"] = "date";
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
				}
				else
				{
					$result[$key]["Type"] = "string";
				}
			}
			elseif ($property["PROPERTY_TYPE"] == "L")
			{
				$result[$key]["Type"] = "select";

				$result[$key]["Options"] = array();
				$dbPropertyEnums = \CIBlockProperty::getPropertyEnum($property["ID"]);
				while ($listPropertyEnum = $dbPropertyEnums->getNext())
				{
					$result[$key]["Options"][(self::getVersion() > 1) ?
						$listPropertyEnum["XML_ID"] : $listPropertyEnum["ID"]] = $listPropertyEnum["~VALUE"];
				}
			}
			elseif ($property["PROPERTY_TYPE"] == "N")
			{
				$result[$key]["Type"] = "double";
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
			}
		}

		$list = new \CList($iblockId);
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
				if($field["ROW_COUNT"] && $field["COL_COUNT"])
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
			$result[$k]["Complex"] = $documentFieldTypes[$result[$k]["Type"]]["Complex"];
		}

		return $result;
	}

	public static function isFeatureEnabled($documentType, $feature)
	{
		return in_array($feature, array(\CBPDocumentService::FEATURE_MARK_MODIFIED_FIELDS));
	}

	public static function getDocumentAdminPage($documentId)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new \CBPArgumentNullException("documentId");

		$db = \CIBlockElement::getList(
			array(),
			array("ID" => $documentId, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y"),
			false,
			false,
			array("ID", "IBLOCK_ID", "IBLOCK_TYPE_ID", "DETAIL_PAGE_URL")
		);
		if ($ar = $db->fetch())
		{
			foreach(GetModuleEvents("iblock", "CIBlockDocument_OnGetDocumentAdminPage", true) as $arEvent)
			{
				$url = ExecuteModuleEventEx($arEvent, array($ar));
				if($url)
					return $url;
			}
			return "/bitrix/admin/iblock_element_edit.php?view=Y&ID=".$documentId."&IBLOCK_ID=".
				$ar["IBLOCK_ID"]."&type=".$ar["IBLOCK_TYPE_ID"];
		}

		return null;
	}
}
