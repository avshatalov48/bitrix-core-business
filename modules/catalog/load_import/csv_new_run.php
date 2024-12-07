<?php
//<title>CSV (new)</title>
/** @global int $line_num */
/** @global int $correct_lines */
/** @global int $error_lines */
/** @global string $tmpid */

/** @global string $URL_DATA_FILE */
/** @global int $IBLOCK_ID */
/** @global array $arIBlock */
/** @global string $fields_type */
/** @global string $delimiter_r */
/** @global string $delimiter_other_r */
/** @global string $metki_f */
/** @global string $first_names_r */
/** @global string $first_names_f */
/** @global int $CUR_FILE_POS */
/** @global string $USE_TRANSLIT */
/** @global string $TRANSLIT_LANG */
/** @global string $USE_UPDATE_TRANSLIT */
/** @global string $PATH2IMAGE_FILES */
/** @global string $outFileAction */
/** @global string $inFileAction */

use Bitrix\Main,
	Bitrix\Catalog,
	Bitrix\Iblock;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/import_setup_templ.php');
$startImportExecTime = microtime(true);

global $USER;
global $APPLICATION;
$bTmpUserCreated = false;
if (!CCatalog::IsUserExists())
{
	$bTmpUserCreated = true;
	if (isset($USER))
		$USER_TMP = $USER;
	$USER = new CUser();
}

$strImportErrorMessage = "";
$strImportOKMessage = "";

global
	$arCatalogAvailProdFields,
	$defCatalogAvailProdFields,
	$arCatalogAvailPriceFields,
	$defCatalogAvailPriceFields,
	$arCatalogAvailValueFields,
	$defCatalogAvailValueFields,
	$arCatalogAvailQuantityFields,
	$defCatalogAvailQuantityFields,
	$arCatalogAvailGroupFields,
	$defCatalogAvailGroupFields,
	$defCatalogAvailCurrencies;

if (!isset($arCatalogAvailProdFields))
	$arCatalogAvailProdFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_ELEMENT);
if (!isset($arCatalogAvailPriceFields))
	$arCatalogAvailPriceFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_CATALOG);
if (!isset($arCatalogAvailValueFields))
	$arCatalogAvailValueFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE);
if (!isset($arCatalogAvailQuantityFields))
	$arCatalogAvailQuantityFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE_EXT);
if (!isset($arCatalogAvailGroupFields))
	$arCatalogAvailGroupFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_SECTION);

if (!isset($defCatalogAvailProdFields))
	$defCatalogAvailProdFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_ELEMENT);
if (!isset($defCatalogAvailPriceFields))
	$defCatalogAvailPriceFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CATALOG);
if (!isset($defCatalogAvailValueFields))
	$defCatalogAvailValueFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE);
if (!isset($defCatalogAvailQuantityFields))
	$defCatalogAvailQuantityFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE_EXT);
if (!isset($defCatalogAvailGroupFields))
	$defCatalogAvailGroupFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_SECTION);
if (!isset($defCatalogAvailCurrencies))
	$defCatalogAvailCurrencies = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CURRENCY);

$NUM_CATALOG_LEVELS = intval(COption::GetOptionString("catalog", "num_catalog_levels"));

$max_execution_time = intval($max_execution_time);
if ($max_execution_time <= 0)
	$max_execution_time = 0;
if (defined('BX_CAT_CRON') && true == BX_CAT_CRON)
	$max_execution_time = 0;

if (defined("CATALOG_LOAD_NO_STEP") && CATALOG_LOAD_NO_STEP)
	$max_execution_time = 0;

