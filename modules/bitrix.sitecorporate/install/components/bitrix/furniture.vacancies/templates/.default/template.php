<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<a name="top"></a>
<ul>
<?
foreach ($arResult['ITEMS'] as $key=>$val):
?>
	<li class="point-faq"><a href="#<?=$val["ID"]?>"><?=$val['NAME']?></a></li>
<?
endforeach;
?>
</ul>

<?
foreach ($arResult['ITEMS'] as $key=>$val):
?>
<?
	$this->AddEditAction($val['ID'],$val['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($val['ID'],$val['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('FAQ_DELETE_CONFIRM', array("#ELEMENT#" => CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_NAME")))));
?>
<?
	if ($key > 0):
?>
<div class="hr"></div>
<?
	endif;
?>
<div id="<?=$this->GetEditAreaId($val['ID']);?>">
<a name="<?=$val["ID"]?>"></a>
<h3><?=$val['NAME']?></h3>
<p>
	<?=$val['PREVIEW_TEXT']?>
	<?=$val['DETAIL_TEXT']?>
</p><p>
	<a href="#top"><?=GetMessage("SUPPORT_FAQ_GO_UP")?></a>
</p>
</div>
<?endforeach;?>