<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?foreach ($arResult["SITES"] as $key => $arSite):?>

	<?if ($arSite["CURRENT"] == "Y"):?>
		<span title="<?=$arSite["NAME"]?>"><?=$arSite["NAME"]?></span>&nbsp;
	<?else:?>
		<a href="<?if(is_array($arSite['DOMAINS']) && $arSite['DOMAINS'][0] <> '' || $arSite['DOMAINS'] <> ''):?>http://<?endif?><?=(is_array($arSite["DOMAINS"]) ? $arSite["DOMAINS"][0] : $arSite["DOMAINS"])?><?=$arSite["DIR"]?>" title="<?=$arSite["NAME"]?>"><?=$arSite["NAME"]?></a>&nbsp;
	<?endif?>

<?endforeach;?>