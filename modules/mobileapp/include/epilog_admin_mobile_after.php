<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$mobileAdminPageHtml = ob_get_contents();
ob_end_clean();

CJSCore::Init();
CMobile::Init();
?>
<!DOCTYPE html>
<html<?=$APPLICATION->ShowProperty("Manifest");?> class="<?=CMobile::$platform;?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
		<meta name="format-detection" content="telephone=no">
		<?
			$APPLICATION->ShowCSS();
			$APPLICATION->ShowHeadStrings(true);
			$APPLICATION->ShowHeadStrings();
			$APPLICATION->ShowHeadScripts();
		?>
		<title><?$APPLICATION->ShowTitle()?></title>
	</head>
	<body class="<?=$APPLICATION->ShowProperty("BodyClass")?>">
		<?=$mobileAdminPageHtml?>
	</body>
</html>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php"); ?>
