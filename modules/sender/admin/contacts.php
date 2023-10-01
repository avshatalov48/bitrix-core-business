<?
require_once(__DIR__ ."/page_header.php");

/** @var CMain $APPLICATION */
/** @var array $senderAdminPaths */
$APPLICATION->IncludeComponent("bitrix:sender.contact", ".default", array(
	'SEF_MODE' => 'N',
	'PATH_TO_LETTER_EDIT' => $senderAdminPaths['LETTER_EDIT'],
	'SHOW_SETS' => true,
));

require_once(__DIR__ . "/page_footer.php");