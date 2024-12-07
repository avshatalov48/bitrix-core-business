<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

if (isset($arParams["DATA"]) && !empty($arParams["DATA"]) && is_array($arParams["DATA"]))
{
	$content = "";
	$activeTabId = "";
	$jsObjName = "catalogTabs_".$arResult["ID"];
	$tabIDList = array();
?>
<div id="<? echo $arResult["ID"]; ?>" class="bx-catalog-tab-section-container"<?=isset($arResult["WIDTH"]) ? ' style="width: '.$arResult["WIDTH"].'px;"' : ''?>>
	<ul class="bx-catalog-tab-list" style="left: 0;">
		<?
		foreach ($arParams["DATA"] as $tabId => $arTab)
		{
			if (isset($arTab["NAME"]) && isset($arTab["CONTENT"]))
			{
				$id = $arResult["ID"].$tabId;
				$tabActive = (isset($arTab["ACTIVE"]) && $arTab["ACTIVE"] == "Y");
				?><li id="<?=$id?>"><span><?=$arTab["NAME"]?></span></li><?
				if($tabActive || $activeTabId === "")
					$activeTabId = $tabId;

				$content .= '<div id="'.$id.'_cont" class="tab-off">'.$arTab["CONTENT"].'</div>';
				$tabIDList[] = $tabId;
			}
		}
		?>
	</ul>
	<div class="bx-catalog-tab-body-container">
		<div class="bx-catalog-tab-container">
			<?=$content?>
		</div>
	</div>
</div>
<?
$arJSParams = array(
	'activeTabId' =>  $activeTabId,
	'tabsContId' => $arResult["ID"],
	'tabList' => $tabIDList
);
?>
<script>
var <?=$jsObjName?> = new JCCatalogTabs(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
<?
}
?>