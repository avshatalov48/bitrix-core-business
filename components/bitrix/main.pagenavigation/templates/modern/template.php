<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * @var array $arResult
 * @var array $arParam
 * @var CBitrixComponentTemplate $this
 */

/** @var PageNavigationComponent $component */
$component = $this->getComponent();

$this->setFrameMode(true);

?>
<div class="modern-page-navigation">
	<span class="modern-page-title"><?=GetMessage("pages")?></span>
<?
if($arResult["REVERSED_PAGES"] === true):
	$first = true;
	if ($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):
		if (($arResult["CURRENT_PAGE"]+1) == $arResult["PAGE_COUNT"]):
?>
			<a class="modern-page-previous" href="<?=htmlspecialcharsbx($arResult["URL"])?>"><?=GetMessage("nav_prev")?></a>
<?
		else:
?>
			<a class="modern-page-previous" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]+1))?>"><?=GetMessage("nav_prev")?></a>
<?
		endif;

		if ($arResult["START_PAGE"] < $arResult["PAGE_COUNT"]):
			$first = false;
?>
			<a class="modern-page-first" href="<?=htmlspecialcharsbx($arResult["URL"])?>">1</a>
<?
			if ($arResult["START_PAGE"] < ($arResult["PAGE_COUNT"] - 1)):
?>
				<a class="modern-page-dots" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["START_PAGE"] + ($arResult["PAGE_COUNT"] - $arResult["START_PAGE"]) / 2))?>">...</a>
<?
			endif;
		endif;
	endif;

	$page = $arResult["START_PAGE"];
	do
	{
		$pageNumber = $arResult["PAGE_COUNT"] - $page + 1;
		
		if ($page == $arResult["CURRENT_PAGE"]):
?>
			<span class="<?=($first ? "modern-page-first " : "")?>modern-page-current"><?=$pageNumber?></span>
<?
		elseif($page == $arResult["PAGE_COUNT"]):
?>
			<a href="<?=htmlspecialcharsbx($arResult["URL"])?>" class="<?=($first ? "modern-page-first" : "")?>"><?=$pageNumber?></a>
<?
		else:
?>
			<a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($page))?>" class="<?=($first ? "modern-page-first" : "")?>"><?=$pageNumber?></a>
<?
		endif;
		
		$page--;
		$first = false;
	}
	while($page >= $arResult["END_PAGE"]);
	
	if ($arResult["CURRENT_PAGE"] > 1):
		if ($arResult["END_PAGE"] > 1):
			if ($arResult["END_PAGE"] > 2):
?>
				<a class="modern-page-dots" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate(round($arResult["END_PAGE"] / 2)))?>">...</a>
<?
			endif;
?>
			<a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate(1))?>"><?=$arResult["PAGE_COUNT"]?></a>
<?
		endif;
	
?>
		<a class="modern-page-next" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1))?>"><?=GetMessage("nav_next")?></a>
<?
	endif; 

else:
	$first = true;

	if ($arResult["CURRENT_PAGE"] > 1):
		if ($arResult["CURRENT_PAGE"] > 2):
?>
			<a class="modern-page-previous" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1))?>"><?=GetMessage("nav_prev")?></a>
<?
		else:
?>
			<a class="modern-page-previous" href="<?=htmlspecialcharsbx($arResult["URL"])?>"><?=GetMessage("nav_prev")?></a>
<?
		endif;
		
		if ($arResult["START_PAGE"] > 1):
			$first = false;
?>
			<a class="modern-page-first" href="<?=htmlspecialcharsbx($arResult["URL"])?>">1</a>
<?
			if ($arResult["START_PAGE"] > 2):
?>
				<a class="modern-page-dots" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate(round($arResult["START_PAGE"] / 2)))?>">...</a>
<?
			endif;
		endif;
	endif;

	$page = $arResult["START_PAGE"];
	do
	{
		if ($page == $arResult["CURRENT_PAGE"]):
?>
			<span class="<?=($first ? "modern-page-first " : "")?>modern-page-current"><?=$page?></span>
<?
		elseif($page == 1):
?>
			<a href="<?=htmlspecialcharsbx($arResult["URL"])?>" class="<?=($first ? "modern-page-first" : "")?>">1</a>
<?
		else:
?>
			<a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($page))?>" class="<?=($first ? "modern-page-first" : "")?>"><?=$page?></a>
<?
		endif;

		$page++;
		$first = false;
	}
	while($page <= $arResult["END_PAGE"]);

	if($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):
		if ($arResult["END_PAGE"] < $arResult["PAGE_COUNT"]):
			if ($arResult["END_PAGE"] < ($arResult["PAGE_COUNT"] - 1)):
?>
				<a class="modern-page-dots" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate(round($arResult["END_PAGE"] + ($arResult["PAGE_COUNT"] - $arResult["END_PAGE"]) / 2)))?>">...</a>
<?
			endif;
?>
			<a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["PAGE_COUNT"]))?>"><?=$arResult["PAGE_COUNT"]?></a>
<?
		endif;
?>
		<a class="modern-page-next" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]+1))?>"><?=GetMessage("nav_next")?></a>
<?
	endif;
endif;

if ($arResult["SHOW_ALL"]):
	if ($arResult["ALL_RECORDS"]):
?>
		<a class="modern-page-pagen" href="<?=htmlspecialcharsbx($arResult["URL"])?>"><?=GetMessage("nav_paged")?></a>
<?
	else:
?>
		<a class="modern-page-all" href="<?=htmlspecialcharsbx($component->replaceUrlTemplate("all"))?>"><?=GetMessage("nav_all")?></a>
<?
	endif;
endif
?>
</div>