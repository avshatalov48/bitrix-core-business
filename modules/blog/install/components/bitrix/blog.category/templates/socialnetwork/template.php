<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css');
$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css');

if(strlen($arResult["FATAL_ERROR_MESSAGE"])>0)
{
	?><span class='errortext'><?=$arResult["FATAL_ERROR_MESSAGE"]?></span><br /><br /><?
}
else
{
	if(strlen($arResult["ERROR_MESSAGE"])>0)
	{
		?><span class='errortext'><?=$arResult["ERROR_MESSAGE"]?></span><br /><br /><?
	}
	?>
	<script>
		BX.message({
			BLOG_CONFIRM_DELETE:'<?=GetMessageJS("BLOG_CONFIRM_DELETE")?>'
		});
	</script>
	<form action="<?=POST_FORM_ACTION_URI?>" id="REPLIER" name="REPLIER" method="post" enctype="multipart/form-data">
	<?=bitrix_sessid_post();?>
	<input type="hidden" name="ID" id="category_id">
	<input type="hidden" name="category_del" id="category_del">
	<input type="hidden" name="BACK_URL" value="<?=$arResult["BACK_URL"]?>">

	<table border=0 cellspacing=1 cellpadding=3 class="blog-category" width=300>
	<?foreach($arResult["CATEGORY"] as $v)
	{
		if(IntVal($v["CNT"])<=0)
			$v["CNT"] = 0;
		?>
		<input type="hidden" id="count_<?=$v["ID"]?>" value="<?=$v["CNT"]?>">
		<input type="hidden" id="name_<?=$v["ID"]?>" value="<?=$v["NAME"]?>">
		<tr>
			<td width="100%" nowrap><?=$v["NAME"]?> (<?=$v["CNT"]?>)</td>
			<td><a href="javascript:category_edit(<?=$v["ID"]?>)" title="<?=GetMessage("BLOG_NAME_CHANGE")?>" class="blog-category-edit"></a></td>
			<td><a href="javascript:category_del(<?=$v["ID"]?>)" title="<?=GetMessage("BLOG_GROUP_DELETE")?>" class="blog-category-delete"></a></td>
		</tr>
		<?
	}
	?>
	<tr>
		<td colspan="3">
			<div>
			<a href="javascript:category_edit(0)" title="<?=GetMessage("BLOG_GROUP_ADD")?>" class="blog-category-add"></a>&nbsp;
			<a href="javascript:category_edit(0)" title="<?=GetMessage("BLOG_GROUP_ADD")?>"><?=GetMessage("BLOG_ADD")?></a><br clear="all" />
			</div>
			<div id="edit_form" style="display:none" class="blog-category-tag-input-form">
				<?=GetMessage("BLOG_GROUP_NAME")?><br />

				<div class="blog-category-tag-input-wrap">
					<input name="NAME" id="category_name" class="blog-category-tag-input" maxlength="255">
				</div>
				<input type="hidden" name="save" value="Y">
				<div class="feed-add-post-buttons-post">
					<a id="tagSubmitButton" class="feed-add-button feed-add-com-button" href="javascript:void(0)" onclick="submitForm()"> OK </a>
					<a class="feed-cancel-com" href="javascript:void(0)" onclick="show_form(0)"><?=GetMessage("BLOG_CANCEL")?></a>
				</div>
			</div>
		</td>
	</tr>
	</table>
	</form>
<?
}