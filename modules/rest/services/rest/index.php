<?
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);
define("BX_SENDPULL_COUNTER_QUEUE_DISABLE", true);

if(
	!isset($_REQUEST['sessid'])
	&& !isset($_REQUEST['livechat_auth_id'])
)
{
	define('BX_SECURITY_SESSION_VIRTUAL', true);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent("bitrix:rest.provider", "", array(
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/rest/",
	"SEF_URL_TEMPLATES" => array(
		"path" => "#method#",
	)
	),
	false,
	array('HIDE_ICONS' => 'Y')
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>