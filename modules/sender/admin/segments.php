<?
require_once(__DIR__ ."/page_header.php");

/** @var \CAllMain $APPLICATION */
/** @var array $senderAdminPaths */
$APPLICATION->IncludeComponent("bitrix:sender.segment", ".default", array(
	'SEF_MODE' => 'N',
	'PATH_TO_CONTACT_LIST' => $senderAdminPaths['CONTACT_LIST'],
	'PATH_TO_CONTACT_IMPORT' => $senderAdminPaths['CONTACT_IMPORT'],
	'ONLY_CONNECTOR_FILTERS' => false,
	'SHOW_CONTACT_SETS' => true,
));

require_once(__DIR__ . "/page_footer.php");