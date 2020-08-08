<?php
namespace Bitrix\Sale\PaySystem\Domain\Verification;

/**
 * Interface IVerificationable
 * @package Bitrix\Sale\PaySystem\Domain\Verification
 */
interface IVerificationable extends \Bitrix\Sale\Domain\Verification\IVerificationable
{
	/**
	 * @return array
	 */
	public static function getModeList(): array;
}
