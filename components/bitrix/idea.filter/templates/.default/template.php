<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 */
?>
<div class="status-box-l">
	<div class="status-box-r">
		<div class="status-box-m">
			<?foreach($arResult["STATUSES"] as $arStatus):?>
				<div class="status-item<?if($arStatus["SELECTED"]):?>-selected<?endif;?>">
					<div>
						<div>
							<a <?if(!$arStatus["SELECTED"]):?>href="<?=$arStatus["URL"]?>"<?endif;?>><?=$arStatus["VALUE"]?></a>
						</div>
					</div>
				</div>
			<?endforeach;?>
			<br clear="both" />
		</div>
	</div>
</div>
<?
$arSort = array(
	"DATE_PUBLISH" => GetMessage("IDEA_SORT_BY_DATE_PUBLISH"),
	"RATING_TOTAL_VALUE" => GetMessage("IDEA_SORT_BY_RATING_TOTAL_VALUE"),
	"NUM_COMMENTS" => GetMessage("IDEA_SORT_BY_NUM_COMMENTS"),
);
?>
<div class="idea-sort-by-box">
	<div class="idea-sort-by-box2">
		<div class="idea-sort-by-box-body">
			<?foreach($arSort as $Sort=>$SortName):?>
				<div class="idea-sort-by-link<?=$arResult["SORT_ORDER"]==$Sort?"-selected":""?>"><a href="<?=$APPLICATION->GetCurPageParam("order=".$Sort, array("order"))?>"><?=$SortName?></a></div>
			<?endforeach;?>
			<div class="idea-sort-by-title"><?=GetMessage("IDEA_SORT_BY")?>:</div>
			<br clear="both" />
		</div>
	</div>
</div>