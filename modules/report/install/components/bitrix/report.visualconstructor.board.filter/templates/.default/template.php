<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Report\VisualConstructor\Helper\Filter $filter */
$filter = $arResult['FILTER'];
?>

<div class="pagetitle-container pagetitle-flexible-space">
	<?php
	$APPLICATION->IncludeComponent(
		"bitrix:main.ui.filter",
		"",
		$filter->getFilterParameters(),
		$component,
		array("HIDE_ICONS" => true)
	); ?>
</div>


