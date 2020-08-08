<?
//<title>CSV (new)</title>
use Bitrix\Main,
	Bitrix\Catalog;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/import_setup_templ.php');
/** @global string $ACTION */
/** @global string $URL_DATA_FILE */
/** @global string $DATA_FILE_NAME */
/** @global int $IBLOCK_ID */
/** @global string $fields_type */
/** @global string $first_names_r */
/** @global string $delimiter_r */
/** @global string $delimiter_other_r */
/** @global string $first_names_f */
/** @global string $metki_f */

global $APPLICATION, $USER;

$NUM_CATALOG_LEVELS = (int)Main\Config\Option::get('catalog', 'num_catalog_levels');
if ($NUM_CATALOG_LEVELS <= 0)
	$NUM_CATALOG_LEVELS = 3;

$arSetupErrors = array();

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

//********************  ACTIONS  **************************************//
if (($ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY') && $STEP == 1)
{
	if (isset($arOldSetupVars['IBLOCK_ID']))
		$IBLOCK_ID = $arOldSetupVars['IBLOCK_ID'];
	if (isset($arOldSetupVars['URL_DATA_FILE']))
		$URL_DATA_FILE = $arOldSetupVars['URL_DATA_FILE'];
	if (isset($arOldSetupVars['DATA_FILE_NAME']))
		$DATA_FILE_NAME = $arOldSetupVars['DATA_FILE_NAME'];
}

if ($STEP > 1)
{
	if ($URL_DATA_FILE <> '' && file_exists($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && $APPLICATION->GetFileAccessPermission($URL_DATA_FILE)>="R")
		$DATA_FILE_NAME = $URL_DATA_FILE;

	if ($DATA_FILE_NAME == '')
		$arSetupErrors[] = GetMessage("CATI_NO_DATA_FILE");

	if (empty($arSetupErrors))
	{
		$IBLOCK_ID = (int)$IBLOCK_ID;
		$arIBlock = array();
		if ($IBLOCK_ID <= 0)
		{
			$arSetupErrors[] = GetMessage("CATI_NO_IBLOCK");
		}
		else
		{
			$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
			if (false === $arIBlock)
			{
				$arSetupErrors[] = GetMessage("CATI_NO_IBLOCK");
			}
		}
	}

	if (empty($arSetupErrors))
	{
		if (!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, 'iblock_admin_display'))
			$arSetupErrors[] = GetMessage("CATI_NO_IBLOCK_RIGHTS");
	}

	if (!empty($arSetupErrors))
	{
		$STEP = 1;
	}
}

if (($ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY') && $STEP == 2)
{
	if (isset($arOldSetupVars['fields_type']))
		$fields_type = $arOldSetupVars['fields_type'];
	if (isset($arOldSetupVars['delimiter_r']))
		$delimiter_r = $arOldSetupVars['delimiter_r'];
	if (isset($arOldSetupVars['delimiter_r_char']))
		$delimiter_r_char = $arOldSetupVars['delimiter_r_char'];
	if (isset($arOldSetupVars['delimiter_other_r']))
		$delimiter_other_r = $arOldSetupVars['delimiter_other_r'];
	if (isset($arOldSetupVars['first_names_r']))
		$first_names_r = $arOldSetupVars['first_names_r'];
	if (isset($arOldSetupVars['first_names_f']))
		$first_names_f = $arOldSetupVars['first_names_f'];
	if (isset($arOldSetupVars['metki_f']))
		$metki_f = $arOldSetupVars['metki_f'];
}

if ($STEP > 2)
{
	$csvFile = new CCSVData();
	$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

	if ($fields_type != "F" && $fields_type != "R")
		$arSetupErrors[] = GetMessage("CATI_NO_FILE_FORMAT");

	$arDataFileFields = array();
	if (empty($arSetupErrors))
	{
		$fields_type = (($fields_type == "F") ? "F" : "R" );

		$csvFile->SetFieldsType($fields_type);

		if (isset($first_names) && !empty($first_names))
		{
			$first_names_r = $first_names;
			$first_names_f = $first_names;
		}

		if ($fields_type == "R")
		{
			$first_names_r = ($first_names_r == "Y" ? "Y" : "N");
			$csvFile->SetFirstHeader(($first_names_r == "Y") ? true : false);

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
				$arSetupErrors[] = GetMessage("CATI_NO_DELIMITER");

			if (empty($arSetupErrors))
			{
				$csvFile->SetDelimiter($delimiter_r_char);
			}
		}
		else
		{
			$first_names_f = ($first_names_f == "Y" ? "Y" : "N");
			$csvFile->SetFirstHeader(($first_names_f == "Y") ? true : false);

			if ($metki_f == '')
				$arSetupErrors[] = GetMessage("CATI_NO_METKI");

			if (empty($arSetupErrors))
			{
				$arMetkiTmp = preg_split("/[\D]/i", $metki_f);

				$arMetki = array();
				for ($i = 0, $intCount = count($arMetkiTmp); $i < $intCount; $i++)
				{
					$arMetkiTmp[$i] = intval($arMetkiTmp[$i]);
					if (0 < $arMetkiTmp[$i])
					{
						$arMetki[] = $arMetkiTmp[$i];
					}
				}

				if (!is_array($arMetki) || count($arMetki)<1)
					$arSetupErrors[] = GetMessage("CATI_NO_METKI");

				if (empty($arSetupErrors))
				{
					$csvFile->SetWidthMap($arMetki);
				}
			}
		}

		if (empty($arSetupErrors))
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
				$arSetupErrors[] = GetMessage("CATI_NO_DATA");
			}
			$NUM_FIELDS = count($arDataFileFields);
		}
	}

	if (!empty($arSetupErrors))
	{
		$STEP = 2;
	}
}

if (($ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY') && $STEP == 3)
{
	if (isset($arOldSetupVars['IBLOCK_ID']) && $IBLOCK_ID == $arOldSetupVars['IBLOCK_ID'])
	{
		for ($i = 0, $intCountDataFileFields = count($arDataFileFields); $i < $intCountDataFileFields; $i++)
		{
			if (isset($arOldSetupVars['field_'.$i]))
				${'field_'.$i} = $arOldSetupVars['field_'.$i];
		}
		if (isset($arOldSetupVars['USE_TRANSLIT']))
			$USE_TRANSLIT = $arOldSetupVars['USE_TRANSLIT'];
		if (isset($arOldSetupVars['TRANSLIT_LANG']))
			$TRANSLIT_LANG = $arOldSetupVars['TRANSLIT_LANG'];
		if (isset($arOldSetupVars['USE_UPDATE_TRANSLIT']))
			$USE_UPDATE_TRANSLIT = $arOldSetupVars['USE_UPDATE_TRANSLIT'];
	}
	if (isset($arOldSetupVars['PATH2IMAGE_FILES']))
		$PATH2IMAGE_FILES = $arOldSetupVars['PATH2IMAGE_FILES'];
	if (isset($arOldSetupVars['IMAGE_RESIZE']))
		$IMAGE_RESIZE = $arOldSetupVars['IMAGE_RESIZE'];
	if (isset($arOldSetupVars['outFileAction']))
		$outFileAction = $arOldSetupVars['outFileAction'];
	if (isset($arOldSetupVars['inFileAction']))
		$inFileAction = $arOldSetupVars['inFileAction'];
	if (isset($arOldSetupVars['CLEAR_EMPTY_PRICE']))
		$CLEAR_EMPTY_PRICE = $arOldSetupVars['CLEAR_EMPTY_PRICE'];
	if (isset($arOldSetupVars['CML2_LINK_IS_XML']))
		$CML2_LINK_IS_XML = $arOldSetupVars['CML2_LINK_IS_XML'];
	if (isset($arOldSetupVars['max_execution_time']))
		$max_execution_time = $arOldSetupVars['max_execution_time'];
	if (isset($arOldSetupVars['SETUP_PROFILE_NAME']))
		$SETUP_PROFILE_NAME = $arOldSetupVars['SETUP_PROFILE_NAME'];
}

if ($STEP > 3)
{
	$USE_TRANSLIT = (isset($USE_TRANSLIT) && 'Y' == $USE_TRANSLIT ? 'Y' : 'N');
	$TRANSLIT_LANG = (isset($TRANSLIT_LANG) ? (string)$TRANSLIT_LANG : '');
	$USE_UPDATE_TRANSLIT = (isset($USE_UPDATE_TRANSLIT) && $USE_UPDATE_TRANSLIT == 'N' ? 'N' : 'Y');
	if ('Y' == $USE_TRANSLIT)
	{
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
			$arSetupErrors[] = GetMessage("CATI_CODE_TRANSLIT_LANG_ERR");
		}
	}
	$CLEAR_EMPTY_PRICE = (isset($CLEAR_EMPTY_PRICE) && 'Y' == $CLEAR_EMPTY_PRICE ? 'Y' : 'N');
	$CML2_LINK_IS_XML = (isset($CML2_LINK_IS_XML) && 'Y' == $CML2_LINK_IS_XML ? 'Y' : 'N');
	if (!empty($arSetupErrors))
	{
		$STEP = 3;
	}
}
//********************  END ACTIONS  **********************************//

$aMenu = array(
	array(
		"TEXT"=>GetMessage("CATI_ADM_RETURN_TO_LIST"),
		"TITLE"=>GetMessage("CATI_ADM_RETURN_TO_LIST_TITLE"),
		"LINK"=>"/bitrix/admin/cat_import_setup.php?lang=".LANGUAGE_ID,
		"ICON"=>"btn_list",
	)
);

$context = new CAdminContextMenu($aMenu);

$context->Show();

if (!empty($arSetupErrors))
	ShowError(implode('<br>', $arSetupErrors));

$actionParams = "";
if ($adminSidePanelHelper->isSidePanel())
{
	$actionParams = "?IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER";
}
?>
<!--suppress JSUnresolvedVariable -->
<form method="POST" action="<? echo $APPLICATION->GetCurPage().$actionParams; ?>" ENCTYPE="multipart/form-data" name="dataload">
<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("CAT_ADM_CSV_IMP_TAB1"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_CSV_IMP_TAB1_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("CAT_ADM_CSV_IMP_TAB2"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_CSV_IMP_TAB2_TITLE")),
	array("DIV" => "edit3", "TAB" => GetMessage("CAT_ADM_CSV_IMP_TAB3"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_CSV_IMP_TAB3_TITLE")),
	array("DIV" => "edit4", "TAB" => GetMessage("CAT_ADM_CSV_IMP_TAB4"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_CSV_IMP_TAB4_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$tabControl->Begin();

$tabControl->BeginNextTab();

if ($STEP == 1)
{
	?><tr class="heading">
		<td colspan="2"><? echo GetMessage("CATI_DATA_LOADING"); ?></td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CATI_DATA_FILE_SITE"); ?>:</td>
		<td valign="top" width="60%">
			<input type="text" name="URL_DATA_FILE" size="40" value="<? echo htmlspecialcharsbx($URL_DATA_FILE); ?>">
			<input type="button" value="<? echo GetMessage("CATI_BUTTON_CHOOSE")?>" onclick="cmlBtnSelectClick();"><?
CAdminFileDialog::ShowScript(
	array(
		"event" => "cmlBtnSelectClick",
		"arResultDest" => array("FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "URL_DATA_FILE"),
		"arPath" => array("PATH" => "/upload/catalog", "SITE" => SITE_ID),
		"select" => 'F',// F - file only, D - folder only, DF - files & dirs
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'csv',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
		?></td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CATI_INFOBLOCK"); ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($IBLOCK_ID))
				$IBLOCK_ID = 0;
			echo GetIBlockDropDownListEx(
				$IBLOCK_ID,
				'IBLOCK_TYPE_ID',
				'IBLOCK_ID',
				array('CHECK_PERMISSIONS' => 'Y','MIN_PERMISSION' => 'W'),
				"",
				"",
				'class="adm-detail-iblock-types"',
				'class="adm-detail-iblock-list"'
			);
		?></td>
	</tr>
	<?
}

$tabControl->EndTab();

$tabControl->BeginNextTab();

if ($STEP == 2)
{
	?><tr class="heading">
		<td colspan="2"><? echo GetMessage("CATI_CHOOSE_APPR_FORMAT"); ?></td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage('CAT_ADM_CSV_IMP_FIELD_FORMAT'); ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($fields_type) || ('R' != $fields_type && 'F' != $fields_type))
				$fields_type = 'R';
			?><input type="radio" name="fields_type" id="id_fields_type_r" value="R" <? if ($fields_type=="R") echo "checked"; ?> onClick="ChangeExtra(this);"><label for="id_fields_type_r"><? echo GetMessage("CATI_RAZDELITEL"); ?></label><br>
			<input type="radio" name="fields_type" id="id_fields_type_f" value="F" <? if ($fields_type=="F") echo "checked"; ?> onClick="ChangeExtra(this);"><label for="id_fields_type_f"><? echo GetMessage("CATI_FIXED"); ?></label>
			<script type="text/javascript">
			function ChangeExtra(obj)
			{
				if (!obj)
					return;
				if (obj.id == 'id_fields_type_r' || obj.id == 'id_fields_type_f')
				{
					BX.style(BX('type_r_razdel_ttl'), 'display', (obj.id == 'id_fields_type_r' && obj.checked ? 'block' : 'none'));
					BX.style(BX('type_r_razdel_fld'), 'display', (obj.id == 'id_fields_type_r' && obj.checked ? 'block' : 'none'));
					BX.style(BX('type_f_metki_ttl'), 'display', (obj.id == 'id_fields_type_f' && obj.checked ? 'block' : 'none'));
					BX.style(BX('type_f_metki_fld'), 'display', (obj.id == 'id_fields_type_f' && obj.checked ? 'block' : 'none'));
				}
			}

			</script>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%">
			<div id="type_r_razdel_ttl" style="display: <? echo ('R' == $fields_type ? 'block' : 'none'); ?>;"><? echo GetMessage("CATI_RAZDEL_TYPE"); ?>:</div>
			<div id="type_f_metki_ttl" style="display: <? echo ('F' == $fields_type ? 'block' : 'none'); ?>;"><? echo GetMessage("CATI_FIX_MET"); ?>:<br /><small><? echo GetMessage("CATI_FIX_MET_DESCR"); ?></small></div>
		</td>
		<td valign="top" width="60%">
			<div id="type_r_razdel_fld" style="display: <? echo ('R' == $fields_type ? 'block' : 'none'); ?>;"><?
			if (!isset($delimiter_r) || empty($delimiter_r))
				$delimiter_r = 'TZP';
				?><input type="radio" name="delimiter_r" value="TZP" <? if ($delimiter_r=="TZP") echo "checked"; ?>><? echo GetMessage("CATI_TZP"); ?><br>
				<input type="radio" name="delimiter_r" value="ZPT" <? if ($delimiter_r=="ZPT") echo "checked"; ?>><? echo GetMessage("CATI_ZPT"); ?><br>
				<input type="radio" name="delimiter_r" value="TAB" <? if ($delimiter_r=="TAB") echo "checked"; ?>><? echo GetMessage("CATI_TAB"); ?><br>
				<input type="radio" name="delimiter_r" value="SPS" <? if ($delimiter_r=="SPS") echo "checked"; ?>><? echo GetMessage("CATI_SPS"); ?><br>
				<input type="radio" name="delimiter_r" value="OTR" <? if ($delimiter_r=="OTR") echo "checked"; ?>><? echo GetMessage("CATI_OTR"); ?>
				<input type="text" name="delimiter_other_r" size="3" value="<? echo htmlspecialcharsbx($delimiter_other_r); ?>">
			</div>
			<div id="type_f_metki_fld" style="display: <? echo ('F' == $fields_type ? 'block' : 'none'); ?>;"><?
				if (!isset($metki_f))
					$metki_f = '';
				?><textarea name="metki_f" rows="7" cols="3"><? echo htmlspecialcharsbx($metki_f); ?></textarea>
			</div>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CATI_FIRST_NAMES"); ?>:</td>
		<td valign="top" width="60%"><?
			$first_names = '';
			if ('R' == $fields_type)
			{
				if (isset($first_names_r))
					$first_names = $first_names_r;
			}
			else
			{
				if (isset($first_names_f))
					$first_names = $first_names_f;
			}
		?><input type="hidden" name="first_names" value="N"><input type="checkbox" name="first_names" value="Y" <? if ('Y' == $first_names) echo "checked"; ?>></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage("CATI_DATA_SAMPLES"); ?></td>
	</tr>
	<tr>
		<td valign="top" align="center" colspan="2"><?
			$sContent = '';
			$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, "rb");
			$sContent = fread($file_id, 10000);
			fclose($file_id);
			if ($sContent != '')
			{
				$key = mb_strrpos($sContent, "\r\n");
				if ($key === false)
					$key = mb_strrpos($sContent, "\n");
				if ($key !== false)
					$sContent = mb_substr($sContent, 0, $key);
				unset($key);
			}
			?><textarea name="data" rows="7" cols="90"><? echo htmlspecialcharsbx($sContent); ?></textarea>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();

$tabControl->BeginNextTab();

if ($STEP == 3)
{
	?><tr class="heading">
		<td colspan="2"><? echo GetMessage("CATI_FIELDS_SOOT"); ?></td>
	</tr>
	<?
	$boolCatalog = false;
	$boolOffers = false;
	$rsCatalogs = CCatalog::GetList(
		array(),
		array('IBLOCK_ID' => $IBLOCK_ID),
		false,
		false,
		array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID')
	);
	if ($arCatalog = $rsCatalogs->Fetch())
	{
		$boolCatalog = true;
		$boolOffers = ((int)$arCatalog['PRODUCT_IBLOCK_ID'] > 0);
	}

	$arAvailFields = array();
	$intCount = 0;
	$boolSep = true;
	$strVal = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailProdFields);
	$arVal = explode(",", $strVal);
	foreach ($arCatalogAvailProdFields as &$arOneCatalogAvailProdFields_tmp)
	{
		$mxKey = array_search($arOneCatalogAvailProdFields_tmp['value'],$arVal);
		if (false !== $mxKey)
		{
			$arAvailFields[$intCount] = array(
				"value"=>$arOneCatalogAvailProdFields_tmp["value"],
				"name"=>$arOneCatalogAvailProdFields_tmp["name"],
			);
			if ('IE_XML_ID' == $arOneCatalogAvailProdFields_tmp["value"] || 'IE_NAME' == $arOneCatalogAvailProdFields_tmp["value"])
				$arAvailFields[$intCount]['STYLE'] = 'background-color:#FFCCCC;';
			if ($boolSep)
			{
				$arAvailFields[$intCount]['SEP'] = GetMessage('CAT_ADM_CSV_IMP_SEP_ELEMENTS');
				$boolSep = false;
			}
			$intCount++;
		}
	}
	if (isset($arOneCatalogAvailProdFields_tmp))
		unset($arOneCatalogAvailProdFields_tmp);

	$properties = CIBlockProperty::GetList(array("SORT"=>"ASC", "NAME" => "ASC", "ID"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID, "ACTIVE"=>"Y", 'CHECK_PERMISSIONS' => 'N'));
	while ($prop_fields = $properties->Fetch())
	{
		$arAvailFields[$intCount] = array(
			"value"=>"IP_PROP".$prop_fields["ID"],
			"name"=>GetMessage("CATI_FI_PROPS").' "'.$prop_fields["NAME"].'"'.' ['.(''!= trim($prop_fields["CODE"]) ? $prop_fields["CODE"] : $prop_fields["ID"]).']',
		);
		if ($boolSep)
		{
			$arAvailFields[$intCount]['SEP'] = GetMessage('CAT_ADM_CSV_IMP_SEP_ELEMENTS');
			$boolSep = false;
		}
		$intCount++;
	}

	$boolSep = true;
	$strVal = COption::GetOptionString("catalog", "allowed_group_fields", $defCatalogAvailGroupFields);
	$arVal = explode(",", $strVal);
	for ($k_old = -1, $k = 0; $k < $NUM_CATALOG_LEVELS; $k++)
	{
		$strLevel = ' - '.str_replace('#LEVEL#', ($k+1), GetMessage('CAT_ADM_CSV_IMP_SECT_LEVEL'));
		foreach ($arCatalogAvailGroupFields as $arOnerCatalogAvailGroupFields)
		{
			$mxKey = array_search($arOnerCatalogAvailGroupFields['value'],$arVal);
			if (false !== $mxKey)
			{
				$arAvailFields[$intCount] = array(
					"value"=>$arOnerCatalogAvailGroupFields["value"].$k,
					"name"=> $arOnerCatalogAvailGroupFields["name"].$strLevel,
				);
				if ($boolSep)
				{
					$arAvailFields[$intCount]['SEP'] = GetMessage('CAT_ADM_CSV_IMP_SEP_SECTIONS');
					$boolSep = false;
				}
				if ($k_old != $k)
				{
					$arAvailFields[$intCount]['SUB_SEP'] = str_replace('#LEVEL#',($k+1),GetMessage("CAT_ADM_CSV_IMP_SECTION_LEVEL"));
					$k_old = $k;
				}
				$intCount++;
			}
		}
		if (isset($arOnerCatalogAvailGroupFields))
			unset($arOnerCatalogAvailGroupFields);
/*		if (!empty($arSectionProps))
		{
			foreach ($arSectionProps as &$arOneSectionProp)
			{
				$arAvailFields[$intCount] = array(
					"value" => 'SP_'.$arOneSectionProp['FIELD_NAME'].'_'.$k,
					"name" => GetMessage('CAT_ADM_CSV_IMP_DESCR_SECT_PROP').' "'.($arOneSectionProp['EDIT_FORM_LABEL'] ? $arOneSectionProp['EDIT_FORM_LABEL'] : $arOneSectionProp['FIELD_NAME']).'"',
				);
				if ($boolSep)
				{
					$arAvailFields[$intCount]['SEP'] = GetMessage('CAT_ADM_CSV_IMP_SEP_SECTIONS');
					$boolSep = false;
				}
				if ($k_old != $k)
				{
					$arAvailFields[$intCount]['SUB_SEP'] = str_replace('#LEVEL#',($k+1),GetMessage("CAT_ADM_CSV_IMP_SECTION_LEVEL"));
					$k_old = $k;
				}
				$intCount++;
			}
			if (isset($arOneSectionProp))
				unset($arOneSectionProp);
		} */
	}

	if ($boolCatalog)
	{
		$boolUseStoreControl = Catalog\Config\State::isUsedInventoryManagement();
		$arDisableFields = array(
			'CP_QUANTITY' => true,
			'CP_PURCHASING_PRICE' => true,
			'CP_PURCHASING_CURRENCY' => true,
		);
		$boolSep = true;
		$strVal = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailPriceFields);
		$arVal = explode(",", $strVal);
		foreach ($arCatalogAvailPriceFields as $arOneCatalogAvailProdFields_tmp)
		{
			$mxKey = array_search($arOneCatalogAvailProdFields_tmp['value'],$arVal);
			if (false !== $mxKey)
			{
				$arAvailFields[$intCount] = array(
					"value"=>$arOneCatalogAvailProdFields_tmp["value"],
					"name"=>$arOneCatalogAvailProdFields_tmp["name"],
				);
				if ($boolSep)
				{
					$arAvailFields[$intCount]['SEP'] = GetMessage('CAT_ADM_CSV_IMP_SEP_PRODUCT');
					$boolSep = false;
				}
				if ($boolUseStoreControl && array_key_exists($arAvailFields[$intCount]['value'], $arDisableFields))
				{
					$arAvailFields[$intCount]['DISABLE'] = true;
				}
				$intCount++;
			}
		}
		if (isset($arOneCatalogAvailProdFields_tmp))
			unset($arOneCatalogAvailProdFields_tmp);

		$boolSep = true;
		$strVal = $defCatalogAvailQuantityFields;
		$arVal = explode(",", $strVal);
		foreach ($arCatalogAvailQuantityFields as $arOneCatalogAvailQuantityFields)
		{
			$mxKey = array_search($arOneCatalogAvailQuantityFields['value'],$arVal);
			if (false !== $mxKey)
			{
				$arAvailFields[$intCount] = array(
					"value"=>$arOneCatalogAvailQuantityFields["value"],
					"name"=>$arOneCatalogAvailQuantityFields["name"],
				);
				if ($boolSep)
				{
					$arAvailFields[$intCount]['SEP'] = GetMessage('CAT_ADM_CSV_IMP_SEP_PRICES');
					$boolSep = false;
				}
				$intCount++;
			}
		}
		if (isset($arOneCatalogAvailQuantityFields))
			unset($arOneCatalogAvailQuantityFields);

		$strVal = COption::GetOptionString("catalog", "allowed_price_fields", $defCatalogAvailValueFields);
		$arVal = explode(",", $strVal);
		$db_prgr = CCatalogGroup::GetList(array("SORT" => "ASC"), array());
		while ($prgr = $db_prgr->Fetch())
		{
			foreach ($arCatalogAvailValueFields as $arOneCatalogAvailValueFields)
			{
				$mxKey = array_search($arOneCatalogAvailValueFields['value'],$arVal);
				if (false !== $mxKey)
				{
					$strName = ($prgr['NAME_LANG'] ?
						str_replace(array('#TYPE#','#NAME#'),array($prgr["NAME"],$prgr['NAME_LANG']),GetMessage('EST_PRICE_TYPE2')):
						str_replace("#TYPE#", $prgr["NAME"], GetMessage("EST_PRICE_TYPE"))
					);
					$arAvailFields[$intCount] = array(
						"value" => $arOneCatalogAvailValueFields['value']."_".$prgr["ID"],
						"name" => $strName.": ".$arOneCatalogAvailValueFields["name"],
					);
					if ($boolSep)
					{
						$arAvailFields[$intCount]['SEP'] = GetMessage('CAT_ADM_CSV_IMP_SEP_PRICES');
						$boolSep = false;
					}
					$intCount++;
				}
			}
			if (isset($arOneCatalogAvailValueFields))
				unset($arOneCatalogAvailValueFields);
		}
	}
	for ($i = 0, $intCountDataFileFields = count($arDataFileFields); $i < $intCountDataFileFields; $i++)
	{
		?><tr>
			<td width="40%"><b><? echo GetMessage("CATI_FIELD"); ?> <? echo $i+1; ?></b> (<? echo htmlspecialcharsbx(TruncateText($arDataFileFields[$i], 15)); ?>):</td>
			<td width="60%">
				<select name="field_<? echo $i; ?>">
				<option value="" style="font-weight: bold; text-align: center;"> --- </option>
				<?
				foreach ($arAvailFields as $field)
				{
					if (!empty($field['SEP']))
					{
						?><option value="" style="font-weight: bold; text-align: center;">--- <?=htmlspecialcharsbx($field['SEP']); ?> ---</option><?
					}
					if (!empty($field['SUB_SEP']))
					{
						?><option value="" style="font-style: italic; text-align: center;">--- <?=htmlspecialcharsbx($field['SUB_SEP']); ?> ---</option><?
					}
					$strStyle = '';
					if (isset($field['DISABLE']))
						$strStyle .= 'text-decoration: line-through; color: #aaaaaa;';
					if (!empty($field['STYLE']))
						$strStyle .= $field['STYLE'];
					$selected = (${"field_".$i} == $field["value"] || (!isset(${"field_".$i}) && $field["value"]==$arDataFileFields[$i]));
					?><option value="<?=htmlspecialcharsbx($field['value']); ?>" <?=(!empty($strStyle) ? 'style="'.$strStyle.'"' : ''); ?><?=($selected ? ' selected' : ''); ?>><?=htmlspecialcharsbx($field["name"]); ?></option><?
				}
				unset($field);
				?>
				</select>
			</td>
		</tr><?
	}
	?>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage("CATI_ADDIT_SETTINGS"); ?></td>
	</tr>
	<tr>
		<td width="40%"><? echo GetMessage("CATI_IMG_PATH"); ?>:</td>
		<td width="60%">
			<input type="text" name="PATH2IMAGE_FILES" size="40" value="<?echo htmlspecialcharsbx($PATH2IMAGE_FILES); ?>"><br>
			<small><?echo GetMessage("CATI_IMG_PATH_DESCR") ?></small>
		</td>
	</tr>
	<tr>
		<td width="40%"><label for="IMAGE_RESIZE_Y"><? echo GetMessage("CATI_IMG_RESIZE"); ?></label>:</td>
		<td width="60%">
			<input type="hidden" name="IMAGE_RESIZE" id="IMAGE_RESIZE_N" value="N">
			<input type="checkbox" name="IMAGE_RESIZE" id="IMAGE_RESIZE_Y" value="Y" <? echo (isset($IMAGE_RESIZE) && 'Y' == $IMAGE_RESIZE ? "checked": ""); ?>>
		</td>
	</tr>
	<?
	$USE_TRANSLIT = (isset($USE_TRANSLIT) && $USE_TRANSLIT == 'Y' ? 'Y' : 'N');
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
		$USE_TRANSLIT = 'N';
	?>
	<tr>
		<td width="40%"><label for="USE_TRANSLIT_Y"><? echo GetMessage('CATI_USE_CODE_TRANSLIT'); ?></label>:</td>
		<td width="60%">
			<input type="hidden" name="USE_TRANSLIT" id="USE_TRANSLIT_N" value="N"><?
			if ($boolOutTranslit)
			{
				echo GetMessage('CATI_USE_CODE_TRANSLIT_OUT');
			}
			else
			{
				?><input type="checkbox" name="USE_TRANSLIT" id="USE_TRANSLIT_Y" value="Y" <? echo (isset($USE_TRANSLIT) && 'Y' == $USE_TRANSLIT ? ' checked' : ''); ?>><?
			}
			?>
		</td>
	</tr>
	<?
	if (!isset($TRANSLIT_LANG) || empty($TRANSLIT_LANG))
		$TRANSLIT_LANG = LANGUAGE_ID;
	if (!isset($USE_UPDATE_TRANSLIT) || $USE_UPDATE_TRANSLIT != 'N')
		$USE_UPDATE_TRANSLIT = 'Y';
	if ($boolOutTranslit)
	{
		?><input type="hidden" name="TRANSLIT_LANG" value="<?=htmlspecialcharsbx($TRANSLIT_LANG); ?>"><?
		?><input type="hidden" name="USE_UPDATE_TRANSLIT" value="<?=htmlspecialcharsbx($USE_UPDATE_TRANSLIT); ?>"><?
	}
	else
	{
		?><tr id="tr_TRANSLIT_LANG" style="display: <?=($USE_TRANSLIT == 'Y' ? 'table-row' : 'none'); ?>;">
			<td width="40%"><? echo GetMessage('CATI_CODE_TRANSLIT_LANG'); ?>:</td>
			<td width="60%">
				<? echo CLanguage::SelectBox('TRANSLIT_LANG', $TRANSLIT_LANG); ?>
			</td>
		</tr>
		<tr id="tr_USE_UPDATE_TRANSLIT" style="display: <?=($USE_TRANSLIT == 'Y' ? 'table-row' : 'none'); ?>;">
			<td width="40%"><? echo GetMessage('CATI_CODE_TRANSLIT_FOR_UPDATE'); ?>:</td>
			<td width="60%">
				<input type="hidden" name="USE_UPDATE_TRANSLIT" id="USE_UPDATE_TRANSLIT_N" value="N">
				<input type="checkbox" name="USE_UPDATE_TRANSLIT" id="USE_UPDATE_TRANSLIT_Y" value="Y"<?=($USE_UPDATE_TRANSLIT == 'Y' ? ' checked' : ''); ?>>
			</td>
		</tr><?
	}
	?>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CATI_OUTFILE"); ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($outFileAction) || empty($outFileAction) || !in_array($outFileAction, array('H', 'D', 'M', 'F')))
				$outFileAction = 'F';
			?><input type="radio" name="outFileAction" value="H" <?if ($outFileAction=="H") echo "checked";?>> <? echo GetMessage("CATI_OF_DEACT"); ?><br>
			<input type="radio" name="outFileAction" value="D" <?if ($outFileAction=="D") echo "checked";?>> <? echo GetMessage("CATI_OF_DEL"); ?><br>
			<input type="radio" name="outFileAction" value="M" <?if ($outFileAction=="M") echo "checked";?>> <? echo GetMessage("CATI_OF_CAN_BUY"); ?> <span class="required">*</span><br>
			<input type="radio" name="outFileAction" value="F" <?if ($outFileAction=="F") echo "checked";?>> <? echo GetMessage("CATI_OF_KEEP"); ?>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CATI_INACTIVE_PRODS"); ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($inFileAction) || 'A' != $inFileAction)
				$inFileAction = 'F';
			?><input type="radio" name="inFileAction" value="F" <?if ($inFileAction=="F") echo "checked";?>> <?echo GetMessage("CATI_KEEP_AS_IS");?><br>
			<input type="radio" name="inFileAction" value="A" <?if ($inFileAction=="A") echo "checked";?>> <?echo GetMessage("CATI_ACTIVATE_PROD");?>
		</td>
	</tr>
	<tr>
		<td width="40%"><? echo GetMessage('CATI_CLEAR_EMPTY_PRICE'); ?>:</td>
		<td width="60%"><?
			if (!isset($CLEAR_EMPTY_PRICE) || 'Y' != $CLEAR_EMPTY_PRICE)
				$CLEAR_EMPTY_PRICE = 'N';
			?><input type="hidden" name="CLEAR_EMPTY_PRICE" value="N">
			<input type="checkbox" name="CLEAR_EMPTY_PRICE" value="Y"<? echo ('Y' == $CLEAR_EMPTY_PRICE ? ' checked' : ''); ?>>
		</td>
	</tr><?
	if (!isset($CML2_LINK_IS_XML))
		$CML2_LINK_IS_XML = 'N';
	if ($boolOffers)
	{
	?>
	<tr>
		<td width="40%"><? echo GetMessage('CATI_CML2_LINK_IS_XML'); ?>:</td>
		<td width="60%">
			<input type="hidden" name="CML2_LINK_IS_XML" value="N">
			<input type="checkbox" name="CML2_LINK_IS_XML" value="Y" <? echo ('Y' == $CML2_LINK_IS_XML ? 'checked' : ''); ?>>
		</td>
	</tr>
	<?
	}
	?><tr>
		<td width="40%"><? echo GetMessage("CATI_AUTO_STEP_TIME"); ?>:</td>
		<td width="60%">
			<input type="text" name="max_execution_time" size="40" value="<? echo intval($max_execution_time); ?>"><br>
			<small><?echo GetMessage("CATI_AUTO_STEP_TIME_NOTE");?></small>
		</td>
	</tr>
	<?
	if ($ACTION=="IMPORT_SETUP" || $ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY')
	{
	?><tr class="heading">
		<td colspan="2"><? echo GetMessage("CATI_IMPORT_SCHEME_NAME"); ?></td>
	</tr>
	<tr>
		<td width="40%"><? echo GetMessage("CATI_IMPORT_SCHEME_NAME"); ?>:</td>
		<td width="60%">
			<input type="text" name="SETUP_PROFILE_NAME" size="40" value="<? echo htmlspecialcharsbx($SETUP_PROFILE_NAME); ?>">
		</td>
	</tr><?
	}
	?>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage("CATI_DATA_SAMPLES"); ?></td>
	</tr>
	<tr>
		<td valign="top" align="center" colspan="2"><?
			$sContent = '';
			$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, "rb");
			$sContent = fread($file_id, 10000);
			fclose($file_id);
			if ($sContent != '')
			{
				$key = mb_strrpos($sContent, "\r\n");
				if ($key === false)
					$key = mb_strrpos($sContent, "\n");
				if ($key !== false)
					$sContent = mb_substr($sContent, 0, $key);
				unset($key);
			}
			?><textarea name="data" rows="7" cols="90"><? echo htmlspecialcharsbx($sContent); ?></textarea>
		</td>
	</tr><?
}

$tabControl->EndTab();

$tabControl->BeginNextTab();

if ($STEP == 4)
{
	$FINITE = true;
}

$tabControl->EndTab();

$tabControl->Buttons();

?><? echo bitrix_sessid_post();?><?

if ($ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY')
{
	?><input type="hidden" name="PROFILE_ID" value="<? echo intval($PROFILE_ID); ?>"><?
}

if ($STEP < 4)
{
	?><input type="hidden" name="STEP" value="<? echo intval($STEP) + 1; ?>">
	<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
	<input type="hidden" name="ACT_FILE" value="<? echo htmlspecialcharsbx($_REQUEST["ACT_FILE"]); ?>">
	<input type="hidden" name="ACTION" value="<? echo htmlspecialcharsbx($ACTION); ?>">
	<?
	if ($STEP > 1)
	{
		?><input type="hidden" name="IBLOCK_ID" value="<? echo intval($IBLOCK_ID); ?>">
		<input type="hidden" name="URL_DATA_FILE" value="<? echo htmlspecialcharsbx($DATA_FILE_NAME); ?>"><?
	}
	if ($STEP > 2)
	{
		?><input type="hidden" name="fields_type" value="<?echo htmlspecialcharsbx($fields_type); ?>"><?
		if ($fields_type == "R")
		{
			?><input type="hidden" name="delimiter_r" value="<? echo htmlspecialcharsbx($delimiter_r); ?>">
			<input type="hidden" name="delimiter_other_r" value="<? echo htmlspecialcharsbx($delimiter_other_r); ?>">
			<input type="hidden" name="first_names_r" value="<? echo htmlspecialcharsbx($first_names_r); ?>"><?
		}
		else
		{
			?><input type="hidden" name="metki_f" value="<? echo htmlspecialcharsbx($metki_f); ?>">
			<input type="hidden" name="first_names_f" value="<?echo htmlspecialcharsbx($first_names_f) ?>"><?
		}
		$arfieldsString = array(
			'IBLOCK_ID',
			'URL_DATA_FILE',
			'fields_type',
			'delimiter_r',
			'delimiter_other_r',
			'first_names_r',
			'metki_f',
			'first_names_f',
			'PATH2IMAGE_FILES',
			'USE_TRANSLIT',
			'TRANSLIT_LANG',
			'USE_UPDATE_TRANSLIT',
			'IMAGE_RESIZE',
			'outFileAction',
			'inFileAction',
			'max_execution_time',
			'CLEAR_EMPTY_PRICE',
			'CML2_LINK_IS_XML'
		);
		for ($i = 0, $intCountDataFileFields = count($arDataFileFields); $i < $intCountDataFileFields; $i++)
		{
			$arfieldsString[] = 'field_'.$i;
		}
		?><input type="hidden" name="SETUP_FIELDS_LIST" value="<? echo implode(',',$arfieldsString); ?>"><?
	}
	if ($STEP > 1)
	{
		?><input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("CATI_BACK") ?>"><?
	}
	?><input type="submit" value="<? echo ($STEP==3) ? (($ACTION=="IMPORT") ? GetMessage("CATI_NEXT_STEP_F") : GetMessage("CICML_SAVE")) : GetMessage("CATI_NEXT_STEP")." &gt;&gt;" ?>" name="submit_btn"><?
}

$tabControl->End();

if (3 == $STEP)
{
	echo BeginNote();
	?><span class="required">*</span> <? echo GetMessage("CATI_OF_CAN_BUY_DESCR"); ?><?
	echo EndNote();
}

?></form>
<script type="text/javascript">
<?if ($STEP < 2):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
tabControl.DisableTab("edit4");
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");
tabControl.DisableTab("edit4");
<?elseif ($STEP == 3):?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit4");
<?elseif ($STEP == 4):?>
tabControl.SelectTab("edit4");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
<?endif;?>
function showTranslitSettings()
{
	var useTranslit = BX('USE_TRANSLIT_Y'),
		translitLang = BX('tr_TRANSLIT_LANG'),
		translitUpdate = BX('tr_USE_UPDATE_TRANSLIT');
	if (!BX.type.isElementNode(useTranslit) || !BX.type.isElementNode(translitLang) || !BX.type.isElementNode(translitUpdate))
		return;
	BX.style(translitLang, 'display', (useTranslit.checked ? 'table-row' : 'none'));
	BX.style(translitUpdate, 'display', (useTranslit.checked ? 'table-row' : 'none'));
}
BX.ready(function(){
	var useTranslit = BX('USE_TRANSLIT_Y'),
		translitLang = BX('tr_TRANSLIT_LANG'),
		translitUpdate = BX('tr_USE_UPDATE_TRANSLIT');
	if (BX.type.isElementNode(useTranslit) && BX.type.isElementNode(translitLang) && BX.type.isElementNode(translitUpdate))
		BX.bind(useTranslit, 'click', showTranslitSettings);
});
</script>