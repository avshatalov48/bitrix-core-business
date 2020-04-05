<?php
//region HEAD
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/prolog.php");

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Translate;

Loc::loadLanguageFile(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';

	\CAdminMessage::ShowMessage('Translate module not found');

	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
}

/** @global \CMain $APPLICATION */
$permissionRight = $APPLICATION->GetGroupRight('translate');
if ($permissionRight == \Bitrix\Translate\Permission::DENY)
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$hasPermissionEditPhp = $USER->CanDoOperation('edit_php');


define("HELP_FILE","translate_list.php");

$APPLICATION->SetTitle(Loc::getMessage("TRANS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//endregion

//-----------------------------------------------------------------------------------
//region Grid

if(preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
{
	$path = "";
}
// If not specified then
if (strlen($path)<=0)
{
	$path = \Bitrix\Translate\TRANSLATE_DEFAULT_PATH;
}

$path = Rel2Abs("/", "/".$path."/");
if (!\Bitrix\Translate\Permission::isAllowPath($path))
{
	$path = \Bitrix\Translate\TRANSLATE_DEFAULT_PATH;
}

$path = htmlspecialcharsbx($path);

$aTabs = array();
$aTabs[] = array(
	"DIV" => "edit1",
	"TAB" => Loc::getMessage("TRANS_SEARCH"),
	"ICON" => "translate_search",
	"TITLE" => Loc::getMessage("TRANS_SEARCH_TITLE"),
	'ONSELECT' => "_trSetBTN('search');"
);
if ($hasPermissionEditPhp)
{
	$aTabs[] = array(
		"DIV" => "edit2",
		"TAB" => Loc::getMessage("TRANS_REPLACE"),
		"ICON" => "translate_replace",
		"TITLE" => Loc::getMessage("TRANS_REPLACE_TITLE"),
		'ONSELECT' => "_trSetBTN('replace');"
	);
}
$tabControl = new \CAdminTabControl("tabControl2", $aTabs, false);

?>
<form name="form_search" method="post" action="">
<input type="hidden" name="path" value="<?=$path?>">
<input type="hidden" name="tr_search" value="1">
<input type="hidden" id="replace_oper" name="replace_oper" value="N">
<?=bitrix_sessid_post()?>
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_LANGUAGE")?>:</td>
		<td>
			<select name="search_language">
				<?
				$iterator = Main\Localization\LanguageTable::getList([
					'select' => ['ID', 'NAME'],
					'filter' => [
						'ID' => Translate\Translation::getEnabledLanguages(),
						'=ACTIVE' => 'Y'
					],
					'order' => ['DEF' => 'DESC', 'SORT' => 'ASC']
				]);
				while ($row = $iterator->fetch())
				{
					$isSelected = (isset($search_language) && $search_language == $row['ID']) || (LANGUAGE_ID == $row['ID']);
					?><option value="<?= $row['ID'] ?>" <?= ($isSelected ? ' selected=""' : '') ?>><?= $row['NAME'] ?> (<?= $row['ID'] ?>)</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_PHRASE")?>:</td>
		<td><input type="text" name="search_phrase" value="<?=htmlspecialcharsbx($search_phrase)?>" ></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_MESSAGE")?>:</td>
		<td><input type="checkbox" name="search_message" value="Y" checked="checked"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_MNEMONIC")?>:</td>
		<td><input type="checkbox" name="search_mnemonic" value="Y"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_CASESENS")?>:</td>
		<td><input type="checkbox" name="search_case_sens" value="Y"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_SUBFOLDERS")?>:</td>
		<td><input type="checkbox" name="search_subfolders" value="Y" checked="checked"></td>
	</tr>
<?
$tabControl->EndTab();

if ($hasPermissionEditPhp)
{
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_LANGUAGE")?>:</td>
		<td>
			<select name="search_language2">
				<?
				$iterator = Main\Localization\LanguageTable::getList([
					'select' => ['ID', 'NAME'],
					'filter' => [
						'ID' => Translate\Translation::getEnabledLanguages(),
						'=ACTIVE' => 'Y'
					],
					'order' => ['DEF' => 'DESC', 'SORT' => 'ASC']
				]);
				while ($row = $iterator->fetch())
				{
					$isSelected = (isset($search_language2) && $search_language2 == $row['ID']) || (LANGUAGE_ID == $row['ID']);
					?><option value="<?= $row['ID'] ?>" <?= ($isSelected ? ' selected=""' : '') ?>><?= $row['NAME'] ?> (<?= $row['ID'] ?>)</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_PHRASE")?>:</td>
		<td><input type="text" name="search_phrase2" value="<?=htmlspecialcharsbx($search_phrase2)?>" ></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_REPLACE_PHRASE")?>:</td>
		<td><input type="text" name="replace_phrase2" value="<?=htmlspecialcharsbx($replace_phrase2)?>" ></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_MESSAGE")?>:</td>
		<td><input type="checkbox" name="search_message2" value="Y" checked="checked"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_MNEMONIC")?>:</td>
		<td><input type="checkbox" name="search_mnemonic2" value="Y"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_CASESENS")?>:</td>
		<td><input type="checkbox" name="search_case_sens2" value="Y"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("TR_SEARCH_SUBFOLDERS")?>:</td>
		<td><input type="checkbox" name="search_subfolders2" value="Y" checked="checked"></td>
	</tr>
	<?
	$tabControl->EndTab();
}
?>
<script type="text/javascript">
var _trBTNSearch = [
	{
		'title': '<?=Loc::getMessage("TR_SEARCH_SUBMIT_BUTTON");?>',
		'id': 'tr_submit',
		'action': function () {document.forms.form_search.submit();}
	},
	{
		'title': '<?=Loc::getMessage("TR_CANCEL_SUBMIT_BUTTON");?>',
		'id': 'tr_cancel',
		'action': function () {BX.WindowManager.Get().Close();}
	}
];

var _trBTNReplace = [
	{
		'title': '<?=Loc::getMessage("TR_REPLACE_SUBMIT_BUTTON");?>',
		'id': 'tr_submit',
		'action': function () {document.forms.form_search.submit();}
	},
	{
		'title': '<?=Loc::getMessage("TR_CANCEL_SUBMIT_BUTTON");?>',
		'id': 'tr_cancel',
		'action': function () {BX.WindowManager.Get().Close();}
	}
];

function _trSetBTN(type)
{
	BX.WindowManager.Get().ClearButtons();
	if (type == 'search') {
		BX.WindowManager.Get().SetButtons(_trBTNSearch);
		BX('replace_oper').value = 'N';
	} else {
		BX.WindowManager.Get().SetButtons(_trBTNReplace);
		BX('replace_oper').value = 'Y';
	}
}
_trSetBTN('search');


BX.bind(document, 'keypress', function (event)
{
	event || (event = window.event);
	if (event.keyCode == 13 || event.charCode == 13) {
		document.forms.form_search.submit();
	}
});

</script>
<?

$tabControl->Buttons();
$tabControl->End();
?>

</form>
<?
//endregion

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");