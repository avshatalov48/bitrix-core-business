<?
$module_id = "seo";

if (!$USER->CanDoOperation('seo_settings'))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

CModule::IncludeModule('seo');

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$seoRight = $APPLICATION->GetGroupRight($module_id);
if ($seoRight>="R") :

$arAllOptions = Array(
	Array("property_window_title", GetMessage('SEO_OPT_PROP_WINDOW_TITLE'), array("text"), "title"),
	Array("property_description", GetMessage('SEO_OPT_PROP_DESCRIPTION'), array("text"), "description"),
	Array("property_keywords", GetMessage('SEO_OPT_PROP_KEYWORDS'), array("text"), "keywords"),
	//Array("property_internal_keywords", GetMessage('SEO_OPT_PROP_INTERNAL_KEYWORDS'), array("text"), "keywords_inner"),
);

$bShowYandexServices =
	COption::GetOptionString('main', 'vendor', '') == '1c_bitrix'
	&& \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID) == 'ru';


$aTabs = array();

if($bShowYandexServices)
{
	$aTabs[] = array("DIV" => "edit0", "TAB" => GetMessage('SEO_OPT_TAB_CLOUDADV'), "ICON" => "seo_settings", "TITLE" => GetMessage('SEO_OPT_TAB_CLOUDADV_TITLE'));
}


$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage('SEO_OPT_TAB_PROP'), "ICON" => "seo_settings", "TITLE" => GetMessage('SEO_OPT_TAB_PROP_TITLE'));
$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage('SEO_OPT_TAB_SEARCHERS'), "ICON" => "seo_settings", "TITLE" => GetMessage('SEO_OPT_TAB_SEARCHERS_TITLE'));
$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "seo_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && $Update.$Apply.$RestoreDefaults <> '' && check_bitrix_sessid())
{
	if ($RestoreDefaults <> '')
	{
		COption::RemoveOption('seo');

		$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));

		if (CModule::IncludeModule('statistic'))
		{
			$arFilter = array('ACTIVE' => 'Y', 'NAME' => 'Google|MSN|Bing', 'NAME_EXACT_MATCH' => 'Y');
			if (COption::GetOptionString('main', 'vendor') == '1c_bitrix')
				$arFilter['NAME'] .= '|Yandex';

			$strSearchers = '';
			$dbRes = CSearcher::GetList($by = 's_id', $order = 'asc', $arFilter, $is_filtered);
			while ($arRes = $dbRes->Fetch())
			{
				$strSearchers .= ($strSearchers == '' ? '' : ',').$arRes['ID'];
			}

			COption::SetOptionString('seo', 'searchers_list', $strSearchers);
		}
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			$name = $arOption[0];
			$val = $_POST[$name];
			if ($arOption[2][0] == "checkbox" && $val != "Y")
				$val = "N";

			COption::SetOptionString("seo", $name, $val, $arOption[1]);
		}

		COption::SetOptionString('seo', 'searchers_list', is_array($_POST['arSearchersList']) ? implode(',', $_POST['arSearchersList']) : '');
		COption::SetOptionString('seo', 'counters', $_POST['counters']);
	}
}

$arCurrentSearchers = array();
$searchers = COption::GetOptionString('seo', 'searchers_list', '');
if ($searchers <> '' && CModule::IncludeModule('statistic'))
{
	$arSearchersList = explode(',', $searchers);

	$dbRes = CSearcher::GetList($by = 's_name', $order = 'asc', array('ID' => implode('|', $arSearchersList)), $is_filtered);
	while ($arRes = $dbRes->GetNext())
	{
		$arCurrentSearchers[$arRes['ID']] = $arRes['NAME'];
	}
}
else
{
	$arSearchersList = array();
}

$counters = COption::GetOptionString(
	'seo',
	'counters',
	SEO_COUNTERS_DEFAULT
);

$tabControl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&amp;lang=<?echo LANG?>" name="seo_settings">
<?=bitrix_sessid_post();?>
<?
if($bShowYandexServices):
	$tabControl->BeginNextTab();

