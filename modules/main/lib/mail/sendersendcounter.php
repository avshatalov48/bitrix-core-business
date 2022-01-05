<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage bitrix24
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Main\Mail;

use Bitrix\Main\Type;

class SenderSendCounter
{
	public const DEFAULT_LIMIT = 250;
	/**
	 * Returns the daily counter.
	 *
	 * @param string $email email for search current counter
	 * @return int
	 */
	public function get($email)
	{
		$counter = 0;
		$date = new Type\Date();

		$res = Internal\SenderSendCounterTable::getList(array(
			"filter" => array(
				"=DATE_STAT" => $date,
				"=EMAIL" => $email,
			)
		));

		if($cnt = $res->fetch())
		{
			$counter = $cnt["CNT"];
		}

		return $counter;
	}

	/**
	 * Returns the monthly counter.
	 *
	 * @param string $email email for search current counter
	 * @return int
	 */
	public function getMonthly($email)
	{
		$counter = 0;
		$date = new Type\Date(date("01.m.Y"), "d.m.Y");

		$res = Internals\SenderSendCounterTable::getList(array(
			"select" => array(
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'SUM(CNT)'),
			),
			"filter" => array(
				">=DATE_STAT" => $date,
				"=EMAIL" => $email,
			)
		));

		if($cnt = $res->fetch())
		{
			$counter = $cnt["CNT"];
		}

		return $counter;
	}

	/**
	 * @param int $email
	 * @param int $increment
	 */
	public function increment($email, $increment = 1)
	{
		$insert = array(
			"DATE_STAT" => new Type\Date(),
			"EMAIL" => $email,
			"CNT" => $increment,
		);
		$update = array(
			"CNT" => new \Bitrix\Main\DB\SqlExpression("?# + ?i", "CNT", $increment),
		);

		Internal\SenderSendCounterTable::mergeData($insert, $update);
	}
}
