<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * @var array $arResult
 */
?>
<div class="bx-new-layout-include">
<?
foreach ($arResult["TOPIC"] as $res)
{
	?>
	<div class="frm-mp-info">
		<div class="frm-mp-info-inner">
			<div class="frm-mp-date intranet-date"><?echo $res["LAST_POST_DATE"];?></div>
			<div class="frm-mp-post"><a href="<?=$res["read"]?>"><?echo $res["TITLE"]?></a></div>
			<?if(isset($res['MESSAGE'])):?>
				<div><?=$res['MESSAGE']['POST_MESSAGE_TEXT']?></div>
				<br>
				<div class="frm-mp-post"><?=GetMessage("FRM_MP_AUTHOR")?> <?=$res['MESSAGE']['AUTHOR_NAME']?></div>
				<div class="frm-mp-post"><?=GetMessage("FRM_MP_DATE")?> <?=$res['MESSAGE']['POST_DATE']?></div>
			<?endif?>
			<?
			if(intval($res["VIEWS"]) > 0)
			{
				?><div class="frm-mp-post"><?=GetMessage("FRM_MP_VIEWS")?> <?=$res["VIEWS"]?></div><?
			}
			if(intval($res["POSTS"]) > 0)
			{
				?><div class="frm-mp-post"><?=GetMessage("FRM_MP_POSTS")?> <?=$res["POSTS"]?></div><?
			}
			?>
			<div class="bx-users-delimiter"></div>
		</div>
	</div>
	<?
}

if (($arParams['SET_NAVIGATION'] == 'Y') && !empty($arResult["NAV_STRING"]) && ($arResult["NAV_RESULT"]->NavPageCount > 1))
{
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
}
?>
</div>
