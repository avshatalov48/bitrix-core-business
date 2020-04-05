<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<table border="0" cellpadding="4" cellspacing="0" width="75%" class="blog-groups">
<?
foreach($arResult["GROUPS_TABLE"] as $row)
{
        if(is_array($row))
	{
		?><tr><?
		foreach($row as $item)
		{
			?><td nowrap><a href="<?=$item["URL"]?>" class="blog-group-icon"></a>&nbsp;<a href="<?=$item["URL"]?>"><?echo $item["NAME"]?></a></td><?
		}
		?></tr><?
	}
}
?>
</table>