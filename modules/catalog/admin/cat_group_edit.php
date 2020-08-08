<?
use Bitrix\Main,
	Bitrix\Main\Localization,
	Bitrix\Main\Loader,
	Bitrix\Catalog,
	Bitrix\Crm\Order;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."cat_group_admin.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_group')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_group');

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$strError = "";
$bVarsFromForm = false;
$arFields = array();

$ID = intval($ID);

if (Catalog\Config\State::isExceededPriceTypeLimit())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("CAT_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$arLangList = array();
$iterator = Localization\LanguageTable::getList([
	'select' => ['LID', 'NAME', 'SORT'],
	'order' => ['SORT' => 'ASC']
]);
while ($row = $iterator->fetch())
{
	$arLangList[] = [
		'LID' => $row['LID'],
		'NAME' => $row['NAME']
	];
}
unset($row, $iterator);

$arUserGroupList = array();

if ($adminSidePanelHelper->isPublicSidePanel())
{
	if (Loader::includeModule('crm'))
	{
		$arUserGroupList = Order\BuyerGroup::getPublicList();
	}
}
else
{
	$iterator = Main\GroupTable::getList([
		'select' => ['ID', 'NAME', 'C_SORT'],
		'order' => ['C_SORT' => 'ASC']
	]);
	while ($row = $iterator->fetch())
	{
		$arUserGroupList[] = array(
			'ID' => (int)$row['ID'],
			'NAME' => $row['NAME']
		);
	}
	unset($row, $iterator);
}

if (!$bReadOnly && 'POST' == $_SERVER['REQUEST_METHOD'] && ($save <> '' || $apply <> '') && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();

	$arGroupID = array();
	if (!empty($_POST['USER_GROUP']) && is_array($_POST['USER_GROUP']))
	{
		foreach ($_POST['USER_GROUP'] as $intValue)
		{
			$intValue = (int)$intValue;
			if ($intValue > 0)
			{
				$arGroupID[] = $intValue;
			}
		}
		unset($intValue);
	}

	$arGroupBuyID = array();
	if (!empty($_POST['USER_GROUP_BUY']) && is_array($_POST['USER_GROUP_BUY']))
	{
		foreach ($_POST['USER_GROUP_BUY'] as $intValue)
		{
			$intValue = (int)$intValue;
			if ($intValue > 0)
			{
				$arGroupBuyID[] = $intValue;
			}
		}
		unset($intValue);
	}

	if ($adminSidePanelHelper->isPublicSidePanel() && Loader::includeModule('crm'))
	{
		$groupUserBuyList = [];
		$groupUserList = [];

		$iterator = Catalog\GroupAccessTable::getList([
			'select' => ['GROUP_ID', 'ACCESS']
		]);
		while ($row = $iterator->fetch())
		{
			$row['GROUP_ID'] = (int)$row['GROUP_ID'];
			if ($row['ACCESS'] === Catalog\GroupAccessTable::ACCESS_BUY)
			{
				$groupUserBuyList[] = $row['GROUP_ID'];
			}
			else
			{
				$groupUserList[] = $row['GROUP_ID'];
			}
		}
		unset($row, $iterator);

		$arGroupID = Order\BuyerGroup::prepareGroupIds($groupUserList, $arGroupID);
		$arGroupBuyID = Order\BuyerGroup::prepareGroupIds($groupUserBuyList, $arGroupBuyID);

		unset($groupUserBuyList, $groupUserList);
	}

	$arUserLang = array();
	foreach ($arLangList as $arOneLang)
	{
		$arUserLang[$arOneLang['LID']] = trim(isset($_POST['NAME_LANG'][$arOneLang['LID']]) ? $_POST['NAME_LANG'][$arOneLang['LID']] : '');
	}
	unset($arOneLang);

	$arFields = array(
		'NAME' => (isset($_POST['NAME']) ? $_POST['NAME'] : ''),
		'BASE' => (isset($_POST['BASE']) && 'Y' == $_POST['BASE'] ? 'Y' : 'N'),
		'SORT' => intval(isset($_POST['SORT']) ? $_POST['SORT'] : 100),
		'XML_ID' => (isset($_POST['XML_ID']) ? $_POST['XML_ID'] : ''),
		'USER_GROUP' => $arGroupID,
		'USER_GROUP_BUY' => $arGroupBuyID,
		'USER_LANG' => $arUserLang,
	);

	$DB->StartTransaction();
	if (0 < $ID)
	{
		$bVarsFromForm = !CCatalogGroup::Update($ID, $arFields);
	}
	else
	{
		$ID = CCatalogGroup::Add($arFields);
		$bVarsFromForm = (!(0 < intval($ID)));
	}

	if (!$bVarsFromForm)
	{
		$DB->Commit();
		if ($adminSidePanelHelper->isAjaxRequest())
		{
			$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $ID));
		}
		else
		{
			if ($save <> '')
			{
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($listUrl);
			}
			elseif ($apply <> '')
			{
				$applyUrl = $selfFolderUrl."cat_group_edit.php?lang=".$lang."&ID=".$ID;
				$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
				LocalRedirect($applyUrl);
			}
		}
	}
	else
	{
		if ($ex = $APPLICATION->GetException())
			$strError = $ex->GetString()."<br>";
		else
			$strError = (0 < $ID ? GetMessage("ERROR_UPDATING_TYPE") : GetMessage("ERROR_ADDING_TYPE"))."<br>";

		$DB->Rollback();

		$adminSidePanelHelper->sendJsonErrorResponse($strError);
	}
}

