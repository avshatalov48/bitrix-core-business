<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if ($arResult["showAll"] == "Y")
{
	?><div class="sonet-blog-menu"><?
	if($arResult["show4MeAll"] == "Y" || $arResult["showAll"] == "Y")
	{
		?><a href="<?=$arResult["PATH_TO_4ME_ALL"]?>" class="sonet-log-pagetitle-button<?if($arResult["page"] == "all"):?> sonet-log-pagetitle-button-active<?endif;?>" title="<?=GetMessage("BLOG_MENU_4ME_ALL_TITLE")?>"><span class="sonet-log-pagetitle-button-left-s"></span><span class="sonet-log-pagetitle-button-text"><?=GetMessage("BLOG_MENU_4ME_ALL")?></span><span class="sonet-log-pagetitle-button-right-s"></span></a><?
	}
	if($arResult["PATH_TO_MINE"] <> '')
	{
		?><a href="<?=$arResult["PATH_TO_MINE"]?>" class="sonet-log-pagetitle-button<?if($arResult["page"] == "mine"):?> sonet-log-pagetitle-button-active<?endif;?>" title="<?=GetMessage("BLOG_MENU_MINE_TITLE")?>"><span class="sonet-log-pagetitle-button-left-s"></span><span class="sonet-log-pagetitle-button-text"><?=GetMessage("BLOG_MENU_MINE")?></span><span class="sonet-log-pagetitle-button-right-s"></span></a><?
	}
	if($arResult["show4Me"] == "Y")
	{
		?><a href="<?=$arResult["PATH_TO_4ME"]?>" class="sonet-log-pagetitle-button<?if($arResult["page"] == "forme"):?> sonet-log-pagetitle-button-active<?endif;?>" title="<?=GetMessage("BLOG_MENU_4ME_TITLE")?>"><span class="sonet-log-pagetitle-button-left-s"></span><span class="sonet-log-pagetitle-button-text"><?=GetMessage("BLOG_MENU_4ME")?></span><span class="sonet-log-pagetitle-button-right-s"></span></a><?
	}
	
	if ($arResult["urlToDraft"] <> '' && intval($arResult["CntToDraft"]) > 0)
	{
		?><a href="<?=$arResult["urlToDraft"]?>" class="sonet-log-pagetitle-button<?if($arResult["page"] == "draft"):?> sonet-log-pagetitle-button-active<?endif;?>" title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>"><span class="sonet-log-pagetitle-button-left-s"></span><span class="sonet-log-pagetitle-button-text"><?=GetMessage("BLOG_MENU_DRAFT_MESSAGES")?><span class="sonet-log-pagetitle-button-counter"><?=$arResult["CntToDraft"]?></span></span><span class="sonet-log-pagetitle-button-right-s"></span></a><?
	}
	if ($arResult["urlToModeration"] <> '' && intval($arResult["CntToModerate"]) > 0)
	{
		?><a href="<?=$arResult["urlToModeration"]?>" class="sonet-log-pagetitle-button<?if($arResult["page"] == "moderation"):?> sonet-log-pagetitle-button-active<?endif;?>" title="<?=GetMessage("BLOG_MENU_MODERATION_MESSAGES_TITLE")?>"><span class="sonet-log-pagetitle-button-left-s"></span><span class="sonet-log-pagetitle-button-text"><?=GetMessage("BLOG_MENU_MODERATION_MESSAGES")?><span class="sonet-log-pagetitle-button-counter"><?=$arResult["CntToModerate"]?></span></span><span class="sonet-log-pagetitle-button-right-s"></span></a><?
	}
	?>
	</div>
	<?
}
?>