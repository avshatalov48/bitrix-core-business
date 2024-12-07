<?php
use Bitrix\Main\UrlPreview\UrlMetadataTable;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
global $USER, $APPLICATION;

$id = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('id');
if(!$id)
{
	die;
}

$metadata = UrlMetadataTable::getById($id)->fetch();
if($metadata['EMBED'] && !empty($metadata['EMBED']) && !str_contains($metadata['EMBED'], '<iframe'))
{
	echo $metadata['EMBED'];
}

die;