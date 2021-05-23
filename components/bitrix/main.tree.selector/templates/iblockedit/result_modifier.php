<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams['BAN_SYM'] = trim($arParams['BAN_SYM']);
$arParams['REP_SYM'] = mb_substr($arParams['REP_SYM'], 0, 1);
$arParams['BUTTON_TITLE'] = trim($arParams['BUTTON_TITLE']);