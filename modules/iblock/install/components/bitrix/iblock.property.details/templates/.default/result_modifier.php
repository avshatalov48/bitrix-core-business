<?php

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 */

$arResult['SHOW_FIELD_CALLBACK'] = function (array $field, array $values)
{
	$typesFolder = (new Directory(__DIR__ . '/types'))->getPath();

	$type = $field['type'] ?? 'text';
	$file = new File($typesFolder . "/{$type}.php");
	if (
		$file->getDirectoryName() === $typesFolder
		&& $file->isExists()
	)
	{
		include $file->getPath();
	}
	else
	{
		include $typesFolder .'/text.php';
	}
};
