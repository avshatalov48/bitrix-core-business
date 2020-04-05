<?
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/prolog.php");
$TRANS_RIGHT = $APPLICATION->GetGroupRight("translate");
if($TRANS_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('translate');
IncludeModuleLangFile(__FILE__);
define("HELP_FILE","translate_list.php");

$APPLICATION->SetTitle(GetMessage("TRANS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
	$path = "";
// If not specified then
if (strlen($path)<=0)
	$path = TRANSLATE_DEFAULT_PATH;

$path = Rel2Abs("/", "/".$path."/");
if (!isAllowPath($path))
	$path = TRANSLATE_DEFAULT_PATH;

$path = htmlspecialcharsbx($path);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("TRANS_SEARCH"),
		"ICON" => "translate_search", "TITLE" => GetMessage("TRANS_SEARCH_TITLE"),
		'ONSELECT' => "_trSetBTN('search');"),
	array("DIV" => "edit2", "TAB" => GetMessage("TRANS_REPLACE"),
		"ICON" => "translate_replace", "TITLE" => GetMessage("TRANS_REPLACE_TITLE"),
			'ONSELECT' => "_trSetBTN('replace');")
);
$tabControl = new CAdminTabControl("tabControl2", $aTabs, false);

?>
<form name="form_search" method="POST" action="">
<input type="hidden" name="path" value="<?=$path?>">
<input type="hidden" name="tr_search" value="1">
<input type="hidden" id="replace_oper" name="replace_oper" value="N">
<?=bitrix_sessid_post()?>
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_PHRASE")?>:</td>
		<td><input type="text" name="search_phrase" value="<?=htmlspecialcharsbx($search_phrase)?>" ></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_MESSAGE")?>:</td>
		<td><input type="checkbox" name="search_message" value="Y" checked="checked"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_MNEMONIC")?>:</td>
		<td><input type="checkbox" name="search_mnemonic" value="Y"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_CASESENS")?>:</td>
		<td><input type="checkbox" name="search_case_sens" value="Y"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_SUBFOLDERS")?>:</td>
		<td><input type="checkbox" name="search_subfolders" value="Y" checked="checked"></td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->BeginNextTab();
?>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_PHRASE")?>:</td>
		<td><input type="text" name="search_phrase2" value="<?=htmlspecialcharsbx($search_phrase)?>" ></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_REPLACE_PHRASE")?>:</td>
		<td><input type="text" name="replace_phrase2" value="<?=htmlspecialcharsbx($replace_phrase)?>" ></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_MESSAGE")?>:</td>
		<td><input type="checkbox" name="search_message2" value="Y" checked="checked"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_MNEMONIC")?>:</td>
		<td><input type="checkbox" name="search_mnemonic2" value="Y"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_CASESENS")?>:</td>
		<td><input type="checkbox" name="search_case_sens2" value="Y"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("TR_SEARCH_SUBFOLDERS")?>:</td>
		<td><input type="checkbox" name="search_subfolders2" value="Y" checked="checked"></td>
	</tr>
<script type="text/javascript">
var _trBTNSearch = [
	{
		'title': '<?=GetMessage("TR_SEARCH_SUBMIT_BUTTON");?>',
		'id': 'tr_submit',
		'action': function () {document.forms.form_search.submit();}
	},
	{
		'title': '<?=GetMessage("TR_CANCEL_SUBMIT_BUTTON");?>',
		'id': 'tr_cancel',
		'action': function () {BX.WindowManager.Get().Close();}
	}
];

var _trBTNReplace = [
	{
		'title': '<?=GetMessage("TR_REPLACE_SUBMIT_BUTTON");?>',
		'id': 'tr_submit',
		'action': function () {document.forms.form_search.submit();}
	},
	{
		'title': '<?=GetMessage("TR_CANCEL_SUBMIT_BUTTON");?>',
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
$tabControl->EndTab();
$tabControl->Buttons();
$tabControl->End();
?>

</form>
<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");