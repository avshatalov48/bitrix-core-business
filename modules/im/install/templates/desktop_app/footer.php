<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?php
\Bitrix\Main\Loader::includeModule("ui");
\Bitrix\Main\UI\Extension::load('helper');

$helpUrl = \Bitrix\UI\Util::getHelpdeskUrl(true)."/widget2/";
$helpUrl = CHTTP::urlAddParams($helpUrl, [
	"url" => urlencode("https://".$_SERVER["HTTP_HOST"].$APPLICATION->GetCurPageParam()),
	"user_id" => $USER->GetID(),
	"is_cloud" => IsModuleInstalled('bitrix24') ? "1" : "0",
	"action" => "open",
]);

?>
<script>
	BX.Helper.init({
		frameOpenUrl : '<?=CUtil::JSEscape($helpUrl)?>',
		langId: '<?=LANGUAGE_ID?>'
	});
</script>

</body>
</html>