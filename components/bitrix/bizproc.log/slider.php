<?
define("STOP_STATISTICS", true);

$SITE_ID = '';
if (isset($_REQUEST["site_id"]) && is_string($_REQUEST["site_id"]))
	$SITE_ID = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $_REQUEST["site_id"]), 0, 2);

if ($SITE_ID != '')
	define("SITE_ID", $SITE_ID);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
?>
<!DOCTYPE html>
<html>
<head><? $APPLICATION->showHead(); ?></head>
<body style="background: #eef2f4 !important; ">
<div class="bizproc-log-slider-container">
	<div class="bizproc-log-slider-header">
		<div class="bizproc-log-slider-header-title"><?=GetMessage('BPABL_SLIDER_TITLE')?></div>
	</div>
<?
if (!empty($_REQUEST['WORKFLOW_ID']))
{
	$APPLICATION->IncludeComponent("bitrix:bizproc.log",
		'modern',
		array(
			"COMPONENT_VERSION" => 2,
			"ID" => (string)$_REQUEST['WORKFLOW_ID'],
			"SET_TITLE" => "N",
			"INLINE_MODE" => "Y",
			"AJAX_MODE" => "Y",
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_HISTORY' => 'N'
		)
	);
}
else
{
	ShowError(GetMessage('BPABL_SLIDER_ERROR'));
}
?>
</div>
</body>
</html><?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();