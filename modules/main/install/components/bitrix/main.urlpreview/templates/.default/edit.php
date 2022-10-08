<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

$this->addExternalCss("/bitrix/css/main/font-awesome.css");
?>

<div
	class="urlpreview urlpreview__edit"
	<?= $arResult['STYLE'] <> '' ? 'style="'.$arResult['STYLE'].'"' : '' ?>
	id="<?= $arResult['ELEMENT_ID']?>"
	data-field-id="<?= $arResult['FIELD_ID']?>"
	<?= isset($arResult['SELECTED_IMAGE']) ? 'data-image-id="'.$arResult['SELECTED_IMAGE'].'"' : ''?>
>
	<input type="hidden" class="urlpreview__ufvalue" name="<?= htmlspecialcharsbx($arResult['FIELD_NAME'])?>" value="<?= $arResult['FIELD_VALUE']?>">
	<div class="urlpreview__detach"><i class="fa fa-times"></i></div>
	<? if(isset($arResult['DYNAMIC_PREVIEW'])): ?>
		<div class="urlpreview__frame-inner">
			<?= $arResult['DYNAMIC_PREVIEW'] ?>
		</div>
	<? else: ?>
		<div class="urlpreview__frame">
			<? if($arResult['SHOW_CONTAINER']): ?>
				<div class="urlpreview__container <?=$arResult['METADATA']['CONTAINER']['CLASSES']?>">
					<?if(isset($arResult['METADATA']['IMAGE'])):?>
						<div class="urlpreview__image">
							<?if(isset($arResult['METADATA']['EMBED'])):?>
								<img src="<?=$arResult['METADATA']['IMAGE']?>" onerror="this.style.display='none';">
								<div class="urlpreview__play">
									<i class="fa fa-play"></i>
								</div>
							<?else:?>
								<a href="<?= $arResult['METADATA']['URL']?>" target="_blank">
									<img src="<?=$arResult['METADATA']['IMAGE']?>" onerror="this.style.display='none';">
								</a>
							<?endif?>
						</div>
					<?endif?>
					<?if(isset($arResult['METADATA']['EMBED'])):?>
						<div class="urlpreview__embed">
							<?=$arResult['METADATA']['EMBED']?>
						</div>
					<?endif?>
				</div>
			<? endif ?>
			<? if($arResult['SELECT_IMAGE']): ?>
				<div class="urlpreview__container">
					<div class="urlpreview__carousel" style="display: none;">
						<? foreach($arResult['METADATA']['EXTRA']['IMAGES'] as $imageUrl): ?>
							<div class="urlpreview__image">
								<img src="<?=$imageUrl?>">
							</div>
						<? endforeach ?>
						<div class="urlpreview__button urlpreview__button-prev"></div>
						<div class="urlpreview__button urlpreview__button-next"></div>
					</div>
				</div>
			<? endif ?>
			<? if(isset($arResult['METADATA']['TITLE']) && $arResult['METADATA']['TITLE'] != ''): ?>
				<div class="urlpreview__title">	<?= $arResult['METADATA']['TITLE'] ?></div>
			<? endif ?>
			<? if(isset($arResult['METADATA']['DESCRIPTION']) && $arResult['METADATA']['DESCRIPTION'] != ''): ?>
				<div class="urlpreview__description"><?= $arResult['METADATA']['DESCRIPTION'] ?></div>
			<? endif ?>
		</div>
	<? endif ?>
	<div class="urlpreview__clearfix"></div>
</div>

