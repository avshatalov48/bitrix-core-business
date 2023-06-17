<?php
/** @global CMain $APPLICATION */
/** @global CUser $USER */

use Bitrix\Main;
use Bitrix\Main\Loader;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('iblock');
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$request = Main\Context::getCurrent()->getRequest();

set_time_limit(0);
$IBLOCK_ID = (int)$request->get('IBLOCK_ID');
$STEP = (int)$request->get('STEP');
if ($STEP <= 0)
{
	$STEP = 1;
}

if ($request->isPost())
{
	if ($request->getPost('backButton') !== null)
	{
		$STEP -= 2;
	}
	if ($request->getPost('backButton2') !== null)
	{
		$STEP = 1;
	}
}

$NUM_CATALOG_LEVELS = (int)Main\Config\Option::get('iblock', 'num_catalog_levels');
if ($NUM_CATALOG_LEVELS <= 0)
{
	$NUM_CATALOG_LEVELS = 3;
}
$max_execution_time = (int)($request->get('max_execution_time'));
if ($max_execution_time <= 0)
{
	$max_execution_time = 0;
}

$URL_DATA_FILE = (string)$request->get('URL_DATA_FILE');

if (isset($_REQUEST["CUR_LOAD_SESS_ID"]) && $_REQUEST["CUR_LOAD_SESS_ID"] <> '')
	$CUR_LOAD_SESS_ID = $_REQUEST["CUR_LOAD_SESS_ID"];
else
	$CUR_LOAD_SESS_ID = "CL".time();

$bAllLinesLoaded = True;
$CUR_FILE_POS = (int)$request->get('CUR_FILE_POS');
$curFilePos = 0;
$strError = '';
$line_num = 0;
$correct_lines = 0;
$error_lines = 0;
$killed_lines = 0;
$io = CBXVirtualIo::GetInstance();

/////////////////////////////////////////////////////////////////////
$arIBlockAvailProdFields = array(
	"IE_XML_ID" => array(
		"field" => "XML_ID",
		"important" => "Y",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_UNIXML")." (B_IBLOCK_ELEMENT.XML_ID)",
	) ,
	"IE_NAME" => array(
		"field" => "NAME",
		"important" => "Y",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_NAME")." (B_IBLOCK_ELEMENT.NAME)",
	) ,
	"IE_PREVIEW_PICTURE" => array(
		"field" => "PREVIEW_PICTURE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_CATIMG")." (B_IBLOCK_ELEMENT.PREVIEW_PICTURE)",
	) ,
	"IE_PREVIEW_TEXT" => array(
		"field" => "PREVIEW_TEXT",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_CATDESCR")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT)",
	) ,
	"IE_PREVIEW_TEXT_TYPE" => array(
		"field" => "PREVIEW_TEXT_TYPE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_CATDESCRTYPE")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT_TYPE)",
	) ,
	"IE_DETAIL_PICTURE" => array(
		"field" => "DETAIL_PICTURE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_DETIMG")." (B_IBLOCK_ELEMENT.DETAIL_PICTURE)",
	) ,
	"IE_DETAIL_TEXT" => array(
		"field" => "DETAIL_TEXT",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_DETDESCR")." (B_IBLOCK_ELEMENT.DETAIL_TEXT)",
	) ,
	"IE_DETAIL_TEXT_TYPE" => array(
		"field" => "DETAIL_TEXT_TYPE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_DETDESCRTYPE")." (B_IBLOCK_ELEMENT.DETAIL_TEXT_TYPE)",
	) ,
	"IE_ACTIVE" => array(
		"field" => "ACTIVE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_ACTIV")." (B_IBLOCK_ELEMENT.ACTIVE)",
	) ,
	"IE_ACTIVE_FROM" => array(
		"field" => "ACTIVE_FROM",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_ACTIVFROM")." (B_IBLOCK_ELEMENT.ACTIVE_FROM)",
	) ,
	"IE_ACTIVE_TO" => array(
		"field" => "ACTIVE_TO",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_ACTIVTO")." (B_IBLOCK_ELEMENT.ACTIVE_TO)",
	) ,
	"IE_SORT" => array(
		"field" => "SORT",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_SORT")." (B_IBLOCK_ELEMENT.SORT)",
	) ,
	"IE_CODE" => array(
		"field" => "CODE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_CODE")." (B_IBLOCK_ELEMENT.CODE)",
	) ,
	"IE_TAGS" => array(
		"field" => "TAGS",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FI_TAGS")." (B_IBLOCK_ELEMENT.TAGS)",
	) ,
);
$arIBlockAvailGroupFields = array(
	"IC_GROUP" => array(
		"field" => "NAME",
		"important" => "Y",
		"name" => GetMessage("IBLOCK_ADM_IMP_FG_NAME")." (B_IBLOCK_SECTION.NAME)",
	) ,
	"IC_XML_ID" => array(
		"field" => "XML_ID",
		"important" => "Y",
		"name" => GetMessage("IBLOCK_ADM_IMP_FG_UNIXML")." (B_IBLOCK_SECTION.XML_ID)",
	) ,
	"IC_ACTIVE" => array(
		"field" => "ACTIVE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FG_ACTIV")." (B_IBLOCK_SECTION.ACTIVE)",
	) ,
	"IC_SORT" => array(
		"field" => "SORT",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FG_SORT")." (B_IBLOCK_SECTION.SORT)",
	) ,
	"IC_DESCRIPTION" => array(
		"field" => "DESCRIPTION",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FG_DESCR")." (B_IBLOCK_SECTION.DESCRIPTION)",
	) ,
	"IC_DESCRIPTION_TYPE" => array(
		"field" => "DESCRIPTION_TYPE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FG_DESCRTYPE")." (B_IBLOCK_SECTION.DESCRIPTION_TYPE)",
	) ,
	"IC_CODE" => array(
		"field" => "CODE",
		"important" => "N",
		"name" => GetMessage("IBLOCK_ADM_IMP_FG_CODE")." (B_IBLOCK_SECTION.CODE)",
	),
);
/////////////////////////////////////////////////////////////////////

class CAssocData extends CCSVData
{
	var $__rows = array();
	var $__pos = array();
	var $__last_pos = 0;
	var $NUM_FIELDS = 0;
	var $IBLOCK_ID = 0;
	var $tmpid = "";
	var $PK = array();
	var $GROUP_REGEX = "";

	function __construct($fields_type = "R", $first_header = false, $NUM_FIELDS = 0)
	{
		parent::__construct($fields_type, $first_header);
		$this->NUM_FIELDS = (int)$NUM_FIELDS;
	}

	function GetPos()
	{
		if(empty($this->__pos))
			return parent::GetPos();
		else
			return $this->__pos[count($this->__pos) - 1];
	}

	function Fetch()
	{
		if (empty($this->__rows))
		{
			$this->__last_pos = $this->GetPos();
			return parent::Fetch();
		}
		else
		{
			$this->__last_pos = array_pop($this->__pos);
			return array_pop($this->__rows);
		}
	}

	function PutBack($row)
	{
		$this->__rows[] = $row;
		$this->__pos[] = $this->__last_pos;
	}

	function AddPrimaryKey($field_name, $field_ind)
	{
		$this->PK[$field_name] = $field_ind;
	}

	function SetGroupFields($arGroupFields)
	{
		$ar = array();
		foreach ($arGroupFields as $name => $arField)
			$ar[] = $name;

		$this->GROUP_REGEX = "/^(".implode("|", $ar).")\\d+\$/";
	}

