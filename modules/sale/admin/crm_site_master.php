<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");

$APPLICATION->IncludeComponent(
	'bitrix:sale.crm.site.master',
	'.default'
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");