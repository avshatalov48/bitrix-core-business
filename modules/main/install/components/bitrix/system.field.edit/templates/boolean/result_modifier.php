<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arResult["FIELD_LABEL"] = \CUserTypeBoolean::getLabels($arParams["arUserField"]);
