<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult))
{
	?>
	<div class="blog-info">
	<?if($arResult["Avatar_FORMATED"] <> '')
	{
		?>
		<?=$arResult["Avatar_FORMATED"]?>
		<br /><br />
		<?
	}
	?>
	</div>
	<?
	if(!empty($arResult["CATEGORY"]))
	{
		?>
		<div align="left" style="padding-left:20px;" class="blog-info">
		<b><?=GetMessage("BLOG_BLOG_BLOGINFO_CAT")?></b><br />
		<?
		foreach($arResult["CATEGORY"] as $arCategory)
		{
			if($arCategory["SELECTED"]=="Y")
				echo "<b>";
			?>
			<a href="<?=$arCategory["urlToCategory"]?>" title="<?GetMessage("BLOG_BLOG_BLOGINFO_CAT_VIEW")?>"><?=$arCategory["NAME"]?></a>
			<?
			if($arCategory["SELECTED"]=="Y")
				echo "</b>";
			?>
			<br />
			<?
		}
		?></div><?
	}
	if($arResult["BLOG_PROPERTIES"]["SHOW"] == "Y"):
		?><br /><div align="left" style="padding-left:20px;">
		<table cellspacing="0" cellpadding="2" class="blog-info" style="width:0%;"><?
		foreach ($arResult["BLOG_PROPERTIES"]["DATA"] as $FIELD_NAME => $arBlogField):
			if($arBlogField["VALUE"] <> ''):?>
				<tr>
					<td valign="top"><b><?=$arBlogField["EDIT_FORM_LABEL"]?>:</b></td>
					<td valign="top">
							<?$APPLICATION->IncludeComponent(
								"bitrix:system.field.view", 
								$arBlogField["USER_TYPE"]["USER_TYPE_ID"], 
								array("arUserField" => $arBlogField), null, array("HIDE_ICONS"=>"Y"));?>
					</td>
					<td>&nbsp;</td>
				</tr>			
			<?endif;
		endforeach;
		?></table></div><br /><?
	endif;
}
?>	
