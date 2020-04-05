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
$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-'.$arParams['TEMPLATE_THEME'] : '';
$this->setFrameMode(true);?>
<div class="mb-3 search-form<?=$themeClass;?>">
	<form action="<?=$arResult["FORM_ACTION"]?>">
		<div class="input-group">
			<input type="text" name="q" value="" class="form-control" placeholder="<?=GetMessage("BSF_T_SEARCH_BUTTON");?>">
			<div class="input-group-append">
				<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
			</div>
		</div>
	</form>
</div>