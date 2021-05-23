<?
use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $this CBitrixComponentTemplate
 * @var $arResult array
 * @var $APPLICATION CAllMain
 */
$APPLICATION->AddHeadScript($this->GetFolder() . "/fastclick.js");
$APPLICATION->SetAdditionalCSS("/bitrix/css/main/font-awesome.css");
$messages = Loc::loadLanguageFile(Path::normalize(__FILE__));
if (toUpper(SITE_CHARSET) != "UTF8")
{
	$messages = \Bitrix\Main\Text\Encoding::convertEncodingArray($messages, SITE_CHARSET, "UTF8");
}
?>

<script>
	var appDir = "<?=$arResult["folder"]?>";
	var dataPath = "<?=$this->GetFolder();?>";
	var BXMmessage = <?=json_encode($messages)?>;
</script>
<?

if ($arResult["page_path"])
{
	include($arResult["page_path"]);
	die();
}

?>

<div class="api-demo-welcome-block">
	<?= GetMessage("MB_DEMO_WELCOME") ?>
</div>





