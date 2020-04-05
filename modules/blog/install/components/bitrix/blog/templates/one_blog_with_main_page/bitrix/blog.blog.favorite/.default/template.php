<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach($arResult["POST"] as $arPost)
{
	if($arPost["FIRST"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	<span class="blog-post-date"><b><a href="<?=$arPost["urlToPost"]?>"><?
	if(strlen($arPost["TITLE"])>0) 
		echo $arPost["TITLE"]; 
	else 
		echo GetMessage("BLOG_MAIN_MES_NO_SUBJECT"); 
	?></a></b>
	</span><br />
	<?
	if(strlen($arPost["IMG"]) > 0)
		echo $arPost["IMG"];
	?>
	<small><?=$arPost["TEXT_FORMATED"]?></small><br clear="left"/>
	<span class="blog-post-info">
		<br />
		<nobr>
		<a href="<?=$arPost["urlToPost"]?>" class="blog-clock" title="<?=GetMessage("BLOG_BLOG_M_DATE")?>"><?=$arPost["DATE_PUBLISH_FORMATED"]?></a>
		<?if(IntVal($arPost["VIEWS"]) > 0):?>
			&nbsp;&nbsp;<a href="<?=$arPost["urlToPost"]?>" class="blog-eye" title="<?=GetMessage("BLOG_BLOG_M_VIEWS")?>"><?=$arPost["VIEWS"]?></a>
		<?endif;?>
		<?if(IntVal($arPost["NUM_COMMENTS"]) > 0):?>
			&nbsp;&nbsp;<a href="<?=$arPost["urlToPost"]?>" class="blog-comment-num" title="<?=GetMessage("BLOG_BLOG_M_NUM_COMMENTS")?>"><?=$arPost["NUM_COMMENTS"]?></a>
		<?endif;?>
		</nobr>
	</span><br clear="left">
	<?
}
?>	