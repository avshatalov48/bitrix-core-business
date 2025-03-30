<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
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
<div class="search-form">
<form action="<?=$arResult['FORM_ACTION']?>">
	<?php if ($arParams['USE_SUGGEST'] === 'Y'):
?><?php $APPLICATION->IncludeComponent(
				'bitrix:search.suggest.input',
				'',
				[
					'NAME' => 'q',
					'VALUE' => '',
					'INPUT_SIZE' => 15,
					'DROPDOWN_SIZE' => 10,
				],
				$component, ['HIDE_ICONS' => 'Y']
	);?><?php else:?><input type="text" name="q" value="" size="15" maxlength="50" /><?php endif;?>&nbsp;<input name="s" type="submit" value="<?=GetMessage('BSF_T_SEARCH_BUTTON');?>" />
</form>
</div>
