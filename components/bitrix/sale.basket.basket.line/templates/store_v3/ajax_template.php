<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arParams
 */

$this->includeLangFile('template.php');

$cartId = $arParams['cartId'];
require(realpath(dirname(__FILE__)).'/top_template.php');