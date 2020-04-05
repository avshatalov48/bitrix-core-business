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
<?//elements detail?>
<table cellpadding="0" cellspacing="0" class="data-table" width="100%">
	<tr>
		<th>
		<?
		//add edit element button
		if(isset($arResult['ITEM']['EDIT_BUTTON']))
			echo $arResult['ITEM']['EDIT_BUTTON'];
		?>
		<?=$arResult['ITEM']['NAME']?>
		</th>
	</tr>
	<tr>
		<td>
		<?=$arResult['ITEM']['PREVIEW_TEXT']?>
		<?=$arResult['ITEM']['DETAIL_TEXT']?>
		
		<?if ($arParams["SHOW_RATING"] == "Y"):?>
			<div class="faq-rating" style="float: right">
			<?$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:rating.vote", $arParams["RATING_TYPE"],
				Array(
					"ENTITY_TYPE_ID" => "IBLOCK_ELEMENT",
					"ENTITY_ID" => $arResult['ITEM']['ID'],
					"OWNER_ID" => $arResult['ITEM']['CREATED_BY'],
					"USER_VOTE" => $arResult['RATING'][$arResult['ITEM']['ID']]["USER_VOTE"],
					"USER_HAS_VOTED" => $arResult['RATING'][$arResult['ITEM']['ID']]["USER_HAS_VOTED"],
					"TOTAL_VOTES" => $arResult['RATING'][$arResult['ITEM']['ID']]["TOTAL_VOTES"],
					"TOTAL_POSITIVE_VOTES" => $arResult['RATING'][$arResult['ITEM']['ID']]["TOTAL_POSITIVE_VOTES"],
					"TOTAL_NEGATIVE_VOTES" => $arResult['RATING'][$arResult['ITEM']['ID']]["TOTAL_NEGATIVE_VOTES"],
					"TOTAL_VALUE" => $arResult['RATING'][$arResult['ITEM']['ID']]["TOTAL_VALUE"],
					"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);?>
			</div>
		<?endif;?>	
		</td>
	</tr>
</table>