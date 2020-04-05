<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Report\VisualConstructor\Helper\Filter $filter */
$filter = $arResult['FILTER'];
foreach ($filter->getJsList() as $jsPath)
{
	\Bitrix\Main\Page\Asset::getInstance()->addJs($jsPath);
}

foreach ($filter->getCssList() as $cssPath)
{
	\Bitrix\Main\Page\Asset::getInstance()->addCss($cssPath);
}
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


<?php

foreach ($filter->getStringList() as $string)
{
	echo $string;
}

