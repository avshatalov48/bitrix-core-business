<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$mobileAdminPageHtml = ob_get_contents();
ob_end_clean();

CMobile::Init();

?>
<!DOCTYPE html>
<html<?=$APPLICATION->ShowProperty("Manifest");?> class="<?=CMobile::$platform;?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
		<meta name="format-detection" content="telephone=no"><?
			$APPLICATION->ShowHeadStrings(true);
			$APPLICATION->ShowHeadStrings();
			$APPLICATION->ShowHeadScripts();
			CJSCore::Init('ajax');
		?><title><?$APPLICATION->ShowTitle()?></title>
	</head>
	<body class="<?=$APPLICATION->ShowProperty("BodyClass")?>">
		<?=$mobileAdminPageHtml?>
	</body>
</html>

<script type="text/javascript">
	var pullParams = {
			enable:true,
			pulltext:"<?=GetMessage("PULL_TEXT")?>",
			downtext:"<?=GetMessage("DOWN_TEXT")?>",
			loadtext:"<?=GetMessage("LOAD_TEXT")?>"
		};
	if(app.enableInVersion(2))
		pullParams.action = "RELOAD";
	else
		pullParams.callback = function(){document.location.reload();};
	app.pullDown(pullParams);
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php"); ?>