	function FetchAssoc()
	{
		global $line_num;
		$result = array();
		while ($ar = $this->Fetch())
		{
			$line_num++;
			//Search for "PRIMARY KEY"
			foreach ($this->PK as $pk_field => $pk_ind)
			{
				if (array_key_exists($pk_field, $result))
				{
					//Check for Next record
					if ($result[$pk_field] !== trim($ar[$pk_ind]))
					{
						$line_num--;
						$this->PutBack($ar);
						return $result;
					}
					else
					{
						//When XML_ID do match we skip NAME check
						break;
					}
				}
			}
			for ($i = 0; $i < $this->NUM_FIELDS; $i++)
			{
				$key = $GLOBALS["field_".$i];
				$value = trim($ar[$i]);
				if (preg_match($this->GROUP_REGEX, $key))
				{
					//IBlockSection
					if (!array_key_exists($key, $result))
						$result[$key] = array();

					$result[$key][] = $value;
				}
				elseif (preg_match("/^IP_PROP/", $key))
				{
					//Multiple values only for properties
					if (!array_key_exists($key, $result))
					{
						$result[$key] = $value;
					}
					elseif (!is_array($result[$key]) && $result[$key] !== $value)
					{
						$result[$key] = array(
							$result[$key],
						);
						$result[$key][] = $value;
					}
					elseif (is_array($result[$key]) && !in_array($value, $result[$key]))
					{
						$result[$key][] = $value;
					}
					else
					{
						//we ignore repeated values
					}
				}
				else
				{
					$result[$key] = $value;
				}
			}
			if (empty($this->PK))
				return $result;
		}
		//eof
		if (empty($result))
			return $ar;
		else
			return $result;
	}

	function MapSections($arRes)
	{
		global $NUM_CATALOG_LEVELS, $arIBlockAvailGroupFields;
		static $arSectionCache = array();
		$bs = new CIBlockSection;
		$result = array();
		while (true)
		{
			$stop_processing = false;
			// this array is path to element
			$arGroupsTmp = array();
			for ($i = 0; $i < $NUM_CATALOG_LEVELS; $i++)
			{
				$bOK = false; //will be true when at least one important field met
				$arGroupsTmp1 = array(
					"TMP_ID" => $this->tmpid,
				);
				foreach ($arIBlockAvailGroupFields as $key => $value)
				{
					$fkey = $value["field"];
					if (array_key_exists($key.$i, $arRes) && !empty($arRes[$key.$i]))
					{
						$arGroupsTmp1[$fkey] = array_shift($arRes[$key.$i]);
					}
					if ($value["important"] == "Y" && isset($arGroupsTmp1[$fkey]) && $arGroupsTmp1[$fkey] <> '')
						$bOK = true;
				}
				// drop empty target sections
				if ($bOK)
				{
					// When group does not have name  "<Empty name>"
					if ($arGroupsTmp1["NAME"] == '')
						$arGroupsTmp1["NAME"] = GetMessage("IBLOCK_ADM_IMP_NOMAME");

					if (!$stop_processing)
					{
						$arGroupsTmp[] = $arGroupsTmp1;
					}
				}
				else
				{
					$stop_processing = true;
				}
			}

			//Finished with groups
			if (empty($arGroupsTmp))
				break;

			// Create sections tree. Save section code for elemet insertions
			$LAST_GROUP_CODE = 0;
			foreach ($arGroupsTmp as $i => $arGroup)
			{
				$arFilter = array(
					"IBLOCK_ID" => $this->IBLOCK_ID,
					"CHECK_PERMISSIONS" => "N",
				);

				if (isset($arGroup["XML_ID"]) && mb_strlen($arGroup["XML_ID"]))
				{
					$arFilter["=XML_ID"] = $arGroup["XML_ID"];
				}
				elseif (isset($arGroup["NAME"]) && mb_strlen($arGroup["NAME"]))
				{
					$arFilter["=NAME"] = $arGroup["NAME"];
				}

				if ($LAST_GROUP_CODE > 0)
				{
					$arFilter["SECTION_ID"] = $LAST_GROUP_CODE;
					$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = $LAST_GROUP_CODE;
				}
				else
				{
					$arFilter["SECTION_ID"] = 0;
					$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = false;
				}

				$cache_id = md5(serialize($arFilter));
				if (array_key_exists($cache_id, $arSectionCache))
				{
					$arr = $arSectionCache[$cache_id];
				}
				else
				{
					$res = CIBlockSection::GetList(array() , $arFilter);
					if ($arr = $res->Fetch())
						$arSectionCache[$cache_id] = $arr;
				}

				if ($arr)
				{
					$arGroupsTmp[$i]["IBLOCK_ID"] = $arr["IBLOCK_ID"];
					$LAST_GROUP_CODE = $arr["ID"];
					$bUpdate = false;
					foreach ($arGroupsTmp[$i] as $field_code => $field_value)
					{
						if ($field_value."" !== $arr[$field_code]."")
						{
							$bUpdate = true;
							break;
						}
					}
					if ($bUpdate)
					{
						$bs->Update($LAST_GROUP_CODE, $arGroupsTmp[$i]);
						unset($arSectionCache[$cache_id]);
					}
				}
				else
				{
					$arGroupsTmp[$i]["IBLOCK_ID"] = $this->IBLOCK_ID;
					$arGroupsTmp[$i]["ACTIVE"] = "Y";
					$LAST_GROUP_CODE = $bs->Add($arGroupsTmp[$i]);
				}
			}
			if ($LAST_GROUP_CODE > 0)
				$result[$LAST_GROUP_CODE] = $LAST_GROUP_CODE;
		}
		return $result;
	}

	function MapEnum($prop_id, $value)
	{
		static $arEnumCache = array();
		if (is_array($value))
		{
			foreach ($value as $k => $v)
				$value[$k] = $this->MapEnum($prop_id, $v);
		}
		else
		{
			if (!isset($arEnumCache[$prop_id]))
				$arEnumCache[$prop_id] = array();

			if (array_key_exists($value, $arEnumCache[$prop_id]))
			{
				$value = $arEnumCache[$prop_id][$value];
			}
			else
			{
				$res2 = CIBlockProperty::GetPropertyEnum($prop_id, array() , array(
					"IBLOCK_ID" => $this->IBLOCK_ID,
					"VALUE" => $value,
				));
				if ($arRes2 = $res2->Fetch())
					$value = $arEnumCache[$prop_id][$value] = $arRes2["ID"];
				else
					$value = $arEnumCache[$prop_id][$value] = CIBlockPropertyEnum::Add(array(
						"PROPERTY_ID" => $prop_id,
						"VALUE" => $value,
						"TMP_ID" => $this->tmpid,
					));
			}
		}
		return $value;
	}

	function MapFiles($value)
	{
		global $PATH2PROP_FILES;
		$io = CBXVirtualIo::GetInstance();

		if (!is_array($value))
			$value = array(
				$value,
			);

		$result = array();
		$j = 0;
		foreach ($value as $file_name)
		{
			if ($file_name <> '')
			{
				if (preg_match("/^(ftp|ftps|http|https):\\/\\//", $file_name))
					$arFile = CFile::MakeFileArray($file_name);
				else
					$arFile = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2PROP_FILES."/".$file_name));

				if (isset($arFile["tmp_name"]))
					$result["n".($j++)] = $arFile;
			}
		}
		return $result;
	}
}

$delimiterList = [
	'TZP' => GetMessage("IBLOCK_ADM_IMP_TZP"),
	'ZPT' => GetMessage("IBLOCK_ADM_IMP_ZPT"),
	'TAB' => GetMessage("IBLOCK_ADM_IMP_TAB"),
	'SPS' => GetMessage("IBLOCK_ADM_IMP_SPS"),
	'OTR' => GetMessage("IBLOCK_ADM_IMP_OTR"),
];

$fields_type = (string)$request->getPost('fields_type');
$first_names_r = (string)$request->getPost('first_names_r');
$delimiter_r = (string)$request->getPost('delimiter_r');
if (!isset($delimiterList[$delimiter_r]))
{
	$delimiter_r = 'TZP';
}
$delimiter_other_r = (string)$request->getPost('delimiter_other_r');
$metki_f = (string)$request->getPost('metki_f');
$first_names_f = (string)$request->getPost('first_names_f');
$PATH2IMAGE_FILES = (string)$request->getPost('PATH2IMAGE_FILES');
$IMAGE_RESIZE = (string)$request->getPost('IMAGE_RESIZE');
$PATH2PROP_FILES = (string)$request->getPost('PATH2PROP_FILES');
$outFileAction = (string)($request->getPost('outFileAction'));
if ($outFileAction !== 'H' && $outFileAction !== 'D' && $outFileAction !== 'F')
{
	$outFileAction = 'F';
}
$inFileAction = (string)$request->getPost('inFileAction');
if ($inFileAction !== 'F' && $inFileAction !== 'A')
{
	$inFileAction = 'F';
}

