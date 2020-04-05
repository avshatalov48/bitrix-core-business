<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

/** @var PageNavigationComponent $component */
$component = $this->getComponent();

$this->setFrameMode(true);

$colorSchemes = array(
	"green" => "bx-green",
	"yellow" => "bx-yellow",
	"red" => "bx-red",
	"blue" => "bx-blue",
);
if(isset($colorSchemes[$arParams["TEMPLATE_THEME"]]))
{
	$colorScheme = $colorSchemes[$arParams["TEMPLATE_THEME"]];
}
else
{
	$colorScheme = "";
}
?>

<div class="bx-pagination <?=$colorScheme?>">
	<div class="bx-pagination-container">
		<ul>
<?if($arResult["REVERSED_PAGES"] === true):?>

	<?if ($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
		<?if (($arResult["CURRENT_PAGE"]+1) == $arResult["PAGE_COUNT"]):?>
			<li class="bx-pag-prev"><a href="<?=htmlspecialcharsbx($arResult["URL"])?>"><span><?echo GetMessage("round_nav_back")?></span></a></li>
		<?else:?>
			<li class="bx-pag-prev"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]+1))?>"><span><?echo GetMessage("round_nav_back")?></span></a></li>
		<?endif?>
			<li class=""><a href="<?=htmlspecialcharsbx($arResult["URL"])?>"><span>1</span></a></li>
	<?else:?>
			<li class="bx-pag-prev"><span><?echo GetMessage("round_nav_back")?></span></li>
			<li class="bx-active"><span>1</span></li>
	<?endif?>

	<?
	$page = $arResult["START_PAGE"] - 1;
	while($page >= $arResult["END_PAGE"] + 1):
	?>
		<?if ($page == $arResult["CURRENT_PAGE"]):?>
			<li class="bx-active"><span><?=($arResult["PAGE_COUNT"] - $page + 1)?></span></li>
		<?else:?>
			<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($page))?>"><span><?=($arResult["PAGE_COUNT"] - $page + 1)?></span></a></li>
		<?endif?>

		<?$page--?>
	<?endwhile?>

	<?if ($arResult["CURRENT_PAGE"] > 1):?>
		<?if($arResult["PAGE_COUNT"] > 1):?>
			<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate(1))?>"><span><?=$arResult["PAGE_COUNT"]?></span></a></li>
		<?endif?>
			<li class="bx-pag-next"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1))?>"><span><?echo GetMessage("round_nav_forward")?></span></a></li>
	<?else:?>
		<?if($arResult["PAGE_COUNT"] > 1):?>
			<li class="bx-active"><span><?=$arResult["PAGE_COUNT"]?></span></li>
		<?endif?>
			<li class="bx-pag-next"><span><?echo GetMessage("round_nav_forward")?></span></li>
	<?endif?>

<?else:?>

	<?if ($arResult["CURRENT_PAGE"] > 1):?>
		<?if ($arResult["CURRENT_PAGE"] > 2):?>
			<li class="bx-pag-prev"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1))?>"><span><?echo GetMessage("round_nav_back")?></span></a></li>
		<?else:?>
			<li class="bx-pag-prev"><a href="<?=htmlspecialcharsbx($arResult["URL"])?>"><span><?echo GetMessage("round_nav_back")?></span></a></li>
		<?endif?>
			<li class=""><a href="<?=htmlspecialcharsbx($arResult["URL"])?>"><span>1</span></a></li>
	<?else:?>
			<li class="bx-pag-prev"><span><?echo GetMessage("round_nav_back")?></span></li>
			<li class="bx-active"><span>1</span></li>
	<?endif?>

	<?
	$page = $arResult["START_PAGE"] + 1;
	while($page <= $arResult["END_PAGE"]-1):
	?>
		<?if ($page == $arResult["CURRENT_PAGE"]):?>
			<li class="bx-active"><span><?=$page?></span></li>
		<?else:?>
			<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($page))?>"><span><?=$page?></span></a></li>
		<?endif?>
		<?$page++?>
	<?endwhile?>

	<?if($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
		<?if($arResult["PAGE_COUNT"] > 1):?>
			<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["PAGE_COUNT"]))?>"><span><?=$arResult["PAGE_COUNT"]?></span></a></li>
		<?endif?>
			<li class="bx-pag-next"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]+1))?>"><span><?echo GetMessage("round_nav_forward")?></span></a></li>
	<?else:?>
		<?if($arResult["PAGE_COUNT"] > 1):?>
			<li class="bx-active"><span><?=$arResult["PAGE_COUNT"]?></span></li>
		<?endif?>
			<li class="bx-pag-next"><span><?echo GetMessage("round_nav_forward")?></span></li>
	<?endif?>
<?endif?>

<?if ($arResult["SHOW_ALL"]):?>
	<?if ($arResult["ALL_RECORDS"]):?>
			<li class="bx-pag-all"><a href="<?=htmlspecialcharsbx($arResult["URL"])?>" rel="nofollow"><span><?echo GetMessage("round_nav_pages")?></span></a></li>
	<?else:?>
			<li class="bx-pag-all"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate("all"))?>" rel="nofollow"><span><?echo GetMessage("round_nav_all")?></span></a></li>
	<?endif?>
<?endif?>
		</ul>
		<div style="clear:both"></div>
	</div>
</div>