$boolRealBase = false;

$arDefaultValues = array(
	'NAME' => '',
	'BASE' => 'N',
	'SORT' => 100,
	'XML_ID' => '',
);

$arSelect = array_merge(array('ID'), array_keys($arDefaultValues));

$arCatalogGroup = array();
$arGroupUserList = array();
$arGroupUserBuyList = array();
$arGroupLangList = array();

$rsCatalogGroups = CCatalogGroup::GetList(array(),array('ID' => $ID), false, false, $arSelect);
if (!($arCatalogGroup = $rsCatalogGroups->Fetch()))
{
	$ID = 0;
	$arCatalogGroup = $arDefaultValues;
}
else
{
	$rsGroups = CCatalogGroup::GetGroupsList(array("CATALOG_GROUP_ID" => $ID));
	while ($arGroup = $rsGroups->Fetch())
	{
		$arGroup['GROUP_ID'] = intval($arGroup['GROUP_ID']);
		if ('Y' == $arGroup['BUY'])
			$arGroupUserBuyList[] = $arGroup['GROUP_ID'];
		else
			$arGroupUserList[] = $arGroup['GROUP_ID'];
	}
	$rsLangs = CCatalogGroup::GetLangList(array("CATALOG_GROUP_ID" => $ID));
	while ($arLang = $rsLangs->Fetch())
	{
		$arGroupLangList[$arLang['LID']] = $arLang['NAME'];
	}
	$boolRealBase = (0 < $ID && 'Y' == $arCatalogGroup['BASE']);
}

if ($bVarsFromForm)
{
	$arCatalogGroup = $arFields;
	$arGroupUserList = $arFields['USER_GROUP'];
	$arGroupUserBuyList = $arFields['USER_GROUP_BUY'];
	$arGroupLangList = $arFields['USER_LANG'];
}

$sDocTitle = ($ID>0) ? GetMessage("CAT_EDIT_RECORD", array("#ID#" => $ID)) : GetMessage("CAT_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("CGEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => $listUrl
	)
);

