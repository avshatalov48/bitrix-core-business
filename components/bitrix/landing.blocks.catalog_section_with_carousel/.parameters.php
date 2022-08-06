<?php

use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (Loader::includeModule('iblock'))
{
	$templateProps = @\CComponentUtil::GetTemplateProps(
		'bitrix:catalog.section',
		'store_v3',
	);

	$arComponentParameters = @\CComponentUtil::GetComponentProps(
		'bitrix:catalog.section',
		[],
		$templateProps
	);
}
