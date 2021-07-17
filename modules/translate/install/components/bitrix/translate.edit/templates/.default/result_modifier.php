<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var \CBitrixComponentTemplate $this
 * @var \TranslateEditComponent $component
 */
$component = $this->getComponent();
if ($component->hasErrors())
{
	$arResult['ERROR_MESSAGE'] = $component->getFirstError()->getMessage();
}
if ($component->hasWarnings())
{
	$arResult['WARNING_MESSAGE'] = $component->getFirstWarning()->getMessage();
}
