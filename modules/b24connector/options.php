<?

$module_id = "b24connector";

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Connection;
use Bitrix\Main\Page\Asset;

$moduleAccess = $APPLICATION->GetGroupRight($module_id);

if($moduleAccess >= "W"):

	/**
	 * @global CUser $USER
	 * @global CMain $APPLICATION
	 **/

	Loader::includeModule($module_id);
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
	IncludeModuleLangFile(__FILE__);

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => Loc::getMessage("B24C_OPTIONS"), "ICON" => "", "TITLE" => Loc::getMessage("B24C_OPTIONS")),
		array("DIV" => "edit2", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "ICON" => "", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"))
	);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);

	if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Update"] != "" && check_bitrix_sessid())
	{
		if(isset($_REQUEST["disconnect"]) && $_REQUEST["disconnect"] == 'Y')
			Connection::delete();

		ob_start();
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
		ob_end_clean();

		if($_REQUEST["back_url_settings"] <> '')
			LocalRedirect($_REQUEST["back_url_settings"]);

		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
	}

	\Bitrix\Main\UI\Extension::load("main.core");
	Asset::getInstance()->addString('<link rel="stylesheet" type="text/css" href="/bitrix/css/b24connector/style.css">');
	Asset::getInstance()->addJs("/bitrix/js/b24connector/connector.js");

	$tabControl->Begin();
	?>
	<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
	<?$tabControl->BeginNextTab();?>

	<?if(Connection::isExist()):?>
		<tr>
			<td width="40%"><?=Loc::getMessage('B24C_CONNECTED')?>:</td>
			<td width="60%">
				<input type="text" name="b24_host" value="<?=\Bitrix\B24Connector\Connection::getDomain()?>" disabled>
			</td>
		</tr>
		<tr>
			<td width="40%"><?=Loc::getMessage('B24C_DISCONNECT')?>:</td>
			<td width="60%">
				<input type="checkbox" name="disconnect" value="Y">
			</td>
		</tr>
	<?else:?>
		<tr>
			<td colspan="2">
				<?=Connection::getOptionButtonHtml(Loc::getMessage('B24C_CONNECT'))?>
			</td>
		</tr>
	<?endif;?>

	<?$tabControl->BeginNextTab();?>
	<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>

	<?$tabControl->Buttons();?>
		<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
		<?=bitrix_sessid_post();?>
		<?if($_REQUEST["back_url_settings"] <> ''):?>
			<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
			<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
		<?endif;?>
	<?$tabControl->End();?>
	</form>

<?endif;?>