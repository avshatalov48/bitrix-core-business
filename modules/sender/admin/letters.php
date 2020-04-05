<?
require_once(__DIR__ ."/page_header.php");

/** @var \CAllMain $APPLICATION */
/** @var array $senderAdminPaths */
$APPLICATION->IncludeComponent("bitrix:sender.letter", ".default", array(
	'SEF_MODE' => 'N',
	'PATH_TO_SEGMENT_ADD' => $senderAdminPaths['SEGMENT_ADD'],
	'PATH_TO_SEGMENT_EDIT' => $senderAdminPaths['SEGMENT_EDIT'],
	'PATH_TO_CAMPAIGN_ADD' => $senderAdminPaths['CAMPAIGN_ADD'],
	'PATH_TO_CAMPAIGN_EDIT' => $senderAdminPaths['CAMPAIGN_EDIT'],
	'SHOW_CAMPAIGNS' => true,
));

require_once(__DIR__ . "/page_footer.php");