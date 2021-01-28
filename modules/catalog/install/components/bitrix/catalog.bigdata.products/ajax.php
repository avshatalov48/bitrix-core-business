<?php
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC", "Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);
define("PUBLIC_AJAX_MODE", true);

if (empty($_POST['parameters']))
{
	echo 'no parameters found';
	return;
}

if (isset($_REQUEST['site_id']) && !empty($_REQUEST['site_id']))
{
	if (!is_string($_REQUEST['site_id']))
		die();
	if (preg_match('/^[a-z0-9_]{2}$/i', $_REQUEST['site_id']) === 1)
		define('SITE_ID', $_REQUEST['site_id']);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$signer = new \Bitrix\Main\Security\Sign\Signer;

$parameters = $signer->unsign($_POST['parameters'], 'bx.bd.products.recommendation');
$template = $signer->unsign($_POST['template'], 'bx.bd.products.recommendation');

$APPLICATION->IncludeComponent(
	"bitrix:catalog.bigdata.products",
	$template,
	unserialize(base64_decode($parameters), ['allowed_classes' => false]),
	false
);