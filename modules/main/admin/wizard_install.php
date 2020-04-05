<?
require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/wizard.php");

IncludeModuleLangFile(__FILE__);

if(!$USER->CanDoOperation('edit_php')):
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
elseif (!check_bitrix_sessid()):?>
	<span style="color:red"><?=GetMessage("MAIN_WIZARD_INSTALL_SESSION_EXPIRED")?></span>
	<form action="<?=$APPLICATION->GetCurPageParam(bitrix_sessid_get(), Array("sessid"))?>" method="post">
		<?=CAdminUtil::dumpVars($_POST, array("USER_LOGIN", "USER_PASSWORD", "sessid"));?>
		<br><input type="submit" value="<?=GetMessage("MAIN_WIZARD_INSTALL_RELOAD_PAGE")?>">
	</form>
<?
else:

	$arWizardNameTmp = explode(":", $_REQUEST["wizardName"]);
	$arWizardName = array();
	foreach ($arWizardNameTmp as $a)
	{
		$a = preg_replace("#[^a-z0-9_.-]+#i", "", $a);
		if (strlen($a) > 0)
			$arWizardName[] = $a;
	}

	if (count($arWizardName) > 2)
	{
		$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$arWizardName[0]."/install/wizards/".$arWizardName[1]."/".$arWizardName[2];

		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$arWizardName[0]."/install/wizards/".$arWizardName[1]."/".$arWizardName[2],
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$arWizardName[1]."/".$arWizardName[2],
			true,
			true,
			false,
			""
		);

		$arWizardName = array($arWizardName[1], $arWizardName[2]);
	}

	$installer = new CWizard($arWizardName[0].(count($arWizardName) > 1 ? ":".$arWizardName[1] : ""));
	$installer->Install();
endif;
?>