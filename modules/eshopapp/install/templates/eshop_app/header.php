<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("mobileapp")) die();

CMobile::Init();
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE html>
<html<?=$APPLICATION->ShowProperty("Manifest");?> class="<?=CMobile::$platform;?>">
<head>
	<?$APPLICATION->ShowHead();?>
	<meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
	<meta name="format-detection" content="telephone=no">
	<!--<link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/template_styles.css")?>" type="text/css" rel="stylesheet" />-->
	<?//$APPLICATION->ShowHeadStrings();?>
	<?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/script.js");?>
	<?CJSCore::Init('ajax');?>
	<title><?$APPLICATION->ShowTitle()?></title>
</head>
<?$APPLICATION->IncludeComponent("bitrix:eshopapp.data","",Array(
),false, Array("HIDE_ICONS" => "Y"));
?>
<body id="body" class="<?=$APPLICATION->ShowProperty("BodyClass");?>">
<?if (!CMobile::getInstance()->getDevice()) $APPLICATION->ShowPanel();?>

<script type="text/javascript">
	app.pullDown({
		enable:true,
		callback:function(){document.location.reload();},
		downtext:"<?=GetMessage("MB_PULLDOWN_DOWN")?>",
		pulltext:"<?=GetMessage("MB_PULLDOWN_PULL")?>",
		loadtext:"<?=GetMessage("MB_PULLDOWN_LOADING")?>"
	});
</script>
<?
if ($APPLICATION->GetCurPage(true) != SITE_DIR."eshop_app/personal/cart/index.php")
{
?>
	<script type="text/javascript">
		app.addButtons({menuButton: {
			type:    'basket',
			style:   'custom',
			callback: function()
			{
				app.openNewPage("<?=SITE_DIR?>eshop_app/personal/cart/");
			}
		}});
	</script>
<?
}
?>
<div class="wrap">
<?
?>