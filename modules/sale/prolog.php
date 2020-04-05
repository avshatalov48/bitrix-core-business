<?
define('ADMIN_MODULE_NAME', 'sale');
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
define('ADMIN_MODULE_ICON', '<a href="/bitrix/admin/sale_order.php?lang='.LANGUAGE_ID.'"><img src="/bitrix/images/sale/sale.gif" width="48" height="48" border="0" alt="'.Loc::getMessage("SALE_ICON_TITLE").'" title="'.Loc::getMessage("SALE_ICON_TITLE").'"></a>');