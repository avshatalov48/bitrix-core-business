<?
define("ADMIN_MODULE_NAME", "messageservice");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

//$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage("MESSAGESERVICE_SENDER_SMS_TITLE"));

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
]);

$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

$isSlider = isset($_REQUEST['IFRAME_TYPE']) && $_REQUEST['IFRAME_TYPE'] === 'SIDE_SLIDER';

if ($isSlider)
{
	$APPLICATION->RestartBuffer();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<?$APPLICATION->ShowHead(); ?>
	</head>
	<body>
	<?
}

?>
	<div style="background: white">
		<?
		/** @var \CAllMain $APPLICATION */
		$APPLICATION->IncludeComponent("bitrix:messageservice.config.sender.limits", "", []);
		?>
	</div>
<?

if ($isSlider)
{
	?></body></html><?
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");