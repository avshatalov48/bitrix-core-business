<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user";
include("util_menu.php");

if (isset($arResult["VARIABLES"]["user_id"]) && $USER->GetID() !== $arResult["VARIABLES"]["user_id"])
{
	ShowError(GetMessage("SONET_PASS_ACCESS_ERROR"));
	return;
}

if (
	\Bitrix\Main\ModuleManager::isModuleInstalled("intranet")
	&& SITE_TEMPLATE_ID == "bitrix24"
	&& \Bitrix\Main\Context::getCurrent()->getRequest()->get('IFRAME') === 'Y'
)
{
	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:main.app.passwords",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARENT" => $this->getComponent()
		)
	);
}
else
{
	$APPLICATION->IncludeComponent("bitrix:main.app.passwords", "", array());

	if (IsModuleInstalled("intranet"))
	{
		$request = Bitrix\Main\Context::getCurrent()->getRequest();
		$downloadUrl = "http://dl.bitrix24.com/b24/bitrix24_desktop.exe";
		if (mb_stripos($request->getUserAgent(), "Macintosh") !== false)
		{
			$downloadUrl = "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg";
		}
		?>
		<div class="bx-apps-attached-block">
			<span class="bx-apps-icon download"></span> <a href="<?=$downloadUrl?>" style="margin-right: 20px;text-transform: uppercase;"><?=GetMessage("main_app_pass_desktop")?></a>
			<?=GetMessage("main_app_pass_mobile")?>
			<span class="bx-apps-icon iOS"></span> <a href="https://itunes.apple.com/<?=\Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID)?>/app/bitrix24/id561683423?l=ru&ls=1&mt=8">iOS</a>
			<span class="bx-apps-icon android"></span> <a href="https://play.google.com/store/apps/details?id=com.bitrix24.android">android</a>
		</div>
		<?
	}
}
?>