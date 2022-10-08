<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogCompilationComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();
