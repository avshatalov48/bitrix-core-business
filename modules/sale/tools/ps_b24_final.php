<?php

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

Loc::loadMessages(__FILE__);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('SALE_RESULT_MESSAGE_TITLE'));


$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$action = $request->get('action');

$text = Loc::getMessage('SALE_RESULT_MESSAGE_'.mb_strtoupper($action));

echo "<div align='center'><h1>".$text."</h1></div>";