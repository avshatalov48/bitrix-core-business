<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<div class="alert-danger"><?=Loc::getMessage('SALE_HPS_YANDEX_INVOICE_FAILURE', array('#PAYMENT_ID#' => $params['PAYMENT_ID']));?></div>