?>
<tr>
	<td>
<?
		\Bitrix\Main\Localization\Loc::loadMessages(dirname(__FILE__).'/admin/seo_search.php');
		\Bitrix\Main\Localization\Loc::loadMessages(dirname(__FILE__).'/admin/seo_adv.php');

		$engine = new \Bitrix\Seo\Engine\YandexDirect();
		require_once(dirname(__FILE__)."/admin/tab/seo_search_yandex_direct_auth.php");

		if(\Bitrix\Seo\Service::isRegistered())
		{
?>
		<a href="javascript:void(0)" onclick="return clearCloudAdvRegister()"><?=GetMessage("SEO_OPT_TAB_CLOUDADV_CLEAR")?></a>
<script>
	function clearCloudAdvRegister()
	{
		if(confirm('<?=CUtil::JSEscape(GetMessage('SEO_OPT_TAB_CLOUDADV_CLEAR_CONFIRM'))?>'))
		{
			BX.ajax.loadJSON('/bitrix/tools/seo_yandex_direct.php?action=unregister&sessid=' + BX.bitrix_sessid(), function(result)
			{
				if(result['result'])
				{
					BX.reload();
				}
				else if(result["error"])
				{
					alert('<?=CUtil::JSEscape(GetMessage("SEO_ERROR"))?> : ' + result['error']['message']);
				}
			});
		}
	}
</script>
<?
		}
?>
	</td>
</tr>

<?
endif;
$tabControl->BeginNextTab();

foreach($arAllOptions as $arOption):
	$val = COption::GetOptionString("seo", $arOption[0], $arOption[3]);
	$type = $arOption[2];

?>
<tr>
	<td valign="top" width="50%"><?
	if ($type[0] == "checkbox")
		echo "<label for=\"".htmlspecialcharsbx($arOption[0])."\">".$arOption[1]."</label>";
	else
		echo $arOption[1];
?>: </td>
	<td valign="top" width="50%"><?
	if($type[0]=="checkbox"):
		?><input type="checkbox" name="<?echo htmlspecialcharsbx($arOption[0])?>" id="<?echo htmlspecialcharsbx($arOption[0])?>" value="Y"<?if($val=="Y")echo" checked";?> /><?
	elseif ($type[0]=="text"):
		?><input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($arOption[0])?>" /><?
	elseif($type[0]=="textarea"):
		?><textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo htmlspecialcharsbx($val)?></textarea><?
	endif;
	?></td>
</tr>
<?
endforeach;
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="30%" valign="top"><?echo GetMessage('SEO_OPT_COUNTERS')?>: </td>
		<td width="70%"><textarea cols="50" rows="7" name="counters"><?echo htmlspecialcharsbx($counters)?></textarea></td>
	</tr>
	<tr>
		<td width="30%" valign="top"><?echo GetMessage('SEO_OPT_SEARCHERS')?>: </td>
		<td width="70%">
<?
if (CModule::IncludeModule('statistic'))
{
	if (count($arCurrentSearchers) > 0)
		echo GetMessage('SEO_OPT_SEARCHERS_SELECTED'),": <b>",implode(', ', $arCurrentSearchers).'</b><br /><br />';

	echo SelectBoxM("arSearchersList[]", CSearcher::GetDropdownList(), $arSearchersList, "", false, 20);
}
else
{
	CAdminMessage::ShowMessage(GetMessage('SEO_OPT_ERR_NO_STATS'));
}
?>
		</td>
	</tr>
<?

$tabControl->BeginNextTab();

//group_rights2 work some strange
//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights2.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");

$tabControl->Buttons();?>
<script language="JavaScript">
function confirmRestoreDefaults()
{
	return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>');
}
</script>
<input type="submit" name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
<input type="hidden" name="Update" value="Y">
<input type="reset" name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirmRestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
<?$tabControl->End();?>
</form>

<?endif;?>
