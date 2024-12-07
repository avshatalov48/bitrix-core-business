<?php
//<title>CommerceML MySql Fast - BETA VERS</title>
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/import_setup_templ.php');

$arSetupErrors = array();

//********************  ACTIONS  **************************************//
if (($ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY') && $STEP == 1)
{
	if (isset($arOldSetupVars['URL_FILE_1C']))
		$URL_FILE_1C = $arOldSetupVars['URL_FILE_1C'];
	if (isset($arOldSetupVars['IBLOCK_TYPE_ID']))
		$IBLOCK_TYPE_ID = $arOldSetupVars['IBLOCK_TYPE_ID'];
	if (isset($arOldSetupVars['keepExistingProperties']))
		$keepExistingProperties = $arOldSetupVars['keepExistingProperties'];
	if (isset($arOldSetupVars['keepExistingData']))
		$keepExistingData = $arOldSetupVars['keepExistingData'];
	if (isset($arOldSetupVars['activateFileData']))
		$activateFileData = $arOldSetupVars['activateFileData'];
	if (isset($arOldSetupVars['deleteComments']))
		$deleteComments = $arOldSetupVars['deleteComments'];
	if (isset($arOldSetupVars['cmlDebug']))
		$cmlDebug = $arOldSetupVars['cmlDebug'];
	if (isset($arOldSetupVars['cmlMemoryDebug']))
		$cmlMemoryDebug = $arOldSetupVars['cmlMemoryDebug'];
	if (isset($arOldSetupVars['SETUP_PROFILE_NAME']))
		$SETUP_PROFILE_NAME = $arOldSetupVars['SETUP_PROFILE_NAME'];
	if (isset($arOldSetupVars['USE_TRANSLIT']))
		$USE_TRANSLIT = $arOldSetupVars['USE_TRANSLIT'];
	if (isset($arOldSetupVars['ADD_TRANSLIT']))
		$ADD_TRANSLIT = $arOldSetupVars['ADD_TRANSLIT'];
}
if ($STEP > 1)
{
	$DATA_FILE_NAME = "";

	if ($URL_FILE_1C <> '' && file_exists($_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C))
		$DATA_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C;

	if ($DATA_FILE_NAME == '')
	{
		$arSetupErrors[] = GetMessage("CICML_ERROR_NO_DATAFILE");
	}

	if ($IBLOCK_TYPE_ID == '')
	{
		$arSetupErrors[] = GetMessage("CICML_ERROR_NO_IBLOCKTYPE");
	}

	$USE_TRANSLIT = (isset($USE_TRANSLIT) && 'Y' == $USE_TRANSLIT ? 'Y' : 'N');
	$ADD_TRANSLIT = (isset($ADD_TRANSLIT) && 'Y' == $ADD_TRANSLIT ? 'Y' : 'N');

	if (!empty($arSetupErrors))
	{
		$STEP = 1;
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
	ShowError(implode('<br />', $arSetupErrors));

$actionParams = "";
if ($adminSidePanelHelper->isSidePanel())
{
	$actionParams = "?IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER";
}
?>
<form method="POST" action="<? echo $APPLICATION->GetCurPage().$actionParams; ?>" ENCTYPE="multipart/form-data" name="dataload">
<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("CAT_ADM_CML1_IMP_TAB1"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_CML1_IMP_TAB1_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("CAT_ADM_CML1_IMP_TAB2"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_CML1_IMP_TAB2_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);

$tabControl->Begin();

$tabControl->BeginNextTab();

if ($STEP == 1)
{
	?><tr class="heading">
		<td colspan="2"><? echo GetMessage("CICML_DATA_IMPORT"); ?></td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CICML_F_DATAFILE2");?></td>
		<td valign="top" width="60%">
			<input type="text" name="URL_FILE_1C" size="40" value="<?= htmlspecialcharsbx($URL_FILE_1C) ?>">
			<input type="button" value="<? echo GetMessage("CML_S_SELECT"); ?>" onclick="cmlBtnSelectClick()">
<?
CAdminFileDialog::ShowScript(
	array(
		"event" => "cmlBtnSelectClick",
		"arResultDest" => array("FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "URL_FILE_1C"),
		"arPath" => array("PATH" => "/upload/catalog", "SITE" => SITE_ID),
		"select" => 'F',// F - file only, D - folder only, DF - files & dirs
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'xml',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
		?></td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CICML_F_IBLOCK");?></td>
		<td valign="top" width="60%">
			<select name="IBLOCK_TYPE_ID" class="adm-detail-iblock-types"><?
				if (!isset($IBLOCK_TYPE_ID))
					$IBLOCK_TYPE_ID = '';
				?><option value="">- <?echo GetMessage("CICML_F_IBLOCK_SELECT") ?> -</option><?
				$rsIBlockTypes = CIBlockType::GetList(array('ID' => 'ASC'));
				while ($arIBlockType = $rsIBlockTypes->Fetch())
				{
					if($arIBLang = CIBlockType::GetByIDLang($arIBlockType["ID"], LANGUAGE_ID))
					{
						?><option value="<? echo htmlspecialcharsbx($arIBlockType['ID']); ?>"<? echo ($arIBlockType['ID'] == $IBLOCK_TYPE_ID ? ' selected' : ''); ?>><? echo htmlspecialcharsex($arIBLang["NAME"]); ?> [<? echo htmlspecialcharsex($arIBlockType['ID']); ?>]</option><?
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage('CATI_ADDIT_SETTINGS'); ?></td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CML_S_KEEP_PRP") ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($keepExistingProperties) || ('N' != $keepExistingProperties && 'Y' != $keepExistingProperties))
			{
				$keepExistingProperties = 'Y';
			}
			?><input type="radio" name="keepExistingProperties" id="keepExistingProperties_N" value="N" <?if ($keepExistingProperties=="N") echo "checked";?>> <label for="keepExistingProperties_N"><? echo GetMessage("CML_S_NO"); ?></label><br>
			<input type="radio" name="keepExistingProperties" id="keepExistingProperties_Y" value="Y" <?if ($keepExistingProperties=="Y") echo "checked";?>> <label for="keepExistingProperties_Y"><? echo GetMessage("CML_S_YES"); ?></label>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CML_S_KEEP_DATA"); ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($keepExistingData) || ('N' != $keepExistingData && 'Y' != $keepExistingData))
			{
				$keepExistingData = 'Y';
			}
			?><input type="radio" name="keepExistingData" id="keepExistingData_N" value="N" <?if ($keepExistingData=="N") echo "checked";?>> <label for="keepExistingData_N"><? echo GetMessage("CML_S_NO"); ?></label><br>
			<input type="radio" name="keepExistingData" id="keepExistingData_Y" value="Y" <?if ($keepExistingData=="Y") echo "checked";?>> <label for="keepExistingData_Y"><? echo GetMessage("CML_S_YES"); ?></label>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CML_S_ACT_DATA"); ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($activateFileData) || ('N' != $activateFileData && 'Y' != $activateFileData))
			{
				$activateFileData = 'Y';
			}
			?><input type="radio" name="activateFileData" id="activateFileData_Y" value="Y" <?if ($activateFileData=="Y") echo "checked";?>> <label for="activateFileData_Y"><? echo GetMessage("CML_S_YES"); ?></label><br>
			<input type="radio" name="activateFileData" id="activateFileData_N" value="N" <?if ($activateFileData=="N") echo "checked";?>> <label for="activateFileData_N"><? echo GetMessage("CML_S_NO"); ?></label>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CML_S_COMMNT"); ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($deleteComments) || ('N' != $deleteComments && 'Y' != $deleteComments))
			{
				$deleteComments = 'N';
			}
			?><input type="radio" name="deleteComments" id="deleteComments_N" value="N" <?if ($deleteComments=="N") echo "checked";?>> <label for="deleteComments_N"><? echo GetMessage("CML_S_NO"); ?></label><br>
			<input type="radio" name="deleteComments" id="deleteComments_Y" value="Y" <?if ($deleteComments=="Y") echo "checked";?>> <label for="deleteComments_Y"><? echo GetMessage("CML_S_YES"); ?></label>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CML_S_FDEB") ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($cmlDebug) || ('N' != $cmlDebug && 'Y' != $cmlDebug))
			{
				$cmlDebug = 'N';
			}
			?><input type="radio" name="cmlDebug" id="cmlDebug_N" value="N" <?if ($cmlDebug=="N") echo "checked";?>> <label for="cmlDebug_N"><? echo GetMessage("CML_S_NO"); ?></label><br>
			<input type="radio" name="cmlDebug" id="cmlDebug_Y" value="Y" <?if ($cmlDebug=="Y") echo "checked";?>> <label for="cmlDebug_Y"><? echo GetMessage("CML_S_YES"); ?></label>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage("CML_S_MEMDEB"); ?>:</td>
		<td valign="top" width="60%"><?
			if (!isset($cmlMemoryDebug) || ('N' != $cmlMemoryDebug && 'Y' != $cmlMemoryDebug))
			{
				$cmlMemoryDebug = 'N';
			}
			?><input type="radio" name="cmlMemoryDebug" id="cmlMemoryDebug_N" value="N" <?if ($cmlMemoryDebug=="N") echo "checked";?>> <label for="cmlMemoryDebug_N"><? echo GetMessage("CML_S_NO"); ?></label><br>
			<input type="radio" name="cmlMemoryDebug" id="cmlMemoryDebug_Y" value="Y" <?if ($cmlMemoryDebug=="Y") echo "checked";?>> <label for="cmlMemoryDebug_Y"><? echo GetMessage("CML_S_YES"); ?></label>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage('CATI_USE_CODE_TRANSLIT'); ?>:</td>
		<td valign="top" width="60%">
			<input type="hidden" name="USE_TRANSLIT" id="USE_TRANSLIT_N" value="N">
			<input type="checkbox" name="USE_TRANSLIT" id="USE_TRANSLIT_Y" value="Y" <? echo (isset($USE_TRANSLIT) && 'Y' == $USE_TRANSLIT ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td valign="top" width="40%"><? echo GetMessage('CATI_ADD_CODE_TRANSLIT'); ?>:</td>
		<td valign="top" width="60%">
			<input type="hidden" name="ADD_TRANSLIT" id="ADD_TRANSLIT_N" value="N">
			<input type="checkbox" name="ADD_TRANSLIT" id="ADD_TRANSLIT_Y" value="Y" <? echo (isset($ADD_TRANSLIT) && 'Y' == $ADD_TRANSLIT ? ' checked' : ''); ?>>
		</td>
	</tr>
	<?if ($ACTION=="IMPORT_SETUP" || $ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY')
	{
		?><tr class="heading">
			<td colspan="2"><? echo GetMessage("CICML_SAVE_SCHEME") ?></td>
		</tr>
		<tr>
			<td valign="top" width="40%"><? echo GetMessage("CICML_SSCHEME_NAME") ?>:</td>
			<td valign="top" width="60%">
				<input type="text" name="SETUP_PROFILE_NAME" size="40" value="<?echo htmlspecialcharsbx($SETUP_PROFILE_NAME)?>">
			</td>
		</tr><?
	}
}
$tabControl->EndTab();

$tabControl->BeginNextTab();

if ($STEP == 2)
{
	$FINITE = true;
}

$tabControl->EndTab();

$tabControl->Buttons();

?>

<? echo bitrix_sessid_post(); ?>
<?
if ($ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY')
{
	?><input type="hidden" name="PROFILE_ID" value="<? echo intval($PROFILE_ID); ?>"><?
}

if ($STEP < 2)
{
	?><input type="hidden" name="STEP" value="<? echo intval($STEP) + 1;?>">
	<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
	<input type="hidden" name="ACT_FILE" value="<? echo htmlspecialcharsbx($_REQUEST["ACT_FILE"]); ?>">
	<input type="hidden" name="ACTION" value="<? echo htmlspecialcharsbx($ACTION); ?>">
	<input type="hidden" name="SETUP_FIELDS_LIST" value="URL_FILE_1C,IBLOCK_TYPE_ID,keepExistingProperties,keepExistingData,clearTempTables,deleteComments,cmlDebug,cmlMemoryDebug,activateFileData,USE_TRANSLIT,ADD_TRANSLIT">
	<input type="submit" value="<? echo (($ACTION=="IMPORT")?GetMessage("CICML_NEXT_STEP_F"):GetMessage("CICML_SAVE"))." &gt;&gt;" ?>" name="submit_btn"><?
}

$tabControl->End();

?></form>
<script>
<?if ($STEP < 2):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
<?endif;?>
</script>
