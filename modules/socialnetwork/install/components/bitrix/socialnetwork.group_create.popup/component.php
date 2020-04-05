<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult["PATH_TO_GROUP_EDIT"] = (strlen($arParams["~PATH_TO_GROUP_EDIT"]) > 0 ? $arParams["~PATH_TO_GROUP_EDIT"] : "");
$arResult["GROUP_NAME"] = (strlen($arParams["~GROUP_NAME"]) > 0 ? $arParams["~GROUP_NAME"] : "");
$arResult["IS_PROJECT"] = (isset($arParams["IS_PROJECT"]) && $arParams["IS_PROJECT"] == 'Y' ? 'Y' : 'N');

$this->IncludeComponentTemplate();
?>