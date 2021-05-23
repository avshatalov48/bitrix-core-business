<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

print CFile::ShowFile(
	$arResult['additionalParameters']['VALUE'],
	$arResult['userField']['SETTINGS']['MAX_SHOW_SIZE'],
	$arResult['userField']['SETTINGS']['LIST_WIDTH'],
	$arResult['userField']['SETTINGS']['LIST_HEIGHT'],
	true
);