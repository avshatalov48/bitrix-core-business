<?php

use Bitrix\Main\Loader;

$documentRoot = Loader::getDocumentRoot();
if (is_dir($documentRoot . '/bitrix/modules/ui/dev/'))
{
	// developer mode
	Loader::registerNamespace('Bitrix\Ui\Dev',	$documentRoot . '/bitrix/modules/ui/dev');
}