$DATA_FILE_NAME = '';
$arDataFileFields = [];

/////////////////////////////////////////////////////////////////////
if (($request->isPost() || $CUR_FILE_POS > 0) && $STEP > 1 && check_bitrix_sessid())
{
	$fieldList = [
		'fields_type',
		'fields_type',
		'delimiter_r',
		'delimiter_r',
		'first_names_f',
		'first_names_f',
		'PATH2IMAGE_FILES',
		'IMAGE_RESIZE',
		'PATH2PROP_FILES',
		'outFileAction',
		'inFileAction',
		'max_execution_time',
	];
	$NUM_FIELDS = 0;

	//*****************************************************************//
	if (isset($_FILES["DATA_FILE"]) && is_uploaded_file($_FILES["DATA_FILE"]["tmp_name"]))
	{
		if (mb_strtolower(GetFileExtension($_FILES["DATA_FILE"]["name"])) !== "csv")
		{
			$strError.= GetMessage("IBLOCK_ADM_IMP_NOT_CSV")."<br>";
		}
		else
		{
			$DATA_FILE_NAME =
				'/' . Main\Config\Option::get('main', 'upload_dir', 'upload')
				. '/'. basename($_FILES['DATA_FILE']['name'])
			;
			if ($APPLICATION->GetFileAccessPermission($DATA_FILE_NAME) >= 'W')
			{
				copy($_FILES['DATA_FILE']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $DATA_FILE_NAME);
			}
			else
			{
				$DATA_FILE_NAME = '';
			}
		}
	}

	if ($strError === '')
	{
		if ($DATA_FILE_NAME === '')
		{
			if ($URL_DATA_FILE !== '')
			{
				$URL_DATA_FILE = trim(str_replace("\\", "/", trim($URL_DATA_FILE)) , "/");
				$FILE_NAME = rel2abs($_SERVER['DOCUMENT_ROOT'], '/' . $URL_DATA_FILE);
				if (
					(mb_strlen($FILE_NAME) > 1)
					&& ($FILE_NAME === '/' . $URL_DATA_FILE)
					&& $io->FileExists($_SERVER['DOCUMENT_ROOT'] . $FILE_NAME)
					&& ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= 'W')
				)
				{
					$DATA_FILE_NAME = $FILE_NAME;
				}
			}
		}

		if ($DATA_FILE_NAME === '')
		{
			$strError .= GetMessage('IBLOCK_ADM_IMP_NO_DATA_FILE_SIMPLE') . '<br>';
		}

		if (!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, 'element_edit_any_wf_status'))
		{
			$strError .= GetMessage('IBLOCK_ADM_IMP_NO_IBLOCK') . '<br>';
		}
	}

	if ($strError === '')
	{
		if (
			$CUR_FILE_POS > 0
			&& isset($_SESSION[$CUR_LOAD_SESS_ID]['LOAD_SCHEME'])
			&& is_array($_SESSION[$CUR_LOAD_SESS_ID]['LOAD_SCHEME'])
		)
		{
			foreach ($_SESSION[$CUR_LOAD_SESS_ID]['LOAD_SCHEME'] as $fieldName => $value)
			{
				$GLOBALS[$fieldName] = $value;
			}

			$STEP = 4;
		}
	}

	if ($strError !== '')
	{
		$STEP = 1;
	}
	//*****************************************************************//

	$csvFile = new CAssocData;
	$bFirstHeaderTmp = false;
	if ($STEP > 2)
	{
		//*****************************************************************//
		$csvFile->LoadFile($io->GetPhysicalName($_SERVER['DOCUMENT_ROOT'] . $DATA_FILE_NAME));
		if ($fields_type !== 'F' && $fields_type !== 'R')
		{
			$strError .= GetMessage('IBLOCK_ADM_IMP_NO_FILE_FORMAT') . '<br>';
		}

		if ($strError === '')
		{
			$fields_type = ($fields_type === 'F' ? 'F' : 'R');
			$csvFile->SetFieldsType($fields_type);
			if ($fields_type === 'R')
			{
				$first_names_r = ($first_names_r === 'Y' ? 'Y' : 'N');
				$csvFile->SetFirstHeader($first_names_r === 'Y');
				$delimiter_r_char = '';
				switch ($delimiter_r)
				{
					case 'TAB':
						$delimiter_r_char = "\t";
						break;
					case 'ZPT':
						$delimiter_r_char = ',';
						break;
					case 'SPS':
						$delimiter_r_char = ' ';
						break;
					case 'OTR':
						$delimiter_r_char = mb_substr($delimiter_other_r, 0, 1);
						break;
					case 'TZP':
						$delimiter_r_char = ';';
						break;
				}
				if (mb_strlen($delimiter_r_char) !== 1)
				{
					$strError .= GetMessage('IBLOCK_ADM_IMP_NO_DELIMITER') . '<br>';
				}

				if ($strError == '')
				{
					$csvFile->SetDelimiter($delimiter_r_char);
				}
			}
			else
			{
				$first_names_f = ($first_names_f === 'Y' ? 'Y' : 'N');
				$csvFile->SetFirstHeader($first_names_f === 'Y');
				if ($metki_f === '')
				{
					$strError .= GetMessage('IBLOCK_ADM_IMP_NO_METKI') . '<br>';
				}

				if ($strError === '')
				{
					$arMetki = array();
					foreach (preg_split("/[\D]/i", $metki_f) as $metka)
					{
						$metka = (int)$metka;
						if ($metka > 0)
						{
							$arMetki[] = $metka;
						}
					}

					if (empty($arMetki))
					{
						$strError .= GetMessage('IBLOCK_ADM_IMP_NO_METKI') . '<br>';
					}

					if ($strError === '')
					{
						$csvFile->SetWidthMap($arMetki);
					}
				}
			}

			if ($strError === '')
			{
				$bFirstHeaderTmp = $csvFile->GetFirstHeader();
				$csvFile->SetFirstHeader(false);
				$arRes = $csvFile->Fetch();
				if (!empty($arRes) && is_array($arRes))
				{
					$arDataFileFields = $arRes;
				}
				else
				{
					$strError .= GetMessage("IBLOCK_ADM_IMP_NO_DATA")."<br>";
				}
				$NUM_FIELDS = count($arDataFileFields);
			}
		}

		if ($strError !== '')
		{
			$STEP = 2;
		}
		//*****************************************************************//

	}
	if ($STEP > 3)
	{
		//*****************************************************************//
		$bFieldsPres = false;
		for ($i = 0; $i < $NUM_FIELDS; $i++)
		{
			if (${"field_".$i} <> '')
			{
				$bFieldsPres = True;
				break;
			}
		}
		if (!$bFieldsPres)
			$strError.= GetMessage("IBLOCK_ADM_IMP_NO_FIELDS")."<br>";

		if ($strError == '')
		{
			$csvFile->SetPos($CUR_FILE_POS);
			if ($CUR_FILE_POS <= 0 && $bFirstHeaderTmp)
			{
				$arRes = $csvFile->Fetch();
			}
			$io = CBXVirtualIo::GetInstance();
			$bs = new CIBlockSection;
			$el = new CIBlockElement;
			$el->CancelWFSetMove();
			$tmpid = md5(uniqid(""));
			$arIBlockProperty = array();
			$bThereIsGroups = False;
			if ($CUR_FILE_POS > 0 && is_set($_SESSION, $CUR_LOAD_SESS_ID))
			{
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "tmpid"))
					$tmpid = $_SESSION[$CUR_LOAD_SESS_ID]["tmpid"];

				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "line_num"))
					$line_num = intval($_SESSION[$CUR_LOAD_SESS_ID]["line_num"]);

				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "correct_lines"))
					$correct_lines = intval($_SESSION[$CUR_LOAD_SESS_ID]["correct_lines"]);

				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "error_lines"))
					$error_lines = intval($_SESSION[$CUR_LOAD_SESS_ID]["error_lines"]);

				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "killed_lines"))
					$killed_lines = intval($_SESSION[$CUR_LOAD_SESS_ID]["killed_lines"]);

				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "arIBlockProperty"))
					$arIBlockProperty = $_SESSION[$CUR_LOAD_SESS_ID]["arIBlockProperty"];

				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "bThereIsGroups"))
					$bThereIsGroups = $_SESSION[$CUR_LOAD_SESS_ID]["bThereIsGroups"];
			}
			// Prepare arrays for elements load
			$bWorkFlow = CModule::IncludeModule('workflow');
			foreach ($arIBlockAvailProdFields as $key => $arField)
			{
				if ($arField["field"] === "XML_ID")
				{
					for ($i = 0; $i < $NUM_FIELDS; $i++)
						if ($key === $GLOBALS["field_".$i])
							$csvFile->AddPrimaryKey($key, $i);
				}
				elseif ($arField["field"] === "NAME")
				{
					for ($i = 0; $i < $NUM_FIELDS; $i++)
						if ($key === $GLOBALS["field_".$i])
							$csvFile->AddPrimaryKey($key, $i);
				}
			}
			$csvFile->tmpid = $tmpid;
			$csvFile->IBLOCK_ID = $IBLOCK_ID;
			$csvFile->NUM_FIELDS = $NUM_FIELDS;
			$csvFile->SetGroupFields($arIBlockAvailGroupFields);
			$arIBlockFileProperty = array();
			// Main loop
			while ($arRes = $csvFile->FetchAssoc())
			{
				$strErrorR = "";
				// Create element
				$arLoadProductArray = array(
					"MODIFIED_BY" => $USER->GetID() ,
					"IBLOCK_ID" => $IBLOCK_ID,
					"TMP_ID" => $tmpid,
					"IBLOCK_SECTION" => $csvFile->MapSections($arRes) ,
				);

				//Preserve existing sections
				if(empty($arLoadProductArray["IBLOCK_SECTION"]))
					unset($arLoadProductArray["IBLOCK_SECTION"]);
				else
					$arLoadProductArray["IBLOCK_SECTION_ID"] = key($arLoadProductArray["IBLOCK_SECTION"]);

				$bThereIsGroups |= !empty($arLoadProductArray["IBLOCK_SECTION"]);
				foreach ($arIBlockAvailProdFields as $key => $arField)
				{
					if (array_key_exists($key, $arRes))
						$arLoadProductArray[$arField["field"]] = $arRes[$key];
				}

				$arFilter = array(
					"IBLOCK_ID" => $IBLOCK_ID,
					"CHECK_PERMISSIONS" => "N",
				);
				if($arLoadProductArray["XML_ID"] <> '')
				{
					$arFilter["=XML_ID"] = $arLoadProductArray["XML_ID"];
				}
				elseif($arLoadProductArray["NAME"] <> '')
				{
					$arFilter["=NAME"] = $arLoadProductArray["NAME"];
				}
				else
				{
					$strErrorR .= GetMessage("IBLOCK_ADM_IMP_LINE_NO")." ".$line_num.". ".GetMessage("IBLOCK_ADM_IMP_NOIDNAME")."<br>";
				}

				if ($strErrorR == '')
				{
					$arLoadProductArray["PROPERTY_VALUES"] = array();
					foreach ($arRes as $key => $value)
					{
						if (strncmp($key, "IP_PROP", 7) == 0)
						{
							$cur_prop_id = (int)mb_substr($key, 7);
							if (!array_key_exists($cur_prop_id, $arIBlockProperty))
							{
								$res1 = CIBlockProperty::GetByID($cur_prop_id, $IBLOCK_ID);
								$arIBlockProperty[$cur_prop_id] = $res1->Fetch();
							}
							if (is_array($arIBlockProperty[$cur_prop_id]))
							{
								if ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"] == "L")
									$value = $csvFile->MapEnum($cur_prop_id, $value);
								elseif ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"] == "N")
									$value = str_replace(",", ".", $value);
								elseif ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"] == "F")
								{
									$value = $csvFile->MapFiles($value);
									$arIBlockFileProperty[$cur_prop_id] = $cur_prop_id;
								}
								$arLoadProductArray["PROPERTY_VALUES"][$cur_prop_id] = $value;
							}
						}
					}
				}

				if ($strErrorR == '')
				{
					if (array_key_exists("PREVIEW_PICTURE", $arLoadProductArray))
					{
						if ($arLoadProductArray["PREVIEW_PICTURE"] <> '')
						{
							if (preg_match("/^(http|https):\\/\\//", $arLoadProductArray["PREVIEW_PICTURE"]))
							{
								$arLoadProductArray["PREVIEW_PICTURE"] = CFile::MakeFileArray($arLoadProductArray["PREVIEW_PICTURE"]);
							}
							else
							{
								$arLoadProductArray["PREVIEW_PICTURE"] = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$arLoadProductArray["PREVIEW_PICTURE"]));
								if (is_array($arLoadProductArray["PREVIEW_PICTURE"]))
									$arLoadProductArray["PREVIEW_PICTURE"]["COPY_FILE"] = "Y";
							}
						}
						if (!is_array($arLoadProductArray["PREVIEW_PICTURE"]))
							unset($arLoadProductArray["PREVIEW_PICTURE"]);
					}

					if (array_key_exists("DETAIL_PICTURE", $arLoadProductArray))
					{
						if ($arLoadProductArray["DETAIL_PICTURE"] <> '')
						{
							if (preg_match("/^(http|https):\\/\\//", $arLoadProductArray["DETAIL_PICTURE"]))
							{
								$arLoadProductArray["DETAIL_PICTURE"] = CFile::MakeFileArray($arLoadProductArray["DETAIL_PICTURE"]);
							}
							else
							{
								$arLoadProductArray["DETAIL_PICTURE"] = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$arLoadProductArray["DETAIL_PICTURE"]));
								if (is_array($arLoadProductArray["DETAIL_PICTURE"]))
									$arLoadProductArray["DETAIL_PICTURE"]["COPY_FILE"] = "Y";
							}
						}
						if (!is_array($arLoadProductArray["DETAIL_PICTURE"]))
							unset($arLoadProductArray["DETAIL_PICTURE"]);
					}

					$res = CIBlockElement::GetList(
						[],
						$arFilter,
						false,
						false,
						[
							'ID',
							'IBLOCK_ID',
							'TMP_ID',
							'PREVIEW_PICTURE',
							'DETAIL_PICTURE',
						]
					);

					if ($arr = $res->Fetch())
					{
						$PRODUCT_ID = $arr["ID"];
						if ($arr["TMP_ID"] !== $tmpid)
						{
							if (is_set($arLoadProductArray, "PREVIEW_PICTURE") && intval($arr["PREVIEW_PICTURE"]) > 0)
							{
								$arLoadProductArray["PREVIEW_PICTURE"]["old_file"] = $arr["PREVIEW_PICTURE"];
							}

							if (is_set($arLoadProductArray, "DETAIL_PICTURE") && intval($arr["DETAIL_PICTURE"]) > 0)
							{
								$arLoadProductArray["DETAIL_PICTURE"]["old_file"] = $arr["DETAIL_PICTURE"];
							}

							if (!empty($arLoadProductArray["PROPERTY_VALUES"]))
							{
								$arPropsLoaded = $arLoadProductArray["PROPERTY_VALUES"];
								$dbPropFiles = CIBlockElement::GetProperty($IBLOCK_ID, $PRODUCT_ID, "sort", "asc", array(
									"CHECK_PERMISSIONS" => "N",
								));
								while ($arPropFile = $dbPropFiles->Fetch())
								{
									if($arPropFile["PROPERTY_TYPE"] == "F" && array_key_exists($arPropFile["ID"], $arPropsLoaded))
										$arLoadProductArray["PROPERTY_VALUES"][$arPropFile["ID"]][$arPropFile["PROPERTY_VALUE_ID"]] = array(
											"del" => "Y",
											"tmp_name" => "",
										);
									elseif(
										$arPropFile["PROPERTY_TYPE"] != "F"
										&& !array_key_exists($arPropFile["ID"], $arPropsLoaded)
										&& (is_array($arPropFile["VALUE"]) || $arPropFile["VALUE"] <> '')
									)
									{
										$arLoadProductArray["PROPERTY_VALUES"][$arPropFile["ID"]][$arPropFile["PROPERTY_VALUE_ID"]] = array(
											"VALUE" => $arPropFile["VALUE"],
											"DESCRIPTION" => $arPropFile["DESCRIPTION"],
										);
									}
								}
							}
							else
							{
								unset($arLoadProductArray["PROPERTY_VALUES"]);
							}

							$res = $el->Update($PRODUCT_ID, $arLoadProductArray, $bWorkFlow, true, $IMAGE_RESIZE === "Y");
						}
						else
						{
							$res = true;
						}
					}
					else
					{
						if ($arLoadProductArray["ACTIVE"] != "N")
							$arLoadProductArray["ACTIVE"] = "Y";

						$PRODUCT_ID = $el->Add($arLoadProductArray, $bWorkFlow, true, $IMAGE_RESIZE === "Y");
						$res = ($PRODUCT_ID > 0);
					}

					if (!$res)
					{
						$strErrorR.= GetMessage("IBLOCK_ADM_IMP_LINE_NO")." ".$line_num.". ".GetMessage("IBLOCK_ADM_IMP_ERROR_LOADING")." ".$el->LAST_ERROR."<br>";
					}
				}

				if ($strErrorR == '')
				{
					$correct_lines++;
				}
				else
				{
					$error_lines++;
					$strError.= $strErrorR;
				}

				if ($max_execution_time > 0 && (getmicrotime() - START_EXEC_TIME) > $max_execution_time)
				{
					$bAllLinesLoaded = false;
					break;
				}
			}
			// delete sections and elements which no in datafile. Properties does not deleted
			if ($bAllLinesLoaded)
			{
				if (is_set($_SESSION, $CUR_LOAD_SESS_ID))
					unset($_SESSION[$CUR_LOAD_SESS_ID]);

				if ($bThereIsGroups)
				{
					if ($outFileAction === 'D')
					{
						$res = CIBlockSection::GetList(
							[],
							[
								'IBLOCK_ID' => $IBLOCK_ID,
								'CHECK_PERMISSIONS' => 'N',
								'!TMP_ID' => $tmpid,
							],
							false,
							[
								'ID',
								'IBLOCK_ID',
							]
						);
						while ($arr = $res->Fetch())
						{
							CIBlockSection::Delete($arr['ID']);
						}
						unset($arr, $res);
					}
					elseif ($outFileAction === 'H')
					{
						$res = CIBlockSection::GetList(
							[],
							[
								'IBLOCK_ID' => $IBLOCK_ID,
								'CHECK_PERMISSIONS' => 'N',
								'!TMP_ID' => $tmpid,
								'ACTIVE' => 'Y',
							],
							false,
							[
								'ID',
								'IBLOCK_ID',
								'NAME',
							]
						);
						while ($arr = $res->Fetch())
						{
							$bs->Update(
								$arr["ID"],
								[
									'NAME' => $arr['NAME'],
									'ACTIVE' => 'N',
								]
							);
						}
						unset($arr, $res);
					}
					if ($inFileAction === 'A')
					{
						$res = CIBlockSection::GetList(
							[],
							[
								'IBLOCK_ID' => $IBLOCK_ID,
								'CHECK_PERMISSIONS' => 'N',
								'TMP_ID' => $tmpid,
								'ACTIVE' => 'N',
							],
							false,
							[
								'ID',
								'IBLOCK_ID',
								'NAME',
							]
						);
						while ($arr = $res->Fetch())
						{
							$bs->Update(
								$arr["ID"],
								[
									'NAME' => $arr['NAME'],
									'ACTIVE' => 'Y',
								]
							);
						}
						unset($arr, $res);
					}
				}

				if ($outFileAction === 'D')
				{
					$res = CIBlockElement::GetList(
						[],
						[
							'IBLOCK_ID' => $IBLOCK_ID,
							'CHECK_PERMISSIONS' => 'N',
							'!=TMP_ID' => $tmpid,
						],
						false,
						false,
						[
							'ID',
							'IBLOCK_ID',
						]
					);
					while ($arr = $res->Fetch())
					{
						CIBlockElement::Delete($arr["ID"]);
						$killed_lines++;
					}
					unset($arr, $res);
				}
				elseif ($outFileAction === 'H')
				{
					$res = CIBlockElement::GetList(
						array(),
						array(
							"IBLOCK_ID" => $IBLOCK_ID,
							"CHECK_PERMISSIONS" => "N",
							"!=TMP_ID" => $tmpid,
							"ACTIVE" => "Y",
						),
						false,
						false,
						array(
							"ID",
							"IBLOCK_ID",
						)
					);
					$elementFields = [
						"ACTIVE" => "N",
					];
					if (Main\Config\Option::get('iblock', 'change_user_by_group_active_modify') === 'Y')
					{
						$elementFields['MODIFIED_BY'] = $USER->GetID();
					}
					while ($arr = $res->Fetch())
					{
						$el->Update($arr["ID"], $elementFields);
						$killed_lines++;
					}
					unset($arr, $res);
				}

				if ($inFileAction === 'A')
				{
					$res = CIBlockElement::GetList(
						[],
						[
							'IBLOCK_ID' => $IBLOCK_ID,
							'CHECK_PERMISSIONS' => 'N',
							'TMP_ID' => $tmpid,
							'ACTIVE' => 'N',
						],
						false,
						false,
						[
							'ID',
							'IBLOCK_ID',
						]
					);
					while ($arr = $res->Fetch())
					{
						$el->Update($arr['ID'], ['ACTIVE' => 'Y']);
					}
					unset($arr, $res);
				}
			}
			else
			{
				if ($CUR_LOAD_SESS_ID == '')
					$CUR_LOAD_SESS_ID = "CL".time();

				$_SESSION[$CUR_LOAD_SESS_ID]["tmpid"] = $tmpid;
				$_SESSION[$CUR_LOAD_SESS_ID]["line_num"] = $line_num;
				$_SESSION[$CUR_LOAD_SESS_ID]["correct_lines"] = $correct_lines;
				$_SESSION[$CUR_LOAD_SESS_ID]["error_lines"] = $error_lines;
				$_SESSION[$CUR_LOAD_SESS_ID]["killed_lines"] = $killed_lines;
				$_SESSION[$CUR_LOAD_SESS_ID]["arIBlockProperty"] = $arIBlockProperty;
				$_SESSION[$CUR_LOAD_SESS_ID]["bThereIsGroups"] = $bThereIsGroups;
				$_SESSION[$CUR_LOAD_SESS_ID]["NUM_FIELDS"] = $NUM_FIELDS;
				$loadScheme = [];
				foreach ($fieldList as $fieldName)
				{
					if (isset($GLOBALS[$fieldName]))
					{
						$loadScheme[$fieldName] = $GLOBALS[$fieldName];
					}
				}
				for ($i = 0; $i < $NUM_FIELDS; $i++)
				{
					$fieldName = 'field_' . $i;
					if (isset($GLOBALS[$fieldName]))
					{
						$loadScheme[$fieldName] = $GLOBALS[$fieldName];
					}
				}
				$_SESSION[$CUR_LOAD_SESS_ID]["LOAD_SCHEME"] = $loadScheme;
				$curFilePos = $csvFile->GetPos();
			}
		}
		if ($strError <> '')
		{
			$strError.= GetMessage("IBLOCK_ADM_IMP_TOTAL_ERRS")." ".$error_lines.".<br>";
			$strError.= GetMessage("IBLOCK_ADM_IMP_TOTAL_COR1")." ".$correct_lines." ".GetMessage("IBLOCK_ADM_IMP_TOTAL_COR2")."<br>";
			$STEP = 3;
		}
		//*****************************************************************//

	}
	//*****************************************************************//

}
/////////////////////////////////////////////////////////////////////
$APPLICATION->SetTitle(GetMessage("IBLOCK_ADM_IMP_PAGE_TITLE").$STEP);
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
CAdminMessage::ShowMessage($strError);
if (!$bAllLinesLoaded)
{
	$strParams = bitrix_sessid_get() . '&CUR_FILE_POS=' . $curFilePos
		. '&CUR_LOAD_SESS_ID=' . urlencode($CUR_LOAD_SESS_ID)
		. '&STEP=4&URL_DATA_FILE=' . urlencode($DATA_FILE_NAME)
		. '&IBLOCK_ID=' . $IBLOCK_ID
		. '&fields_type=' . urlencode($fields_type)
		. '&max_execution_time=' . $max_execution_time
	;
	if ($fields_type === "R")
	{
		$strParams .=
			'&delimiter_r=' . urlencode($delimiter_r)
			. '&delimiter_other_r=' . urlencode($delimiter_other_r)
			. '&first_names_r=' . urlencode($first_names_r)
		;
	}
	else
	{
		$strParams .=
			'&metki_f=' . urlencode($metki_f)
			. '&first_names_f=' . urlencode($first_names_f)
		;
	}

	echo GetMessage("IBLOCK_ADM_IMP_AUTO_REFRESH"); ?>
	<a href="<?= $APPLICATION->GetCurPage(); ?>?lang=<?= LANGUAGE_ID; ?>&<?= $strParams ?>"><?= GetMessage("IBLOCK_ADM_IMP_AUTO_REFRESH_STEP"); ?></a><br>

	<script type="text/javascript">
	function DoNext()
	{
		window.location="<?= $APPLICATION->GetCurPage(); ?>?lang=<?= LANGUAGE_ID; ?>&<?= $strParams ?>";
	}
	setTimeout('DoNext()', 2000);
	</script>
	<?php
}
?>
<form method="POST" action="<?=$APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID; ?>" ENCTYPE="multipart/form-data" name="dataload" id="dataload">
<?php
$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('IBLOCK_ADM_IMP_TAB1') ,
		'ICON' => 'iblock',
		'TITLE' => GetMessage('IBLOCK_ADM_IMP_TAB1_ALT'),
	],
	[
		'DIV' => 'edit2',
		'TAB' => GetMessage('IBLOCK_ADM_IMP_TAB2') ,
		'ICON' => 'iblock',
		'TITLE' => GetMessage('IBLOCK_ADM_IMP_TAB2_ALT'),
	],
	[
		'DIV' => 'edit3',
		'TAB' => GetMessage('IBLOCK_ADM_IMP_TAB3') ,
		'ICON' => 'iblock',
		'TITLE' => GetMessage('IBLOCK_ADM_IMP_TAB3_ALT'),
	],
	[
		'DIV' => 'edit4',
		'TAB' => GetMessage('IBLOCK_ADM_IMP_TAB4') ,
		'ICON' => 'iblock',
		'TITLE' => GetMessage('IBLOCK_ADM_IMP_TAB4_ALT'),
	],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$tabControl->Begin();

