<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$this->getComponent()->includePageComponent(
	'bitrix:mail.blacklist.list', '',
	$arResult,
	$component
);
