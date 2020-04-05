<?
//<title>Yandex simple</title>
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global string $ACTION */
/** @global array $arOldSetupVars */
use Bitrix\Currency\CurrencyTable;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/export_setup_templ.php');

global $APPLICATION;

$strCatalogDefaultFolder = COption::GetOptionString("catalog", "export_default_path", CATALOG_DEFAULT_EXPORT_PATH);

$arSetupErrors = array();

if (($ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY') && $STEP == 1)
{
	if (isset($arOldSetupVars['YANDEX_EXPORT']))
		$YANDEX_EXPORT = $arOldSetupVars['YANDEX_EXPORT'];
	if (isset($arOldSetupVars['SETUP_FILE_NAME']))
		$SETUP_FILE_NAME = str_replace($strCatalogDefaultFolder, '', $arOldSetupVars['SETUP_FILE_NAME']);
	if (isset($arOldSetupVars['SETUP_PROFILE_NAME']))
		$SETUP_PROFILE_NAME = $arOldSetupVars['SETUP_PROFILE_NAME'];
	if (isset($arOldSetupVars['SETUP_SERVER_NAME']))
		$SETUP_SERVER_NAME = $arOldSetupVars['SETUP_SERVER_NAME'];
	if (isset($arOldSetupVars['CURRENCY']))
		$currencyYandex = $arOldSetupVars['CURRENCY'];
	if (isset($arOldSetupVars['USE_HTTPS']))
		$USE_HTTPS = $arOldSetupVars['USE_HTTPS'];
}

if ($STEP > 1)
{
	if (empty($YANDEX_EXPORT) || !is_array($YANDEX_EXPORT))
		$arSetupErrors[] = GetMessage("CET_ERROR_NO_IBLOCKS");

	if (strlen($SETUP_FILE_NAME)<=0)
	{
		$arSetupErrors[] = GetMessage("CET_ERROR_NO_FILENAME");
	}
	if (empty($arSetupErrors))
	{
		$SETUP_FILE_NAME = str_replace('//','/',$strCatalogDefaultFolder.Rel2Abs("/", $SETUP_FILE_NAME));
		if (preg_match(BX_CATALOG_FILENAME_REG,$SETUP_FILE_NAME))
		{
			$arSetupErrors[] = GetMessage("CES_ERROR_BAD_EXPORT_FILENAME");
		}
		elseif ($APPLICATION->GetFileAccessPermission($SETUP_FILE_NAME) < "W")
		{
			$arSetupErrors[] = str_replace("#FILE#", $SETUP_FILE_NAME, GetMessage('CET_YAND_RUN_ERR_SETUP_FILE_ACCESS_DENIED'));
		}
	}

	if (!isset($USE_HTTPS) || $USE_HTTPS != 'Y')
		$USE_HTTPS = 'N';

	if (($ACTION=="EXPORT_SETUP" || $ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY') && strlen($SETUP_PROFILE_NAME)<=0)
	{
		$arSetupErrors[] = GetMessage("CET_ERROR_NO_PROFILE_NAME");
	}

	if (!empty($arSetupErrors))
	{
		$STEP = 1;
	}
}

$aMenu = array(
	array(
		"TEXT" => GetMessage("CATI_ADM_RETURN_TO_LIST"),
		"TITLE" => GetMessage("CATI_ADM_RETURN_TO_LIST_TITLE"),
		"LINK" => "/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID,
		"ICON" => "btn_list",
	)
);

$context = new CAdminContextMenu($aMenu);

$context->Show();

if (!empty($arSetupErrors))
	ShowError(implode('<br>', $arSetupErrors));

?>
<form method="POST" action="<? echo $APPLICATION->GetCurPage(); ?>" enctype="multipart/form-data" name="dataload">
<?

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("CAT_ADM_MISC_EXP_TAB1"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_MISC_EXP_TAB1_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("CAT_ADM_MISC_EXP_TAB2"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_MISC_EXP_TAB2_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);

$tabControl->Begin();
$tabControl->BeginNextTab();

if ($STEP==1)
{
?><tr>
	<td colspan="2"><?
	if (!isset($YANDEX_EXPORT) || !is_array($YANDEX_EXPORT))
		$YANDEX_EXPORT = array();

	$arYandexKeys = array();
	if (!empty($YANDEX_EXPORT))
		$arYandexKeys = array_fill_keys($YANDEX_EXPORT, true);
	$boolAll = false;
	$intCountChecked = 0;
	$intCountAvailIBlock = 0;
	$arIBlockIDs = array();
	$rsCatalogs = CCatalog::GetList(
		array(),
		array(),
		false,
		false,
		array('IBLOCK_ID')
	);
	while ($arCatalog = $rsCatalogs->Fetch())
		$arIBlockIDs[] = (int)$arCatalog['IBLOCK_ID'];

	if (empty($arIBlockIDs))
		$arIBlockIDs[] = -1;
	$arIBlockList = array();
	$db_res = CIBlock::GetList(
		array("IBLOCK_TYPE" => "ASC", "NAME" => "ASC"),
		array('ID' => $arIBlockIDs, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'Y', 'MIN_PERMISSION' => 'U')
	);
	while ($res = $db_res->Fetch())
	{
		$arSiteList = array();
		$rsSites = CIBlock::GetSite($res["ID"]);
		while ($arSite = $rsSites->Fetch())
		{
			$arSiteList[] = $arSite["SITE_ID"];
		}

		$boolYandex = isset($arYandexKeys[$res['ID']]);
		$arIBlockList[] = array(
			'ID' => $res['ID'],
			'NAME' => $res['NAME'],
			'IBLOCK_TYPE_ID' => $res['IBLOCK_TYPE_ID'],
			'YANDEX_EXPORT' => $boolYandex,
			'SITE_LIST' => '('.implode(' ',$arSiteList).')',
		);
		if ($boolYandex)
			$intCountChecked++;
		$intCountAvailIBlock++;
	}
	if ($intCountChecked == $intCountAvailIBlock)
		$boolAll = true;
	?><table class="internal" width="100%">
	<tr class="heading">
		<td><? echo GetMessage("CET_CATALOG");?></td>
		<td><? echo GetMessage("CET_EXPORT2YANDEX");?>&nbsp;
			<input style="vertical-align: middle;" type="checkbox" id="yandex_export_all" value="Y" onclick="checkAll(this,<? echo $intCountAvailIBlock; ?>);"<? echo ($boolAll ? ' checked' : ''); ?>>
		</td>
	</tr><?
	foreach ($arIBlockList as $key => $arIBlock)
	{
	?><tr>
		<td><? echo htmlspecialcharsEx("[".$arIBlock["IBLOCK_TYPE_ID"]."] ".$arIBlock["NAME"]." ".$arIBlock['SITE_LIST']); ?></td>
		<td align="center">
			<input type="checkbox" name="YANDEX_EXPORT[<? echo $key; ?>]" id="YANDEX_EXPORT_<? echo $key; ?>" value="<? echo $arIBlock["ID"]; ?>"<? if ($arIBlock['YANDEX_EXPORT']) echo " checked"; ?> onclick="checkOne(this,<? echo $intCountAvailIBlock; ?>);">
		</td>
	</tr><?
	}
	?></table>
	<input type="hidden" name="count_checked" id="count_checked" value="<? echo $intCountChecked; ?>">
	<script type="text/javascript">
	function checkAll(obj, cnt)
	{
		var boolCheck = obj.checked,
			i;
		for (i = 0; i < cnt; i++)
		{
			BX('YANDEX_EXPORT_'+i, true).checked = boolCheck;
		}
		BX('count_checked', true).value = (boolCheck ? cnt : 0);
	}
	function checkOne(obj, cnt)
	{
		var boolCheck = obj.checked,
			intCurrent = parseInt(BX('count_checked', true).value, 10);
		intCurrent += (boolCheck ? 1 : -1);
		BX('yandex_export_all', true).checked = (intCurrent >= cnt);
		BX('count_checked', true).value = intCurrent;
	}
	</script>
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('CAT_YANDEX_USE_HTTPS'); ?></td>
	<td width="60%">
		<input type="hidden" name="USE_HTTPS" value="N">
		<input type="checkbox" name="USE_HTTPS" value="Y"<? echo ($USE_HTTPS == 'Y' ? ' checked' : ''); ?>
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage("CET_SERVER_NAME");?></td>
	<td width="60%">
		<input type="text" name="SETUP_SERVER_NAME" value="<?echo (strlen($SETUP_SERVER_NAME)>0) ? htmlspecialcharsbx($SETUP_SERVER_NAME) : '' ?>" size="50" /> <input type="button" onclick="this.form['SETUP_SERVER_NAME'].value = window.location.host;" value="<?echo GetMessage('CET_SERVER_NAME_SET_CURRENT')?>" />
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage("CET_SAVE_FILENAME");?></td>
	<td width="60%"><b><? echo htmlspecialcharsEx($strCatalogDefaultFolder); ?></b>
		<input type="text" name="SETUP_FILE_NAME" value="<?echo htmlspecialcharsbx(strlen($SETUP_FILE_NAME)>0 ? str_replace($strCatalogDefaultFolder, '', $SETUP_FILE_NAME) : "yandex_".mt_rand(0, 999999).".php"); ?>" size="50">
	</td>
</tr>
<?
	if ($ACTION=="EXPORT_SETUP" || $ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY')
	{
?><tr>
	<td width="40%"><?echo GetMessage("CET_PROFILE_NAME");?></td>
	<td width="60%">
		<input type="text" name="SETUP_PROFILE_NAME" value="<?echo htmlspecialcharsbx($SETUP_PROFILE_NAME)?>" size="30">
	</td>
</tr><?
	}
}

$tabControl->EndTab();

$tabControl->BeginNextTab();

if ($STEP==2)
{
	$YANDEX_EXPORT = array_values($YANDEX_EXPORT);
	$FINITE = true;
}

$tabControl->EndTab();

$tabControl->Buttons();

?><? echo bitrix_sessid_post();?><?
if ($ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY')
{
	?><input type="hidden" name="PROFILE_ID" value="<? echo intval($PROFILE_ID); ?>"><?
}

if (2 > $STEP)
{
	?><input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
<input type="hidden" name="ACT_FILE" value="<?echo htmlspecialcharsbx($_REQUEST["ACT_FILE"]) ?>">
<input type="hidden" name="ACTION" value="<?echo htmlspecialcharsbx($ACTION) ?>">
<input type="hidden" name="STEP" value="<?echo intval($STEP) + 1 ?>">
<input type="hidden" name="SETUP_FIELDS_LIST" value="YANDEX_EXPORT,SETUP_SERVER_NAME,SETUP_FILE_NAME,USE_HTTPS">
<input type="submit" value="<?echo ($ACTION=="EXPORT")?GetMessage("CET_EXPORT"):GetMessage("CET_SAVE")?>">
	<?
}

$tabControl->End();
?></form>
<script type="text/javascript">
<?if ($STEP < 2):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
<?endif;?>
</script>