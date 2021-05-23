<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<select name="site" onChange="location.href=this.value">
<?
foreach ($arResult["SITES"] as $key => $arSite):
?>
	<option value="<?if(is_array($arSite['DOMAINS']) && $arSite['DOMAINS'][0] <> '' || $arSite['DOMAINS'] <> ''):?>http://<?endif?><?=(is_array($arSite["DOMAINS"]) ? $arSite["DOMAINS"][0] : $arSite["DOMAINS"])?><?=$arSite["DIR"]?>" <?if ($arSite["CURRENT"] == "Y"):?>SELECTED="1"<?endif;?>><?=$arSite["NAME"]?></option>
<?
endforeach;
?>
</select>