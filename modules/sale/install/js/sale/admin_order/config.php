<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/install/js/sale/admin_order/config.php');

return [
	'css' => 'dist/admin_order.bundle.css',
	'js' => 'dist/admin_order.bundle.js',
	'rel' => [
		'sale.barcode',
		'main.core',
	],
	'skip_core' => false,
	"lang_additional" => array(
		'SALE_JS_ADMIN_ORDER_CONF_BARCODE' => Loc::getMessage('SALE_JS_ADMIN_ORDER_CONF_BARCODE'),
		'SALE_JS_ADMIN_ORDER_CONF_BARCODES' => Loc::getMessage('SALE_JS_ADMIN_ORDER_CONF_BARCODES'),
		'SALE_JS_ADMIN_ORDER_CONF_CLOSE' => Loc::getMessage('SALE_JS_ADMIN_ORDER_CONF_CLOSE'),
		'SALE_JS_ADMIN_ORDER_CONF_MARKING_CODE' => Loc::getMessage('SALE_JS_ADMIN_ORDER_CONF_MARKING_CODE'),
		'SALE_JS_ADMIN_ORDER_CONF_INPUT_BARCODES' => Loc::getMessage('SALE_JS_ADMIN_ORDER_CONF_INPUT_BARCODES')
	)
];