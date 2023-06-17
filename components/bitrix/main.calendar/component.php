<?php
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$arParams['INPUT_NAME_FINISH'] = (string)($arParams['INPUT_NAME_FINISH'] ?? '');
$arParams['FORM_NAME'] = (string)($arParams['FORM_NAME'] ?? '');
$arParams['SHOW_TIME'] = (string)($arParams['SHOW_TIME'] ?? 'N');
if ($arParams['SHOW_TIME'] !== 'Y')
{
	$arParams['SHOW_TIME'] = 'N';
}
$arParams['HIDE_TIMEBAR'] = (string)($arParams['HIDE_TIMEBAR'] ?? '');
if ($arParams['HIDE_TIMEBAR'] === '')
{
	$arParams['HIDE_TIMEBAR'] = ($arParams['SHOW_TIME'] === 'Y' ? 'N' : 'Y');
}

CJSCore::Init(array('popup', 'date'));

$this->IncludeComponentTemplate();
