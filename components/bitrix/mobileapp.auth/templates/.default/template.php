<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$APPLICATION->AddHeadString("
<script type=\"text/javascript\">
		document.addEventListener('DOMContentLoaded', function() {
			BX.message({
				MobileAppOfflineTitle: '".CUtil::JSEscape(GetMessage("MOBILE_APP_OFFLINE_TITLE"))."',
				MobileAppOfflineMessage: '".CUtil::JSEscape(GetMessage("MOBILE_APP_OFFLINE_MESSAGE"))."'
			});
		}, false);
</script>
", true);
?>