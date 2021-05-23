<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/** @var array $arResult */

array_walk($arResult['REPORT'], function($item, $key) {
	echo $key.'='.$item.'|';
});
