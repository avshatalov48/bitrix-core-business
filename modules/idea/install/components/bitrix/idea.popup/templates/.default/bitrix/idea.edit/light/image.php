<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->IncludeLangFile('template.php');
/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 */
while (ob_get_clean());
$APPLICATION->RestartBuffer();
if(!isset($_POST["blog_upload_image"]))
{
?>
<html>
<head></head>
<body style="overflow: hidden; margin: 0!important; padding: 6px 0 0 0!important;">
<form action="<?= POST_FORM_ACTION_URI ?>" method="post" enctype="multipart/form-data" style="margin: 0!important; padding: 0!important;">
	<?= bitrix_sessid_post() ?>
	<input type="file" size="30" name="BLOG_UPLOAD_FILE" id="bx_lhed_blog_img_input"/>
	<input type="hidden" value="Y" name="blog_upload_image"/>
</form>
</body>
</html>
<?
}
else
{
?><script><?if(!empty($arResult["Image"])):?>
(function () {
	var imgTable = top.BX('blog-post-image');
	if(imgTable)
	{
		imgTable.appendChild( top.BX.create('DIV', {
			attrs : {
				className : 'blog-post-image-item'
			},
			html : [
				'<div class="blog-post-image-item-border">',
					'<label for="IMAGE_ID_title_<?=$arResult["Image"]["ID"]?>">',
						'<img src="<?=$arResult["Image"]["PARAMS"]["SRC"]?>" id="<?=$arResult["Image"]["ID"]?>" />',
					'</label>',
				'</div>',
				'<div class="blog-post-image-item-text">',
					'<input id="IMAGE_ID_title_<?=$arResult["Image"]["ID"]?>" name="IMAGE_ID_title[<?=$arResult["Image"]["ID"]?>]" value="<?=CUtil::JSEscape($arResult["Image"]["TITLE"])?>" title="<?= GetMessage("BLOG_BLOG_IN_IMAGES_TITLE") ?>" />',
				'</div>',
				'<div class="blog-post-image-item-act">',
					'<input type="checkbox" name="IMAGE_ID_del[<?= $arResult["Image"]["ID"] ?>]" id="img_del_<?= $arResult["Image"]["ID"] ?>">',
					'<label for="img_del_<?= $arResult["Image"]["ID"] ?>"><?= GetMessage("BLOG_DELETE") ?></label>',
				'</div>'
			].join('')
		}));
	}
	window.bxBlogImageId = top.bxBlogImageId = '<?=$arResult["Image"]["ID"]?>';
})();<?
elseif($arResult["ERROR_MESSAGE"] <> ''):
?>alert('<?=$arResult["ERROR_MESSAGE"]?>');	<?
endif;
?></script><?
}
die();
?>