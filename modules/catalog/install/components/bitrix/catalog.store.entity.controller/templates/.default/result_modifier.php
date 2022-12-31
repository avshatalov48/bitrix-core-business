<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

// remove `PATH_TO` item, because both warehouses and documents have a key `list`.
unset($arResult['PATH_TO']['LIST']);
