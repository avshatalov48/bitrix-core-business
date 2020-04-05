<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
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

$APPLICATION->IncludeComponent(
	"bitrix:disk.volume",
	"",
	array(
		'SEF_MODE' => $arParams['SEF_MODE'],
		'SEF_FOLDER' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_DISK_VOLUME'], array('ACTION' => '', 'user_id' => $arResult['VARIABLES']['user_id'])),
	),
	$component,
	array("HIDE_ICONS" => "Y")
);