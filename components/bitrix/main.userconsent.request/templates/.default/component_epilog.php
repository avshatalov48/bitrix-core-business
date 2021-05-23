<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$path = $templateFolder;
\CJSCore::RegisterExt('main_user_consent', Array(
	'js' => $path . '/user_consent.js',
	'css' => $path . '/user_consent.css',
	'lang' => $path . '/user_consent.php',
	'rel' =>   array()
));
CUtil::InitJSCore(array('popup', 'ajax', 'main_user_consent'));