<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<div class="alert-success"><?=Loc::getMessage('SALE_HPS_ALFABANK_SUCCESS', array('#PAYMENT_ID#' => $params['PAYMENT_ID']));?></div>