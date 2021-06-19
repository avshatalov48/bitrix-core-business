<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2012 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');

$module_id = 'wiki';
CModule::IncludeModule($module_id);

CModule::IncludeModule('iblock');
$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($MOD_RIGHT>='R'):

	// set up form
	$arAllOptions =	Array(
		Array('allow_html', Loc::getMessage('WIKI_OPTIONS_ALLOW_HTML'), 'Y', Array('checkbox')),
		Array('image_max_width', Loc::getMessage('WIKI_OPTIONS_IMAGE_MAX_WIDTH'), '600', Array('text')),
		Array('image_max_height', Loc::getMessage('WIKI_OPTIONS_IMAGE_MAX_HEIGHT'), '600', Array('text')),
		Array('note' => Loc::getMessage('WIKI_OPTIONS_IMAGE_DESCR'))
	);

	if(IsModuleInstalled('forum'))
	{
		if(!CModule::IncludeModule('forum'))
			return false;

		$rsForum = CForumNew::GetList();
		$arForumList = Array();
		$arForumList[] = '';

		while($arForum=$rsForum->Fetch())
			$arForumList[$arForum['ID']]=$arForum['NAME'];

		$socnet_message_per_page = isset($_POST['socnet_message_per_page']) ? $_POST['socnet_message_per_page'] : COption::GetOptionString('wiki', 'socnet_message_per_page','20');

		$arForumOptions =	Array(
			array('socnet_use_review', Loc::getMessage('WIKI_OPTIONS_SOCNET_USE_REVIEW'), 'Y', Array('checkbox')),
			array('socnet_forum_id', Loc::getMessage('WIKI_OPTIONS_SOCNET_FORUM_ID'), '', Array('selectbox', $arForumList)),
			array('socnet_message_per_page', Loc::getMessage('WIKI_OPTIONS_SOCNET_MESSAGE_PER_PAGE'), $socnet_message_per_page, Array('text')),
			array('socnet_use_captcha', Loc::getMessage('WIKI_OPTIONS_SOCNET_USE_CAPTCHA'), 'Y', Array('checkbox'))
		);
	}

if($MOD_RIGHT>='Y' || $USER->IsAdmin()):

	if ($REQUEST_METHOD=='GET' && $RestoreDefaults <> '' && check_bitrix_sessid())
	{
		COption::RemoveOption($module_id);
		$z = CGroup::GetList('id', 'asc', array('ACTIVE' => 'Y', 'ADMIN' => 'N'));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr['ID']));
	}

	if($REQUEST_METHOD=='POST' && $Update <> '' && check_bitrix_sessid())
	{
		$arOptions = $arAllOptions;
		if(IsModuleInstalled('forum'))
			$arOptions = array_merge($arAllOptions, $arForumOptions);

		//fix: http://jabber.bx/view.php?id=20941 (for compatibility)
		COption::RemoveOption($module_id,'socnet_message_per_page');

		foreach($arOptions as $option)
		{
			if(!is_array($option) || isset($option['note']))
				continue;

			$name = $option[0];
			$val = ${$name};
			if($option[3][0] == 'checkbox' && $val != 'Y')
				$val = 'N';
			if($option[3][0] == 'multiselectbox')
				$val = @implode(',', $val);
			if ($name == 'image_max_width' || $name == 'image_max_height')
				$val = (int) $val;

			COption::SetOptionString($module_id, $name, $val, $option[1]);
		}

		if(IsModuleInstalled('socialnetwork'))
		{
			COption::SetOptionString($module_id, 'socnet_iblock_id', $_POST['socnet_iblock_id']);
			COption::SetOptionString($module_id, 'socnet_iblock_type_id', $_POST['socnet_iblock_type_id']);
			COption::SetOptionString($module_id, 'socnet_enable', $_POST['socnet_enable']);
			CWikiSocnet::EnableSocnet($_POST['socnet_enable'] === 'Y');
		}
	}

endif; //if($MOD_RIGHT>="W"):

$aTabs = array();
$aTabs[] = array('DIV' => 'set', 'TAB' => Loc::getMessage('MAIN_TAB_SET'), 'ICON' => 'wiki_settings', 'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET'));

if(IsModuleInstalled('socialnetwork'))
{
	$aTabs[] = array(
		'DIV' => 'socnet',
		'TAB' => Loc::getMessage('WIKI_TAB_SOCNET'),
		'TITLE' => Loc::getMessage('WIKI_TAB_TITLE_SOCNET'),
		'ICON' => 'wiki_settings'
	);
}
$aTabs[] = array('DIV' => 'rights', 'TAB' => Loc::getMessage('MAIN_TAB_RIGHTS'), 'ICON' => 'wiki_settings', 'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_RIGHTS'));

$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>
<?
$tabControl->Begin();
?>
<style>
table.edit-table td.field-name  {
	width: 40% !important;
}
</style>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>" name="wiki_settings">
<?$tabControl->BeginNextTab();?>
<?__AdmSettingsDrawList('wiki', $arAllOptions);?>
<?
if(IsModuleInstalled('socialnetwork'))
{
	$socnet_iblock_id = COption::GetOptionString($module_id, 'socnet_iblock_id');
	$socnet_enable = COption::GetOptionString($module_id, 'socnet_enable') == 'Y' && CWikiSocnet::IsEnabledSocnet() ? 'Y' : 'N';
	$tabControl->BeginNextTab();

	__AdmSettingsDrawRow('wiki', array('socnet_enable', Loc::getMessage('WIKI_OPTIONS_SOCNET_ENABLE'), $socnet_enable, Array('checkbox')))
	?>
	<tr>
		<td><?echo Loc::getMessage('WIKI_OPTIONS_SOCNET_IBLOCK_ID')?></td>
		<td><?echo GetIBlockDropDownList($socnet_iblock_id, 'socnet_iblock_type_id', 'socnet_iblock_id', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"');?></td>
	</tr>
	<?
	if(IsModuleInstalled('forum'))
	{
		__AdmSettingsDrawList('wiki', $arForumOptions);
	}
}?>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/group_rights.php');?>
<?$tabControl->Buttons();?>
<script language="JavaScript">
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo rawurlencode($mid)."&".bitrix_sessid_get();?>";
}
</script>
<input type="submit" name="Update" <?if ($MOD_RIGHT<'W') echo "disabled" ?> value="<?echo Loc::getMessage('MAIN_SAVE')?>">
<input type="reset" name="reset" value="<?echo Loc::getMessage('MAIN_RESET')?>">
<input type="hidden" name="Update" value="Y">
<?=bitrix_sessid_post();?>
<input type="button" <?if ($MOD_RIGHT<'W') echo "disabled" ?> title="<?echo Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" OnClick="RestoreDefaults();" value="<?echo Loc::getMessage('MAIN_RESTORE_DEFAULTS')?>">
<?$tabControl->End();?>
</form>
<?endif;
?>