if ($ID > 0 && !$bReadOnly)
{
	if (Catalog\Config\Feature::isMultiPriceTypesEnabled())
	{
		$aMenu[] = array("SEPARATOR" => "Y");
		$addUrl = $selfFolderUrl."cat_group_edit.php?lang=".LANGUAGE_ID;
		$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
		$aMenu[] = array(
			"TEXT" => GetMessage("CGEN_NEW_GROUP"),
			"ICON" => "btn_new",
			"LINK" => $addUrl
		);
	}

	if (Catalog\Config\Feature::isMultiPriceTypesEnabled() || !$boolRealBase)
	{
		$deleteUrl = $selfFolderUrl."cat_group_admin.php?action=delete&ID[]=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb";
		$buttonAction = "LINK";
		if ($adminSidePanelHelper->isPublicFrame())
		{
			$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
			$buttonAction = "ONCLICK";
		}
		$aMenu[] = array(
			"TEXT" => GetMessage("CGEN_DELETE_GROUP"),
			"ICON" => "btn_delete",
			$buttonAction => "javascript:if(confirm('".GetMessage("CGEN_DELETE_GROUP_CONFIRM")."')) top.window.location.href='".$deleteUrl."';",
			"WARNING" => "Y"
		);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if (!empty($strError))
	CAdminMessage::ShowMessage($strError);

?>
<?
$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form method="POST" action="<?=$actionUrl?>" name="catalog_edit">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("CGEN_TAB_GROUP"), "ICON" => "catalog", "TITLE" => GetMessage("CGEN_TAB_GROUP_DESCR"))
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
	if ($ID>0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?echo $ID ?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?echo GetMessage("BASE") ?></td>
		<td width="60%"><?
		if (!$boolRealBase)
		{
			?>
			<input type="hidden" name="BASE" value="N" />
			<input type="checkbox" id="ch_BASE" name="BASE" value="Y" <? echo ('Y' == $arCatalogGroup['BASE'] ? 'checked' : ''); ?>/>
			<?
		}
		else
		{
			?><input type="hidden" name="BASE" value="Y" /><? echo GetMessage('BASE_YES'); ?><?
		}
		?></td>
	</tr>
	<tr>
		<td width="40%">&nbsp;</td>
		<td width="60%"><?
		if (!$boolRealBase)
		{
			echo GetMessage("BASE_COMMENT");
		}
		else
		{
			echo GetMessage("BASE_COMMENT_Y");
		}
		?></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("BT_CAT_GROUP_EDIT_FIELDS_XML_ID"); ?></td>
		<td width="60%"><input type="text" name="XML_ID" value="<? echo htmlspecialcharsbx($arCatalogGroup['XML_ID']); ?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("CODE") ?></td>
		<td width="60%"><input type="text" name="NAME" value="<? echo htmlspecialcharsbx($arCatalogGroup['NAME']); ?>"></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("SORT2") ?></td>
		<td width="60%"><input type="text" name="SORT" value="<? echo intval($arCatalogGroup['SORT']); ?>"></td>
	</tr>
	<?
	foreach ($arLangList as &$arOneLang)
	{
		?><tr>
			<td width="40%"><?echo GetMessage("NAME") ?> (<?=htmlspecialcharsbx($arOneLang['NAME']); ?>):</td>
			<td width="60%"><input type="text" name="NAME_LANG[<?=htmlspecialcharsbx($arOneLang['LID']); ?>]" value="<?=htmlspecialcharsbx(isset($arGroupLangList[$arOneLang['LID']]) ? $arGroupLangList[$arOneLang['LID']] : ''); ?>"></td>
		</tr><?
	}
	if (isset($arOneLang))
		unset($arOneLang);
	?>
	<tr class="adm-detail-required-field">
		<td valign="top" width="40%">
			<?echo GetMessage('CAT_GROUPS');?>
		</td>
		<td width="60%">
			<select name="USER_GROUP[]" multiple size="8">
			<?
			foreach ($arUserGroupList as &$arOneGroup)
			{
				?><option value="<? echo $arOneGroup["ID"]; ?>"<?if (in_array($arOneGroup["ID"], $arGroupUserList)) echo " selected"?>><? echo "[".$arOneGroup["ID"]."] ".htmlspecialcharsbx($arOneGroup["NAME"]); ?></option><?
			}
			if (isset($arOneGroup))
				unset($arOneGroup);
			?>
			</select>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td valign="top" width="40%">
			<?echo GetMessage('CAT_GROUPS_BUY');?>
		</td>
		<td width="60%">
			<select name="USER_GROUP_BUY[]" multiple size="8">
			<?
			foreach ($arUserGroupList as &$arOneGroup)
			{
				?><option value="<? echo $arOneGroup["ID"]; ?>"<?if (in_array($arOneGroup["ID"], $arGroupUserBuyList)) echo " selected"?>><? echo "[".$arOneGroup["ID"]."] ".htmlspecialcharsbx($arOneGroup["NAME"]); ?></option><?
			}
			if (isset($arOneGroup))
				unset($arOneGroup);
			?>
			</select>
		</td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled" => $bReadOnly, "back_url" => $listUrl));
$tabControl->End();
?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>