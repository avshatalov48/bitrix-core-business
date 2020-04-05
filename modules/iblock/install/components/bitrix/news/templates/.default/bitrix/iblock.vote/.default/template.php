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
<div class="iblock-vote">
	<form method="post" action="<?=POST_FORM_ACTION_URI?>">
		<select name="rating">
			<?foreach($arResult["VOTE_NAMES"] as $i=>$name):?>
				<option value="<?=$i?>"><?=$name?></option>
			<?endforeach?>
		</select>
		<?echo bitrix_sessid_post();?>
		<input type="hidden" name="back_page" value="<?=$arResult["BACK_PAGE_URL"]?>" />
		<input type="hidden" name="vote_id" value="<?=$arResult["ID"]?>" />
		<input type="submit" name="vote" value="<?=GetMessage("T_IBLOCK_VOTE_BUTTON")?>" />
	</form>
</div>

