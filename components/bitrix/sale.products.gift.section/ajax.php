<?
/** @global \CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if (!\Bitrix\Main\Loader::includeModule('iblock'))
	return;

$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
	$template = $signer->unsign((string)$request->get('template'), 'sale.products.gift.section');
	$parameters = $signer->unsign((string)$request->get('parameters'), 'sale.products.gift.section');
}
catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
{
	die();
}

$APPLICATION->IncludeComponent(
	'bitrix:sale.products.gift.section',
	$template,
	unserialize(base64_decode($parameters), ['allowed_classes' => [
		\Bitrix\Main\Type\DateTime::class,
		\Bitrix\Main\Type\Date::class,
		\Bitrix\Main\Web\Uri::class,
		\DateTime::class,
		\DateTimeZone::class,
	]]),
	false
);