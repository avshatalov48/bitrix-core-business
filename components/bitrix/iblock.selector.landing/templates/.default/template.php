<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var IblockElement $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$settings = $arResult['SETTINGS'];

$pageTitleFilter = ($settings['FILTER']['PAGETITLE'] === 'Y');
if ($pageTitleFilter)
{
	$this->SetViewTarget('inside_pagetitle');
}
$APPLICATION->includeComponent(
	'bitrix:main.ui.filter',
	'',
	$arResult['FILTER'],
	$component,
	['HIDE_ICONS' => true]
);
if ($pageTitleFilter)
{
	$this->EndViewTarget();
}
unset($pageTitleFilter);

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID'],
	$component,
	['HIDE_ICONS' => true]
);

$APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	'',
	[
		//'ID' => ''
		'BUTTONS' => [
			['TYPE' => 'save'],
			['TYPE' => 'cancel']
		],
		'ALIGN' => 'center'
	],
	$component,
	['HIDE_ICONS' => true]
);

$filterSettings = [
	'defaultFilter' => (isset($settings['FILTER']['DEFAULT']) ? $settings['FILTER']['DEFAULT'] : []),
	'internalFilter' => (isset($settings['FILTER']['INTERNAL']) ? $settings['FILTER']['INTERNAL'] : []),
	'useQuickSearch' => !$arResult['FILTER']['DISABLE_SEARCH']
];
if ($filterSettings['useQuickSearch'])
{
	$filterSettings['quickSearchField'] = [
		'field' => (string)$settings['FILTER']['QUICK_SEARCH_FIELD']['FIELD'],
		'name' => (string)$settings['FILTER']['QUICK_SEARCH_FIELD']['NAME']
	];
}
?>
<script type="text/javascript">
	BX.ready(function() {
		BX.IblockSelectorLanding.create(
			'<?=\CUtil::jsEscape($arResult['FILTER']['FILTER_ID']); ?>',
			<?=\CUtil::PhpToJSObject($filterSettings, false, true, false); ?>
		);
	});
</script>
<?
unset($settings);