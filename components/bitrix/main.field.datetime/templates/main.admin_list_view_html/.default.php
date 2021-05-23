<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

print (
!empty($arResult['additionalParameters']['VALUE']) ?
	$arResult['additionalParameters']['VALUE'] : '&nbsp;'
);