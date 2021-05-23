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
<?//display sections?>
<table cellspacing="0" cellpadding="0" class="data-table" width="100%"> 	
	<tr> 		
		<td class="border-gray-body">
		<?foreach ($arResult['SECTIONS'] as $val):?>
		<?if($arParams["SECTION_ID"]==$val["ID"]) $SELECTED_ITEM = $val?>
			<nobr>
				<div style="padding: 2px 2px 2px <?=17*$val['REAL_DEPTH'].'px'?>;">
					<div class="<?=($arParams["SECTION_ID"]==$val["ID"])?'':'un'?>selected-arrow-faq"></div>
					<?='<a href="'.$val['SECTION_PAGE_URL'].'" class="'.($arParams["SECTION_ID"]==$val["ID"]?'':'un').'selected-faq-item">'.$val['NAME'].'</a> ('.$val['ELEMENT_CNT'].')'?>
					<br clear="all">
				</div>
			</nobr>
		<?endforeach;?>
		</td>
	</tr>
</table>
<?if(isset($SELECTED_ITEM)):?>
<h2><?=$SELECTED_ITEM["NAME"]?></h2>
<?endif;?>