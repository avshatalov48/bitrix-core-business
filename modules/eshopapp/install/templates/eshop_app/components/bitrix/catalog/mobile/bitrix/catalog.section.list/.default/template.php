<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="item_listcategory">
	<ul>
<?foreach($arResult["SECTIONS"] as $arSection):
	$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_EDIT"));
	$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM')));?>
	<li id="<?=$this->GetEditAreaId($arSection['ID']);?>">
		<a href="<?=$arSection["SECTION_PAGE_URL"]?>">
		<?if ($arSection["PICTURE"]["SRC"]):?>
			<img class="item_listcategory_img" src="<?=$arSection["PICTURE"]["SRC"]?>" >
		<?else:?>
			<img class="item_listcategory_img" src="<?=SITE_TEMPLATE_PATH?>/components/bitrix/catalog/mobile/bitrix/catalog.section.list/.default/images/no_image.png" >
		<?endif?>
			<?=$arSection["NAME"]?><?if($arParams["COUNT_ELEMENTS"]):?>&nbsp;(<?=$arSection["ELEMENT_CNT"]?>)<?endif;?>
		</a>
	</li>
<?endforeach;?>
	</ul>
</div>

<?if (is_array($arResult["SECTIONS"]) && count($arResult["SECTIONS"]) > 0):?>
<div class="bgline"><br></div>
<?endif?>
