<?
require_once(__DIR__ ."/page_header.php");

/** @var \CAllMain $APPLICATION */
/** @var array $senderAdminPaths */
$APPLICATION->IncludeComponent("bitrix:sender.template", ".default", array(
	'SEF_MODE' => 'N',
));

require_once(__DIR__ . "/page_footer.php");