$tabControl->BeginNextTab();
if ($STEP == 1)
{
?>
	<tr>
		<td width="40%"><?= GetMessage("IBLOCK_ADM_IMP_DATA_FILE"); ?></td>
		<td width="60%">
			<input type="text" name="URL_DATA_FILE" value="<?= htmlspecialcharsbx($URL_DATA_FILE); ?>" size="30">
			<input type="button" value="<?= GetMessage("IBLOCK_ADM_IMP_OPEN"); ?>" OnClick="BtnClick()">
			<?php
			CAdminFileDialog::ShowScript([
				'event' => 'BtnClick',
				'arResultDest' => [
					'FORM_NAME' => 'dataload',
					'FORM_ELEMENT_NAME' => 'URL_DATA_FILE',
				],
				'arPath' => [
					'SITE' => SITE_ID,
					'PATH' => '/' .Main\Config\Option::get('main', 'upload_dir', 'upload'),
				],
				'select' => 'F', // F - file only, D - folder only
				'operation' => 'O', // O - open, S - save
				'showUploadTab' => true,
				'showAddToMenuTab' => false,
				'fileFilter' => 'csv',
				'allowAllFiles' => true,
				'SaveConfig' => true,
			]);
?>
		</td>
	</tr>

	<tr>
		<td><?= GetMessage("IBLOCK_ADM_IMP_INFOBLOCK"); ?></td>
		<td>
			<?php
			echo GetIBlockDropDownList(
					$IBLOCK_ID,
					'IBLOCK_TYPE_ID',
					'IBLOCK_ID',
					false,
					'class="adm-detail-iblock-types"',
					'class="adm-detail-iblock-list"'
			);
			?>
		</td>
	</tr>
	<?php
}
$tabControl->EndTab();

