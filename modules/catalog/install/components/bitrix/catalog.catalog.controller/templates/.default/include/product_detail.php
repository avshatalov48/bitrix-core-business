<?php
if (!defined('URL_BUILDER_TYPE'))
{
	define('URL_BUILDER_TYPE', 'INVENTORY');
}
if (!defined('SELF_FOLDER_URL'))
{
	define('SELF_FOLDER_URL', '/shop/documents-catalog/');
}
if (!defined('INTERNAL_ADMIN_PAGE'))
{
	define('INTERNAL_ADMIN_PAGE', 'Y');
}
include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/admin/iblock_element_edit.php');