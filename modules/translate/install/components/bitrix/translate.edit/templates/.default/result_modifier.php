<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var \CBitrixComponentTemplate $this */
/** @var \TranslateEditComponent $component */
$component = $this->getComponent();

if($component->hasErrors())
{
	/** @var \Bitrix\Main\Error $error */
	$error = $component->getFirstError();
	$arResult['ERROR_MESSAGE'] = $error->getMessage();
}
