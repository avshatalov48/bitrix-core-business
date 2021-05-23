<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if ($arResult)
{

?>
	<p>
	<b><?=GetMessage("SPCAT3_TEXT_LINK")?></b><br /><br />

	<?=GetMessage("SPCAT3_VIEW")?> <a href="http://<?=$arParams["SHOP_URL"]?>/?<?=$arResult["affiliateParam"]?>=<?=$arResult["arAffiliate"]["ID"] ?>"><?=$arParams["SHOP_NAME"]?></a><br />
	<?=GetMessage("SPCAT3_HTML")?> &lt;a href="http://<?=$arParams["SHOP_URL"]?>/?<?=$arResult["affiliateParam"]?>=<?=$arResult["arAffiliate"]["ID"] ?>"&gt;<?=$arParams["SHOP_NAME"]?>&lt;/a&gt;<br /><br />

	<?=$arResult["affiliateParam"]?>=<?=$arResult["arAffiliate"]["ID"] ?> <?=GetMessage("SPCAT3_PARTNER_ID")?><br /><br />

	<?=GetMessage("SPCAT3_NOTE")?></p>
	
	<?
	if ($arResult["SHOW_TIER_INFO"])
	{
		?>
		<p">
		<b><?=GetMessage("SPCAT3_AFF_REG")?></b><br /><br />

		<?=GetMessage("SPCAT3_VIEW")?> <a href="http://<?=$arParams["SHOP_URL"]?><?=$arParams["AFF_REG_PAGE"]?>?<?=$arResult["affiliateParam"]?>=<?=$arResult["arAffiliate"]["ID"] ?>"><?=str_replace("#NAME#", $arParams["SHOP_NAME"], GetMessage("SPCAT3_LINK_TEXT"))?></a><br />
		<?=GetMessage("SPCAT3_HTML")?> &lt;a href="http://<?=$arParams["SHOP_URL"]?><?=$arParams["AFF_REG_PAGE"]?>?<?=$arResult["affiliateParam"]?>=<?=$arResult["arAffiliate"]["ID"]?>"&gt;<?=str_replace("#NAME#", $arParams["SHOP_NAME"], GetMessage("SPCAT3_LINK_TEXT")) ?>&lt;/a&gt;<br /><br />
		</p>
		<?
	}
	?>
<?
}
else
{
	?><?=ShowError(GetMessage("SPCAT3_UNACTIVE_AFF"))?><?
}
?>