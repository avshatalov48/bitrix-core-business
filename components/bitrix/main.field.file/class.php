<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\FileType;

/**
 * Class FileUfComponent
 */
class FileUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return FileType::USER_TYPE_ID;
	}
}