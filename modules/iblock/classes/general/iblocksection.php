<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Iblock;

IncludeModuleLangFile(__FILE__);

class CAllIBlockSection
{
	public string $LAST_ERROR = '';
	protected static $arSectionCodeCache = array();
	protected static $arSectionPathCache = array();
	protected static $arSectionNavChainCache = array();

	protected ?array $iblock;
	protected ?string $iblockLanguage;

	protected string $currentDateTimeFunction;

	public function __construct()
	{
		$this->iblock = null;
		$this->iblockLanguage = null;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$this->currentDateTimeFunction = $helper->getCurrentDateTimeFunction();
		unset($helper, $connection);
	}

	public function setIblock(?int $iblockId): void
	{
		$iblock = null;
		$language = null;
		if ($iblockId !== null)
		{
			$iblock = CIBlock::GetArrayByID($iblockId);
			if (!is_array($iblock))
			{
				$iblock = null;
			}
			else
			{
				$iblock['ID'] = (int)$iblock['ID'];
				$language = static::getIblockLanguage($iblock['ID']);
			}
		}
		$this->iblock = $iblock;
		$this->iblockLanguage = $language;
	}

	public static function GetFilter($arFilter=Array())
	{
		global $DB;
		$arIBlockFilter = Array();
		$arSqlSearch = Array();
		$bSite = false;
		foreach($arFilter as $key => $val)
		{
			$res = CIBlock::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			$key = mb_strtoupper($key);
			switch($key)
			{
			case "ACTIVE":
			case "GLOBAL_ACTIVE":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "string_equal", $cOperationType);
				break;
			case "IBLOCK_ACTIVE":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.ACTIVE", $val, "string_equal", $cOperationType);
				break;
			case "LID":
			case "SITE_ID":
				$str = CIBlock::FilterCreate("BS.SITE_ID", $val, "string_equal", $cOperationType);
				if($str <> '')
				{
					$arIBlockFilter[] = $str;
					$bSite = true;
				}
				break;
			case "IBLOCK_NAME":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.NAME", $val, "string", $cOperationType);
				break;
			case "IBLOCK_EXTERNAL_ID":
			case "IBLOCK_XML_ID":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.XML_ID", $val, "string", $cOperationType);
				break;
			case "IBLOCK_TYPE":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.IBLOCK_TYPE_ID", $val, "string", $cOperationType);
				break;
			case "TIMESTAMP_X":
			case "DATE_CREATE":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "date", $cOperationType);
				break;
			case "IBLOCK_CODE":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.CODE", $val, "string", $cOperationType);
				break;
			case "IBLOCK_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "number", $cOperationType);
				$arIBlockFilter[] = CIBlock::FilterCreate("B.ID", $val, "number", $cOperationType);
				break;
			case "NAME":
			case "XML_ID":
			case "TMP_ID":
			case "CODE":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "string", $cOperationType);
				break;
			case "EXTERNAL_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.XML_ID", $val, "string", $cOperationType);
				break;
			case "ID":
			case "DEPTH_LEVEL":
			case "MODIFIED_BY":
			case "CREATED_BY":
			case "SOCNET_GROUP_ID":
			case "PICTURE":
			case "DETAIL_PICTURE":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "number", $cOperationType);
				break;
			case "SECTION_ID":
				if(!is_array($val) && (int)$val<=0)
					$arSqlSearch[] = CIBlock::FilterCreate("BS.IBLOCK_SECTION_ID", "", "number", $cOperationType, false);
				else
					$arSqlSearch[] = CIBlock::FilterCreate("BS.IBLOCK_SECTION_ID", $val, "number", $cOperationType);
				break;
			case "RIGHT_MARGIN":
				$arSqlSearch[] = "BS.RIGHT_MARGIN ".($cOperationType=="N"?">":"<=").(int)$val;
				break;
			case "LEFT_MARGIN":
				$arSqlSearch[] = "BS.LEFT_MARGIN ".($cOperationType=="N"?"<":">=").(int)$val;
				break;
			case "LEFT_BORDER":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.LEFT_MARGIN", $val, "number", $cOperationType);
				break;
			case "RIGHT_BORDER":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.RIGHT_MARGIN", $val, "number", $cOperationType);
				break;
			case "HAS_ELEMENT":
				$arSqlSearch[] = "EXISTS (
					SELECT BS1.ID
					FROM b_iblock_section BS1
					INNER JOIN b_iblock_section_element BSE1 ON BSE1.IBLOCK_SECTION_ID = BS1.ID
						AND BSE1.ADDITIONAL_PROPERTY_ID IS NULL
					INNER JOIN b_iblock_element BE1 ON BE1.ID = BSE1.IBLOCK_ELEMENT_ID
					WHERE BE1.ID = ".intval($val)."
					AND BS1.LEFT_MARGIN >= BS.LEFT_MARGIN
					AND BS1.RIGHT_MARGIN <= BS.RIGHT_MARGIN
				)";
				break;
			}
		}

		static $IBlockFilter_cache = array();
		if($bSite)
		{
			if(is_array($arIBlockFilter) && count($arIBlockFilter)>0)
			{
				$sIBlockFilter = "";
				foreach($arIBlockFilter as $val)
					if($val <> '')
						$sIBlockFilter .= "  AND ".$val;

				if(!array_key_exists($sIBlockFilter, $IBlockFilter_cache))
				{
					$strSql =
						"SELECT DISTINCT B.ID ".
						"FROM b_iblock B, b_iblock_site BS ".
						"WHERE B.ID = BS.IBLOCK_ID ".
							$sIBlockFilter;

					$arIBLOCKFilter = array();
					$dbRes = $DB->Query($strSql);
					while($arRes = $dbRes->Fetch())
						$arIBLOCKFilter[] = $arRes["ID"];
					$IBlockFilter_cache[$sIBlockFilter] = $arIBLOCKFilter;
				}
				else
				{
					$arIBLOCKFilter = $IBlockFilter_cache[$sIBlockFilter];
				}

				if(count($arIBLOCKFilter) > 0)
					$arSqlSearch[] = "B.ID IN (".implode(", ", $arIBLOCKFilter).") ";
			}
		}
		else
		{
			foreach($arIBlockFilter as $val)
				if($val <> '')
					$arSqlSearch[] = $val;
		}

		return $arSqlSearch;
	}

	public static function GetTreeList($arFilter = array(), $arSelect = array())
	{
		return CIBlockSection::GetList(array("left_margin"=>"asc"), $arFilter, false, $arSelect);
	}

	public static function GetNavChain($IBLOCK_ID, $SECTION_ID, $arSelect = array(), $arrayResult = false)
	{
		global $DB;

		$arrayResult = ($arrayResult === true);

		$IBLOCK_ID = (int)$IBLOCK_ID;

		$arFields = array(
			"ID" => "BS.ID",
			"CODE" => "BS.CODE",
			"XML_ID" => "BS.XML_ID",
			"EXTERNAL_ID" => "BS.XML_ID",
			"IBLOCK_ID" => "BS.IBLOCK_ID",
			"IBLOCK_SECTION_ID" => "BS.IBLOCK_SECTION_ID",
			"SORT" => "BS.SORT",
			"NAME" => "BS.NAME",
			"ACTIVE" => "BS.ACTIVE",
			"GLOBAL_ACTIVE" => "BS.GLOBAL_ACTIVE",
			"PICTURE" => "BS.PICTURE",
			"DESCRIPTION" => "BS.DESCRIPTION",
			"DESCRIPTION_TYPE" => "BS.DESCRIPTION_TYPE",
			"LEFT_MARGIN" => "BS.LEFT_MARGIN",
			"RIGHT_MARGIN" => "BS.RIGHT_MARGIN",
			"DEPTH_LEVEL" => "BS.DEPTH_LEVEL",
			"SEARCHABLE_CONTENT" => "BS.SEARCHABLE_CONTENT",
			"MODIFIED_BY" => "BS.MODIFIED_BY",
			"CREATED_BY" => "BS.CREATED_BY",
			"DETAIL_PICTURE" => "BS.DETAIL_PICTURE",
			"TMP_ID" => "BS.TMP_ID",

			"LIST_PAGE_URL" => "B.LIST_PAGE_URL",
			"SECTION_PAGE_URL" => "B.SECTION_PAGE_URL",
			"IBLOCK_TYPE_ID" => "B.IBLOCK_TYPE_ID",
			"IBLOCK_CODE" => "B.CODE",
			"IBLOCK_EXTERNAL_ID" => "B.XML_ID",
			"SOCNET_GROUP_ID" => "BS.SOCNET_GROUP_ID",
		);

		$arSqlSelect = array();
		foreach($arSelect as $field)
		{
			$field = mb_strtoupper($field);
			if (isset($arFields[$field]))
				$arSqlSelect[$field] = $arFields[$field]." AS ".$field;
		}

		if (isset($arSqlSelect["DESCRIPTION"]))
			$arSqlSelect["DESCRIPTION_TYPE"] = $arFields["DESCRIPTION_TYPE"]." AS DESCRIPTION_TYPE";

		if (isset($arSqlSelect["LIST_PAGE_URL"]) || isset($arSqlSelect["SECTION_PAGE_URL"]))
		{
			$arSqlSelect["ID"] = $arFields["ID"]." AS ID";
			$arSqlSelect["CODE"] = $arFields["CODE"]." AS CODE";
			$arSqlSelect["EXTERNAL_ID"] = $arFields["EXTERNAL_ID"]." AS EXTERNAL_ID";
			$arSqlSelect["IBLOCK_TYPE_ID"] = $arFields["IBLOCK_TYPE_ID"]." AS IBLOCK_TYPE_ID";
			$arSqlSelect["IBLOCK_ID"] = $arFields["IBLOCK_ID"]." AS IBLOCK_ID";
			$arSqlSelect["IBLOCK_CODE"] = $arFields["IBLOCK_CODE"]." AS IBLOCK_CODE";
			$arSqlSelect["IBLOCK_EXTERNAL_ID"] = $arFields["IBLOCK_EXTERNAL_ID"]." AS IBLOCK_EXTERNAL_ID";
			$arSqlSelect["GLOBAL_ACTIVE"] = $arFields["GLOBAL_ACTIVE"]." AS GLOBAL_ACTIVE";
			//$arr["LANG_DIR"],
		}

		if (!empty($arSelect))
		{
			$field = "IBLOCK_SECTION_ID";
			$arSqlSelect[$field] = $arFields[$field]." AS ".$field;
			$strSelect = implode(", ", $arSqlSelect);
		}
		else
		{
			$strSelect = "
				BS.*,
				B.LIST_PAGE_URL,
				B.SECTION_PAGE_URL,
				B.IBLOCK_TYPE_ID,
				B.CODE as IBLOCK_CODE,
				B.XML_ID as IBLOCK_EXTERNAL_ID,
				BS.XML_ID as EXTERNAL_ID
			";
		}

		$key = md5($strSelect);
		if (!isset(self::$arSectionNavChainCache[$key]))
			self::$arSectionNavChainCache[$key] = array();

		$sectionPath = array();
		do
		{
			$SECTION_ID = (int)$SECTION_ID;

			if (!isset(self::$arSectionNavChainCache[$key][$SECTION_ID]))
			{
				$rsSection = $DB->Query("
					SELECT
						".$strSelect."
					FROM
						b_iblock_section BS
						INNER JOIN b_iblock B ON B.ID = BS.IBLOCK_ID
					WHERE BS.ID=".$SECTION_ID."
						".($IBLOCK_ID > 0 ? "AND BS.IBLOCK_ID=".$IBLOCK_ID : "")."
				");
				self::$arSectionNavChainCache[$key][$SECTION_ID] = $rsSection->Fetch();
			}

			if (self::$arSectionNavChainCache[$key][$SECTION_ID])
			{
				$sectionPath[] = self::$arSectionNavChainCache[$key][$SECTION_ID];
				$SECTION_ID = self::$arSectionNavChainCache[$key][$SECTION_ID]["IBLOCK_SECTION_ID"];
			}
			else
			{
				$SECTION_ID = 0;
			}
		}
		while ($SECTION_ID > 0);

		$sectionPath = array_reverse($sectionPath);
		if ($arrayResult)
			return $sectionPath;

		$res = new CDBResult;
		$res->InitFromArray($sectionPath);
		$res = new CIBlockResult($res);
		$res->bIBlockSection = true;
		return $res;
	}

	///////////////////////////////////////////////////////////////////
	// Function returns section by ID
	///////////////////////////////////////////////////////////////////
	public static function GetByID($ID)
	{
		return CIBlockSection::GetList(Array(), Array("ID"=>(int)$ID));
	}

	///////////////////////////////////////////////////////////////////
	// New section
	///////////////////////////////////////////////////////////////////
	public function Add($arFields, $bResort=true, $bUpdateSearch=true, $bResizePictures=false)
	{
		global $USER, $DB, $APPLICATION;

		if (array_key_exists('EXTERNAL_ID', $arFields))
		{
			$arFields['XML_ID'] = $arFields['EXTERNAL_ID'];
			unset($arFields['EXTERNAL_ID']);
		}
		unset($arFields["GLOBAL_ACTIVE"]);
		unset($arFields["DEPTH_LEVEL"]);
		unset($arFields["LEFT_MARGIN"]);
		unset($arFields["RIGHT_MARGIN"]);

		$strWarning = '';

		if ($this->iblock !== null && $this->iblock['ID'] === (int)$arFields["IBLOCK_ID"])
		{
			$arIBlock = $this->iblock;
		}
		else
		{
			$arIBlock = CIBlock::GetArrayByID($arFields["IBLOCK_ID"]);
		}

		if($bResizePictures && is_array($arIBlock))
		{
			$arDef = $arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"];

			if(
				$arDef["FROM_DETAIL"] === "Y"
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arFields["DETAIL_PICTURE"]["size"] > 0
				&& (
					$arDef["UPDATE_WITH_DETAIL"] === "Y"
					|| $arFields["PICTURE"]["size"] <= 0
				)
			)
			{
				$arNewPreview = $arFields["DETAIL_PICTURE"];
				$arNewPreview["COPY_FILE"] = "Y";
				$arNewPreview["description"] = $arFields["PICTURE"]["description"];
				$arFields["PICTURE"] = $arNewPreview;
			}

			if(
				array_key_exists("PICTURE", $arFields)
				&& is_array($arFields["PICTURE"])
				&& $arDef["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["PICTURE"], $arDef);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["PICTURE"]["description"];
					$arFields["PICTURE"] = $arNewPicture;
				}
				elseif($arDef["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arNewPicture."<br>";
				}
			}

			if(
				array_key_exists("PICTURE", $arFields)
				&& is_array($arFields["PICTURE"])
				&& $arDef["USE_WATERMARK_FILE"] === "Y"
			)
			{
				if(
					$arFields["PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["PICTURE"]["tmp_name"] === $arFields["DETAIL_PICTURE"]["tmp_name"]
						|| ($arFields["PICTURE"]["COPY_FILE"] == "Y" && !$arFields["PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["PICTURE"]["tmp_name"], $tmp_name);
					$arFields["PICTURE"]["copy"] = true;
					$arFields["PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_FILE_POSITION"],
					"type" => "file",
					"size" => "real",
					"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
					"file" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_FILE"]),
				));
			}

			if(
				array_key_exists("PICTURE", $arFields)
				&& is_array($arFields["PICTURE"])
				&& $arDef["USE_WATERMARK_TEXT"] === "Y"
			)
			{
				if(
					$arFields["PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["PICTURE"]["tmp_name"] === $arFields["DETAIL_PICTURE"]["tmp_name"]
						|| ($arFields["PICTURE"]["COPY_FILE"] == "Y" && !$arFields["PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["PICTURE"]["tmp_name"], $tmp_name);
					$arFields["PICTURE"]["copy"] = true;
					$arFields["PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_TEXT_POSITION"],
					"type" => "text",
					"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
					"text" => $arDef["WATERMARK_TEXT"],
					"font" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
					"color" => $arDef["WATERMARK_TEXT_COLOR"],
				));
			}

			$arDef = $arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["DEFAULT_VALUE"];

			if(
				array_key_exists("DETAIL_PICTURE", $arFields)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["DETAIL_PICTURE"], $arDef);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["DETAIL_PICTURE"]["description"];
					$arFields["DETAIL_PICTURE"] = $arNewPicture;
				}
				elseif($arDef["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["DETAIL_PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_DETAIL_PICTURE").": ".$arNewPicture."<br>";
				}
			}

			if(
				array_key_exists("DETAIL_PICTURE", $arFields)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["USE_WATERMARK_FILE"] === "Y"
			)
			{
				if(
					$arFields["DETAIL_PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["DETAIL_PICTURE"]["tmp_name"] === $arFields["PICTURE"]["tmp_name"]
						|| ($arFields["DETAIL_PICTURE"]["COPY_FILE"] == "Y" && !$arFields["DETAIL_PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["DETAIL_PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["DETAIL_PICTURE"]["tmp_name"], $tmp_name);
					$arFields["DETAIL_PICTURE"]["copy"] = true;
					$arFields["DETAIL_PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["DETAIL_PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_FILE_POSITION"],
					"type" => "file",
					"size" => "real",
					"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
					"file" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_FILE"]),
				));
			}

			if(
				array_key_exists("DETAIL_PICTURE", $arFields)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["USE_WATERMARK_TEXT"] === "Y"
			)
			{
				if(
					$arFields["DETAIL_PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["DETAIL_PICTURE"]["tmp_name"] === $arFields["PICTURE"]["tmp_name"]
						|| ($arFields["DETAIL_PICTURE"]["COPY_FILE"] == "Y" && !$arFields["DETAIL_PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["DETAIL_PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["DETAIL_PICTURE"]["tmp_name"], $tmp_name);
					$arFields["DETAIL_PICTURE"]["copy"] = true;
					$arFields["DETAIL_PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["DETAIL_PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_TEXT_POSITION"],
					"type" => "text",
					"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
					"text" => $arDef["WATERMARK_TEXT"],
					"font" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
					"color" => $arDef["WATERMARK_TEXT_COLOR"],
				));
			}
		}

		$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($arFields["IBLOCK_ID"], 0);
		if (array_key_exists("PICTURE", $arFields))
		{
			if (!is_array($arFields["PICTURE"]))
			{
				unset($arFields["PICTURE"]);
			}
			elseif (
				($arFields["PICTURE"]["name"] ?? '') === ''
				&& ($arFields["PICTURE"]["del"] ?? '') === ''
			)
			{
				unset($arFields["PICTURE"]);
			}
			else
			{
				$arFields["PICTURE"]["MODULE_ID"] = "iblock";
				if (isset($arFields["PICTURE"]["name"]))
				{
					$arFields["PICTURE"]["name"] = \Bitrix\Iblock\Template\Helper::makeFileName(
						$ipropTemplates
						, "SECTION_PICTURE_FILE_NAME"
						, $arFields
						, $arFields["PICTURE"]
					);
				}
			}
		}

		if (array_key_exists("DETAIL_PICTURE", $arFields))
		{
			if (!is_array($arFields["DETAIL_PICTURE"]))
			{
				unset($arFields["DETAIL_PICTURE"]);
			}
			elseif (
				($arFields["DETAIL_PICTURE"]["name"] ?? '') === ''
				&& ($arFields["DETAIL_PICTURE"]["del"] ?? '') === ''
			)
			{
				unset($arFields["DETAIL_PICTURE"]);
			}
			else
			{
				$arFields["DETAIL_PICTURE"]["MODULE_ID"] = "iblock";
				if (isset($arFields["DETAIL_PICTURE"]["name"]))
				{
					$arFields["DETAIL_PICTURE"]["name"] = \Bitrix\Iblock\Template\Helper::makeFileName(
						$ipropTemplates
						, "SECTION_DETAIL_PICTURE_FILE_NAME"
						, $arFields
						, $arFields["DETAIL_PICTURE"]
					);
				}
			}
		}

		$arFields["IBLOCK_SECTION_ID"] = isset($arFields["IBLOCK_SECTION_ID"])? intval($arFields["IBLOCK_SECTION_ID"]): 0;
		if($arFields["IBLOCK_SECTION_ID"] == 0)
			$arFields["IBLOCK_SECTION_ID"] = false;

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";
		else
			$arFields["ACTIVE"] = "Y";

		if(!array_key_exists("DESCRIPTION_TYPE", $arFields) || $arFields["DESCRIPTION_TYPE"]!="html")
			$arFields["DESCRIPTION_TYPE"]="text";

		if(!isset($arFields["DESCRIPTION"]))
			$arFields["DESCRIPTION"] = false;

		$arFields["SEARCHABLE_CONTENT"] =
			mb_strtoupper(
				$arFields["NAME"]."\r\n".
				($arFields["DESCRIPTION_TYPE"]=="html" ?
					HTMLToTxt($arFields["DESCRIPTION"]) :
					$arFields["DESCRIPTION"]
				)
			);

		unset($arFields["DATE_CREATE"]);
		$arFields["~DATE_CREATE"] = $DB->CurrentTimeFunction();
		if(is_object($USER))
		{
			$user_id = intval($USER->GetID());
			if(!isset($arFields["CREATED_BY"]) || intval($arFields["CREATED_BY"]) <= 0)
				$arFields["CREATED_BY"] = $user_id;
			if(!isset($arFields["MODIFIED_BY"]) || intval($arFields["MODIFIED_BY"]) <= 0)
				$arFields["MODIFIED_BY"] = $user_id;
		}

		$IBLOCK_ID = intval($arFields["IBLOCK_ID"]);

		if(!$this->CheckFields($arFields))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		elseif($IBLOCK_ID && !$GLOBALS["USER_FIELD_MANAGER"]->CheckFields("IBLOCK_".$IBLOCK_ID."_SECTION", 0, $arFields))
		{
			$Result = false;
			$err = $APPLICATION->GetException();
			if(is_object($err))
				$this->LAST_ERROR .= str_replace("<br><br>", "<br>", $err->GetString()."<br>");
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			if (!isset($arFields['TIMESTAMP_X']))
			{
				$arFields['~TIMESTAMP_X'] = $this->currentDateTimeFunction;
			}

			if(array_key_exists("PICTURE", $arFields))
			{
				$SAVED_PICTURE = $arFields["PICTURE"];
				CFile::SaveForDB($arFields, "PICTURE", "iblock");
			}

			if(array_key_exists("DETAIL_PICTURE", $arFields))
			{
				$SAVED_DETAIL_PICTURE = $arFields["DETAIL_PICTURE"];
				CFile::SaveForDB($arFields, "DETAIL_PICTURE", "iblock");
			}

			$arFields['SORT'] = (int)($arFields['SORT'] ?? 500);

			CIBlock::_transaction_lock($IBLOCK_ID);

			unset($arFields["ID"]);
			$ID = intval($DB->Add("b_iblock_section", $arFields, Array("DESCRIPTION","SEARCHABLE_CONTENT"), "iblock"));
			$arFields["ID"] = $ID;
			unset($arFields['~TIMESTAMP_X']);

			if(array_key_exists("PICTURE", $arFields))
				$arFields["PICTURE"] = $SAVED_PICTURE;
			if(array_key_exists("DETAIL_PICTURE", $arFields))
				$arFields["DETAIL_PICTURE"] = $SAVED_DETAIL_PICTURE;

			if ($bResort)
			{
				self::recountTreeAfterAdd($arFields);
			}

			$GLOBALS["USER_FIELD_MANAGER"]->Update("IBLOCK_".$IBLOCK_ID."_SECTION", $ID, $arFields);

			if($bUpdateSearch)
				CIBlockSection::UpdateSearch($ID);

			if(
				CIBlock::GetArrayByID($IBLOCK_ID, "SECTION_PROPERTY") === "Y"
				&& isset($arFields["SECTION_PROPERTY"])
				&& is_array($arFields["SECTION_PROPERTY"])
			)
			{
				foreach($arFields["SECTION_PROPERTY"] as $PROPERTY_ID => $arLink)
				{
					$arLink['INVALIDATE'] = 'N';
					CIBlockSectionPropertyLink::Add($ID, $PROPERTY_ID, $arLink);
				}
				unset($arLink);
				unset($PROPERTY_ID);
			}

			if($arIBlock["FIELDS"]["LOG_SECTION_ADD"]["IS_REQUIRED"] == "Y")
			{
				$USER_ID = is_object($USER)? intval($USER->GetID()) : 0;
				$arEvents = GetModuleEvents("main", "OnBeforeEventLog", true);
				if(empty($arEvents) || ExecuteModuleEventEx($arEvents[0], array($USER_ID))===false)
				{
					$rsSection = CIBlockSection::GetList(array(), array("=ID"=>$ID), false,  array("LIST_PAGE_URL", "NAME", "CODE"));
					$arSection = $rsSection->GetNext();
					$res = array(
						"ID" => $ID,
						"CODE" => $arSection["CODE"],
						"NAME" => $arSection["NAME"],
						"SECTION_NAME" => $arIBlock["SECTION_NAME"],
						"USER_ID" => $USER_ID,
						"IBLOCK_PAGE_URL" => $arSection["LIST_PAGE_URL"],
					);
					CEventLog::Log(
						"IBLOCK",
						"IBLOCK_SECTION_ADD",
						"iblock",
						$arIBlock["ID"],
						serialize($res)
					);
				}
			}

			if($arIBlock["RIGHTS_MODE"] === "E")
			{
				$obSectionRights = new CIBlockSectionRights($arIBlock["ID"], $ID);
				$obSectionRights->ChangeParents(array(), array($arFields["IBLOCK_SECTION_ID"]));

				if(array_key_exists("RIGHTS", $arFields) && is_array($arFields["RIGHTS"]))
					$obSectionRights->SetRights($arFields["RIGHTS"]);
			}

			if (array_key_exists("IPROPERTY_TEMPLATES", $arFields))
			{
				$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($arIBlock["ID"], $ID);
				$ipropTemplates->set($arFields["IPROPERTY_TEMPLATES"]);
			}

			$Result = $ID;

			/************* QUOTA *************/
			CDiskQuota::recalculateDb();
			/************* QUOTA *************/
		}

		$arFields["RESULT"] = &$Result;

		foreach (GetModuleEvents("iblock", "OnAfterIBlockSectionAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		CIBlock::clearIblockTagCache($arIBlock['ID']);

		return $Result;
	}

	///////////////////////////////////////////////////////////////////
	// Update section properties
	///////////////////////////////////////////////////////////////////
	public function Update($ID, $arFields, $bResort=true, $bUpdateSearch=true, $bResizePictures=false)
	{
		global $USER, $DB, $APPLICATION;

		$ID = (int)$ID;

		$iterator = CIBlockSection::GetList(Array(), Array("ID"=>$ID, "CHECK_PERMISSIONS"=>"N"));
		$db_record = $iterator->Fetch();
		unset($iterator);
		if (empty($db_record))
		{
			return false;
		}

		if (array_key_exists('EXTERNAL_ID', $arFields))
		{
			$arFields['XML_ID'] = $arFields['EXTERNAL_ID'];
			unset($arFields['EXTERNAL_ID']);
		}

		unset($arFields["GLOBAL_ACTIVE"]);
		unset($arFields["DEPTH_LEVEL"]);
		unset($arFields["LEFT_MARGIN"]);
		unset($arFields["RIGHT_MARGIN"]);
		unset($arFields["IBLOCK_ID"]);
		unset($arFields["DATE_CREATE"]);
		unset($arFields["CREATED_BY"]);

		$strWarning = '';

		if ($this->iblock !== null && $this->iblock['ID'] === (int)$db_record["IBLOCK_ID"])
		{
			$arIBlock = $this->iblock;
		}
		else
		{
			$arIBlock = CIBlock::GetArrayByID($db_record["IBLOCK_ID"]);
		}

		if($bResizePictures)
		{
			$arDef = $arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"];

			if(
				$arDef["DELETE_WITH_DETAIL"] === "Y"
				&& $arFields["DETAIL_PICTURE"]["del"] === "Y"
			)
			{
				$arFields["PICTURE"]["del"] = "Y";
			}

			if(
				$arDef["FROM_DETAIL"] === "Y"
				&& (
					$arFields["PICTURE"]["size"] <= 0
					|| $arDef["UPDATE_WITH_DETAIL"] === "Y"
				)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arFields["DETAIL_PICTURE"]["size"] > 0
			)
			{
				if(
					$arFields["PICTURE"]["del"] !== "Y"
					&& $arDef["UPDATE_WITH_DETAIL"] !== "Y"
				)
				{
					$arOldSection = $db_record;
				}
				else
				{
					$arOldSection = false;
				}

				if(!$arOldSection || !$arOldSection["PICTURE"])
				{
					$arNewPreview = $arFields["DETAIL_PICTURE"];
					$arNewPreview["COPY_FILE"] = "Y";
					$arNewPreview["description"] = $arFields["PICTURE"]["description"];
					$arFields["PICTURE"] = $arNewPreview;
				}
			}

			if(
				array_key_exists("PICTURE", $arFields)
				&& is_array($arFields["PICTURE"])
				&& $arFields["PICTURE"]["size"] > 0
				&& $arDef["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["PICTURE"], $arDef);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["PICTURE"]["description"];
					$arFields["PICTURE"] = $arNewPicture;
				}
				elseif($arDef["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arNewPicture."<br>";
				}
			}

			if(
				array_key_exists("PICTURE", $arFields)
				&& is_array($arFields["PICTURE"])
				&& $arDef["USE_WATERMARK_FILE"] === "Y"
			)
			{
				if(
					$arFields["PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["PICTURE"]["tmp_name"] === $arFields["DETAIL_PICTURE"]["tmp_name"]
						|| ($arFields["PICTURE"]["COPY_FILE"] == "Y" && !$arFields["PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["PICTURE"]["tmp_name"], $tmp_name);
					$arFields["PICTURE"]["copy"] = true;
					$arFields["PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_FILE_POSITION"],
					"type" => "file",
					"size" => "real",
					"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
					"file" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_FILE"]),
				));
			}

			if(
				array_key_exists("PICTURE", $arFields)
				&& is_array($arFields["PICTURE"])
				&& $arDef["USE_WATERMARK_TEXT"] === "Y"
			)
			{
				if(
					$arFields["PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["PICTURE"]["tmp_name"] === $arFields["DETAIL_PICTURE"]["tmp_name"]
						|| ($arFields["PICTURE"]["COPY_FILE"] == "Y" && !$arFields["PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["PICTURE"]["tmp_name"], $tmp_name);
					$arFields["PICTURE"]["copy"] = true;
					$arFields["PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_TEXT_POSITION"],
					"type" => "text",
					"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
					"text" => $arDef["WATERMARK_TEXT"],
					"font" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
					"color" => $arDef["WATERMARK_TEXT_COLOR"],
				));
			}

			$arDef = $arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["DEFAULT_VALUE"];

			if(
				array_key_exists("DETAIL_PICTURE", $arFields)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["DETAIL_PICTURE"], $arDef);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["DETAIL_PICTURE"]["description"];
					$arFields["DETAIL_PICTURE"] = $arNewPicture;
				}
				elseif($arDef["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["DETAIL_PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_DETAIL_PICTURE").": ".$arNewPicture."<br>";
				}
			}

			if(
				array_key_exists("DETAIL_PICTURE", $arFields)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["USE_WATERMARK_FILE"] === "Y"
			)
			{
				if(
					$arFields["DETAIL_PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["DETAIL_PICTURE"]["tmp_name"] === $arFields["PICTURE"]["tmp_name"]
						|| ($arFields["DETAIL_PICTURE"]["COPY_FILE"] == "Y" && !$arFields["DETAIL_PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["DETAIL_PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["DETAIL_PICTURE"]["tmp_name"], $tmp_name);
					$arFields["DETAIL_PICTURE"]["copy"] = true;
					$arFields["DETAIL_PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["DETAIL_PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_FILE_POSITION"],
					"type" => "file",
					"size" => "real",
					"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
					"file" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_FILE"]),
					"fill" => "resize",
				));
			}

			if(
				array_key_exists("DETAIL_PICTURE", $arFields)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arDef["USE_WATERMARK_TEXT"] === "Y"
			)
			{
				if(
					$arFields["DETAIL_PICTURE"]["tmp_name"] <> ''
					&& (
						$arFields["DETAIL_PICTURE"]["tmp_name"] === $arFields["PICTURE"]["tmp_name"]
						|| ($arFields["DETAIL_PICTURE"]["COPY_FILE"] == "Y" && !$arFields["DETAIL_PICTURE"]["copy"])
					)
				)
				{
					$tmp_name = CTempFile::GetFileName(basename($arFields["DETAIL_PICTURE"]["tmp_name"]));
					CheckDirPath($tmp_name);
					copy($arFields["DETAIL_PICTURE"]["tmp_name"], $tmp_name);
					$arFields["DETAIL_PICTURE"]["copy"] = true;
					$arFields["DETAIL_PICTURE"]["tmp_name"] = $tmp_name;
				}

				CIBlock::FilterPicture($arFields["DETAIL_PICTURE"]["tmp_name"], array(
					"name" => "watermark",
					"position" => $arDef["WATERMARK_TEXT_POSITION"],
					"type" => "text",
					"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
					"text" => $arDef["WATERMARK_TEXT"],
					"font" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
					"color" => $arDef["WATERMARK_TEXT_COLOR"],
				));
			}
		}

		$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($db_record["IBLOCK_ID"], $db_record["ID"]);
		if (array_key_exists("PICTURE", $arFields))
		{
			if (!is_array($arFields["PICTURE"]))
			{
				unset($arFields["PICTURE"]);
			}
			elseif (
				($arFields["PICTURE"]["name"] ?? '') === ''
				&& ($arFields["PICTURE"]["del"] ?? '') === ''
				&& !array_key_exists("description", $arFields["PICTURE"])
			)
			{
				unset($arFields["PICTURE"]);
			}
			else
			{
				$arFields["PICTURE"]["old_file"] = $db_record["PICTURE"];
				$arFields["PICTURE"]["MODULE_ID"] = "iblock";
				$arFields["PICTURE"]["name"] = \Bitrix\Iblock\Template\Helper::makeFileName(
						$ipropTemplates
						,"SECTION_PICTURE_FILE_NAME"
						,array_merge($db_record, $arFields)
						,$arFields["PICTURE"]
				);
			}
		}

		if (array_key_exists("DETAIL_PICTURE", $arFields))
		{
			if (!is_array($arFields["DETAIL_PICTURE"]))
			{
				unset($arFields["DETAIL_PICTURE"]);
			}
			elseif (
				($arFields["DETAIL_PICTURE"]["name"] ?? '') === ''
				&& ($arFields["DETAIL_PICTURE"]["del"] ?? '') === ''
				&& !array_key_exists("description", $arFields["DETAIL_PICTURE"])
			)
			{
				unset($arFields["DETAIL_PICTURE"]);
			}
			else
			{
				$arFields["DETAIL_PICTURE"]["old_file"] = $db_record["DETAIL_PICTURE"];
				$arFields["DETAIL_PICTURE"]["MODULE_ID"] = "iblock";
				$arFields["DETAIL_PICTURE"]["name"] = \Bitrix\Iblock\Template\Helper::makeFileName(
					$ipropTemplates
					,"SECTION_DETAIL_PICTURE_FILE_NAME"
					,array_merge($db_record, $arFields)
					,$arFields["DETAIL_PICTURE"]
				);
			}
		}

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		if(is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"]!="html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		if(isset($arFields["IBLOCK_SECTION_ID"]))
		{
			$arFields["IBLOCK_SECTION_ID"] = intval($arFields["IBLOCK_SECTION_ID"]);
			if($arFields["IBLOCK_SECTION_ID"] <= 0)
				$arFields["IBLOCK_SECTION_ID"] = false;
		}

		$DESC_tmp = is_set($arFields, "DESCRIPTION")? $arFields["DESCRIPTION"]: $db_record["DESCRIPTION"];
		$DESC_TYPE_tmp = is_set($arFields, "DESCRIPTION_TYPE")? $arFields["DESCRIPTION_TYPE"]: $db_record["DESCRIPTION_TYPE"];

		$arFields["SEARCHABLE_CONTENT"] = mb_strtoupper(
			(is_set($arFields, "NAME")? $arFields["NAME"]: $db_record["NAME"])."\r\n".
			($DESC_TYPE_tmp=="html"? HTMLToTxt($DESC_tmp): $DESC_tmp)
		);

		if(is_object($USER))
		{
			if(!isset($arFields["MODIFIED_BY"]) || intval($arFields["MODIFIED_BY"]) <= 0)
				$arFields["MODIFIED_BY"] = intval($USER->GetID());
		}

		if(!$this->CheckFields($arFields, $ID))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		elseif(!$GLOBALS["USER_FIELD_MANAGER"]->CheckFields("IBLOCK_".$db_record["IBLOCK_ID"]."_SECTION", $ID, $arFields))
		{
			$Result = false;
			$err = $APPLICATION->GetException();
			if(is_object($err))
				$this->LAST_ERROR .= str_replace("<br><br>", "<br>", $err->GetString()."<br>");
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			if(array_key_exists("PICTURE", $arFields))
			{
				$SAVED_PICTURE = $arFields["PICTURE"];
				CFile::SaveForDB($arFields, "PICTURE", "iblock");
			}

			if(array_key_exists("DETAIL_PICTURE", $arFields))
			{
				$SAVED_DETAIL_PICTURE = $arFields["DETAIL_PICTURE"];
				CFile::SaveForDB($arFields, "DETAIL_PICTURE", "iblock");
			}

			if (!isset($arFields['TIMESTAMP_X']))
			{
				$arFields['~TIMESTAMP_X'] = $this->currentDateTimeFunction;
			}

			unset($arFields["ID"]);
			$strUpdate = $DB->PrepareUpdate("b_iblock_section", $arFields, "iblock");
			$arFields["ID"] = $ID;
			unset($arFields['~TIMESTAMP_X']);

			if(array_key_exists("PICTURE", $arFields))
				$arFields["PICTURE"] = $SAVED_PICTURE;
			if(array_key_exists("DETAIL_PICTURE", $arFields))
				$arFields["DETAIL_PICTURE"] = $SAVED_DETAIL_PICTURE;

			CIBlock::_transaction_lock($db_record["IBLOCK_ID"]);

			if($strUpdate <> '')
			{
				$strSql = "UPDATE b_iblock_section SET ".$strUpdate." WHERE ID = ".$ID;
				$arBinds=Array();
				if(array_key_exists("DESCRIPTION", $arFields))
					$arBinds["DESCRIPTION"] = $arFields["DESCRIPTION"];
				if(array_key_exists("SEARCHABLE_CONTENT", $arFields))
					$arBinds["SEARCHABLE_CONTENT"] = $arFields["SEARCHABLE_CONTENT"];
				$DB->QueryBind($strSql, $arBinds);
			}

			if($bResort)
			{
				$this->recountTreeAfterUpdate($arFields, $db_record);
			}

			unset(self::$arSectionCodeCache[$ID]);
			self::$arSectionPathCache = array();
			self::$arSectionNavChainCache = array();

			if($arIBlock["RIGHTS_MODE"] === "E")
			{
				$obSectionRights = new CIBlockSectionRights($arIBlock["ID"], $ID);
				//Check if parent changed with extended rights mode
				if(
					isset($arFields["IBLOCK_SECTION_ID"])
					&& $arFields["IBLOCK_SECTION_ID"] != $db_record["IBLOCK_SECTION_ID"]
				)
				{
					$obSectionRights->ChangeParents(array($db_record["IBLOCK_SECTION_ID"]), array($arFields["IBLOCK_SECTION_ID"]));
				}

				if(array_key_exists("RIGHTS", $arFields) && is_array($arFields["RIGHTS"]))
					$obSectionRights->SetRights($arFields["RIGHTS"]);
			}

			if (array_key_exists("IPROPERTY_TEMPLATES", $arFields))
			{
				$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($arIBlock["ID"], $ID);
				$ipropTemplates->set($arFields["IPROPERTY_TEMPLATES"]);
			}

			$uf_updated = $GLOBALS["USER_FIELD_MANAGER"]->Update("IBLOCK_".$db_record["IBLOCK_ID"]."_SECTION", $ID, $arFields);
			if($uf_updated)
			{
				$DB->Query("UPDATE b_iblock_section SET TIMESTAMP_X = ".$DB->CurrentTimeFunction()." WHERE ID = ".$ID);
			}

			if(
				CIBlock::GetArrayByID($db_record["IBLOCK_ID"], "SECTION_PROPERTY") === "Y"
				&& array_key_exists("SECTION_PROPERTY", $arFields)
				&& is_array($arFields["SECTION_PROPERTY"])
			)
			{
				CIBlockSectionPropertyLink::DeleteBySection($ID, array_keys($arFields["SECTION_PROPERTY"]));
				foreach($arFields["SECTION_PROPERTY"] as $PROPERTY_ID => $arLink)
					CIBlockSectionPropertyLink::Set($ID, $PROPERTY_ID, $arLink);
			}
			if (
				CIBlock::GetArrayByID($db_record["IBLOCK_ID"], "PROPERTY_INDEX") === "Y"
				&& isset($arFields['IBLOCK_SECTION_ID'])
				&& $arFields['IBLOCK_SECTION_ID'] != $db_record['IBLOCK_SECTION_ID']
			)
			{
				Iblock\PropertyIndex\Manager::markAsInvalid($db_record["IBLOCK_ID"]);
			}

			if($bUpdateSearch)
				CIBlockSection::UpdateSearch($ID);

			if($arIBlock["FIELDS"]["LOG_SECTION_EDIT"]["IS_REQUIRED"] == "Y")
			{
				$USER_ID = is_object($USER)? intval($USER->GetID()) : 0;
				$arEvents = GetModuleEvents("main", "OnBeforeEventLog", true);
				if(empty($arEvents) || ExecuteModuleEventEx($arEvents[0],  array($USER_ID))===false)
				{
					$rsSection = CIBlockSection::GetList(array(), array("=ID"=>$ID), false,  array("LIST_PAGE_URL", "NAME", "CODE"));
					$arSection = $rsSection->GetNext();
					$res = array(
						"ID" => $ID,
						"CODE" => $arSection["CODE"],
						"NAME" => $arSection["NAME"],
						"SECTION_NAME" => $arIBlock["SECTION_NAME"],
						"USER_ID" => $USER_ID,
						"IBLOCK_PAGE_URL" => $arSection["LIST_PAGE_URL"],
					);
					CEventLog::Log(
						"IBLOCK",
						"IBLOCK_SECTION_EDIT",
						"iblock",
						$arIBlock["ID"],
						serialize($res)
					);
				}
			}

			$Result = true;

			/*********** QUOTA ***************/
			CDiskQuota::recalculateDb();
			/*********** QUOTA ***************/
		}

		$arFields["ID"] = $ID;
		$arFields["IBLOCK_ID"] = $db_record["IBLOCK_ID"];
		$arFields["RESULT"] = &$Result;

		foreach (GetModuleEvents("iblock", "OnAfterIBlockSectionUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		CIBlock::clearIblockTagCache($arIBlock['ID']);

		return $Result;
	}

	///////////////////////////////////////////////////////////////////
	// Function delete section by its ID
	///////////////////////////////////////////////////////////////////
	public static function Delete($ID, $bCheckPermissions = true)
	{
		global $DB, $APPLICATION, $USER;

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return false;
		}

		$APPLICATION->ResetException();
		foreach (GetModuleEvents("iblock", "OnBeforeIBlockSectionDelete", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->ThrowException($err);
				return false;
			}
		}

		$s = CIBlockSection::GetList(Array(), Array("ID"=>$ID, "CHECK_PERMISSIONS"=>($bCheckPermissions? "Y": "N")));
		if($s = $s->Fetch())
		{
			CIBlock::_transaction_lock($s["IBLOCK_ID"]);

			$iblockelements = CIBlockElement::GetList(
				array(),
				array("SECTION_ID" => $ID, "SHOW_HISTORY" => "Y", "IBLOCK_ID" => $s["IBLOCK_ID"]),
				false,
				false,
				array("ID", "IBLOCK_ID", "WF_PARENT_ELEMENT_ID", "IBLOCK_SECTION_ID")
			);
			while($iblockelement = $iblockelements->Fetch())
			{
				$elementId = (int)$iblockelement["ID"];
				$strSql = "
					SELECT IBLOCK_SECTION_ID
					FROM b_iblock_section_element
					WHERE
						IBLOCK_ELEMENT_ID = ".$elementId."
						AND IBLOCK_SECTION_ID<>".$ID."
						AND ADDITIONAL_PROPERTY_ID IS NULL
					ORDER BY
						IBLOCK_SECTION_ID
				";
				$db_section_element = $DB->Query($strSql);
				if($ar_section_element = $db_section_element->Fetch())
				{
					if ((int)$iblockelement["IBLOCK_SECTION_ID"] == $ID)
					{
						$DB->Query("
							UPDATE b_iblock_element
							SET IBLOCK_SECTION_ID=".$ar_section_element["IBLOCK_SECTION_ID"]."
							WHERE ID=".$elementId."
						");
					}
				}
				elseif((int)$iblockelement["WF_PARENT_ELEMENT_ID"]<=0)
				{
					if(!CIBlockElement::Delete($iblockelement["ID"]))
						return false;
				}
				else
				{
					$DB->Query("
						UPDATE b_iblock_element
						SET IBLOCK_SECTION_ID=NULL, IN_SECTIONS='N'
						WHERE ID=".$elementId."
					");
				}
				unset($elementId);
			}

			$iblocksections = CIBlockSection::GetList(
				array(),
				array("SECTION_ID"=>$ID, "CHECK_PERMISSIONS"=>($bCheckPermissions? "Y": "N")),
				false,
				array("ID")
			);
			while($iblocksection = $iblocksections->Fetch())
			{
				if(!CIBlockSection::Delete($iblocksection["ID"], $bCheckPermissions))
					return false;
			}

			CFile::Delete($s["PICTURE"]);
			CFile::Delete($s["DETAIL_PICTURE"]);

			static $arDelCache;
			if(!is_array($arDelCache))
				$arDelCache = Array();
			if (!isset($arDelCache[$s["IBLOCK_ID"]]))
			{
				$arDelCache[$s["IBLOCK_ID"]] = [];
				$db_ps = $DB->Query("SELECT ID,IBLOCK_ID,VERSION,MULTIPLE FROM b_iblock_property WHERE PROPERTY_TYPE='G' AND (LINK_IBLOCK_ID=".$s["IBLOCK_ID"]." OR LINK_IBLOCK_ID=0 OR LINK_IBLOCK_ID IS NULL)");
				while($ar_ps = $db_ps->Fetch())
				{
					if($ar_ps["VERSION"]==2)
					{
						if($ar_ps["MULTIPLE"]=="Y")
							$strTable = "b_iblock_element_prop_m".$ar_ps["IBLOCK_ID"];
						else
							$strTable = "b_iblock_element_prop_s".$ar_ps["IBLOCK_ID"];
					}
					else
					{
						$strTable = "b_iblock_element_property";
					}
					if (!isset($arDelCache[$s["IBLOCK_ID"]][$strTable]))
					{
						$arDelCache[$s["IBLOCK_ID"]][$strTable] = [];
					}
					$arDelCache[$s["IBLOCK_ID"]][$strTable][] = $ar_ps["ID"];
				}
			}

			if (!empty($arDelCache[$s["IBLOCK_ID"]]))
			{
				foreach($arDelCache[$s["IBLOCK_ID"]] as $strTable=>$arProps)
				{
					if(strncmp("b_iblock_element_prop_s", $strTable, 23)==0)
					{
						$tableFields = $DB->GetTableFields($strTable);
						foreach($arProps as $prop_id)
						{
							$strSql = "UPDATE ".$strTable." SET PROPERTY_".$prop_id."=null";
							if (isset($tableFields["DESCRIPTION_".$prop_id]))
								$strSql .= ",DESCRIPTION_".$prop_id."=null";
							$strSql .= " WHERE PROPERTY_".$prop_id."=".$s["ID"];
							if(!$DB->Query($strSql))
								return false;
						}
					}
					elseif(strncmp("b_iblock_element_prop_m", $strTable, 23)==0)
					{
						$tableFields = $DB->GetTableFields(str_replace("prop_m", "prop_s", $strTable));
						$strSql = "SELECT IBLOCK_PROPERTY_ID, IBLOCK_ELEMENT_ID FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$s["ID"];
						$rs = $DB->Query($strSql);
						while($ar = $rs->Fetch())
						{
							$strSql = "
								UPDATE ".str_replace("prop_m", "prop_s", $strTable)."
								SET	PROPERTY_".$ar["IBLOCK_PROPERTY_ID"]."=null
									".(isset($tableFields["DESCRIPTION_".$ar["IBLOCK_PROPERTY_ID"]])? ",DESCRIPTION_".$ar["IBLOCK_PROPERTY_ID"]."=null": "")."
								WHERE IBLOCK_ELEMENT_ID = ".$ar["IBLOCK_ELEMENT_ID"]."
							";
							if(!$DB->Query($strSql))
								return false;
						}
						$strSql = "DELETE FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$s["ID"];
						if(!$DB->Query($strSql))
							return false;
					}
					else
					{
						$strSql = "DELETE FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$s["ID"];
						if(!$DB->Query($strSql))
							return false;
					}
				}
			}

			CIBlockSectionPropertyLink::DeleteBySection($ID);
			$DB->Query("DELETE FROM b_iblock_section_element WHERE IBLOCK_SECTION_ID=".$ID);

			if(CModule::IncludeModule("search"))
				CSearch::DeleteIndex("iblock", "S".$ID);

			$GLOBALS["USER_FIELD_MANAGER"]->Delete("IBLOCK_".$s["IBLOCK_ID"]."_SECTION", $ID);

			//Delete the hole in the tree
			self::recountTreeOnDelete($s);

			$obSectionRights = new CIBlockSectionRights($s["IBLOCK_ID"], $ID);
			$obSectionRights->DeleteAllRights();

			$ipropTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($s["IBLOCK_ID"], $ID);
			$ipropTemplates->delete();

			/************* QUOTA *************/
			CDiskQuota::recalculateDb();
			/************* QUOTA *************/

			$arIBlockFields = CIBlock::GetArrayByID($s["IBLOCK_ID"], "FIELDS");
			if($arIBlockFields["LOG_SECTION_DELETE"]["IS_REQUIRED"] == "Y")
			{
				$USER_ID = is_object($USER)? intval($USER->GetID()) : 0;
				$arEvents = GetModuleEvents("main", "OnBeforeEventLog", true);
				if(empty($arEvents) || ExecuteModuleEventEx($arEvents[0],  array($USER_ID))===false)
				{
					$rsSection = CIBlockSection::GetList(
						array(),
						array("=ID"=>$ID, "CHECK_PERMISSIONS"=>($bCheckPermissions? "Y": "N")),
						false,
						array("LIST_PAGE_URL", "NAME", "CODE")
					);
					$arSection = $rsSection->GetNext();
					$res = array(
						"ID" => $ID,
						"CODE" => $arSection["CODE"],
						"NAME" => $arSection["NAME"],
						"SECTION_NAME" => CIBlock::GetArrayByID($s["IBLOCK_ID"], "SECTION_NAME"),
						"USER_ID" => $USER_ID,
						"IBLOCK_PAGE_URL" => $arSection["LIST_PAGE_URL"],
					);
					CEventLog::Log(
						"IBLOCK",
						"IBLOCK_SECTION_DELETE",
						"iblock",
						$s["IBLOCK_ID"],
						serialize($res)
					);
				}
			}

			$res = $DB->Query("DELETE FROM b_iblock_section WHERE ID=".$ID);

			if($res)
			{
				foreach (GetModuleEvents("iblock", "OnAfterIBlockSectionDelete", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array($s));

				CIBlock::clearIblockTagCache($s['IBLOCK_ID']);
			}

			return $res;
		}

		return true;
	}

	///////////////////////////////////////////////////////////////////
	// Check function called from Add and Update
	///////////////////////////////////////////////////////////////////
	public function CheckFields(&$arFields, $ID=false)
	{
		global $DB, $APPLICATION;
		$this->LAST_ERROR = "";

		if(($ID===false || array_key_exists("NAME", $arFields)) && (string)$arFields["NAME"] === '')
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION")."<br>";

		$pictureIsArray = isset($arFields["PICTURE"]) && is_array($arFields["PICTURE"]);
		if (
			$pictureIsArray
			&& array_key_exists("bucket", $arFields["PICTURE"])
			&& is_object($arFields["PICTURE"]["bucket"])
		)
		{
			//This is trusted image from xml import
		}
		elseif(
			$pictureIsArray
			&& isset($arFields["PICTURE"]["name"])
		)
		{
			$error = CFile::CheckImageFile($arFields["PICTURE"]);
			if ($error <> '')
				$this->LAST_ERROR .= $error."<br>";
		}

		$detailPictureIsArray = isset($arFields["DETAIL_PICTURE"]) && is_array($arFields["DETAIL_PICTURE"]);
		if(
			$detailPictureIsArray
			&& array_key_exists("bucket", $arFields["DETAIL_PICTURE"])
			&& is_object($arFields["DETAIL_PICTURE"]["bucket"])
		)
		{
			//This is trusted image from xml import
		}
		elseif(
			$detailPictureIsArray
			&& isset($arFields["DETAIL_PICTURE"]["name"])
		)
		{
			$error = CFile::CheckImageFile($arFields["DETAIL_PICTURE"]);
			if ($error <> '')
				$this->LAST_ERROR .= $error."<br>";
		}

		$arIBlock = false;
		$arThis = false;

		if($ID === false)
		{
			if(!array_key_exists("IBLOCK_ID", $arFields))
			{
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";
			}
			else
			{
				$arIBlock = CIBlock::GetArrayByID($arFields["IBLOCK_ID"]);
				if(!$arIBlock)
					$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";
			}
		}
		else
		{
			$rsThis = $DB->Query("SELECT ID, IBLOCK_ID, DETAIL_PICTURE, PICTURE FROM b_iblock_section WHERE ID = ".intval($ID));
			$arThis = $rsThis->Fetch();
			if(!$arThis)
			{
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_ID", array("#ID#" => intval($ID)))."<br>";
			}
			else
			{
				$arIBlock = CIBlock::GetArrayByID($arThis["IBLOCK_ID"]);
				if(!$arIBlock)
					$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";
			}
		}

		$arParent = false;
		$IBLOCK_SECTION_ID = (int)($arFields['IBLOCK_SECTION_ID'] ?? 0);

		if ($IBLOCK_SECTION_ID > 0 && $ID !== false && $this->LAST_ERROR === '')
		{
			if ($IBLOCK_SECTION_ID === (int)$ID)
			{
				$this->LAST_ERROR .= GetMessage('IBLOCK_BAD_BLOCK_SECTION_RECURSE') . '<br>';
			}
		}

		if ($IBLOCK_SECTION_ID > 0 && $this->LAST_ERROR === '')
		{
			$sqlCheck = 'select ID, IBLOCK_ID from b_iblock_section where ID = ' . $IBLOCK_SECTION_ID;
			$rsParent = $DB->Query($sqlCheck);
			$arParent = $rsParent->Fetch();
			if (!$arParent)
			{
				$this->LAST_ERROR = GetMessage('IBLOCK_BAD_BLOCK_SECTION_PARENT') . '<br>';
			}
		}

		if($arParent && $arIBlock)
		{
			if($arParent["IBLOCK_ID"] != $arIBlock["ID"])
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_SECTION_ID_PARENT")."<br>";
		}

		if($arParent && ($this->LAST_ERROR == ''))
		{
			$rch = $DB->Query("
				SELECT 'x'
				FROM
					b_iblock_section bsto
					,b_iblock_section bsfrom
				WHERE
					bsto.ID = ".$arParent["ID"]."
					AND bsfrom.ID = ".intval($ID)."
					AND bsto.LEFT_MARGIN >= bsfrom.LEFT_MARGIN
					AND bsto.LEFT_MARGIN <= bsfrom.RIGHT_MARGIN
			");
			if($rch->Fetch())
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_SECTION_RECURSE")."<br>";
		}

		if($arIBlock)
		{
			if(
				array_key_exists("CODE", $arFields)
				&& (string)$arFields['CODE'] !== ''
				&& is_array($arIBlock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"])
				&& $arIBlock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"]["UNIQUE"] == "Y"
			)
			{
				$res = $DB->Query("
					SELECT ID
					FROM b_iblock_section
					WHERE IBLOCK_ID = ".$arIBlock["ID"]."
					AND CODE = '".$DB->ForSQL((string)$arFields['CODE'])."'
					AND ID <> ".(int)$ID
				);
				if($res->Fetch())
					$this->LAST_ERROR .= GetMessage("IBLOCK_DUP_SECTION_CODE")."<br>";
			}

			foreach($arIBlock["FIELDS"] as $FIELD_ID => $field)
			{
				if(!preg_match("/^SECTION_(.+)$/", $FIELD_ID, $match))
					continue;

				$FIELD_ID = $match[1];

				if($field["IS_REQUIRED"] === "Y")
				{
					switch($FIELD_ID)
					{
					case "NAME":
					case "DESCRIPTION_TYPE":
						//We should never check for this fields
						break;
					case "PICTURE":
						$field["NAME"] = GetMessage("IBLOCK_FIELD_PICTURE");
					case "DETAIL_PICTURE":
						if($arThis && $arThis[$FIELD_ID] > 0)
						{//There was an picture so just check that it is not deleted
							if(
								array_key_exists($FIELD_ID, $arFields)
								&& is_array($arFields[$FIELD_ID])
								&& $arFields[$FIELD_ID]["del"] === "Y"
							)
								$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
						}
						else
						{//There was NO picture so it MUST be present
							if(!array_key_exists($FIELD_ID, $arFields))
							{
								$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
							elseif(is_array($arFields[$FIELD_ID]))
							{
								if(
									$arFields[$FIELD_ID]["del"] === "Y"
									|| (array_key_exists("error", $arFields[$FIELD_ID]) && $arFields[$FIELD_ID]["error"] !== 0)
									|| $arFields[$FIELD_ID]["size"] <= 0
								)
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
							else
							{
								if(intval($arFields[$FIELD_ID]) <= 0)
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
						}
						break;
					default:
						if($ID===false || array_key_exists($FIELD_ID, $arFields))
						{
							if(is_array($arFields[$FIELD_ID]))
								$val = implode("", $arFields[$FIELD_ID]);
							else
								$val = $arFields[$FIELD_ID];
							if($val == '')
								$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
						}
						break;
					}
				}
			}
		}

		$APPLICATION->ResetException();
		if($ID===false)
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockSectionAdd", true);
		else
		{
			$arFields["ID"] = $ID;
			$arFields["IBLOCK_ID"] = $arIBlock["ID"];
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockSectionUpdate", true);
		}

		/****************************** QUOTA ******************************/
		if(empty($this->LAST_ERROR) && (COption::GetOptionInt("main", "disk_space") > 0))
		{
			$quota = new CDiskQuota();
			if(!$quota->checkDiskQuota($arFields))
				$this->LAST_ERROR = $quota->LAST_ERROR;
		}
		/****************************** QUOTA ******************************/

		foreach ($db_events as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$this->LAST_ERROR .= "Unknown error.<br>";
				}
				break;
			}
		}

		if($this->LAST_ERROR <> '')
			return false;

		return true;
	}

	public static function TreeReSort($IBLOCK_ID, $ID=0, $cnt=0, $depth=0, $ACTIVE="Y")
	{
		global $DB;
		$IBLOCK_ID = (int)$IBLOCK_ID;

		if($ID==0)
		{
			CIBlock::_transaction_lock($IBLOCK_ID);
		}

		if($ID > 0)
		{
			$DB->Query("
				UPDATE
					b_iblock_section
				SET
					TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
					,RIGHT_MARGIN=".(int)$cnt."
					,LEFT_MARGIN=".(int)$cnt."
				WHERE
					ID=".(int)$ID
			);
		}

		$strSql = "
			SELECT BS.ID, BS.ACTIVE
			FROM b_iblock_section BS
			WHERE BS.IBLOCK_ID = ".$IBLOCK_ID."
			AND ".($ID>0? "BS.IBLOCK_SECTION_ID=".(int)$ID: "BS.IBLOCK_SECTION_ID IS NULL")."
			ORDER BY BS.SORT, BS.NAME
		";

		$cnt++;
		$res = $DB->Query($strSql);
		while($arr = $res->Fetch())
			$cnt = CIBlockSection::TreeReSort($IBLOCK_ID, $arr["ID"], $cnt, $depth+1, ($ACTIVE=="Y" && $arr["ACTIVE"]=="Y" ? "Y" : "N"));

		if($ID==0)
		{
			return true;
		}

		$DB->Query("
			UPDATE
				b_iblock_section
			SET
				TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
				,RIGHT_MARGIN=".(int)$cnt."
				,DEPTH_LEVEL=".(int)$depth."
				,GLOBAL_ACTIVE='".$ACTIVE."'
			WHERE
				ID=".(int)$ID
		);

		return $cnt+1;
	}

	public static function ReSort($IBLOCK_ID, $ID=0, $cnt=0, $depth=0, $ACTIVE="Y")
	{
		$cnt = self::TreeReSort($IBLOCK_ID, $ID, $cnt, $depth, $ACTIVE);
		$obIBlockRights = new CIBlockRights($IBLOCK_ID);
		$obIBlockRights->Recalculate();

		return $cnt;
	}

	public function UpdateSearch($ID, $bOverWrite=false)
	{
		if(!CModule::IncludeModule("search")) return;

		global $DB;
		$ID = intval($ID);

		static $arGroups = array();
		static $arSITE = array();

		$strSql = "
			SELECT BS.ID, BS.NAME, BS.DESCRIPTION_TYPE, BS.DESCRIPTION, BS.XML_ID as EXTERNAL_ID,
				BS.CODE, BS.IBLOCK_ID, B.IBLOCK_TYPE_ID,
				".$DB->DateToCharFunction("BS.TIMESTAMP_X")." as LAST_MODIFIED,
				B.CODE as IBLOCK_CODE, B.XML_ID as IBLOCK_EXTERNAL_ID, B.SECTION_PAGE_URL,
				B.ACTIVE as ACTIVE1,
				BS.GLOBAL_ACTIVE as ACTIVE2,
				B.INDEX_SECTION, B.RIGHTS_MODE
			FROM b_iblock_section BS, b_iblock B
			WHERE BS.IBLOCK_ID=B.ID
				AND BS.ID=".$ID;

		$dbrIBlockSection = $DB->Query($strSql);

		if($arIBlockSection = $dbrIBlockSection->Fetch())
		{
			$IBLOCK_ID = $arIBlockSection["IBLOCK_ID"];
			$SECTION_URL =
					"=ID=".$arIBlockSection["ID"].
					"&EXTERNAL_ID=".$arIBlockSection["EXTERNAL_ID"].
					"&IBLOCK_TYPE_ID=".$arIBlockSection["IBLOCK_TYPE_ID"].
					"&IBLOCK_ID=".$arIBlockSection["IBLOCK_ID"].
					"&IBLOCK_CODE=".$arIBlockSection["IBLOCK_CODE"].
					"&IBLOCK_EXTERNAL_ID=".$arIBlockSection["IBLOCK_EXTERNAL_ID"].
					"&CODE=".$arIBlockSection["CODE"];

			if($arIBlockSection["ACTIVE1"]!="Y" || $arIBlockSection["ACTIVE2"]!="Y" || $arIBlockSection["INDEX_SECTION"]!="Y")
			{
				CSearch::DeleteIndex("iblock", "S".$arIBlockSection["ID"]);
				return;
			}

			if(!array_key_exists($IBLOCK_ID, $arGroups))
			{
				$arGroups[$IBLOCK_ID] = array();
				$strSql =
					"SELECT GROUP_ID ".
					"FROM b_iblock_group ".
					"WHERE IBLOCK_ID= ".$IBLOCK_ID." ".
					"	AND PERMISSION>='" . CIBlockRights::PUBLIC_READ . "' ".
					"ORDER BY GROUP_ID";

				$dbrIBlockGroup = $DB->Query($strSql);
				while($arIBlockGroup = $dbrIBlockGroup->Fetch())
				{
					$arGroups[$IBLOCK_ID][] = $arIBlockGroup["GROUP_ID"];
					if($arIBlockGroup["GROUP_ID"]==2) break;
				}
			}

			if(!array_key_exists($IBLOCK_ID, $arSITE))
			{
				$arSITE[$IBLOCK_ID] = array();
				$strSql =
					"SELECT SITE_ID ".
					"FROM b_iblock_site ".
					"WHERE IBLOCK_ID= ".$IBLOCK_ID;

				$dbrIBlockSite = $DB->Query($strSql);
				while($arIBlockSite = $dbrIBlockSite->Fetch())
					$arSITE[$IBLOCK_ID][] = $arIBlockSite["SITE_ID"];
			}

			$BODY =
				($arIBlockSection["DESCRIPTION_TYPE"]=="html" ?
					CSearch::KillTags($arIBlockSection["DESCRIPTION"])
				:
					$arIBlockSection["DESCRIPTION"]
				);

			$BODY .= $GLOBALS["USER_FIELD_MANAGER"]->OnSearchIndex("IBLOCK_".$arIBlockSection["IBLOCK_ID"]."_SECTION", $arIBlockSection["ID"]);

			if($arIBlockSection["RIGHTS_MODE"] !== "E")
				$arPermissions = $arGroups[$IBLOCK_ID];
			else
			{
				$obSectionRights = new CIBlockSectionRights($IBLOCK_ID, $arIBlockSection["ID"]);
				$arPermissions = $obSectionRights->GetGroups(array("section_read"));
			}

			CSearch::Index("iblock", "S".$ID, array(
				"LAST_MODIFIED" => $arIBlockSection["LAST_MODIFIED"],
				"TITLE" => $arIBlockSection["NAME"],
				"PARAM1" => $arIBlockSection["IBLOCK_TYPE_ID"],
				"PARAM2" => $IBLOCK_ID,
				"SITE_ID" => $arSITE[$IBLOCK_ID],
				"PERMISSIONS" => $arPermissions,
				"URL" => $SECTION_URL,
				"BODY" => $BODY,
			), $bOverWrite);
		}
	}

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool $bIncCnt
	 * @param bool|array $arSelectedFields
	 * @return CDBResult
	 */
	public static function GetMixedList($arOrder=array("SORT"=>"ASC"), $arFilter=array(), $bIncCnt = false, $arSelectedFields = false)
	{
		$arResult = [];

		if (!is_array($arOrder))
		{
			$arOrder = ['SORT' => 'ASC'];
		}

		if (!is_array($arSelectedFields))
		{
			$arSelectedFields = [];
		};
		$emptySelect = empty($arSelectedFields) || in_array('*', $arSelectedFields);

		if (!empty($arFilter))
		{
			$arFilter = array_change_key_case($arFilter, CASE_UPPER);
		}

		$arFilter = static::normalizeMixedFilter($arFilter);

		if (static::checkLoadSections($arFilter))
		{
			$arSectionFilter = $arFilter;

			$sectionFields = [
				'ID' => true,
				'CODE' => true,
				'XML_ID' => true,
				'EXTERNAL_ID' => true,
				'IBLOCK_ID' => true,
				'IBLOCK_SECTION_ID' => true,
				'TIMESTAMP_X' => true,
				'TIMESTAMP_X_UNIX' => true,
				'SORT' => true,
				'NAME' => true,
				'ACTIVE' => true,
				'GLOBAL_ACTIVE' => true,
				'PICTURE' => true,
				'DESCRIPTION' => true,
				'DESCRIPTION_TYPE' => true,
				'LEFT_MARGIN' => true,
				'RIGHT_MARGIN' => true,
				'DEPTH_LEVEL' => true,
				'SEARCHABLE_CONTENT' => true,
				'MODIFIED_BY' => true,
				'DATE_CREATE' => true,
				'DATE_CREATE_UNIX' => true,
				'CREATED_BY' => true,
				'DETAIL_PICTURE' => true,
				'TMP_ID' => true,

				'LIST_PAGE_URL' => true,
				'SECTION_PAGE_URL' => true,
				'IBLOCK_TYPE_ID' => true,
				'IBLOCK_CODE' => true,
				'IBLOCK_EXTERNAL_ID' => true,
				'SOCNET_GROUP_ID' => true,
			];

			if ($emptySelect)
			{
				$sectionSelect = array_keys($sectionFields);
			}
			else
			{
				$sectionSelect = [];
				foreach ($arSelectedFields as $field)
				{
					if (!isset($sectionFields[$field]))
					{
						continue;
					}
					$sectionSelect[$field] = $field;
				}
				unset($field);
				if (!empty($sectionSelect))
				{
					$sectionSelect = array_values($sectionSelect);
				}
			}

			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList($arOrder, $arSectionFilter, $bIncCnt, $sectionSelect);
			while ($arSection = $rsSection->Fetch())
			{
				$arSection['TYPE'] = Iblock\Grid\RowType::SECTION;
				$arResult[] = $arSection;
			}
			unset($arSection);
			unset($rsSection);
			unset($obSection);
		}

		if (static::checkLoadElements($arFilter))
		{
			$arElementFilter = $arFilter;
			$arElementFilter['SHOW_NEW'] = ($arFilter['SHOW_NEW'] ?? 'Y') === 'N' ? 'N' : 'Y';

			if ($emptySelect)
			{
				$arSelectedFields = ["*"];
			}

			$obElement = new CIBlockElement;

			$rsElement = $obElement->GetList($arOrder, $arElementFilter, false, false, $arSelectedFields);
			while ($arElement = $rsElement->Fetch())
			{
				$arElement['TYPE'] = Iblock\Grid\RowType::ELEMENT;
				$arResult[] = $arElement;
			}
			unset($arElement);
			unset($rsElement);
			unset($obElement);
		}

		$rsResult = new CDBResult;
		$rsResult->InitFromArray($arResult);
		unset($arResult);

		return $rsResult;
	}

	///////////////////////////////////////////////////////////////////
	// GetSectionElementsCount($ID, $arFilter=Array())
	///////////////////////////////////////////////////////////////////
	public static function GetSectionElementsCount($ID, $arFilter=Array())
	{
		global $DB;

		$arJoinProps = array();
		$bJoinFlatProp = false;
		$arSqlSearch = array();

		if(array_key_exists("PROPERTY", $arFilter))
		{
			$val = $arFilter["PROPERTY"];
			foreach($val as $propID=>$propVAL)
			{
				$res = CIBlock::MkOperationFilter($propID);
				$propID = $res["FIELD"];
				$cOperationType = $res["OPERATION"];
				if($db_prop = CIBlockProperty::GetPropertyArray($propID, CIBlock::_MergeIBArrays($arFilter["IBLOCK_ID"], $arFilter["IBLOCK_CODE"])))
				{

					$bSave = false;
					if(array_key_exists($db_prop["ID"], $arJoinProps))
						$iPropCnt = $arJoinProps[$db_prop["ID"]];
					elseif($db_prop["VERSION"]!=2 || $db_prop["MULTIPLE"]=="Y")
					{
						$bSave = true;
						$iPropCnt=count($arJoinProps);
					}

					if(!is_array($propVAL))
						$propVAL = Array($propVAL);

					if($db_prop["PROPERTY_TYPE"]=="N" || $db_prop["PROPERTY_TYPE"]=="G" || $db_prop["PROPERTY_TYPE"]=="E")
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPS.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "number", $cOperationType);
							$bJoinFlatProp = $db_prop["IBLOCK_ID"];
						}
						else
							$r = CIBlock::FilterCreate("FPV".$iPropCnt.".VALUE_NUM", $propVAL, "number", $cOperationType);
					}
					else
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPS.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "string", $cOperationType);
							$bJoinFlatProp = $db_prop["IBLOCK_ID"];
						}
						else
							$r = CIBlock::FilterCreate("FPV".$iPropCnt.".VALUE", $propVAL, "string", $cOperationType);
					}

					if($r <> '')
					{
						if($bSave)
						{
							$db_prop["iPropCnt"] = $iPropCnt;
							$arJoinProps[$db_prop["ID"]] = $db_prop;
						}
						$arSqlSearch[] = $r;
					}
				}
			}
		}

		$strSqlSearch = "";
		foreach($arSqlSearch as $r)
			if($r <> '')
				$strSqlSearch .= "\n\t\t\t\tAND  (".$r.") ";

		$strSqlSearchProp = "";
		foreach($arJoinProps as $propID=>$db_prop)
		{
			if($db_prop["VERSION"]==2)
				$strTable = "b_iblock_element_prop_m".$db_prop["IBLOCK_ID"];
			else
				$strTable = "b_iblock_element_property";
			$i = $db_prop["iPropCnt"];
			$strSqlSearchProp .= "
				INNER JOIN b_iblock_property FP".$i." ON FP".$i.".IBLOCK_ID=BS.IBLOCK_ID AND
				".(intval($propID)>0?" FP".$i.".ID=".intval($propID)." ":" FP".$i.".CODE='".$DB->ForSQL($propID, 200)."' ")."
				INNER JOIN ".$strTable." FPV".$i." ON FP".$i.".ID=FPV".$i.".IBLOCK_PROPERTY_ID AND FPV".$i.".IBLOCK_ELEMENT_ID=BE.ID
			";
		}
		if($bJoinFlatProp)
			$strSqlSearchProp .= "
				INNER JOIN b_iblock_element_prop_s".$bJoinFlatProp." FPS ON FPS.IBLOCK_ELEMENT_ID = BE.ID
			";

		$allElements = (isset($arFilter['CNT_ALL']) && $arFilter['CNT_ALL'] == 'Y');
		$activeElements = (isset($arFilter['CNT_ACTIVE']) && $arFilter['CNT_ACTIVE'] == 'Y');

		$strHint = $DB->type=="MYSQL"?"STRAIGHT_JOIN":"";
		$strSql = "
			SELECT ".$strHint." COUNT(DISTINCT BE.ID) as CNT
			FROM b_iblock_section BS
				INNER JOIN b_iblock_section BSTEMP ON (BSTEMP.IBLOCK_ID=BS.IBLOCK_ID
					AND BSTEMP.LEFT_MARGIN >= BS.LEFT_MARGIN
					AND BSTEMP.RIGHT_MARGIN <= BS.RIGHT_MARGIN)
				INNER JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BSTEMP.ID
				INNER JOIN b_iblock_element BE ON BE.ID=BSE.IBLOCK_ELEMENT_ID AND BE.IBLOCK_ID=BS.IBLOCK_ID
			".$strSqlSearchProp."
			WHERE BS.ID=".intval($ID)."
				AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
				".($allElements ?" OR BE.WF_NEW='Y' ":"").")
				".($activeElements ?
					" AND BE.ACTIVE='Y'
					AND (BE.ACTIVE_TO >= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_TO IS NULL)
					AND (BE.ACTIVE_FROM <= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_FROM IS NULL)"
				:"")."
				".$strSqlSearch;
		//echo "<pre>",htmlspecialcharsbx($strSql),"</pre>";
		$res = $DB->Query($strSql);
		$res = $res->Fetch();

		return (int)($res['CNT'] ?? 0);
	}

	protected static function _check_rights_sql($min_permission, $permissionsBy = null)
	{
		global $DB, $USER;
		$min_permission = (mb_strlen($min_permission) == 1) ? $min_permission : CIBlockRights::PUBLIC_READ;

		if ($permissionsBy !== null)
			$permissionsBy = (int)$permissionsBy;
		if ($permissionsBy < 0)
			$permissionsBy = null;

		if ($permissionsBy !== null)
		{
			$iUserID = $permissionsBy;
			$strGroups = implode(',', CUser::GetUserGroup($permissionsBy));
			$bAuthorized = false;
		}
		else
		{
			if (is_object($USER))
			{
				$iUserID = (int)$USER->GetID();
				$strGroups = $USER->GetGroups();
				$bAuthorized = $USER->IsAuthorized();
			}
			else
			{
				$iUserID = 0;
				$strGroups = "2";
				$bAuthorized = false;
			}
		}

		$stdPermissions = "
			SELECT IBLOCK_ID
			FROM b_iblock_group IBG
			WHERE IBG.GROUP_ID IN (".$strGroups.")
			AND IBG.PERMISSION >= '".$DB->ForSQL($min_permission)."'
		";
		if(!defined("ADMIN_SECTION"))
			$stdPermissions .= "
				AND (IBG.PERMISSION='" . CIBlockRights::FULL_ACCESS . "' OR B.ACTIVE='Y')
			";

		if($min_permission >= CIBlockRights::FULL_ACCESS)
			$operation = 'section_rights_edit';
		elseif($min_permission >= CIBlockRights::EDIT_ACCESS)
			$operation = 'section_edit';
		elseif($min_permission >= CIBlockRights::PUBLIC_READ)
			$operation = 'section_read';
		else
			$operation = '';

		if($operation)
		{
			$acc = new CAccess;
			$acc->UpdateCodes($permissionsBy !== null ? array('USER_ID' => $permissionsBy) : false);
		}

		if($operation == "section_read")
		{
			$extPermissions = "
				SELECT SR.SECTION_ID
				FROM b_iblock_section_right SR
				INNER JOIN b_iblock_right IBR ON IBR.ID = SR.RIGHT_ID
				".($iUserID > 0? "LEFT": "INNER")." JOIN b_user_access UA ON UA.ACCESS_CODE = IBR.GROUP_CODE AND UA.USER_ID = ".$iUserID."
				WHERE SR.SECTION_ID = BS.ID
				AND IBR.OP_SREAD = 'Y'
				".($bAuthorized || $iUserID > 0? "
					AND (UA.USER_ID IS NOT NULL
					".($bAuthorized? "OR IBR.GROUP_CODE = 'AU'": "")."
					".($iUserID > 0? "OR (IBR.GROUP_CODE = 'CR' AND BS.CREATED_BY = ".$iUserID.")": "")."
				)": "")."
			";

			$strResult = "(
				B.ID IN ($stdPermissions)
				OR (B.RIGHTS_MODE = 'E' AND EXISTS ($extPermissions))
			)";
		}
		elseif($operation)
		{
			$extPermissions = "
				SELECT SR.SECTION_ID
				FROM b_iblock_section_right SR
				INNER JOIN b_iblock_right IBR ON IBR.ID = SR.RIGHT_ID
				INNER JOIN b_task_operation T ON T.TASK_ID = IBR.TASK_ID
				INNER JOIN b_operation O ON O.ID = T.OPERATION_ID
				".($iUserID > 0? "LEFT": "INNER")." JOIN b_user_access UA ON UA.ACCESS_CODE = IBR.GROUP_CODE AND UA.USER_ID = ".$iUserID."
				WHERE SR.SECTION_ID = BS.ID
				AND O.NAME = '".$operation."'
				".($bAuthorized || $iUserID > 0? "
					AND (UA.USER_ID IS NOT NULL
					".($bAuthorized? "OR IBR.GROUP_CODE = 'AU'": "")."
					".($iUserID > 0? "OR (IBR.GROUP_CODE = 'CR' AND BS.CREATED_BY = ".$iUserID.")": "")."
				)": "")."
			";

			$strResult = "(
				B.ID IN ($stdPermissions)
				OR (B.RIGHTS_MODE = 'E' AND EXISTS ($extPermissions))
			)";
		}
		else
		{
			$strResult = "(
				B.ID IN ($stdPermissions)
			)";
		}

		return $strResult;
	}

	public static function GetCount($arFilter = [])
	{
		global $DB, $USER;

		$arSqlSearch = CIBlockSection::GetFilter($arFilter);

		$bCheckPermissions = !array_key_exists("CHECK_PERMISSIONS", $arFilter) || $arFilter["CHECK_PERMISSIONS"]!=="N";
		$bIsAdmin = is_object($USER) && $USER->IsAdmin();
		$permissionsBy = null;
		if ($bCheckPermissions && isset($arFilter['PERMISSIONS_BY']))
		{
			$permissionsBy = (int)$arFilter['PERMISSIONS_BY'];
			if ($permissionsBy < 0)
				$permissionsBy = null;
		}
		if ($bCheckPermissions && ($permissionsBy !== null || !$bIsAdmin))
		{
			$arSqlSearch[] = self::_check_rights_sql(
				$arFilter['MIN_PERMISSION'] ?? CIBlockRights::PUBLIC_READ,
				$permissionsBy
			);
		}
		unset($permissionsBy);

		$strSqlSearch = "";
		foreach($arSqlSearch as $i=>$strSearch)
			if($strSearch <> '')
				$strSqlSearch .= "\n\t\t\tAND  (".$strSearch.") ";

		$strSql = "
			SELECT COUNT(DISTINCT BS.ID) as C
			FROM b_iblock_section BS
				INNER JOIN b_iblock B ON BS.IBLOCK_ID = B.ID
			WHERE 1=1
			".$strSqlSearch."
		";

		$res = $DB->Query($strSql);
		$res_cnt = $res->Fetch();

		return (int)($res_cnt["C"] ?? 0);
	}

	public static function UserTypeRightsCheck($entity_id)
	{
		if(preg_match("/^IBLOCK_(\d+)_SECTION$/", $entity_id, $match))
		{
			return CIBlock::GetPermission($match[1]);
		}
		else
			return "D";
	}

	public static function RecalcGlobalActiveFlag($arSection, $distance = 0)
	{
		global $DB;

		$distance = (int)$distance;
		$arSection['LEFT_MARGIN'] += $distance;
		$arSection['RIGHT_MARGIN'] += $distance;

		//Make all children globally active
		$DB->Query("
			UPDATE b_iblock_section SET
				TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
				,GLOBAL_ACTIVE = 'Y'
			WHERE
				IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
				AND LEFT_MARGIN >= ".intval($arSection["LEFT_MARGIN"])."
				AND RIGHT_MARGIN <= ".intval($arSection["RIGHT_MARGIN"])."
		");
		//Select those who is not active
		$strSql = "
			SELECT ID, LEFT_MARGIN, RIGHT_MARGIN
			FROM b_iblock_section
			WHERE IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
			AND LEFT_MARGIN >= ".intval($arSection["LEFT_MARGIN"])."
			AND RIGHT_MARGIN <= ".intval($arSection["RIGHT_MARGIN"])."
			AND ACTIVE = 'N'
			ORDER BY LEFT_MARGIN
		";
		$arUpdate = array();
		$prev_right = 0;
		$rsChildren = $DB->Query($strSql);
		while($arChild = $rsChildren->Fetch())
		{
			if($arChild["RIGHT_MARGIN"] > $prev_right)
			{
				$prev_right = $arChild["RIGHT_MARGIN"];
				$arUpdate[] = "(LEFT_MARGIN >= ".$arChild["LEFT_MARGIN"]." AND RIGHT_MARGIN <= ".$arChild["RIGHT_MARGIN"].")\n";
			}
		}
		if(count($arUpdate) > 0)
		{
			$DB->Query("
				UPDATE b_iblock_section SET
					TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
					,GLOBAL_ACTIVE = 'N'
				WHERE
					IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
					AND (".implode(" OR ", $arUpdate).")
			");
		}
	}

	public static function getSectionCodePath($sectionId)
	{
		if (!isset(self::$arSectionPathCache[$sectionId]))
		{
			self::$arSectionPathCache[$sectionId] = '';
			$res = CIBlockSection::GetNavChain(0, $sectionId, ['ID', 'CODE'], true);
			foreach ($res as $a)
			{
				$a['CODE'] = (string)$a['CODE'];
				self::$arSectionCodeCache[$a['ID']] = rawurlencode($a['CODE']);
				self::$arSectionPathCache[$sectionId] .= rawurlencode($a['CODE']) . '/';
			}
			unset($a, $res);
			self::$arSectionPathCache[$sectionId] = rtrim(self::$arSectionPathCache[$sectionId], '/');
		}
		return self::$arSectionPathCache[$sectionId];
	}

	public static function getSectionCode($sectionId): string
	{
		global $DB;

		$sectionId = (int)$sectionId;
		if (!isset(self::$arSectionCodeCache[$sectionId]))
		{
			self::$arSectionCodeCache[$sectionId] = '';
			$res = $DB->Query("SELECT IBLOCK_ID, CODE FROM b_iblock_section WHERE ID = ".$sectionId);
			$a = $res->Fetch();
			unset($res);
			if ($a)
			{
				self::$arSectionCodeCache[$sectionId] = rawurlencode((string)$a['CODE']);
			}
		}

		return self::$arSectionCodeCache[$sectionId];
	}

	protected static function normalizeMixedFilter(array $filter): array
	{
		$modifyList = [
			// common keys
			'ID_1' => '>=ID',
			'ID_2' => '<=ID',
			'NAME' => '?NAME',
			'TIMESTAMP_X_1' => '>=TIMESTAMP_X',
			'TIMESTAMP_X_2' => '<=TIMESTAMP_X',
			'DATE_CREATE_1' => '>=DATE_CREATE',
			'DATE_CREATE_2' => '<=DATE_CREATE',
			'CODE' => 'CODE',
			'EXTERNAL_ID' => 'XML_ID',
			'MODIFIED_USER_ID' => 'MODIFIED_BY',
			'CREATED_USER_ID' => 'CREATED_BY',
			'DESCRIPTION' => '?SEARCHABLE_CONTENT',

			// specific section keys
			// none

			// specific element keys
			'DATE_ACTIVE_FROM_1' => '>=DATE_ACTIVE_FROM',
			'DATE_ACTIVE_FROM_2' => '<=DATE_ACTIVE_FROM',
			'DATE_ACTIVE_TO_1' => '>=DATE_ACTIVE_TO',
			'DATE_ACTIVE_TO_2' => '<=DATE_ACTIVE_TO',
		];

		$result = [];
		foreach ($filter as $field => $value)
		{
			$newField = $modifyList[$field] ?? $field;
			$result[$newField] = $value;
		}

		if (isset($result['CHECK_PERMISSIONS']))
		{
			$result['MIN_PERMISSION'] = $result['MIN_PERMISSION'] ?? CIBlockRights::PUBLIC_READ;
		}
		if (isset($result['SECTION_ID']))
		{
			if ((string)$result['SECTION_ID'] === '')
			{
				unset($result['SECTION_ID']);
			}
			else
			{
				$result['SECTION_ID'] = (int)$result['SECTION_ID'];
				if ($result['SECTION_ID'] < 0)
				{
					unset($result['SECTION_ID']);
				}
			}
		}

		return $result;
	}

	/**
	 * Returns a filter by element properties and product fields.
	 *
	 * @param array $filter
	 * @return array
	 */
	public static function getElementInherentFilter(array $filter): array
	{
		$result = array();
		$filter = array_filter($filter, [__CLASS__, 'clearNull']);
		if (!empty($filter))
		{
			$prepared = array();
			foreach ($filter as $index => $value)
			{
				if ($index == 'ID' && (is_int($value) || is_string($value)))
				{
					$result['=ID'] = $value;
					break;
				}
				elseif (
					preg_match('/^(>=|<=|>|<|=|!=|)ID$/', $index, $prepared)
					&& $value instanceof \CIBlockElement
				)
				{
					if ($index == 'ID')
						$index = '=ID';
					$result[$index] = $value;
				}
				elseif (($index === 'SUBQUERY' || $index === '=SUBQUERY') && is_array($value))
				{
					$result[$index] = $value;
				}
			}
			unset($index, $value);
			$catalogIncluded = Loader::includeModule('catalog');
			foreach($filter as $index => $value)
			{
				$op = CIBlock::MkOperationFilter($index);
				if (
					strncmp($op['FIELD'], 'PROPERTY_', 9) == 0
					|| ($catalogIncluded && \CProductQueryBuilder::isValidField($op['FIELD']))
				)
				{
					$result[$index] = $value;
				}
			}
			unset($op);
		}
		return $result;
	}

	/**
	 * @param array $filter
	 * @return bool
	 */
	public static function checkLoadSections(array $filter): bool
	{
		$result = true;
		if (!empty($filter))
		{
			$blackList = [
				'TAGS' => true,
				'SHOW_COUNTER' => true,
				'IBLOCK_SECTION_ID' => true,
				'SHOW_COUNTER_START' => true,
				'CHECK_BP_PERMISSIONS' => true,
				'CHECK_BP_VIRTUAL_PERMISSIONS' => true,
				'ACTIVE_FROM' => true,
				'ACTIVE_TO' => true,
				'DATE_ACTIVE_FROM' => true,
				'DATE_ACTIVE_TO' => true,
				'ACTIVE_DATE' => true,
				'RATING_USER_ID' => true,
				'WF_STATUS_ID' => true,
				'WF_LOCK_STATUS' => true,
				'WF_LAST_STATUS_ID' => true,
				'WF_PARENT_ELEMENT_ID' => true,
				'WF_COMMENTS' => true,
				'SEARCHABLE_CONTENT' => true,
				'INCLUDE_SUBSECTIONS' => true,
			];
			$catalogIncluded = Loader::includeModule('catalog');
			foreach($filter as $index => $value)
			{
				if (
					($index === '=ID' && (is_int($value) || is_string($value)))
					|| $value instanceof \CIBlockElement
				)
				{
					$result = false;
					break;
				}
				$op = CIBlock::MkOperationFilter($index);
				$field = $op['FIELD'];
				if ($field === 'SUBQUERY' && is_array($value))
				{
					$result = false;
					break;
				}
				if (
					strncmp($field, 'PROPERTY_', 9) === 0
					|| isset($blackList[$field])
					|| ($catalogIncluded && \CProductQueryBuilder::isRealFilterField($field))
				)
				{
					$result = false;
					break;
				}
			}
			unset($field, $op);
		}
		return $result;
	}

	protected static function checkLoadElements(array $filter): bool
	{
		$result = true;
		if (!empty($filter))
		{
			$blackList = [
				'GLOBAL_ACTIVE' => true,
				'DEPTH_LEVEL' => true,
				'SOCNET_GROUP_ID' => true,
				'RIGHT_MARGIN' => true,
				'LEFT_MARGIN' => true,
				'LEFT_BORDER' => true,
				'RIGHT_BORDER' => true,
				'HAS_ELEMENT' => true,
			];
			foreach($filter as $index => $value)
			{
				$op = CIBlock::MkOperationFilter($index);
				if (isset($blackList[$op['FIELD']]))
				{
					$result = false;
					break;
				}
			}
			unset($op);
		}

		return $result;
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	protected static function getPreparedFilterById(array $filter): array
	{
		$result = array();
		if (isset($filter['ID_1']) || isset($filter['ID_2']))
		{
			if (isset($filter['ID_1']))
				$result['>=ID'] = $filter['ID_1'];
			if (isset($filter['ID_2']))
				$result['<=ID'] = $filter['ID_2'];
		}
		else
		{
			$prepared = array();
			foreach (array_keys($filter) as $index)
			{
				if ($filter[$index] === null || is_object($filter[$index]))
					continue;
				if (preg_match('/^(>=|<=|>|<|=|!=)ID$/', $index, $prepared))
					$result[$index] = $filter[$index];
			}
			unset($index);
			unset($prepared);
		}
		return $result;
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	protected static function clearNull($value): bool
	{
		return $value !== null;
	}

	/**
	 * @param array $arFields ID, IBLOCK_ID, IBLOCK_SECTION_ID, NAME, SORT
	 */
	public static function recountTreeAfterAdd(array $arFields): void
	{
		global $DB;

		$ID = (int)($arFields['ID'] ?? 0);
		$IBLOCK_ID = (int)($arFields['IBLOCK_ID'] ?? 0);
		if ($ID <= 0 || $IBLOCK_ID <= 0)
		{
			return;
		}

		$arParent = false;
		if ($arFields["IBLOCK_SECTION_ID"] !== false)
		{
			$strSql = "
				SELECT BS.ID, BS.ACTIVE, BS.GLOBAL_ACTIVE, BS.DEPTH_LEVEL, BS.LEFT_MARGIN, BS.RIGHT_MARGIN
				FROM b_iblock_section BS
				WHERE BS.IBLOCK_ID = ".$IBLOCK_ID."
				AND BS.ID = ".$arFields["IBLOCK_SECTION_ID"]."
			";
			$rsParent = $DB->Query($strSql);
			$arParent = $rsParent->Fetch();
		}

		$NAME = $arFields["NAME"];
		$SORT = (int)$arFields["SORT"];

		//Find rightmost child of the parent
		$strSql = "
			SELECT BS.ID, BS.RIGHT_MARGIN, BS.GLOBAL_ACTIVE, BS.DEPTH_LEVEL
			FROM b_iblock_section BS
			WHERE BS.IBLOCK_ID = ".$IBLOCK_ID."
			AND ".($arFields["IBLOCK_SECTION_ID"] !== false ? "BS.IBLOCK_SECTION_ID=".$arFields["IBLOCK_SECTION_ID"] : "BS.IBLOCK_SECTION_ID IS NULL")."
			AND (
				(BS.SORT < ".$SORT.")
				OR (BS.SORT = ".$SORT." AND BS.NAME < '".$DB->ForSQL($NAME)."')
			)
			AND BS.ID <> ".$ID."
			ORDER BY BS.SORT DESC, BS.NAME DESC
		";
		$rsChild = $DB->Query($strSql);

		if ($arChild = $rsChild->Fetch())
		{
			//We found the left neighbour
			$arUpdate = array(
				"LEFT_MARGIN" => (int)$arChild["RIGHT_MARGIN"] + 1,
				"RIGHT_MARGIN" => (int)$arChild["RIGHT_MARGIN"] + 2,
				"DEPTH_LEVEL" => (int)$arChild["DEPTH_LEVEL"],
			);

			//in case we adding active section
			if ($arFields["ACTIVE"] != "N")
			{
				//Look up GLOBAL_ACTIVE of the parent
				//if none then take our own
				if ($arParent)//We must inherit active from the parent
				{
					$arUpdate["GLOBAL_ACTIVE"] = $arParent["GLOBAL_ACTIVE"] == "Y" ? "Y" : "N";
				}
				else //No parent was found take our own
				{
					$arUpdate["GLOBAL_ACTIVE"] = "Y";
				}
			}
			else
			{
				$arUpdate["GLOBAL_ACTIVE"] = "N";
			}
		}
		else
		{
			//If we have parent, when take its left_margin
			if ($arParent)
			{
				$arUpdate = array(
					"LEFT_MARGIN" => (int)$arParent["LEFT_MARGIN"] + 1,
					"RIGHT_MARGIN" => (int)$arParent["LEFT_MARGIN"] + 2,
					"GLOBAL_ACTIVE" => ($arParent["GLOBAL_ACTIVE"] == "Y") && ($arFields["ACTIVE"] != "N") ? "Y" : "N",
					"DEPTH_LEVEL" => (int)$arParent["DEPTH_LEVEL"] + 1,
				);
			}
			else
			{
				//We are only one/leftmost section in the iblock.
				$arUpdate = array(
					"LEFT_MARGIN" => 1,
					"RIGHT_MARGIN" => 2,
					"GLOBAL_ACTIVE" => $arFields["ACTIVE"] != "N" ? "Y" : "N",
					"DEPTH_LEVEL" => 1,
				);
			}
		}

		$DB->Query("
			UPDATE b_iblock_section SET
				TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
				,LEFT_MARGIN = ".$arUpdate["LEFT_MARGIN"]."
				,RIGHT_MARGIN = ".$arUpdate["RIGHT_MARGIN"]."
				,DEPTH_LEVEL = ".$arUpdate["DEPTH_LEVEL"]."
				,GLOBAL_ACTIVE = '".$arUpdate["GLOBAL_ACTIVE"]."'
			WHERE
				ID = ".$ID."
		");

		$DB->Query("
			UPDATE b_iblock_section SET
				TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
				,LEFT_MARGIN = LEFT_MARGIN + 2
				,RIGHT_MARGIN = RIGHT_MARGIN + 2
			WHERE
				IBLOCK_ID = ".$IBLOCK_ID."
				AND LEFT_MARGIN >= ".$arUpdate["LEFT_MARGIN"]."
				AND ID <> ".$ID."
		");

		if ($arParent)
		{
			$DB->Query("
				UPDATE b_iblock_section SET
					TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
					,RIGHT_MARGIN = RIGHT_MARGIN + 2
				WHERE
					IBLOCK_ID = ".$IBLOCK_ID."
					AND LEFT_MARGIN <= ".$arParent["LEFT_MARGIN"]."
					AND RIGHT_MARGIN >= ".$arParent["RIGHT_MARGIN"]."
			");
		}
	}

	/**
	 * @param array $arFields ID, ACTIVE, IBLOCK_SECTION_ID, NAME, SORT
	 * @param array $db_record *
	 */
	public static function recountTreeAfterUpdate(array $arFields, array $db_record): void
	{
		global $DB;

		if (empty($arFields) || empty($db_record))
		{
			return;
		}

		$ID = $arFields['ID'];

		$move_distance = 0;

		//Move inside the tree
		if ((isset($arFields["SORT"]) && $arFields["SORT"] != $db_record["SORT"])
			|| (isset($arFields["NAME"]) && $arFields["NAME"] != $db_record["NAME"])
			|| (isset($arFields["IBLOCK_SECTION_ID"]) && $arFields["IBLOCK_SECTION_ID"] != $db_record["IBLOCK_SECTION_ID"]))
		{
			//First "delete" from the tree
			$distance = (int)$db_record["RIGHT_MARGIN"] - (int)$db_record["LEFT_MARGIN"] + 1;
			$DB->Query("
				UPDATE b_iblock_section SET
					TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
					,LEFT_MARGIN = -LEFT_MARGIN
					,RIGHT_MARGIN = -RIGHT_MARGIN
				WHERE
					IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
					AND LEFT_MARGIN >= ".(int)$db_record["LEFT_MARGIN"]."
					AND LEFT_MARGIN <= ".(int)$db_record["RIGHT_MARGIN"]."
			");

			$DB->Query("
				UPDATE b_iblock_section SET
					TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
					,RIGHT_MARGIN = RIGHT_MARGIN - ".$distance."
				WHERE
					IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
					AND RIGHT_MARGIN > ".$db_record["RIGHT_MARGIN"]."
			");

			$DB->Query("
				UPDATE b_iblock_section SET
					TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
					,LEFT_MARGIN = LEFT_MARGIN - ".$distance."
				WHERE
					IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
					AND LEFT_MARGIN > ".$db_record["LEFT_MARGIN"]."
			");

			//Next insert into the the tree almost as we do when inserting the new one

			$PARENT_ID = (int)($arFields["IBLOCK_SECTION_ID"] ?? $db_record["IBLOCK_SECTION_ID"]);
			$NAME = $arFields["NAME"] ?? $db_record["NAME"];
			$SORT = (int)($arFields["SORT"] ?? $db_record["SORT"]);

			$arParents = array();
			$strSql = "
				SELECT BS.ID, BS.ACTIVE, BS.GLOBAL_ACTIVE, BS.DEPTH_LEVEL, BS.LEFT_MARGIN, BS.RIGHT_MARGIN
				FROM b_iblock_section BS
				WHERE BS.IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
				AND BS.ID in (".(int)$db_record["IBLOCK_SECTION_ID"].", ".$PARENT_ID.")
			";
			$rsParents = $DB->Query($strSql);
			while ($arParent = $rsParents->Fetch())
			{
				$arParents[$arParent["ID"]] = $arParent;
			}

			//Find rightmost child of the parent
			$strSql = "
				SELECT BS.ID, BS.RIGHT_MARGIN, BS.DEPTH_LEVEL
				FROM b_iblock_section BS
				WHERE BS.IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
				AND ".($PARENT_ID > 0 ? "BS.IBLOCK_SECTION_ID=".$PARENT_ID : "BS.IBLOCK_SECTION_ID IS NULL")."
				AND (
					(BS.SORT < ".$SORT.")
					OR (BS.SORT = ".$SORT." AND BS.NAME < '".$DB->ForSQL($NAME)."')
				)
				AND BS.ID <> ".$ID."
				ORDER BY BS.SORT DESC, BS.NAME DESC
			";
			$rsChild = $DB->Query($strSql);
			if ($arChild = $rsChild->Fetch())
			{
				//We found the left neighbour
				$arUpdate = array(
					"LEFT_MARGIN" => (int)$arChild["RIGHT_MARGIN"] + 1,
					"DEPTH_LEVEL" => (int)$arChild["DEPTH_LEVEL"],
				);
			}
			else
			{
				//If we have parent, when take its left_margin
				if (isset($arParents[$PARENT_ID]) && $arParents[$PARENT_ID])
				{
					$arUpdate = array(
						"LEFT_MARGIN" => (int)$arParents[$PARENT_ID]["LEFT_MARGIN"] + 1,
						"DEPTH_LEVEL" => (int)$arParents[$PARENT_ID]["DEPTH_LEVEL"] + 1,
					);
				}
				else
				{
					//We are only one/leftmost section in the iblock.
					$arUpdate = array(
						"LEFT_MARGIN" => 1,
						"DEPTH_LEVEL" => 1,
					);
				}
			}

			$move_distance = (int)$db_record["LEFT_MARGIN"] - $arUpdate["LEFT_MARGIN"];

			$DB->Query("
				UPDATE b_iblock_section SET
					TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
					,LEFT_MARGIN = LEFT_MARGIN + ".$distance."
					,RIGHT_MARGIN = RIGHT_MARGIN + ".$distance."
				WHERE
					IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
					AND LEFT_MARGIN >= ".$arUpdate["LEFT_MARGIN"]."
			");

			$DB->Query("
				UPDATE b_iblock_section SET
					TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
					,LEFT_MARGIN = -LEFT_MARGIN - ".$move_distance."
					,RIGHT_MARGIN = -RIGHT_MARGIN - ".$move_distance."
					".($arUpdate["DEPTH_LEVEL"] != (int)$db_record["DEPTH_LEVEL"] ? ",DEPTH_LEVEL = DEPTH_LEVEL - ".($db_record["DEPTH_LEVEL"] - $arUpdate["DEPTH_LEVEL"]) : "")."
				WHERE
					IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
					AND LEFT_MARGIN <= ".(-(int)$db_record["LEFT_MARGIN"])."
					AND LEFT_MARGIN >= ".(-(int)$db_record["RIGHT_MARGIN"])."
			");

			if (isset($arParents[$PARENT_ID]))
			{
				$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
						,RIGHT_MARGIN = RIGHT_MARGIN + ".$distance."
					WHERE
						IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
						AND LEFT_MARGIN <= ".$arParents[$PARENT_ID]["LEFT_MARGIN"]."
						AND RIGHT_MARGIN >= ".$arParents[$PARENT_ID]["RIGHT_MARGIN"]."
				");
			}
		}

		//Check if parent was changed
		if (isset($arFields["IBLOCK_SECTION_ID"]) && $arFields["IBLOCK_SECTION_ID"] != $db_record["IBLOCK_SECTION_ID"])
		{
			$rsSection = CIBlockSection::GetList(
				array(),
				array("ID" => $ID, "CHECK_PERMISSIONS" => "N"),
				false,
				array(
					"ID", "IBLOCK_ID", "IBLOCK_SECTION_ID",
					"LEFT_MARGIN", "RIGHT_MARGIN",
					"ACTIVE", "GLOBAL_ACTIVE"
				)
			);

			$arSection = $rsSection->Fetch();
			unset($rsSection);

			$strSql = "
				SELECT ID, GLOBAL_ACTIVE
				FROM b_iblock_section
				WHERE IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
				AND ID = ".(int)$arFields["IBLOCK_SECTION_ID"]."
			";
			$rsParent = $DB->Query($strSql);
			$arParent = $rsParent->Fetch();

			$arFields['ACTIVE'] ??= null;
			//If new parent is not globally active
			//or we are not active either
			//we must be not globally active too
			if (
				($arParent && $arParent['GLOBAL_ACTIVE'] === 'N')
				|| $arFields['ACTIVE'] === 'N'
			)
			{
				$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
						,GLOBAL_ACTIVE = 'N'
					WHERE
						IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
						AND LEFT_MARGIN >= ".(int)$arSection["LEFT_MARGIN"]."
						AND RIGHT_MARGIN <= ".(int)$arSection["RIGHT_MARGIN"]."
				");
			}
			//New parent is globally active
			//And we WAS NOT active
			//But is going to be
			elseif (
				$arSection['ACTIVE'] === 'N'
				&& $arFields['ACTIVE'] === 'Y'
			)
			{
				static::RecalcGlobalActiveFlag($arSection);
			}
			//New parent is globally active
			//And we WAS active but NOT globally active
			//But is going to be
			elseif (
				(!$arParent || $arParent['GLOBAL_ACTIVE'] === 'Y')
				&& $arSection['GLOBAL_ACTIVE'] === 'N'
				&& (
					$arSection['ACTIVE'] === 'Y'
					|| $arFields['ACTIVE'] === 'Y'
				)
			)
			{
				static::RecalcGlobalActiveFlag($arSection);
			}
			//Otherwise we may not to change anything
		}
		//Parent not changed
		//but we are going to change activity flag
		elseif (isset($arFields["ACTIVE"]) && $arFields["ACTIVE"] != $db_record["ACTIVE"])
		{
			//Make all children globally inactive
			if ($arFields["ACTIVE"] == "N")
			{
				$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
						,GLOBAL_ACTIVE = 'N'
					WHERE
						IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
						AND LEFT_MARGIN >= ".(int)$db_record["LEFT_MARGIN"]."
						AND RIGHT_MARGIN <= ".(int)$db_record["RIGHT_MARGIN"]."
				");
			}
			else
			{
				//Check for parent activity
				$strSql = "
					SELECT ID, GLOBAL_ACTIVE
					FROM b_iblock_section
					WHERE IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
					AND ID = ".(int)$db_record["IBLOCK_SECTION_ID"]."
				";
				$rsParent = $DB->Query($strSql);
				$arParent = $rsParent->Fetch();

				//Parent is active
				//and we changed
				//so need to recalc
				if (!$arParent || $arParent["GLOBAL_ACTIVE"] == "Y")
				{
					static::RecalcGlobalActiveFlag($db_record, -$move_distance);
				}
			}
		}
	}

	/**
	 * @param array $arFields ID
	 */
	public static function recountTreeOnDelete(array $arFields): void
	{
		global $DB;

		$id = (int)($arFields['ID'] ?? 0);
		if ($id <= 0)
		{
			return;
		}

		$ss = $DB->Query("
			SELECT
				IBLOCK_ID,
				LEFT_MARGIN,
				RIGHT_MARGIN
			FROM
				b_iblock_section
			WHERE
				ID = " . $id . "
		")->Fetch();
		if (empty($ss))
		{
			return;
		}

		if (($ss["RIGHT_MARGIN"] > 0) && ($ss["LEFT_MARGIN"] > 0))
		{
			$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
						,RIGHT_MARGIN = RIGHT_MARGIN - 2
					WHERE
						IBLOCK_ID = ".$ss["IBLOCK_ID"]."
						AND RIGHT_MARGIN > ".$ss["RIGHT_MARGIN"]."
				");

			$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type == "ORACLE" ? "NULL" : "TIMESTAMP_X")."
						,LEFT_MARGIN = LEFT_MARGIN - 2
					WHERE
						IBLOCK_ID = ".$ss["IBLOCK_ID"]."
						AND LEFT_MARGIN > ".$ss["LEFT_MARGIN"]."
				");
		}
	}

	public function generateMnemonicCode(string $name, int $iblockId, array $options = []): ?string
	{
		if ($name === '' || $iblockId <= 0)
		{
			return null;
		}

		if ($this->iblock !== null && $this->iblock['ID'] === $iblockId)
		{
			$iblock = $this->iblock;
			$language = $this->iblockLanguage;
		}
		else
		{
			$iblock = CIBlock::GetArrayByID($iblockId);
			if (empty($iblock))
			{
				$iblock = null;
				$language = null;
			}
			else
			{
				$iblock['ID'] = (int)$iblock['ID'];
				$language = static::getIblockLanguage($iblock['ID']);
			}
		}

		if (empty($iblock))
		{
			return null;
		}

		$result = null;
		if (isset($iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']))
		{
			if ($iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']['TRANSLITERATION'] === 'Y'
				&& $iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']['USE_GOOGLE'] === 'N'
			)
			{
				$config = $iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE'];
				$config['LANGUAGE_ID'] = $language;
				$config = array_merge($config, $options);

				if ($config['LANGUAGE_ID'] !== null)
				{
					$settings = [
						'max_len' => $config['TRANS_LEN'],
						'change_case' => $config['TRANS_CASE'],
						'replace_space' => $config['TRANS_SPACE'],
						'replace_other' => $config['TRANS_OTHER'],
						'delete_repeat_replace' => ($config['TRANS_EAT'] == 'Y'),
					];

					$result = CUtil::translit($name, $config['LANGUAGE_ID'], $settings);
				}
			}
		}

		return $result;
	}

	public function isExistsMnemonicCode(string $code, ?int $sectionId, int $iblockId): bool
	{
		if ($code === '')
		{
			return false;
		}
		$filter = [
			'=IBLOCK_ID' => $iblockId,
			'=CODE' => $code,
		];
		if ($sectionId !== null)
		{
			$filter['!=ID'] = $sectionId;
		}

		$row = Iblock\SectionTable::getList([
			'select' => ['ID'],
			'filter' => $filter,
			'limit' => 1,
		])->fetch();

		return !empty($row);
	}

	public function createMnemonicCode(array $section, array $options = []): ?string
	{
		if (!isset($section['NAME']) || $section['NAME'] === '')
		{
			return null;
		}
		$iblockId = $section['IBLOCK_ID'] ?? 0;
		if ($iblockId !== null)
		{
			$iblockId = (int)$iblockId;
		}
		if ($iblockId <= 0)
		{
			return null;
		}

		if ($this->iblock !== null && $this->iblock['ID'] === $iblockId)
		{
			$iblock = $this->iblock;
		}
		else
		{
			$iblock = CIBlock::GetArrayByID($iblockId);
		}

		if (empty($iblock))
		{
			return null;
		}

		$code = null;
		if (isset($iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']))
		{
			$code = $this->generateMnemonicCode($section['NAME'], $iblockId, $options);
			if ($code === null)
			{
				return null;
			}

			if ($iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']['TRANSLITERATION'] === 'Y'
				&& (
					$iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']['UNIQUE'] === 'Y'
					|| ($options['CHECK_UNIQUE'] ?? 'N') === 'Y'
				)
			)
			{
				$id = (int)($section['ID'] ?? null);
				if ($id <= 0)
				{
					$id = null;
				}
				if (!$this->isExistsMnemonicCode($code, $id, $iblockId))
				{
					return $code;
				}

				$checkSimilar = ($options['CHECK_SIMILAR'] ?? 'N') === 'Y';

				$list = [];
				$iterator = Iblock\SectionTable::getList([
					'select' => ['ID', 'CODE'],
					'filter' => [
						'=IBLOCK_ID' => $iblockId,
						'%=CODE' => $code . '%',
					],
				]);
				while ($row = $iterator->fetch())
				{
					if ($checkSimilar && $id === (int)$row['ID'])
					{
						return null;
					}
					$list[$row['CODE']] = true;
				}
				unset($iterator, $row);

				if (isset($list[$code]))
				{
					$code .= '_';
					$i = 1;
					while (isset($list[$code . $i]))
					{
						$i++;
					}

					$code .= $i;
				}
				unset($list);
			}
		}

		return $code;
	}

	protected static function getIblockLanguage(int $iblockId): ?string
	{
		$result = [];
		$iterator = Iblock\IblockSiteTable::getList([
			'select' => ['LANGUAGE_ID' => 'SITE.LANGUAGE_ID'],
			'filter' => ['=IBLOCK_ID' => $iblockId]
		]);
		while ($row = $iterator->fetch())
		{
			$result[$row['LANGUAGE_ID']] = true;
		}
		unset($iterator, $row);

		return count($result) === 1 ? key($result) : null;
	}

	public function getLastError(): string
	{
		return $this->LAST_ERROR;
	}
}
