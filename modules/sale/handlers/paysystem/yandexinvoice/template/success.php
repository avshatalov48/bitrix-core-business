<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<p><?=Loc::getMessage('SALE_HPS_YANDEX_INVOICE_SUCCESS', array('#PAYMENT_ID#' => $params['PAYMENT_ID'], '#SUM#' => $params['PAYMENT_SUM']));?></p>