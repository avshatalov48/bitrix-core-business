<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (empty($arResult["TOPIC"]))
	return 0;
?>
<ul class="last-items-list">
<?
	foreach ($arResult["TOPIC"] as $res)
	{
?>
	<li>
<?
		if (intVal($res["USER_START_ID"]) > 0 ):
			?><a href="<?=$res["user_start_id_profile"]?>" class="item-author"><?=$res["USER_START_NAME"]?></a><?
		else:
			?><span class="item-author"><?=$res["USER_START_NAME"]?></span><?
		endif;
	?> <i>&gt;</i> <?
	?> <a href="<?=$arResult["FORUM"][$res["FORUM_ID"]]["URL"]["LIST"]?>" class="item-category"><?=$arResult["FORUM"][$res["FORUM_ID"]]["NAME"]?></a> <?
	?> <i>&gt;</i> <?
	?> <a href="<?=$res["read"]?>" class="item-name"><?=$res["TITLE"]?></a> 
	</li>
<?
	}
?>
</ul>