$tabControl->BeginNextTab();
if ($STEP == 2)
{
	if ($fields_type !== 'R' && $fields_type !== 'F')
	{
		$fields_type = 'R';
	}
?>
	<tr>
		<td width="40%">&nbsp;</td>
		<td width="60%">
			<script type="text/javascript">
			function DeactivateAllExtra()
			{
				document.getElementById("table_r").disabled = true;
				document.getElementById("table_r1").disabled = true;
				document.getElementById("table_r2").disabled = true;
				document.getElementById("table_f").disabled = true;
				document.getElementById("table_f1").disabled = true;
				document.getElementById("table_f2").disabled = true;

				document.dataload.metki_f.disabled = true;
				document.getElementById("first_names_f_Y").disabled = true;

				var i;
				for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
				{
					document.dataload.delimiter_r[i].disabled = true;
				}
				document.dataload.delimiter_other_r.disabled = true;
				document.getElementById("first_names_r_Y").disabled = true;
			}

			function ChangeExtra()
			{
				var i;
				if (document.dataload.fields_type[0].checked)
				{
					document.getElementById("table_r").disabled = false;
					document.getElementById("table_r1").disabled = false;
					document.getElementById("table_r2").disabled = false;
					document.getElementById("table_f").disabled = true;
					document.getElementById("table_f1").disabled = true;
					document.getElementById("table_f2").disabled = true;

					for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
					{
						document.dataload.delimiter_r[i].disabled = false;
					}
					document.dataload.delimiter_other_r.disabled = false;
					document.getElementById("first_names_r_Y").disabled = false;

					document.dataload.metki_f.disabled = true;
					document.getElementById("first_names_f_Y").disabled = true;

					document.dataload.submit_btn.disabled = false;
				}
				else
				{
					if (document.dataload.fields_type[1].checked)
					{
						document.getElementById("table_r").disabled = true;
						document.getElementById("table_r1").disabled = true;
						document.getElementById("table_r2").disabled = true;
						document.getElementById("table_f").disabled = false;
						document.getElementById("table_f1").disabled = false;
						document.getElementById("table_f2").disabled = false;

						for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
						{
							document.dataload.delimiter_r[i].disabled = true;
						}
						document.dataload.delimiter_other_r.disabled = true;
						document.getElementById("first_names_r_Y").disabled = true;

						document.dataload.metki_f.disabled = false;
						document.getElementById("first_names_f_Y").disabled = false;

						document.dataload.submit_btn.disabled = false;
					}
				}
			}
			</script>
			<input type="radio" name="fields_type" id="fields_type_R" value="R"<?=($fields_type === 'R' ? ' checked' : ''); ?> onClick="ChangeExtra()">
			<label for="fields_type_R"><?= GetMessage("IBLOCK_ADM_IMP_RAZDELITEL"); ?></label><br>
			<input type="radio" name="fields_type" id="fields_type_F" value="F"<?=($fields_type === 'F' ? ' checked' : ''); ?> onClick="ChangeExtra()">
			<label for="fields_type_F"><?= GetMessage("IBLOCK_ADM_IMP_FIXED"); ?></label>
		</td>
	</tr>

	<tr id="table_r" class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_RAZDEL1"); ?></td>
	</tr>
	<tr id="table_r1">
		<td class="adm-detail-valign-top"><?= GetMessage("IBLOCK_ADM_IMP_RAZDEL_TYPE"); ?>:</td>
		<td><?php
			foreach ($delimiterList as $value => $message)
			{
				$safeValueId = htmlspecialcharsbx('delimiter_' . $value);
				?>
				<input
					type="radio" name="delimiter_r" id="<?=$safeValueId; ?>"
					value="<?=htmlspecialcharsbx($value);?>"<?=($delimiter_r === $value ? ' checked' : '');?>
				><label for="<?=$safeValueId; ?>"><?= htmlspecialcharsbx($message); ?></label><br>
				<?php
			}
			?>
			<input type="text" name="delimiter_other_r" size="3" value="<?= htmlspecialcharsbx($delimiter_other_r); ?>">
		</td>
	</tr>
	<tr id="table_r2">
		<td><?= GetMessage("IBLOCK_ADM_IMP_FIRST_NAMES"); ?>:</td>
		<td>
			<input type="hidden" name="first_names_r" id="first_names_r_N" value="N">
			<input type="checkbox" name="first_names_r" id="first_names_r_Y" value="Y"<?=($first_names_r !== 'N' ? ' checked' : ''); ?>>
		</td>
	</tr>

	<tr id="table_f" class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_FIX1"); ?></td>
	</tr>
	<tr id="table_f1">
		<td class="adm-detail-valign-top">
			<?= GetMessage("IBLOCK_ADM_IMP_FIX_MET"); ?><br>
			<small><?= GetMessage("IBLOCK_ADM_IMP_FIX_MET_DESCR"); ?></small>:
		</td>
		<td>
			<textarea name="metki_f" rows="7" cols="3"><?= htmlspecialcharsbx($metki_f); ?></textarea>
		</td>
	</tr>
	<tr id="table_f2">
		<td><?= GetMessage("IBLOCK_ADM_IMP_FIRST_NAMES"); ?>:</td>
		<td>
			<input type="hidden" name="first_names_f" id="first_names_f_N" value="N">
			<input type="checkbox" name="first_names_f" id="first_names_f_Y" value="Y"<?=($first_names_f === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_DATA_SAMPLES"); ?></td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<?php
	$sContent = "";
	if ($DATA_FILE_NAME <> '')
	{
		$DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)) , "/");
		$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
		if (
			(mb_strlen($FILE_NAME) > 1)
			&& ($FILE_NAME == "/".$DATA_FILE_NAME)
			&& $APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W"
		)
		{
			$f = $io->GetFile($_SERVER["DOCUMENT_ROOT"].$FILE_NAME);
			$file_id = $f->open("r");
			$lContent = 0;
			while (($line = fgets($file_id)) !== false)
			{
				$sContent .= $line;
				$lContent += mb_strlen($line);
				if ($lContent > 10000)
				{
					break;
				}
			}
			fclose($file_id);
		}
	}
