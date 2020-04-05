<?
require_once(__DIR__ ."/page_header.php");

/** @var \CAllMain $APPLICATION */
/** @var array $senderAdminPaths */
$APPLICATION->IncludeComponent("bitrix:sender.letter.recipient", ".default", array(
	'CAMPAIGN_ID' => intval($_REQUEST['MAILING_ID']),
	'ID' => intval($_REQUEST['ID']),
	'PATH_TO_ABUSES' => $senderAdminPaths['ABUSES'],
));

require_once(__DIR__ . "/page_footer.php");