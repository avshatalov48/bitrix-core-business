<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (empty($arResult["MESSAGES"]))
	return 0;

// ************************* Input params***************************************************************
$arParams["SHOW_NAV"] = (is_array($arParams["SHOW_NAV"]) ? $arParams["SHOW_NAV"] : array());
$arParams["SHOW_COLUMNS"] = (is_array($arParams["SHOW_COLUMNS"]) ? $arParams["SHOW_COLUMNS"] : array());
$arParams["SHOW_SORTING"] = ($arParams["SHOW_SORTING"] == "Y" ? "Y" : "N");
$arParams["SEPARATE"] = (empty($arParams["SEPARATE"]) ? GetMessage("FTP_IN_FORUM") : $arParams["SEPARATE"]);
// *************************/Input params***************************************************************

?>
<div class="forum-topic-last">
<?

if (in_array("TOP", $arParams["SHOW_NAV"])):
?><div class="forum-navigation"><?=$arResult["NAV_STRING"]?></div><?
endif;
?>
<div class="forum-topic-last-table">
<table class="data-table">
<?
	if ($arParams["SHOW_SORTING"] == "Y")
	{
?>
	<tr>
		<th><div><?=GetMessage("FTP_SORTING_TITLE")?></div><span><?=$arResult["SortingEx"]["TITLE"]?></span></th>
<?

		if (in_array("USER_START_NAME", $arParams["SHOW_COLUMNS"])):
		?><th><div><?=GetMessage("FTP_SORTING_USER_START_NAME")?></div><span><?=$arResult["SortingEx"]["USER_START_NAME"]?></span></th><?
		endif;

		if (in_array("POSTS", $arParams["SHOW_COLUMNS"])):
		?><th><div><?=GetMessage("FTP_SORTING_POSTS")?></div><span><?=$arResult["SortingEx"]["POSTS"]?></span></th><?
		endif;

		if (in_array("VIEWS", $arParams["SHOW_COLUMNS"])):
		?><th><div><?=GetMessage("FTP_SORTING_VIEWS")?></div><span><?=$arResult["SortingEx"]["VIEWS"]?></span></th><?
		endif;

		if (in_array("LAST_POST_DATE", $arParams["SHOW_COLUMNS"])):
		?><th><div><?=GetMessage("FTP_SORTING_LAST_POST_DATE")?></div><span><?=$arResult["SortingEx"]["LAST_POST_DATE"]?></span></th><?
		endif;
?>
	</tr>
<?
	}

	foreach ($arResult["MESSAGES"] as $arMessage)
	{
		$arTopic = $arResult["TOPICS"][$arMessage['TOPIC_ID']];
		$arForum = $arResult["FORUMS"][$arMessage["FORUM_ID"]];
?>
	<tr>
		<td><a href="<?=$arTopic["read"]?>" class="forum-title<?=($arTopic["SORT"] == 100 ? " pinned" : "")?>"><?=$arTopic["TITLE"]?></a>  <?
			?><?=str_replace("#FORUM#",
				"<a href =\"".$arForum["URL"]["LIST"]."\">".$arForum["NAME"]."</a>",
				$arParams["SEPARATE"])?>
				<div class="forum-topic-last-message">
					<?=$arMessage['POST_MESSAGE_TEXT']?>
					<span class="forum-topic-last-author">
						( <?=$arMessage['AUTHOR_NAME']?> @ <?=$arMessage['POST_DATE']?> )
					</span>
				</div>
		</td>
<?

		if (in_array("USER_START_NAME", $arParams["SHOW_COLUMNS"])):
?>
		<td class="user_start_name">
<?
			if (intval($arTopic["USER_START_ID"]) > 0 ):
				?><a href="<?=$arTopic["user_start_id_profile"]?>" class="forum-user"><?=$arTopic["USER_START_NAME"]?></a><?
			else:
				?><?=$arTopic["USER_START_NAME"]?><?
			endif;
?>
		</td>
<?
		endif;

		if (in_array("POSTS", $arParams["SHOW_COLUMNS"])):
?>
		<td class="posts"><?=$arTopic["POSTS"]?></td>
<?
		endif;

		if (in_array("VIEWS", $arParams["SHOW_COLUMNS"])):
?>
			<td class="views"><?=$arTopic["VIEWS"]?></td>
<?
		endif;

		if (in_array("LAST_POST_DATE", $arParams["SHOW_COLUMNS"])):
?>
		<td class="last_post_date">
			<a href="<?=$arTopic["read_last_message"]?>" title="<?=GetMessage("FTP_LAST_MESS")?>"><?=$arTopic["LAST_POST_DATE"]?></a>
		</td>
<?
		endif;
?>
	</tr>
<?
	}
?>
</table></div>
<?

if (in_array("BOTTOM", $arParams["SHOW_NAV"])):
?><div class="forum-navigation"><?=$arResult["NAV_STRING"]?></div><?
endif;

?></div>