?>
			<textarea name="data" rows="10" cols="80" style="width:100%"><?= htmlspecialcharsbx($sContent); ?></textarea>
		</td>
	</tr>
	<?php
}
$tabControl->EndTab();

$tabControl->BeginNextTab();
if ($STEP == 3)
{
?>
	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_FIELDS_SOOT"); ?></td>
	</tr>
	<?php
	$arAvailFields = [];
	foreach ($arIBlockAvailProdFields as $field_name => $arField)
	{
		$arAvailFields[] = [
			"value" => $field_name,
			"name" => $arField["name"],
			"code" => "",
		];
	}

	$properties = CIBlockProperty::GetList(
		[
			"sort" => "asc",
			"name" => "asc",
		],
		[
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $IBLOCK_ID,
			"CHECK_PERMISSIONS" => "N",
		]
	);
	while ($prop_fields = $properties->Fetch())
	{
		$arAvailFields[] = [
			"value" => "IP_PROP".$prop_fields["ID"],
			"name" => GetMessage("IBLOCK_ADM_IMP_FI_PROPS")." \"".$prop_fields["NAME"]."\"",
			"code" => "IP_PROP_".$prop_fields["CODE"],
		];
	}

	for ($k = 0; $k < $NUM_CATALOG_LEVELS; $k++)
	{
		foreach ($arIBlockAvailGroupFields as $field_name => $arField)
		{
			$arAvailFields[] = [
				"value" => $field_name.$k,
				"name" => GetMessage("IBLOCK_ADM_IMP_FI_GROUP_LEV")." ".($k + 1).": ".$arField["name"],
				"code" => "",
			];
		}
	}

	foreach ($arDataFileFields as $i => $field)
	{
		$fieldName = 'field_' . $i;
?>
		<tr>
			<td width="40%">
				<b><?= GetMessage("IBLOCK_ADM_IMP_FIELD"); ?> <?= ($i + 1) ?></b> (<?= htmlspecialcharsbx($field); ?>):
			</td>
			<td width="60%">
				<select name="<?= $fieldName; ?>">
					<option value=""> - </option>
					<?php
		foreach ($arAvailFields as $ar)
		{
			$bSelected =
				isset(${$fieldName})
				&& ${$fieldName} == $ar["value"]
			;
			if (!$bSelected && !isset(${$fieldName}))
			{
				$bSelected = $ar["value"] == $field;
			}

			if (!$bSelected && !isset(${$fieldName}))
			{
				$bSelected = $ar["code"] == $field;
			}
			?>
						<option value="<?= htmlspecialcharsbx($ar["value"]); ?>"<?= ($bSelected ? ' selected' : ''); ?>><?= htmlspecialcharsbx($ar["name"]); ?></option>
			<?php
		}
		?>
				</select>
			</td>
		</tr>
		<?php
	}
?>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_ADDIT_SETTINGS"); ?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?= GetMessage("IBLOCK_ADM_IMP_IMG_PATH"); ?>:</td>
		<td>
			<input type="text" name="PATH2IMAGE_FILES" size="40" value="<?= htmlspecialcharsbx($PATH2IMAGE_FILES); ?>"><br>
			<small><?= GetMessage("IBLOCK_ADM_IMP_IMG_PATH_DESCR"); ?><br></small>
		</td>
	</tr>
	<tr>
		<td><label for="IMAGE_RESIZE"><?= GetMessage("IBLOCK_ADM_IMP_IMG_RESIZE"); ?>:</label></td>
		<td>
			<input type="checkbox" name="IMAGE_RESIZE" id="IMAGE_RESIZE" value="Y" <?= ($IMAGE_RESIZE === "Y" ? "checked" : ""); ?>>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?= GetMessage("IBLOCK_ADM_IMP_PROP_PATH"); ?>:</td>
		<td>
			<input type="text" name="PATH2PROP_FILES" size="40" value="<?= htmlspecialcharsbx($PATH2PROP_FILES); ?>"><br>
			<small><?= GetMessage("IBLOCK_ADM_IMP_PROP_PATH_DESCR"); ?><br></small>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?= GetMessage("IBLOCK_ADM_IMP_OUTFILE"); ?>:</td>
		<td>
			<input type="radio" id="outFileAction_H" name="outFileAction" value="H"<?=($outFileAction === 'H' ? ' checked' : ''); ?>>
			<label for="outFileAction_H"><?= GetMessage("IBLOCK_ADM_IMP_OF_DEACT"); ?></label><br>
			<input type="radio" id="outFileAction_D" name="outFileAction" value="D"<?=($outFileAction === 'D' ? ' checked' : ''); ?>>
			<label for="outFileAction_D"><?= GetMessage("IBLOCK_ADM_IMP_OF_DEL"); ?></label><br>
			<input type="radio" id="outFileAction_F" name="outFileAction" value="F"<?=($outFileAction === 'F' ? ' checked' : ''); ?>>
			<label for="outFileAction_F"><?= GetMessage("IBLOCK_ADM_IMP_OF_KEEP"); ?></label>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?= GetMessage("IBLOCK_ADM_IMP_INACTIVE_PRODS"); ?>:</td>
		<td>
			<input type="radio" id="inFileAction_F" name="inFileAction" value="F"<?=($inFileAction === 'F' ? ' checked' : ''); ?>>
			<label for="inFileAction_F"><?= GetMessage("IBLOCK_ADM_IMP_KEEP_AS_IS"); ?></label><br>
			<input type="radio" id="inFileAction_A" name="inFileAction" value="A"<?=($inFileAction === 'A' ? ' checked' : ''); ?>>
			<label for="inFileAction_A"><?= GetMessage("IBLOCK_ADM_IMP_ACTIVATE_PROD"); ?></label>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?= GetMessage("IBLOCK_ADM_IMP_AUTO_STEP_TIME"); ?>:</td>
		<td align="left">
			<input type="text" name="max_execution_time" size="6" value="<?= htmlspecialcharsbx($max_execution_time); ?>"><br>
			<small><?= GetMessage("IBLOCK_ADM_IMP_AUTO_STEP_TIME_NOTE"); ?><br></small>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_DATA_SAMPLES"); ?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<?php
	$sContent = '';
	if ($DATA_FILE_NAME <> '')
	{
		$DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)) , "/");
		$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
		if (
			(mb_strlen($FILE_NAME) > 1)
			&& ($FILE_NAME === '/' . $DATA_FILE_NAME)
			&& $APPLICATION->GetFileAccessPermission($FILE_NAME) >= 'W'
		)
		{
			$f = $io->GetFile($_SERVER['DOCUMENT_ROOT'] . $FILE_NAME);
			$file_id = $f->open('r');
			$lContent = 0;
			while (($line = fgets($file_id)) !== false)
			{
				$sContent .= $line;
				$lContent += mb_strlen($line);
				if ($lContent > 10000)
				{
					break;
				}
			}
			fclose($file_id);
		}
	}
