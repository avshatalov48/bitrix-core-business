<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="photo-sections-top">
<?foreach($arResult["SECTIONS"] as $arSection):?>
	<?
	$this->AddEditAction('section_'.$arSection['ID'], $arSection['ADD_ELEMENT_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "ELEMENT_ADD"), array('ICON' => 'bx-context-toolbar-create-icon'));
	$this->AddEditAction('section_'.$arSection['ID'], $arSection['EDIT_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_EDIT"));
	$this->AddDeleteAction('section_'.$arSection['ID'], $arSection['DELETE_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BPS_SECTION_DELETE_CONFIRM')));
	?>
	<h3 id="<?=$this->GetEditAreaId('section_'.$arSection['ID']);?>"><a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?></a></h3>
	<table cellpadding="0" cellspacing="0" border="0" class="data-table">
		<?foreach($arSection["ROWS"] as $arItems):?>
			<tr class="head-row" valign="top">
			<?foreach($arItems as $arItem):?>
				<?if(is_array($arItem)):?>
					<?
					$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BPS_ELEMENT_DELETE_CONFIRM')));
					?>
					<td width="<?=$arResult["TD_WIDTH"]?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
						&nbsp;
						<?if($arResult["USER_HAVE_ACCESS"]):?>
							<?if(is_array($arItem["PICTURE"])):?>
								<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img
										border="0"
										src="<?=$arItem["PICTURE"]["SRC"]?>"
										width="<?=$arItem["PICTURE"]["WIDTH"]?>"
										height="<?=$arItem["PICTURE"]["HEIGHT"]?>"
										alt="<?=$arItem["PICTURE"]["ALT"]?>"
										title="<?=$arItem["PICTURE"]["TITLE"]?>"
										/></a><br />
							<?endif?>
						<?else:?>
							<?if(is_array($arItem["PICTURE"])):?>
								<img
									border="0"
									src="<?=$arItem["PICTURE"]["SRC"]?>"
									width="<?=$arItem["PICTURE"]["WIDTH"]?>"
									height="<?=$arItem["PICTURE"]["HEIGHT"]?>"
									alt="<?=$arItem["PICTURE"]["ALT"]?>"
									title="<?=$arItem["PICTURE"]["TITLE"]?>"
									/><br />
							<?endif?>
						<?endif?>
					</td>
				<?else:?>
					<td width="<?=$arResult["TD_WIDTH"]?>" rowspan="<?=$arResult["nRowsPerItem"]?>">
						&nbsp;
					</td>
				<?endif;?>
			<?endforeach?>
			</tr>
			<tr class="data-row">
			<?foreach($arItems as $arItem):?>
				<?if(is_array($arItem)):?>
					<th valign="top" width="<?=$arResult["TD_WIDTH"]?>" class="data-cell">
						&nbsp;
						<?if($arResult["USER_HAVE_ACCESS"]):?>
							<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?><?if($arParams["USE_RATING"] && $arItem["PROPERTIES"]["rating"]["VALUE"]) echo "(".$arItem["PROPERTIES"]["rating"]["VALUE"].")"?></a><br />
						<?else:?>
							<?=$arItem["NAME"]?><?if($arParams["USE_RATING"] && $arItem["PROPERTIES"]["rating"]["VALUE"]) echo "(".$arItem["PROPERTIES"]["rating"]["VALUE"].")"?><br />
						<?endif?>
					</th>
				<?endif;?>
			<?endforeach?>
			</tr>
			<?if($arResult["bDisplayFields"]):?>
			<tr class="data-row">
			<?foreach($arItems as $arItem):?>
				<?if(is_array($arItem)):?>
					<th valign="top" width="<?=$arResult["TD_WIDTH"]?>" class="data-cell">
						<?foreach($arParams["FIELD_CODE"] as $code):?>
							<small><?=GetMessage("IBLOCK_FIELD_".$code)?>&nbsp;:&nbsp;<?=$arItem[$code]?></small><br />
						<?endforeach?>
						<?foreach($arItem["DISPLAY_PROPERTIES"] as $arProperty):?>
							<small><?=$arProperty["NAME"]?>:&nbsp;<?
								if(is_array($arProperty["DISPLAY_VALUE"]))
									echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
								else
									echo $arProperty["DISPLAY_VALUE"];?></small><br />
						<?endforeach?>
					</th>
				<?endif;?>
			<?endforeach?>
			</tr>
			<?endif;?>
		<?endforeach?>
	</table>
	<hr /><br />
<?endforeach;?>
</div>
