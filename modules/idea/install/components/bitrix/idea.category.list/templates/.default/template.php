<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if (!empty($arResult["ITEMS"])):?>
	<ul id="bx-idea-left-menu" class="bx-idea-left-menu">
	<?
	$previousLevel = 0;
	foreach($arResult["ITEMS"] as $arItem):
	//Edit buttons
	$this->AddEditAction($arItem["ID"], $arItem['EDIT_LINK']["ACTION_URL"], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "SECTION_EDIT"));
	$this->AddDeleteAction($arItem["ID"], $arItem['DELETE_LINK']["ACTION_URL"], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
			<?if ($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel):?>
					<?=str_repeat("</ul></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));?>
			<?endif?>
			<?if ($arItem["IS_PARENT"] && $arItem["DEPTH_LEVEL"]==1):?>
					<li class="bx-idea-left-menu-li<?//if($arItem["SELECTED"]):?> bx-idea-left-menu-open<?//endif;?>"><?/*<a class="bx-idea-left-menu-link"><?=$arItem["TEXT"]?></a>*/?>
				<a id="<?=$this->GetEditAreaId($arItem["ID"]);?>" href="<?=$arItem["LINK"]?>" class="bx-idea-left-menu-link<?if($arItem["SELECTED"]):?> bx-idea-active-menu<?endif;?>"><?=$arItem["TEXT"]?></a>
						<span class="bx-idea-left-menu-bullet"></span>
							<ul class="bx-idea-left-menu_2">
			<?else:?>
					<?if($arItem["DEPTH_LEVEL"]==1):?>
						<li class="bx-idea-left-menu-li">
							<a id="<?=$this->GetEditAreaId($arItem["ID"]);?>" class="bx-idea-left-menu-link<?if($arItem["SELECTED"]):?> bx-idea-active-menu<?endif;?>" href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a>
						</li>
					<?else:?>
						<li class="bx-idea-left-menu-li_2" style="margin-left: <?=($arItem["DEPTH_LEVEL"]-2)*12?>px!important">
							<a id="<?=$this->GetEditAreaId($arItem["ID"]);?>" class="bx-idea-left-menu-link<?if($arItem["SELECTED"]):?> bx-idea-active-menu<?endif;?>" href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a>
						</li>
					<?endif;?>
			<?endif?>
			<?$previousLevel = $arItem["DEPTH_LEVEL"]>2?2:$arItem["DEPTH_LEVEL"];?>
	<?endforeach?>
	<?if ($previousLevel > 1)://close last item tags?>
			<?=str_repeat("</ul></li>", ($previousLevel-1) );?>
	<?endif?>
	</ul>
<?endif?>