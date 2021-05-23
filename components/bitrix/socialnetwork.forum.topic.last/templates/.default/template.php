<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if(count($arResult["Topics"]) <= 0)
	echo GetMessage("SONET_FORUM_EMPTY");
foreach($arResult["Topics"] as $arTopic)
{
	if($arTopic["FIRST"]!="Y")
	{
		?><div class="sonet-forum-line"></div><?
	}
	?>
	<span class="sonet-forum-post-date"><?=$arTopic["LAST_POST_DATE"]?></span><br />
	<b><a href="<?=$arTopic["read"]?>"><?
		echo $arTopic["TITLE"]; 
	?></a></b><br />
	<?if($arTopic["DESCRIPTION"] <> '')
	{
		?><small><br /><?=$arTopic["DESCRIPTION"]?></small><br clear="left"/><?
	}?>	
	<br clear="left"/>

	<span class="sonet-forum-post-info">
		<?if(intval($arTopic["VIEWS"]) > 0):?>
			<span class="sonet-forum-eye"><?=GetMessage("SONET_FORUM_M_VIEWS")?></span>:&nbsp;<?=$arTopic["VIEWS"]?>&nbsp;
		<?endif;?>
		<?if(intval($arTopic["POSTS"]) > 0):?>
			<span class="sonet-forum-comment-num "><?=GetMessage("SONET_FORUM_M_NUM_COMMENTS")?></span>:&nbsp;<?=$arTopic["POSTS"]?>
		<?endif;?>
	</span>
	<?
}
?>