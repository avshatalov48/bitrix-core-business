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
$this->setFrameMode(true);?>
<div class="bx-searchform">
<form action="<?=$arResult["FORM_ACTION"]?>">
	<div class="bx-input-group">
		<input type="text" name="q" value="" size="15" maxlength="50" class="bx-form-control" placeholder="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>">
		<span class="bx-input-group-btn">
			<button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
		</span>
	</div>
</form>
</div>