?>
			<textarea name="data" rows="10" cols="80" style="width:100%"><?= htmlspecialcharsbx($sContent); ?></textarea>
		</td>
	</tr>
	<?php
}
$tabControl->EndTab();

$tabControl->BeginNextTab();
if ($STEP == 4)
{
?>
	<tr>
		<td>
		<?php
		switch ($outFileAction)
		{
			case 'D':
				$killedReport = GetMessage('IBLOCK_ADM_IMP_SU_KILLED') . ' <b>' . $killed_lines . '</b>';
				break;
			case 'H':
				$killedReport = GetMessage('IBLOCK_ADM_IMP_SU_HIDED') . ' <b>' . $killed_lines . '</b>';
				break;
			default:
				$killedReport = '';
				break;
		}

		CAdminMessage::ShowMessage([
			'TYPE' => 'PROGRESS',
			'MESSAGE' =>
				$bAllLinesLoaded
					? GetMessage('IBLOCK_ADM_IMP_SUCCESS')
					: GetMessage('IBLOCK_ADM_IMP_AUTO_REFRESH_CONTINUE')
			,
			'DETAILS' =>
				GetMessage('IBLOCK_ADM_IMP_SU_ALL') .' <b>' . $line_num . '</b><br>'
				. GetMessage('IBLOCK_ADM_IMP_SU_CORR') . ' <b>' . $correct_lines . '</b><br>'
				. GetMessage('IBLOCK_ADM_IMP_SU_ER') . ' <b>' . $error_lines . '</b><br>'
				. $killedReport
			,
			'HTML' => true,
		]);
		?>
		</td>
	</tr>
<?php
}
$tabControl->EndTab();