$separateSku = (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';

$bAllLinesLoaded = true;

$io = CBXVirtualIo::GetInstance();

if (!function_exists('CSVCheckTimeout'))
{
	function CSVCheckTimeout($max_execution_time)
	{
		return ($max_execution_time <= 0) || (microtime(true)-START_EXEC_TIME <= (2*$max_execution_time/3));
	}
}

$DATA_FILE_NAME = "";

if ($URL_DATA_FILE <> '')
{
	$URL_DATA_FILE = Rel2Abs("/", $URL_DATA_FILE);
	if (file_exists($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE))
		$DATA_FILE_NAME = $URL_DATA_FILE;
}

if ($DATA_FILE_NAME == '')
	$strImportErrorMessage .= GetMessage("CATI_NO_DATA_FILE")."<br>";

$IBLOCK_ID = intval($IBLOCK_ID);
if ($IBLOCK_ID <= 0)
{
	$strImportErrorMessage .= GetMessage("CATI_NO_IBLOCK")."<br>";
}
else
{
	$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
	if (false === $arIBlock)
	{
		$strImportErrorMessage .= GetMessage("CATI_NO_IBLOCK")."<br>";
	}
}

if ('' == $strImportErrorMessage)
{
	$bWorkflow = CModule::IncludeModule("workflow") && ($arIBlock["WORKFLOW"] != "N");

	$bIBlockIsCatalog = false;
	$arSku = false;
	$rsCatalogs = CCatalog::GetList(
		array(),
		array('IBLOCK_ID' => $IBLOCK_ID),
		false,
		false,
		array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID')
	);
	if ($arCatalog = $rsCatalogs->Fetch())
	{
		$bIBlockIsCatalog = true;
		$arCatalog['IBLOCK_ID'] = (int)$arCatalog['IBLOCK_ID'];
		$arCatalog['PRODUCT_IBLOCK_ID'] = (int)$arCatalog['PRODUCT_IBLOCK_ID'];
		$arCatalog['SKU_PROPERTY_ID'] = (int)$arCatalog['SKU_PROPERTY_ID'];
		if (0 < $arCatalog['PRODUCT_IBLOCK_ID'] && 0 < $arCatalog['SKU_PROPERTY_ID'])
		{
			$arSku = $arCatalog;
		}
	}

	$csvFile = new CCSVData();
	$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

	if ($fields_type!="F" && $fields_type!="R")
		$strImportErrorMessage .= GetMessage("CATI_NO_FILE_FORMAT")."<br>";
}

if ('' == $strImportErrorMessage)
{
	$arDataFileFields = array();
	$fields_type = (($fields_type=="F") ? "F" : "R" );

	$csvFile->SetFieldsType($fields_type);

	if ($fields_type == "R")
	{
		$first_names_r = (($first_names_r=="Y") ? "Y" : "N" );
		$csvFile->SetFirstHeader(($first_names_r=="Y") ? true : false);

		$delimiter_r_char = "";
		switch ($delimiter_r)
		{
			case "TAB":
				$delimiter_r_char = "\t";
				break;
			case "ZPT":
				$delimiter_r_char = ",";
				break;
			case "SPS":
				$delimiter_r_char = " ";
				break;
			case "OTR":
				$delimiter_r_char = mb_substr($delimiter_other_r, 0, 1);
				break;
			case "TZP":
				$delimiter_r_char = ";";
				break;
		}

		if (mb_strlen($delimiter_r_char) != 1)
			$strImportErrorMessage .= GetMessage("CATI_NO_DELIMITER")."<br>";

		if ('' == $strImportErrorMessage)
			$csvFile->SetDelimiter($delimiter_r_char);
	}
	else
	{
		$first_names_f = (($first_names_f=="Y") ? "Y" : "N" );
		$csvFile->SetFirstHeader(($first_names_f=="Y") ? true : false);

		if ($metki_f == '')
			$strImportErrorMessage .= GetMessage("CATI_NO_METKI")."<br>";

		if ('' == $strImportErrorMessage)
		{
			$arMetkiTmp = preg_split("/[\D]/i", $metki_f);

			$arMetki = array();
			for ($i = 0, $intCount = count($arMetkiTmp); $i < $intCount; $i++)
			{
				if (intval($arMetkiTmp[$i]) > 0)
				{
					$arMetki[] = intval($arMetkiTmp[$i]);
				}
			}

			if (!is_array($arMetki) || count($arMetki)<1)
				$strImportErrorMessage .= GetMessage("CATI_NO_METKI")."<br>";

			if ('' == $strImportErrorMessage)
				$csvFile->SetWidthMap($arMetki);
		}
	}

	if ('' == $strImportErrorMessage)
	{
		$bFirstHeaderTmp = $csvFile->GetFirstHeader();
		$csvFile->SetFirstHeader(false);
		if ($arRes = $csvFile->Fetch())
		{
			for ($i = 0, $intCount = count($arRes); $i < $intCount; $i++)
			{
				$arDataFileFields[$i] = $arRes[$i];
			}
		}
		else
		{
			$strImportErrorMessage .= GetMessage("CATI_NO_DATA")."<br>";
		}
		global $NUM_FIELDS;
		$NUM_FIELDS = count($arDataFileFields);
	}
}

if ('' == $strImportErrorMessage)
{
	$bFieldsPres = false;
	for ($i = 0; $i < $NUM_FIELDS; $i++)
	{
		if (${"field_".$i} <> '')
		{
			$bFieldsPres = true;
			break;
		}
	}
	if (!$bFieldsPres)
		$strImportErrorMessage .= GetMessage("CATI_NO_FIELDS")."<br>";
}

if ('' == $strImportErrorMessage)
{
	$USE_TRANSLIT = (isset($USE_TRANSLIT) && 'Y' == $USE_TRANSLIT ? 'Y' : 'N');
	if ('Y' == $USE_TRANSLIT)
	{
		$boolOutTranslit = false;
		if (isset($arIBlock['FIELDS']['CODE']['DEFAULT_VALUE']))
		{
			if ('Y' == $arIBlock['FIELDS']['CODE']['DEFAULT_VALUE']['TRANSLITERATION']
				&& 'Y' == $arIBlock['FIELDS']['CODE']['DEFAULT_VALUE']['USE_GOOGLE'])
			{
				$boolOutTranslit = true;
			}
		}
		if (isset($arIBlock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']))
		{
			if ('Y' == $arIBlock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']['TRANSLITERATION']
				&& 'Y' == $arIBlock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']['USE_GOOGLE'])
			{
				$boolOutTranslit = true;
			}
		}
		if ($boolOutTranslit)
		{
			$USE_TRANSLIT = 'N';
			$strImportErrorMessage .= GetMessage("CATI_USE_CODE_TRANSLIT_OUT")."<br>";
		}
	}
	if ('Y' == $USE_TRANSLIT)
	{
		$TRANSLIT_LANG = (isset($TRANSLIT_LANG) ? strval($TRANSLIT_LANG) : '');
		if (!empty($TRANSLIT_LANG))
		{
			$rsTransLangs = CLanguage::GetByID($TRANSLIT_LANG);
			if (!($arTransLang = $rsTransLangs->Fetch()))
			{
				$TRANSLIT_LANG = '';
			}
		}
		if (empty($TRANSLIT_LANG))
		{
			$USE_TRANSLIT = 'N';
			$strImportErrorMessage .= GetMessage("CATI_CODE_TRANSLIT_LANG_ERR")."<br>";
		}
	}
	$updateTranslit = false;
	if ($USE_TRANSLIT == 'Y')
	{
		$updateTranslit = true;
		if (isset($USE_UPDATE_TRANSLIT) && $USE_UPDATE_TRANSLIT == 'N')
			$updateTranslit = false;
	}
}

$IMAGE_RESIZE = (isset($IMAGE_RESIZE) && 'Y' == $IMAGE_RESIZE ? 'Y' : 'N');
$CLEAR_EMPTY_PRICE = (isset($CLEAR_EMPTY_PRICE) && 'Y' == $CLEAR_EMPTY_PRICE ? 'Y' : 'N');
$CML2_LINK_IS_XML = (isset($CML2_LINK_IS_XML) && 'Y' == $CML2_LINK_IS_XML ? 'Y' : 'N');
if (empty($arSku))
	$CML2_LINK_IS_XML = 'N';

if ('' == $strImportErrorMessage)
{
	$currentUserID = $USER->GetID();

	$boolUseStoreControl = Catalog\Config\State::isUsedInventoryManagement();
	$arDisableFields = array(
		'CP_QUANTITY' => true,
		'CP_PURCHASING_PRICE' => true,
		'CP_PURCHASING_CURRENCY' => true,
	);

	$arProductCache = array();
	$arPropertyListCache = array();
	$arSectionCache = array();
	$arElementCache = array();

	$productPriceCache = array();
	$processedProductPriceCache = array();

	$csvFile->SetPos($CUR_FILE_POS);
	$arRes = $csvFile->Fetch();
	if ($CUR_FILE_POS<=0 && $bFirstHeaderTmp)
	{
		$arRes = $csvFile->Fetch();
	}

	$bs = new CIBlockSection();
	$el = new CIBlockElement();
	$bWasIterations = false;

	$defaultMeasureId = null;
	$measure = CCatalogMeasure::getDefaultMeasure();
	if (!empty($measure))
	{
		if ($measure['ID'] > 0)
			$defaultMeasureId = $measure['ID'];
	}
	unset($measure);

	Iblock\PropertyIndex\Manager::enableDeferredIndexing();
	Catalog\Product\Sku::enableDeferredCalculation();
	if ($arRes)
	{
		$bWasIterations = true;
		if ($bFirstLoadStep)
		{
			$tmpid = md5(uniqid(""));
			$line_num = 0;
			$correct_lines = 0;
			$error_lines = 0;
			$killed_lines = 0;

			$arIBlockProperty = array();
			$arIBlockPropertyValue = array();
			$multiplePropertyValuesCheck = array();
			$bThereIsGroups = false;
			$bDeactivationStarted = false;
			$arProductGroups = array();
			$currentProductSection = [];
			$bUpdatePrice = 'N';
		}

		$boolTranslitElement = false;

		$boolTranslitSection = false;
		$arTranslitElement = array();
		$arTranslitSection = array();
		if ('Y' == $USE_TRANSLIT)
		{
			if (isset($arIBlock['FIELDS']['CODE']['DEFAULT_VALUE']))
			{
				$arTransSettings = $arIBlock['FIELDS']['CODE']['DEFAULT_VALUE'];
				$boolTranslitElement = ($arTransSettings['TRANSLITERATION'] == 'Y');
				$arTranslitElement = array(
					"max_len" => $arTransSettings['TRANS_LEN'],
					"change_case" => $arTransSettings['TRANS_CASE'],
					"replace_space" => $arTransSettings['TRANS_SPACE'],
					"replace_other" => $arTransSettings['TRANS_OTHER'],
					"delete_repeat_replace" => ($arTransSettings['TRANS_EAT'] == 'Y'),
				);
			}
			if (isset($arIBlock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE']))
			{
				$arTransSettings = $arIBlock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE'];
				$boolTranslitSection = ($arTransSettings['TRANSLITERATION'] == 'Y');
				$arTranslitSection = array(
					"max_len" => $arTransSettings['TRANS_LEN'],
					"change_case" => $arTransSettings['TRANS_CASE'],
					"replace_space" => $arTransSettings['TRANS_SPACE'],
					"replace_other" => $arTransSettings['TRANS_OTHER'],
					"delete_repeat_replace" => ($arTransSettings['TRANS_EAT'] == 'Y'),
				);
			}
		}

		// Prepare load arrays
		$strAvailGroupFields = COption::GetOptionString("catalog", "allowed_group_fields", $defCatalogAvailGroupFields);
		$arAvailGroupFields = explode(",", $strAvailGroupFields);
		$arAvailGroupFields_names = array();
		for ($i = 0, $intCount = count($arAvailGroupFields), $intCount2 = count($arCatalogAvailGroupFields); $i < $intCount; $i++)
		{
			for ($j = 0; $j < $intCount2; $j++)
			{
				if ($arCatalogAvailGroupFields[$j]["value"]==$arAvailGroupFields[$i])
				{
					$arAvailGroupFields_names[$arAvailGroupFields[$i]] = array(
						"field" => $arCatalogAvailGroupFields[$j]["field"],
						"important" => $arCatalogAvailGroupFields[$j]["important"]
						);
					break;
				}
			}
		}

		// Prepare load arrays
		$strAvailProdFields = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailProdFields);
		$arAvailProdFields = explode(",", $strAvailProdFields);
		$arAvailProdFields_names = array();
		for ($i = 0, $intCount = count($arAvailProdFields), $intCount2 = count($arCatalogAvailProdFields); $i < $intCount; $i++)
		{
			for ($j = 0; $j < $intCount2; $j++)
			{
				if ($arCatalogAvailProdFields[$j]["value"]==$arAvailProdFields[$i])
				{
					$arAvailProdFields_names[$arAvailProdFields[$i]] = array(
						"field" => $arCatalogAvailProdFields[$j]["field"],
						"important" => $arCatalogAvailProdFields[$j]["important"]
						);
					break;
				}
			}
		}

		// Prepare load arrays
		$strAvailPriceFields = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailPriceFields);
		$arAvailPriceFields = explode(",", $strAvailPriceFields);
		$arAvailPriceFields_names = array();
		for ($i = 0, $intCount = count($arAvailPriceFields), $intCount2 = count($arCatalogAvailPriceFields); $i < $intCount; $i++)
		{
			if ($boolUseStoreControl && array_key_exists($arAvailPriceFields[$i], $arDisableFields))
				continue;

			for ($j = 0; $j < $intCount2; $j++)
			{
				if ($arCatalogAvailPriceFields[$j]["value"]==$arAvailPriceFields[$i])
				{
					$arAvailPriceFields_names[$arAvailPriceFields[$i]] = array(
						"field" => $arCatalogAvailPriceFields[$j]["field"],
						"important" => $arCatalogAvailPriceFields[$j]["important"]
					);
					break;
				}
			}
		}

		// Prepare load arrays
		$strAvailValueFields = COption::GetOptionString("catalog", "allowed_price_fields", $defCatalogAvailValueFields);
		$arAvailValueFields = explode(",", $strAvailValueFields);
		$arAvailValueFields_names = array();
		for ($i = 0, $intCount = count($arAvailValueFields), $intCount2 = count($arCatalogAvailValueFields); $i < $intCount; $i++)
		{
			for ($j = 0; $j < $intCount2; $j++)
			{
				if ($arCatalogAvailValueFields[$j]["value"] == $arAvailValueFields[$i])
				{
					$arAvailValueFields_names[$arAvailValueFields[$i]] = array(
						"field_name_size" =>  $arCatalogAvailValueFields[$j]["value_size"],
						"field" => $arCatalogAvailValueFields[$j]["field"],
						"important" => $arCatalogAvailValueFields[$j]["important"]
					);
					break;
				}
			}
		}

		$previousProductId = false;
		$updateFacet = false;
		$newProducts = array();
		CIBlock::disableClearTagCache();
		// main
		do
		{
			$strErrorR = "";
			$line_num++;


			$arGroupsTmp = array();

			for ($i = 0; $i < $NUM_CATALOG_LEVELS; $i++)
			{
				$arGroupsTmp1 = array();
				foreach ($arAvailGroupFields_names as $key => $value)
				{
					$ind = -1;
					for ($i_tmp = 0; $i_tmp < $NUM_FIELDS; $i_tmp++)
					{
						if (${"field_".$i_tmp} == $key.$i)
						{
							$ind = $i_tmp;
							break;
						}
					}

					if ($ind>-1)
					{
						$arGroupsTmp1[$value["field"]] = trim($arRes[$ind]);
						$bThereIsGroups = true;
					}
				}
				$arGroupsTmp[] = $arGroupsTmp1;
			}

			$i = count($arGroupsTmp)-1;
			while ($i>=0)
			{
				foreach ($arAvailGroupFields_names as $key => $value)
				{
					if ($value["important"]=="Y" && isset($arGroupsTmp[$i][$value["field"]]) && '' !== $arGroupsTmp[$i][$value["field"]])
					{
						break 2;
					}
				}
				unset($arGroupsTmp[$i]);
				$i--;
			}

			for ($i = 0, $intCount = count($arGroupsTmp); $i < $intCount; $i++)
			{
				if (isset($arGroupsTmp[$i]['NAME']) && '' === $arGroupsTmp[$i]["NAME"])
				{
					$arGroupsTmp[$i]["NAME"] = GetMessage("CATI_NOMAME");
				}
				$arGroupsTmp[$i]["TMP_ID"] = $tmpid;
			}

			$LAST_GROUP_CODE = 0;
			$sectionKey = '';
			for ($i = 0, $intCount = count($arGroupsTmp); $i < $intCount; $i++)
			{
				$sectionFilter = '';
				$arFilter = array("IBLOCK_ID" => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N');
				if (isset($arGroupsTmp[$i]["XML_ID"]) && '' !== $arGroupsTmp[$i]["XML_ID"])
				{
					$arFilter["=XML_ID"] = $arGroupsTmp[$i]["XML_ID"];
					$sectionFilter = 'XML'.md5($arGroupsTmp[$i]["XML_ID"]);
				}
				elseif (isset($arGroupsTmp[$i]["NAME"]) && '' !== $arGroupsTmp[$i]["NAME"])
				{
					$arFilter["=NAME"] = $arGroupsTmp[$i]["NAME"];
					$sectionFilter = 'NAME'.md5($arGroupsTmp[$i]["NAME"]);
				}

				if ($LAST_GROUP_CODE>0)
				{
					$arFilter["SECTION_ID"] = $LAST_GROUP_CODE;
					$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = $LAST_GROUP_CODE;
				}
				else
				{
					$arFilter["SECTION_ID"] = 0;
					$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = false;
				}
				$sectionKey .= $LAST_GROUP_CODE.':';
				$sectionIndex = $sectionKey.$sectionFilter;
				if (!isset($arSectionCache[$sectionIndex]))
				{
					if (isset($arGroupsTmp[$i]["PICTURE"]))
					{
						$arGroupsTmp[$i]["PICTURE"] = trim($arGroupsTmp[$i]["PICTURE"]);
						$bFilePres = false;
						if ('' !== $arGroupsTmp[$i]["PICTURE"])
						{
							if (preg_match("/^(ftp|ftps|http|https):\\/\\//", $arGroupsTmp[$i]["PICTURE"]))
							{
								$arGroupsTmp[$i]["PICTURE"] = CFile::MakeFileArray($arGroupsTmp[$i]["PICTURE"]);
							}
							else
							{
								$arGroupsTmp[$i]["PICTURE"] = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$arGroupsTmp[$i]["PICTURE"]));
								if (!empty($arGroupsTmp[$i]["PICTURE"]) && is_array($arGroupsTmp[$i]["PICTURE"]))
									$arGroupsTmp[$i]["PICTURE"]['COPY_FILE'] = 'Y';
							}
							$bFilePres = (!empty($arGroupsTmp[$i]["PICTURE"])
								&& isset($arGroupsTmp[$i]["PICTURE"]["tmp_name"])
								&& '' !== $arGroupsTmp[$i]["PICTURE"]["tmp_name"]
							);
						}
						if (!$bFilePres)
							unset($arGroupsTmp[$i]["PICTURE"]);
					}
					if (isset($arGroupsTmp[$i]["DETAIL_PICTURE"]))
					{
						$arGroupsTmp[$i]["DETAIL_PICTURE"] = trim($arGroupsTmp[$i]["DETAIL_PICTURE"]);
						$bFilePres = false;
						if ('' !== $arGroupsTmp[$i]["DETAIL_PICTURE"])
						{
							if (preg_match("/^(ftp|ftps|http|https):\\/\\//", $arGroupsTmp[$i]["DETAIL_PICTURE"]))
							{
								$arGroupsTmp[$i]["DETAIL_PICTURE"] = CFile::MakeFileArray($arGroupsTmp[$i]["DETAIL_PICTURE"]);
							}
							else
							{
								$arGroupsTmp[$i]["DETAIL_PICTURE"] = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$arGroupsTmp[$i]["DETAIL_PICTURE"]));
								if (!empty($arGroupsTmp[$i]["DETAIL_PICTURE"]) && is_array($arGroupsTmp[$i]["DETAIL_PICTURE"]))
									$arGroupsTmp[$i]["DETAIL_PICTURE"]['COPY_FILE'] = 'Y';
							}
							$bFilePres = (!empty($arGroupsTmp[$i]["DETAIL_PICTURE"])
								&& isset($arGroupsTmp[$i]["DETAIL_PICTURE"]["tmp_name"])
								&& '' !== $arGroupsTmp[$i]["DETAIL_PICTURE"]["tmp_name"]
							);
						}
						if (!$bFilePres)
							unset($arGroupsTmp[$i]["DETAIL_PICTURE"]);
					}

					$res = CIBlockSection::GetList(array(), $arFilter, false, array('ID'));
					if ($arr = $res->Fetch())
					{
						if ($boolTranslitSection && $updateTranslit)
						{
							if (!isset($arGroupsTmp[$i]['CODE']) || '' === $arGroupsTmp[$i]['CODE'])
							{
								$arGroupsTmp[$i]['CODE'] = CUtil::translit($arGroupsTmp[$i]["NAME"], $TRANSLIT_LANG, $arTranslitSection);
							}
						}
						$LAST_GROUP_CODE = $arr["ID"];
						$res = $bs->Update($LAST_GROUP_CODE, $arGroupsTmp[$i], true, true, 'Y' === $IMAGE_RESIZE);
						if (!$res)
						{
							$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".GetMessage("CATI_ERR_UPDATE_SECT")." ".$bs->LAST_ERROR."<br>";
						}
					}
					else
					{
						if ($boolTranslitSection)
						{
							if (!isset($arGroupsTmp[$i]['CODE']) || '' === $arGroupsTmp[$i]['CODE'])
							{
								$arGroupsTmp[$i]['CODE'] = CUtil::translit($arGroupsTmp[$i]["NAME"], $TRANSLIT_LANG, $arTranslitSection);
							}
						}
						$arGroupsTmp[$i]["IBLOCK_ID"] = $IBLOCK_ID;
						$arGroupsTmp[$i]["ACTIVE"] = (isset($arGroupsTmp[$i]["ACTIVE"]) && 'N' === $arGroupsTmp[$i]["ACTIVE"] ? 'N' : 'Y');
						$LAST_GROUP_CODE = $bs->Add($arGroupsTmp[$i], true, true, 'Y' === $IMAGE_RESIZE);
						if (!$LAST_GROUP_CODE)
						{
							$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".GetMessage("CATI_ERR_ADD_SECT")." ".$bs->LAST_ERROR."<br>";
						}
					}

					if ('' === $strErrorR)
					{
						$arSectionCache[$sectionIndex] = $LAST_GROUP_CODE;
					}
				}
				else
				{
					$LAST_GROUP_CODE = $arSectionCache[$sectionIndex];
				}
			}

			$arFilter = array("IBLOCK_ID" => $IBLOCK_ID);
			if ('' === $strErrorR)
			{
				$arLoadProductArray = array(
					"MODIFIED_BY" => $currentUserID,
					"IBLOCK_ID" => $IBLOCK_ID,
					"TMP_ID" => $tmpid
				);
				foreach ($arAvailProdFields_names as $key => $value)
				{
					$ind = -1;
					for ($i_tmp = 0; $i_tmp < $NUM_FIELDS; $i_tmp++)
					{
						if (${"field_".$i_tmp} == $key)
						{
							$ind = $i_tmp;
							break;
						}
					}

					if ($ind>-1)
					{
						$arLoadProductArray[$value["field"]] = trim($arRes[$ind]);
					}
				}

				if (isset($arLoadProductArray["XML_ID"]) && '' !== $arLoadProductArray["XML_ID"])
				{
					$arFilter["=XML_ID"] = $arLoadProductArray["XML_ID"];
				}
				else
				{
					if (isset($arLoadProductArray["NAME"]) && '' !== $arLoadProductArray["NAME"])
					{
						$arFilter["=NAME"] = $arLoadProductArray["NAME"];
					}
					else
					{
						$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".GetMessage("CATI_NOIDNAME")."<br>";
					}
				}
			}

			if ('' === $strErrorR)
			{
				if (isset($arLoadProductArray["PREVIEW_PICTURE"]))
				{
					$arLoadProductArray["PREVIEW_PICTURE"] = trim($arLoadProductArray["PREVIEW_PICTURE"]);
					$bFilePres = false;
					if ('' !== $arLoadProductArray["PREVIEW_PICTURE"])
					{
						if (preg_match("/^(ftp|ftps|http|https):\\/\\//", $arLoadProductArray["PREVIEW_PICTURE"]))
						{
							$arLoadProductArray["PREVIEW_PICTURE"] = CFile::MakeFileArray($arLoadProductArray["PREVIEW_PICTURE"]);
						}
						else
						{
							$arLoadProductArray["PREVIEW_PICTURE"] = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$arLoadProductArray["PREVIEW_PICTURE"]));
							if (!empty($arLoadProductArray["PREVIEW_PICTURE"]) && is_array($arLoadProductArray["PREVIEW_PICTURE"]))
								$arLoadProductArray["PREVIEW_PICTURE"]["COPY_FILE"] = "Y";
						}
						$bFilePres = (!empty($arLoadProductArray["PREVIEW_PICTURE"])
							&& isset($arLoadProductArray["PREVIEW_PICTURE"]["tmp_name"])
							&& '' !== $arLoadProductArray["PREVIEW_PICTURE"]["tmp_name"]
						);
					}
					if (!$bFilePres)
						unset($arLoadProductArray["PREVIEW_PICTURE"]);
				}

				if (isset($arLoadProductArray["DETAIL_PICTURE"]))
				{
					$arLoadProductArray["DETAIL_PICTURE"] = trim($arLoadProductArray["DETAIL_PICTURE"]);
					$bFilePres = false;
					if ('' !== $arLoadProductArray["DETAIL_PICTURE"])
					{
						if (preg_match("/^(ftp|ftps|http|https):\\/\\//", $arLoadProductArray["DETAIL_PICTURE"]))
						{
							$arLoadProductArray["DETAIL_PICTURE"] = CFile::MakeFileArray($arLoadProductArray["DETAIL_PICTURE"]);
						}
						else
						{
							$arLoadProductArray["DETAIL_PICTURE"] = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$arLoadProductArray["DETAIL_PICTURE"]));
							if (!empty($arLoadProductArray["DETAIL_PICTURE"]) && is_array($arLoadProductArray["DETAIL_PICTURE"]))
								$arLoadProductArray["DETAIL_PICTURE"]["COPY_FILE"] = "Y";
						}
						$bFilePres = (!empty($arLoadProductArray["DETAIL_PICTURE"])
							&& isset($arLoadProductArray["DETAIL_PICTURE"]["tmp_name"])
							&& '' !== $arLoadProductArray["DETAIL_PICTURE"]["tmp_name"]
						);
					}
					if (!$bFilePres)
						unset($arLoadProductArray["DETAIL_PICTURE"]);
				}

				$res = CIBlockElement::GetList(
					array(),
					$arFilter,
					false,
					false,
					array('ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'IBLOCK_SECTION_ID')
				);
				if ($arr = $res->Fetch())
				{
					$PRODUCT_ID = (int)$arr['ID'];
					if (isset($arLoadProductArray["PREVIEW_PICTURE"]) && intval($arr["PREVIEW_PICTURE"])>0)
					{
						$arLoadProductArray["PREVIEW_PICTURE"]["old_file"] = $arr["PREVIEW_PICTURE"];
					}
					if (isset($arLoadProductArray["DETAIL_PICTURE"]) && intval($arr["DETAIL_PICTURE"])>0)
					{
						$arLoadProductArray["DETAIL_PICTURE"]["old_file"] = $arr["DETAIL_PICTURE"];
					}
					if ($boolTranslitElement && $updateTranslit)
					{
						if (!isset($arLoadProductArray['CODE']) || '' === $arLoadProductArray['CODE'])
						{
							$arLoadProductArray['CODE'] = CUtil::translit($arLoadProductArray["NAME"], $TRANSLIT_LANG, $arTranslitElement);
						}
					}
					if ($bThereIsGroups)
					{
						if (!isset($currentProductSection[$PRODUCT_ID]))
							$currentProductSection[$PRODUCT_ID] = $arr['IBLOCK_SECTION_ID'];
						$LAST_GROUP_CODE_tmp = (($LAST_GROUP_CODE > 0) ? $LAST_GROUP_CODE : false);
						if (!isset($arProductGroups[$PRODUCT_ID]))
							$arProductGroups[$PRODUCT_ID] = array();
						if (!in_array($LAST_GROUP_CODE_tmp, $arProductGroups[$PRODUCT_ID]))
						{
							$arProductGroups[$PRODUCT_ID][] = $LAST_GROUP_CODE_tmp;
						}
						$arLoadProductArray["IBLOCK_SECTION"] = $arProductGroups[$PRODUCT_ID];
						$arLoadProductArray['IBLOCK_SECTION_ID'] = $currentProductSection[$PRODUCT_ID];
						$updateFacet = true;
					}
					$res = $el->Update($PRODUCT_ID, $arLoadProductArray, $bWorkflow, false, 'Y' === $IMAGE_RESIZE);
				}
				else
				{
					if ($bThereIsGroups)
					{
						$arLoadProductArray["IBLOCK_SECTION"] = (($LAST_GROUP_CODE>0) ? $LAST_GROUP_CODE : false);
					}
					if ($arLoadProductArray["ACTIVE"] != "N")
						$arLoadProductArray["ACTIVE"] = "Y";
					if ($boolTranslitElement)
					{
						if (!isset($arLoadProductArray['CODE']) || '' === $arLoadProductArray['CODE'])
						{
							$arLoadProductArray['CODE'] = CUtil::translit($arLoadProductArray["NAME"], $TRANSLIT_LANG, $arTranslitElement);
						}
					}

					$PRODUCT_ID = $el->Add($arLoadProductArray, $bWorkflow, false, 'Y' === $IMAGE_RESIZE);
					if ($bThereIsGroups)
					{
						if (!isset($arProductGroups[$PRODUCT_ID]))
							$arProductGroups[$PRODUCT_ID] = array();
						$arProductGroups[$PRODUCT_ID][] = (($LAST_GROUP_CODE > 0) ? $LAST_GROUP_CODE : false);
					}
					$res = ($PRODUCT_ID > 0);
					if ($res)
						$newProducts[$PRODUCT_ID] = true;
				}

				if (!$res)
				{
					$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".GetMessage("CATI_ERROR_LOADING")." ".$el->LAST_ERROR."<br>";
				}
			}

			if ('' === $strErrorR)
			{
				$PROP = array();
				for ($i = 0; $i < $NUM_FIELDS; $i++)
				{
					if (0 == strncmp(${"field_".$i}, "IP_PROP", 7))
					{
						$cur_prop_id = intval(mb_substr(${"field_".$i}, 7));
						if (!isset($arIBlockProperty[$cur_prop_id]))
						{
							$res1 = CIBlockProperty::GetByID($cur_prop_id, $IBLOCK_ID);
							if ($arRes1 = $res1->Fetch())
								$arIBlockProperty[$cur_prop_id] = $arRes1;
							else
								$arIBlockProperty[$cur_prop_id] = array();
						}
						if (!empty($arIBlockProperty[$cur_prop_id]) && is_array($arIBlockProperty[$cur_prop_id]))
						{
							$multipleCheckId = $arRes[$i];
							if ('Y' == $CML2_LINK_IS_XML && $cur_prop_id == $arSku['SKU_PROPERTY_ID'])
							{
								$arRes[$i] = trim($arRes[$i]);
								if ('' != $arRes[$i])
								{
									if (!isset($arProductCache[$arRes[$i]]))
									{
										$rsProducts = CIBlockElement::GetList(
											array(),
											array('IBLOCK_ID' => $arSku['PRODUCT_IBLOCK_ID'], '=XML_ID' => $arRes[$i]),
											false,
											false,
											array('ID')
										);
										if ($arParentProduct = $rsProducts->Fetch())
											$arProductCache[$arRes[$i]] = $arParentProduct['ID'];
									}
									$arRes[$i] = (isset($arProductCache[$arRes[$i]]) ? $arProductCache[$arRes[$i]] : '');
								}
							}
							elseif ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"]=="L")
							{
								$arRes[$i] = trim($arRes[$i]);
								if ('' !== $arRes[$i])
								{
									$propValueHash = md5($arRes[$i]);
									if (!isset($arPropertyListCache[$cur_prop_id]))
									{
										$arPropertyListCache[$cur_prop_id] = array();
										$propEnumRes = CIBlockPropertyEnum::GetList(
											array('SORT' => 'ASC', 'VALUE' => 'ASC'),
											array('IBLOCK_ID' => $IBLOCK_ID, 'PROPERTY_ID' => $arIBlockProperty[$cur_prop_id]['ID'])
										);
										while ($propEnumValue = $propEnumRes->Fetch())
											$arPropertyListCache[$cur_prop_id][md5($propEnumValue['VALUE'])] = $propEnumValue['ID'];
									}
									if (!isset($arPropertyListCache[$cur_prop_id][$propValueHash]))
									{
										$arPropertyListCache[$cur_prop_id][$propValueHash] = CIBlockPropertyEnum::Add(
											array(
												"PROPERTY_ID" => $arIBlockProperty[$cur_prop_id]['ID'],
												"VALUE" => $arRes[$i],
												"TMP_ID" => $tmpid
											)
										);
									}
									if (isset($arPropertyListCache[$cur_prop_id][$propValueHash]))
									{
										$arRes[$i] = $arPropertyListCache[$cur_prop_id][$propValueHash];
									}
									else
									{
										$arRes[$i] = '';
									}
								}
							}
							elseif ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"]=="F")
							{
								$arRes[$i] = trim($arRes[$i]);
								if(preg_match("/^(ftp|ftps|http|https):\\/\\//", $arRes[$i]))
									$arRes[$i] = CFile::MakeFileArray($arRes[$i]);
								else
									$arRes[$i] = CFile::MakeFileArray($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES.'/'.$arRes[$i]));

								if (!is_array($arRes[$i]) || !array_key_exists("tmp_name", $arRes[$i]))
									$arRes[$i] = '';
							}
							if (!is_array($arRes[$i]))
							{
								$arRes[$i] = trim($arRes[$i]);
								if ($arRes[$i] == '')
									$multipleCheckId = $arRes[$i];
							}

							if ($arIBlockProperty[$cur_prop_id]["MULTIPLE"]=="Y")
							{
								if (!isset($arIBlockPropertyValue[$PRODUCT_ID]))
								{
									$arIBlockPropertyValue[$PRODUCT_ID] = array();
									$multiplePropertyValuesCheck[$PRODUCT_ID] = array();
								}
								if (
									!isset($arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id])
									|| !is_array($arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id])
								)
								{
									$arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id] = array();
									$multiplePropertyValuesCheck[$PRODUCT_ID][$cur_prop_id] = array();
								}

								if (
									!in_array($multipleCheckId, $multiplePropertyValuesCheck[$PRODUCT_ID][$cur_prop_id])
								)
								{
									$arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id][] = $arRes[$i];
									$multiplePropertyValuesCheck[$PRODUCT_ID][$cur_prop_id][] = $multipleCheckId;
								}

								$PROP[$cur_prop_id] = $arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id];
							}
							else
							{
								$PROP[$cur_prop_id] = $arRes[$i];
							}
						}
					}
				}

				if (!empty($PROP))
				{
					CIBlockElement::SetPropertyValuesEx($PRODUCT_ID, $IBLOCK_ID, $PROP);
					$updateFacet = true;
				}
			}

			if ('' == $strErrorR && $bIBlockIsCatalog)
			{
				$arLoadOfferArray = array(
					'ID' => $PRODUCT_ID,
					'TMP_ID' => $tmpid
				);
				foreach ($arAvailPriceFields_names as $key => $value)
				{
					$ind = -1;
					for ($i_tmp = 0; $i_tmp < $NUM_FIELDS; $i_tmp++)
					{
						if (${"field_".$i_tmp} == $key)
						{
							$ind = $i_tmp;
							break;
						}
					}

					if ($ind > -1)
						$arLoadOfferArray[$value["field"]] = trim($arRes[$ind]);
				}

				$arLoadOfferArray = array(
					'fields' => $arLoadOfferArray,
					'external_fields' => array(
						'IBLOCK_ID' => $IBLOCK_ID
					)
				);
				$row = Catalog\Model\Product::getList(array(
					'select' => array('ID'),
					'filter' => array('=ID' => $PRODUCT_ID)
				))->fetch();
				if (empty($row))
				{
					if ($boolUseStoreControl)
					{
						$arLoadOfferArray['fields']['QUANTITY'] = 0;
						$arLoadOfferArray['fields']['QUANTITY_RESERVED'] = 0;
						$arLoadOfferArray['fields']['QUANTITY_TRACE'] = Catalog\ProductTable::STATUS_YES;
						$arLoadOfferArray['fields']['CAN_BUY_ZERO'] = Catalog\ProductTable::STATUS_NO;
						$arLoadOfferArray['fields']['PURCHASING_PRICE'] = null;
						$arLoadOfferArray['fields']['PURCHASING_CURRENCY'] = null;
					}
					else
					{
						if (isset($arLoadOfferArray['fields']['QUANTITY']) && $arLoadOfferArray['fields']['QUANTITY'] === '')
							unset($arLoadOfferArray['fields']['QUANTITY']);
						$emptyStartPrice = (
							(
								isset($arLoadOfferArray['fields']['PURCHASING_PRICE'])
								&& $arLoadOfferArray['fields']['PURCHASING_PRICE'] === ''
							)
							&&
							(
								isset($arLoadOfferArray['fields']['PURCHASING_CURRENCY'])
								&& $arLoadOfferArray['fields']['PURCHASING_CURRENCY'] === ''
							)
						);
						if ($emptyStartPrice)
						{
							unset($arLoadOfferArray['fields']['PURCHASING_PRICE']);
							unset($arLoadOfferArray['fields']['PURCHASING_CURRENCY']);
						}
						unset($emptyStartPrice);
						if (isset($arLoadOfferArray['fields']['PURCHASING_PRICE']))
						{
							$arLoadOfferArray['fields']['PURCHASING_PRICE'] = str_replace(
								array(' ', ','),
								array('', '.'),
								$arLoadOfferArray['fields']['PURCHASING_PRICE']
							);
						}
					}
					if (isset($arLoadOfferArray['fields']['WEIGHT']) && $arLoadOfferArray['fields']['WEIGHT'] === '')
						unset($arLoadOfferArray['fields']['WEIGHT']);
					if (empty($arLoadOfferArray['fields']['MEASURE']) && $defaultMeasureId !== null)
						$arLoadOfferArray['fields']['MEASURE'] = $defaultMeasureId;
					$productResult = Catalog\Model\Product::add($arLoadOfferArray);
				}
				else
				{
					if (!$boolUseStoreControl)
					{
						if (isset($arLoadOfferArray['fields']['QUANTITY']) && $arLoadOfferArray['fields']['QUANTITY'] === '')
							$arLoadOfferArray['fields']['QUANTITY'] = 0;
						$emptyStartPrice = (
							(
								isset($arLoadOfferArray['fields']['PURCHASING_PRICE'])
								&& $arLoadOfferArray['fields']['PURCHASING_PRICE'] === ''
							)
							&&
							(
								isset($arLoadOfferArray['fields']['PURCHASING_CURRENCY'])
								&& $arLoadOfferArray['fields']['PURCHASING_CURRENCY'] === ''
							)
						);
						if ($emptyStartPrice)
						{
							$arLoadOfferArray['fields']['PURCHASING_PRICE'] = null;
							$arLoadOfferArray['fields']['PURCHASING_CURRENCY'] = null;
						}
						unset($emptyStartPrice);
						if (isset($arLoadOfferArray['fields']['PURCHASING_PRICE']))
						{
							$arLoadOfferArray['fields']['PURCHASING_PRICE'] = str_replace(
								array(' ', ','),
								array('', '.'),
								$arLoadOfferArray['fields']['PURCHASING_PRICE']
							);
						}
					}
					if (isset($arLoadOfferArray['fields']['WEIGHT']) && $arLoadOfferArray['fields']['WEIGHT'] === '')
						$arLoadOfferArray['fields']['WEIGHT'] = 0;
					$productResult = Catalog\Model\Product::update($PRODUCT_ID, $arLoadOfferArray);
				}
				unset($row);
				unset($arLoadOfferArray);
				if (!$productResult->isSuccess())
				{
					$strErrorR .= GetMessage('CATI_LINE_NO').' '.$line_num.'. '.implode('; ', $productResult->getErrorMessages());
				}
				else
				{
					$quantityFrom = 0;
					$quantityTo = 0;
					for ($j = 0; $j < $NUM_FIELDS; $j++)
					{
						if (${"field_".$j} == "CV_QUANTITY_FROM")
							$quantityFrom = (int)$arRes[$j];
						elseif (${"field_".$j} == "CV_QUANTITY_TO")
							$quantityTo = (int)$arRes[$j];
					}
					if ($quantityFrom <= 0)
						$quantityFrom = null;
					if ($quantityTo <= 0)
						$quantityTo = null;

					$arFields = array();
					$priceTypeList = array();
					for ($j = 0; $j < $NUM_FIELDS; $j++)
					{
						foreach ($arAvailValueFields_names as $key => $value)
						{
							if (0 == strncmp(${"field_".$j}, $key."_", $value['field_name_size'] + 1))
							{
								$strTempKey = intval(mb_substr(${"field_".$j}, $value['field_name_size'] + 1));
								if (!isset($arFields[$strTempKey]))
								{
									$arFields[$strTempKey] = array(
										"PRODUCT_ID" => $PRODUCT_ID,
										"CATALOG_GROUP_ID" => $strTempKey,
										"QUANTITY_FROM" => $quantityFrom,
										"QUANTITY_TO" => $quantityTo,
										"TMP_ID" => $tmpid
									);
									$priceTypeList[$strTempKey] = $strTempKey;
								}
								$arFields[$strTempKey][$value["field"]] = trim($arRes[$j]);
							}
						}
					}

					if (!empty($arFields))
					{
						if (!isset($productPriceCache[$PRODUCT_ID]))
						{
							$productPriceCache[$PRODUCT_ID] = array();
							$priceIterator = Catalog\Model\Price::getList(array(
								'select' => array('ID', 'CATALOG_GROUP_ID', 'QUANTITY_FROM', 'QUANTITY_TO'),
								'filter' => array('=PRODUCT_ID' => $PRODUCT_ID, '@CATALOG_GROUP_ID' => $priceTypeList)
							));
							while ($row = $priceIterator->fetch())
							{
								$hash = ($row['QUANTITY_FROM'] === null ? 'ZERO' : $row['QUANTITY_FROM']).'-'.
									($row['QUANTITY_TO'] === null ? 'INF' : $row['QUANTITY_TO']);
								$priceType = (int)$row['CATALOG_GROUP_ID'];
								if (!isset($productPriceCache[$PRODUCT_ID][$priceType]))
									$productPriceCache[$PRODUCT_ID][$priceType] = array();
								$productPriceCache[$PRODUCT_ID][$priceType][$hash] = (int)$row['ID'];
							}
							unset($row, $priceIterator);
						}

						foreach ($arFields as $key => $value)
						{
							$strPriceErr = '';

							$hash = ($value['QUANTITY_FROM'] === null ? 'ZERO' : $value['QUANTITY_FROM']).'-'.
								($value['QUANTITY_TO'] === null ? 'INF' : $value['QUANTITY_TO']);
							$priceType = (int)$value['CATALOG_GROUP_ID'];

							if (!isset($processedProductPriceCache[$PRODUCT_ID][$priceType][$hash]))
							{
								if (!isset($processedProductPriceCache[$PRODUCT_ID]))
									$processedProductPriceCache[$PRODUCT_ID] = array();
								if (!isset($processedProductPriceCache[$PRODUCT_ID][$priceType]))
									$processedProductPriceCache[$PRODUCT_ID][$priceType] = array();

								$priceId = (isset($productPriceCache[$PRODUCT_ID][$priceType][$hash])
									? $productPriceCache[$PRODUCT_ID][$priceType][$hash]
									: null
								);

								if ($priceId !== null)
								{
									$emptyPrice = (
										(isset($value['PRICE']) && '' === $value['PRICE']) &&
										(isset($value['CURRENCY']) && '' === $value['CURRENCY'])
									);
									$boolEraseClear = ('Y' == $CLEAR_EMPTY_PRICE ? $emptyPrice :false);
									if ($boolEraseClear)
									{
										$priceResult = Catalog\Model\Price::delete($priceId);
										if (!$priceResult->isSuccess())
										{
											$strPriceErr = implode('; ', $priceResult->getErrorMessages());
											if ($strPriceErr !== '')
												$strPriceErr = GetMessage('CATI_ERR_PRICE_DELETE').$strPriceErr;
											else
												$strPriceErr = GetMessage('CATI_ERR_PRICE_DELETE');
										}
										unset($priceResult);
									}
									else
									{
										if (!$emptyPrice)
										{
											if (isset($value['PRICE']))
												$value['PRICE'] = str_replace(array(' ', ','), array('', '.'), $value['PRICE']);
										}
										else
										{
											$value = [
												"TMP_ID" => $tmpid
											];
										}

										$priceResult = Catalog\Model\Price::update($priceId, $value);
										if ($priceResult->isSuccess())
										{
											$bUpdatePrice = 'Y';
										}
										else
										{
											$strPriceErr = implode('; ', $priceResult->getErrorMessages());
											if ($strPriceErr !== '')
												$strPriceErr = GetMessage('CATI_ERR_PRICE_UPDATE').$strPriceErr;
											else
												$strPriceErr = GetMessage('CATI_ERR_PRICE_UPDATE_UNKNOWN');
										}
										unset($priceResult);
									}
									unset($productPriceCache[$PRODUCT_ID][$priceType][$hash]);
									$processedProductPriceCache[$PRODUCT_ID][$priceType][$hash] = $priceId;
								}
								else
								{
									$boolEmptyNewPrice = (
										(isset($value['PRICE']) && '' === $value['PRICE'])
										&& (isset($value['CURRENCY']) && '' === $value['CURRENCY'])
									);
									if (!$boolEmptyNewPrice)
									{
										if (isset($value['PRICE']))
											$value['PRICE'] = str_replace(array(' ', ','), array('', '.'), $value['PRICE']);

										$priceResult = Catalog\Model\Price::add($value);
										if ($priceResult->isSuccess())
										{
											$bUpdatePrice = 'Y';
											$processedProductPriceCache[$PRODUCT_ID][$priceType][$hash] = $priceResult->getId();
										}
										else
										{
											$strPriceErr = implode('; ', $priceResult->getErrorMessages());
											if ($strPriceErr !== '')
												$strPriceErr = GetMessage('CATI_ERR_PRICE_ADD').$strPriceErr;
											else
												$strPriceErr = GetMessage('CATI_ERR_PRICE_ADD_UNKNOWN');
										}
										unset($priceResult);
									}
								}
								if ('' != $strPriceErr)
								{
									$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".$strPriceErr.'<br>';
									break;
								}
								else
								{
									$updateFacet = true;
								}
							}
						}
					}
				}
			}

			if ('' == $strErrorR)
			{
				$correct_lines++;
				if ($previousProductId === false)
					$previousProductId = $PRODUCT_ID;
				if ($previousProductId != $PRODUCT_ID)
				{
					CIBlockElement::UpdateSearch($previousProductId, true);
					$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID, $previousProductId);
					$ipropValues->clearValues();
					unset($ipropValues);
					if ($updateFacet)
					{
						if (isset($newProducts[$previousProductId]))
							CCatalogSKU::ClearCache();
					}
					$updateFacet = false;
					if (!empty($productPriceCache[$previousProductId]))
					{
						foreach ($productPriceCache[$previousProductId] as $priceTypeRows)
						{
							if (!empty($priceTypeRows) && is_array($priceTypeRows))
							{
								foreach ($priceTypeRows as $priceId)
								{
									$priceResult = Catalog\Model\Price::delete($priceId);
									if (!$priceResult->isSuccess())
									{
										$strPriceErr = implode('; ', $priceResult->getErrorMessages());
										if ($strPriceErr !== '')
											$strPriceErr = GetMessage('CATI_ERR_PRICE_DELETE').$strPriceErr;
										else
											$strPriceErr = GetMessage('CATI_ERR_PRICE_DELETE');
										$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".$strPriceErr.'<br>';
										break 2;
									}
								}
							}
						}
						unset($productPriceCache[$previousProductId]);
					}
					if (!empty($processedProductPriceCache[$previousProductId]))
						unset($processedProductPriceCache[$previousProductId]);

					if (isset($arIBlockPropertyValue[$previousProductId]))
					{
						unset($arIBlockPropertyValue[$previousProductId]);
					}
					if (isset($multiplePropertyValuesCheck[$previousProductId]))
					{
						unset($multiplePropertyValuesCheck[$previousProductId]);
					}
					if (isset($currentProductSection[$previousProductId]))
					{
						unset($currentProductSection[$previousProductId]);
					}
					if (isset($arProductGroups[$previousProductId]))
					{
						unset($arProductGroups[$previousProductId]);
					}

					$previousProductId = $PRODUCT_ID;
				}
			}
			else
			{
				$error_lines++;
				$strImportErrorMessage .= $strErrorR;
			}

			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time))) break;
		}
		while ($arRes = $csvFile->Fetch());
	}
	if ($PRODUCT_ID > 0)
	{
		CIBlockElement::UpdateSearch($PRODUCT_ID, true);
		$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID, $PRODUCT_ID);
		$ipropValues->clearValues();
		unset($ipropValues);
		if ($updateFacet)
		{
			if (isset($newProducts[$PRODUCT_ID]))
				CCatalogSKU::ClearCache();
		}
		$updateFacet = false;

		if (!empty($productPriceCache[$PRODUCT_ID]))
		{
			foreach ($productPriceCache[$PRODUCT_ID] as $priceTypeRows)
			{
				if (!empty($priceTypeRows) && is_array($priceTypeRows))
				{
					foreach ($priceTypeRows as $priceId)
					{
						$priceResult = Catalog\Model\Price::delete($priceId);
						if (!$priceResult->isSuccess())
						{
							$strPriceErr = implode('; ', $priceResult->getErrorMessages());
							if ($strPriceErr !== '')
								$strPriceErr = GetMessage('CATI_ERR_PRICE_DELETE').$strPriceErr;
							else
								$strPriceErr = GetMessage('CATI_ERR_PRICE_DELETE');
							$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".$strPriceErr.'<br>';
							break 2;
						}
					}
				}
			}
			unset($productPriceCache[$PRODUCT_ID]);
		}
		if (!empty($processedProductPriceCache[$PRODUCT_ID]))
			unset($processedProductPriceCache[$PRODUCT_ID]);
	}
	Catalog\Product\Sku::disableDeferredCalculation();
	Catalog\Product\Sku::calculate();
	Iblock\PropertyIndex\Manager::disableDeferredIndexing();
	Iblock\PropertyIndex\Manager::runDeferredIndexing($IBLOCK_ID);

