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
	const CASES = 4;

	/**
	 * Get caption.
	 *
	 * @param integer $id ID.
	 * @return integer|null
	 */
	public static function getName($id)
	{
		$name = Loc::getMessage('SENDER_TEMPLATES_CATEGORY_' . self::getCode($id));
		return $name ?: parent::getName($id);
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
			self::BASE => 200,
			self::USER => 210,
			self::ADDITIONAL => 220,
		);

		$orderA = isset($order[self::getId($codeA)]) ? $order[self::getId($codeA)] : 100;
		$orderB = isset($order[self::getId($codeB)]) ? $order[self::getId($codeB)] : 100;

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