$tabControl->Buttons();

if ($STEP < 4): ?>
	<input type="hidden" name="STEP" value="<?= ($STEP + 1); ?>">
	<?php
	echo bitrix_sessid_post();
	if ($STEP > 1): ?>
		<input type="hidden" name="URL_DATA_FILE" value="<?= htmlspecialcharsbx($DATA_FILE_NAME); ?>">
		<input type="hidden" name="IBLOCK_ID" value="<?= $IBLOCK_ID ?>">
	<?php
	endif;

	if ($STEP <> 2): ?>
		<input type="hidden" name="fields_type" value="<?= htmlspecialcharsbx($fields_type); ?>">
		<input type="hidden" name="delimiter_r" value="<?= htmlspecialcharsbx($delimiter_r); ?>">
		<input type="hidden" name="delimiter_other_r" value="<?= htmlspecialcharsbx($delimiter_other_r); ?>">
		<input type="hidden" name="first_names_r" value="<?= htmlspecialcharsbx($first_names_r); ?>">
		<input type="hidden" name="metki_f" value="<?= htmlspecialcharsbx($metki_f); ?>">
		<input type="hidden" name="first_names_f" value="<?= htmlspecialcharsbx($first_names_f); ?>">
	<?php
	endif;

	if ($STEP <> 3):
		foreach ($_POST as $name => $value):
			if (preg_match("/^field_(\\d+)$/", $name)):
			?>
				<input type="hidden" name="<?= $name ?>" value="<?= htmlspecialcharsbx($value); ?>">
			<?php
			endif;
		endforeach;
	?>
		<input type="hidden" name="PATH2IMAGE_FILES" value="<?= htmlspecialcharsbx($PATH2IMAGE_FILES); ?>">
		<input type="hidden" name="IMAGE_RESIZE" value="<?= htmlspecialcharsbx($IMAGE_RESIZE); ?>">
		<input type="hidden" name="PATH2PROP_FILES" value="<?= htmlspecialcharsbx($PATH2PROP_FILES); ?>">
		<input type="hidden" name="outFileAction" value="<?= htmlspecialcharsbx($outFileAction); ?>">
		<input type="hidden" name="inFileAction" value="<?= htmlspecialcharsbx($inFileAction); ?>">
		<input type="hidden" name="max_execution_time" value="<?= htmlspecialcharsbx($max_execution_time); ?>">
	<?php
	endif;

	if ($STEP > 1):
	?>
	<input type="submit" name="backButton" value="&lt;&lt; <?= GetMessage("IBLOCK_ADM_IMP_BACK"); ?>">
	<?php
	endif;
?>
	<input type="submit" value="<?= ($STEP === 3
		? GetMessage("IBLOCK_ADM_IMP_NEXT_STEP_F")
		: GetMessage("IBLOCK_ADM_IMP_NEXT_STEP")
	); ?> &gt;&gt;" name="submit_btn" class="adm-btn-save">
	<?php
	if ($STEP === 2)
	{
		?>
		<script type="text/javascript">
			DeactivateAllExtra();
			ChangeExtra();
		</script>
		<?php
	}
?>
<?php
	else: ?>
	<input type="submit" name="backButton2" value="&lt;&lt; <?= GetMessage("IBLOCK_ADM_IMP_2_1_STEP"); ?>" class="adm-btn-save">
<?php
	endif;

$tabControl->End();
?>
</form>
<script type="text/javascript">
<?php
if ($STEP < 2):
?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
tabControl.DisableTab("edit4");
<?php
elseif ($STEP === 2):
?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");
tabControl.DisableTab("edit4");
<?php
elseif ($STEP === 3):
?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit4");
<?php
elseif ($STEP > 3):
?>
tabControl.SelectTab("edit4");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
<?php
endif;
?>
</script>
<?php
require ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
