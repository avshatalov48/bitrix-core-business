<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="board-section-list">
<table><tr>
<?
$strTitle = "";

if($arResult['SECTION']['DEPTH_LEVEL'] > 0 ){
	$count = $arParams["COUNT_ELEMENTS"] && $arResult["SECTION"]["ELEMENT_CNT"] ? "&nbsp;(".$arResult['SECTION']["ELEMENT_CNT"].")" : "";
	echo '<tr><td><a href="'. $arResult["SECTION"]["SECTION_PAGE_URL"] .'" class="board-section-selected">' . $arResult['SECTION']['NAME'] . '</a><span>' . $count . '</span><ul>';
	if ($_REQUEST['SECTION_CODE']==$arResult["SECTION"]['CODE']){
		$strTitle = $arResult['SECTION']['NAME'];
	}
}
?>
<?
$CURRENT_DEPTH=$arResult["SECTION"]["DEPTH_LEVEL"]+1;

$cell = 0;
foreach($arResult["SECTIONS"] as $arSection):

	$this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_EDIT"));
	$this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "SECTION_DELETE"));
	
	$count = $arParams["COUNT_ELEMENTS"] && $arSection["ELEMENT_CNT"] ? "&nbsp;(".$arSection["ELEMENT_CNT"].")" : "";
	
	if($CURRENT_DEPTH < $arSection["DEPTH_LEVEL"]){
		if($CURRENT_DEPTH == 1){
			?>
			<td width="<?=round(100/$arParams["TREE_LINE_ELEMENT_COUNT"])?>%" class="td<?=$cell?>"><div id="<?=$this->GetEditAreaId($arSection['ID']);?>"><?=$link?></div>
			<?	
		}
		echo "<ul>";
	}
	elseif($CURRENT_DEPTH > $arSection["DEPTH_LEVEL"]){
		if($arSection["DEPTH_LEVEL"] == 1){
				echo "</td>";
				$cell++;
				if($cell>=$arParams["TREE_LINE_ELEMENT_COUNT"]){
					$cell = 0;
				?></tr><tr><?	
			}
		}
		echo str_repeat("</ul>", $CURRENT_DEPTH - $arSection["DEPTH_LEVEL"]);
	}
	
	$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];

	if ($_REQUEST['SECTION_CODE']==$arSection['CODE'])
	{
		$link = '<b>'.$arSection["NAME"].'</b> <span>' . $count . '</span>';
		$strTitle = $arSection["NAME"];
	}
	else
		$link = '<a href="'.$arSection["SECTION_PAGE_URL"].'">'.$arSection["NAME"].'</a><span>' . $count . '</span>';
?>
	<?if($CURRENT_DEPTH == 1):?>
		
	<?else:?>
		<li id="<?=$this->GetEditAreaId($arSection['ID']);?>"><?=$link?></li>
	<?endif;?>
	
<?endforeach?>
<?
	if($arResult['SECTION']['DEPTH_LEVEL'] > 0 ){
		echo '</td></tr>';
	}
?>
</table>
</div>
<div class="br"></div>
<?=($strTitle?'<br/><h2>'.$strTitle.'</h2>':'')?>
<div class="hr"></div>
