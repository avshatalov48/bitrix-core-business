<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Templates;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\ClassConstant;

Loc::loadMessages(__FILE__);

/**
 * Class Category
 * @package Bitrix\Sender\Templates
 */
class Category extends ClassConstant
{
	const RECENT = 100;
	const BASE = 1;
	const USER = 2;
	const ADDITIONAL = 3;

	/**
	 * Get caption.
	 *
	 * @param integer $id ID.
	 * @return integer|null
	 */
	public static function getName($id)
	{
		$code = self::getCode($id);
		$name = Loc::getMessage('SENDER_TEMPLATES_CATEGORY_' . $code) ?: $code;
		return $name;
	}

	/**
	 * Sort by code.
	 *
	 * @param string $codeA Code A.
	 * @param string $codeB Code B.
	 * @return integer
	 */
	public static function sortByCode($codeA, $codeB)
	{
		$order = array(
			self::RECENT => 1,
			self::BASE => 2,
			self::USER => 3,
			self::ADDITIONAL => 4,
		);

		$orderA = $order[self::getId($codeA)];
		$orderB = $order[self::getId($codeB)];

		if ($orderA < $orderB)
		{
			return -1;
		}
		elseif ($orderA > $orderB)
		{
			return 1;
		}

		return 0;
	}
}