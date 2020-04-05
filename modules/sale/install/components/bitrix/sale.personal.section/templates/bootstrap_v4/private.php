<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_PRIVATE_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}

if (strlen($arParams["MAIN_CHAIN_NAME"]) > 0)
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_PRIVATE"));
if ($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(Loc::getMessage("SPS_TITLE_PRIVATE"));
}

if (!$USER->IsAuthorized())
{
	?>
	<div class="row">
		<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
			<div class="alert alert-danger"><?=GetMessage("SPS_ACCESS_DENIED")?></div>
		</div>
		<? $authListGetParams = array(); ?>
		<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3" id="catalog-subscriber-auth-form" style="<?=$authStyle?>">
			<?$APPLICATION->AuthForm('', false, false, 'N', false);?>
		</div>
	</div>
	<?
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:main.profile",
		"",
		Array(
			"SET_TITLE" =>$arParams["SET_TITLE"],
			"AJAX_MODE" => $arParams['AJAX_MODE_PRIVATE'],
			"SEND_INFO" => $arParams["SEND_INFO_PRIVATE"],
			"CHECK_RIGHTS" => $arParams['CHECK_RIGHTS_PRIVATE'],
			"EDITABLE_EXTERNAL_AUTH_ID" => $arParams['EDITABLE_EXTERNAL_AUTH_ID'],
		),
		$component
	);
}
