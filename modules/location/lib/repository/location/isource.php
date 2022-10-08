<?php
namespace Bitrix\Location\Repository\Location;

/**
 * Interface ISource
 * @package Bitrix\Location\Repository\Location
 */
interface ISource extends IRepository
{
	/**
	 * Returns source code
	 * @return string
	 */
	public static function getSourceCode(): string;
}