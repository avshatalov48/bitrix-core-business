<?
require_once(__DIR__ ."/page_header.php");

/** @var CMain $APPLICATION */
/** @var array $senderAdminPaths */
$APPLICATION->IncludeComponent("bitrix:sender.trigger", ".default", array(
	'SEF_MODE' => 'N',
	'PATH_TO_LETTER_EDIT' => $senderAdminPaths['LETTER_EDIT'],
	'PATH_TO_LETTER_ADD' => $senderAdminPaths['LETTER_ADD'],
	'PATH_TO_ABUSES' => $senderAdminPaths['ABUSES'],
));

require_once(__DIR__ . "/page_footer.php");