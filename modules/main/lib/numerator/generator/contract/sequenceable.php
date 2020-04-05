<?php
namespace Bitrix\Main\Numerator\Generator\Contract;

use Bitrix\Main\Result;

/**
 * Interface Sequenceable
 * @package Bitrix\Main\Numerator\Generator\Contract
 */
interface Sequenceable
{
	/**
	 * @param $numeratorId
	 * @return mixed
	 */
	public function getNextNumber($numeratorId);

	/**
	 * @param $numeratorId
	 * @param int $newNumber
	 * @param $whereNumber
	 * @return Result
	 */
	public function setNextNumber($numeratorId, $newNumber, $whereNumber);
	/**
	 * @param $numberHash
	 * @return mixed
	 */
	public function setNumberHash($numberHash);
}