//////////////////////////////
// start additional actions //
//////////////////////////////

	// activate 'in-file' sections
	if ($bAllLinesLoaded && $bThereIsGroups && $inFileAction == 'A' && !$bDeactivationStarted)
	{
		$res = CIBlockSection::GetList(
			array(),
			array("IBLOCK_ID" => $IBLOCK_ID, "TMP_ID" => $tmpid, "ACTIVE" => "N", 'CHECK_PERMISSIONS' => 'N'),
			false,
			array('ID', 'NAME')
		);
		while($arr = $res->Fetch())
		{
			$bs->Update($arr["ID"], array("NAME"=>$arr["NAME"], "ACTIVE" => "Y"));
			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time)))
				break;
		}
	}

	// activate 'in-file' elements
	if ($bAllLinesLoaded && $inFileAction=="A" && !$bDeactivationStarted)
	{
		Catalog\Product\Sku::enableDeferredCalculation();
		$res = CIBlockElement::GetList(
			array(),
			array("IBLOCK_ID" => $IBLOCK_ID, "TMP_ID" => $tmpid, "ACTIVE" => "N"),
			false,
			false,
			array('ID')
		);
		while($arr = $res->Fetch())
		{
			$el->Update($arr["ID"], array("ACTIVE" => "Y"));
			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time)))
				break;
		}
		Catalog\Product\Sku::disableDeferredCalculation();
		Catalog\Product\Sku::calculate();
	}

	// update or delete 'not-in-file sections'
	if ($bAllLinesLoaded && $outFileAction != 'F' && $bThereIsGroups)
	{
		if ($outFileAction == "D")
			Catalog\Product\Sku::enableDeferredCalculation();
		$res = CIBlockSection::GetList(
			array(),
			array("IBLOCK_ID" => $IBLOCK_ID, "!TMP_ID" => $tmpid, 'CHECK_PERMISSIONS' => 'N'),
			false,
			array('ID', 'NAME')
		);

		while($arr = $res->Fetch())
		{
			if ($outFileAction=="D")
			{
				CIBlockSection::Delete($arr["ID"]);
			}
			elseif ($outFileAction == 'H' || $outFileAction == 'M') // H or M
			{
				$bDeactivationStarted = true;
				$bs->Update($arr["ID"], array("NAME"=>$arr["NAME"], "ACTIVE" => "N", "TMP_ID" => $tmpid));
			}

			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time)))
				break;
		}
		if ($outFileAction == "D")
		{
			Catalog\Product\Sku::disableDeferredCalculation();
			Catalog\Product\Sku::calculate();
		}
	}

	// update or delete 'not-in-file' elements
	if ($bAllLinesLoaded && $outFileAction != "F")
	{
		Catalog\Product\Sku::enableDeferredCalculation();
		if ($bIBlockIsCatalog && $outFileAction=="M")
		{
			$arProductArray = Catalog\ProductTable::getDefaultAvailableSettings();
			$arProductArray['TMP_ID'] = $tmpid;
			$filter = array('=IBLOCK_ELEMENT.IBLOCK_ID' => $IBLOCK_ID, '!=TMP_ID' => $tmpid);
			if (!$separateSku)
			{
				$filter['!=TYPE'] = Catalog\ProductTable::TYPE_SKU;
			}
			$res = Catalog\Model\Product::getList(array(
				'select' => array('ID'),
				'filter' => $filter,
				'order' => array('ID' => 'ASC')
			));
			while($arr = $res->fetch())
			{
				$result = Catalog\Model\Product::update($arr['ID'], $arProductArray);
				$killed_lines++;

				if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time)))
					break;
			}
			unset($arr, $res);
		}
		else
		{
			$res = CIBlockElement::GetList(
				array('ID' => 'ASC'),
				array("IBLOCK_ID" => $IBLOCK_ID, "!TMP_ID" => $tmpid),
				false,
				false,
				array('ID')
			);
			while($arr = $res->Fetch())
			{
				if ($outFileAction == "D")
				{
					CIBlockElement::Delete($arr["ID"]);
					$killed_lines++;
				}
				elseif ($outFileAction == "H") // H
				{
					$bDeactivationStarted = true;
					$el->Update($arr["ID"], array("ACTIVE" => "N", "TMP_ID" => $tmpid));
					$killed_lines++;
				}

				if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time)))
					break;
			}
			unset($arr, $res);
		}
		Catalog\Product\Sku::disableDeferredCalculation();
		Catalog\Product\Sku::calculate();
	}

	// delete 'not-in-file' element prices
	if ($bAllLinesLoaded && $bIBlockIsCatalog && 'Y' == $bUpdatePrice && $outFileAction=="D")
	{
		$filter = array(
			'=ELEMENT.IBLOCK_ID' => $IBLOCK_ID,
			'!=TMP_ID' => $tmpid
		);
		if (!$separateSku)
		{
			$filter['!=PRODUCT.TYPE'] = Catalog\ProductTable::TYPE_SKU;
		}
		Catalog\Product\Sku::enableDeferredCalculation();
		$res = Catalog\Model\Price::getList(array(
			'select' => array('ID'),
			'filter' => $filter
		));
		while($arr = $res->fetch())
		{
			$priceResult = Catalog\Model\Price::delete($arr['ID']);

			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time)))
				break;
		}
		Catalog\Product\Sku::disableDeferredCalculation();
		Catalog\Product\Sku::calculate();
	}

	if (!$bAllLinesLoaded)
	{
		$bAllDataLoaded = false;

		$INTERNAL_VARS_LIST =
			"tmpid,line_num,correct_lines,"
			. "error_lines,killed_lines,"
			. "arIBlockProperty,arIBlockPropertyValue,"
			. "multiplePropertyValuesCheck,"
			. "bThereIsGroups,"
			. "bDeactivationStarted,"
			. "arProductGroups,"
			. "currentProductSection,"
			. "bUpdatePrice"
		;
		$SETUP_VARS_LIST = "IBLOCK_ID,URL_DATA_FILE,fields_type,first_names_r,delimiter_r,delimiter_other_r,first_names_f,metki_f,PATH2IMAGE_FILES,outFileAction,inFileAction,max_execution_time,IMAGE_RESIZE,USE_TRANSLIT,TRANSLIT_LANG,CLEAR_EMPTY_PRICE,CML2_LINK_IS_XML";
		for ($i = 0; $i < $NUM_FIELDS; $i++)
			$SETUP_VARS_LIST .= ",field_".$i;
		$CUR_FILE_POS = $csvFile->GetPos();
	}
	else
	{
		CIBlock::enableClearTagCache();
		CIBlock::clearIblockTagCache($IBLOCK_ID);
	}
}

if ($bTmpUserCreated)
{
	if (isset($USER_TMP))
	{
		$USER = $USER_TMP;
		unset($USER_TMP);
	}
}
