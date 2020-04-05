<?php

namespace Sale\Handlers\Delivery\Additional\RusPost\Reliability;

use Bitrix\Sale\Internals\EO_Reliability;

/**
 * Class Reliability
 * @package Sale\Handlers\Delivery\Additional\RusPost\Reliability
 */
class Reliability extends EO_Reliability
{
	/**
	 * @param string $fullName
	 * @param string $address
	 * @param string $phone
	 * @return Reliability
	 */
	public static function create(string $fullName, string $address, string $phone)
	{
		$hash = Service::createHash($fullName, $address, $phone);

		return (new static())
			->setFullName($fullName)
			->setAddress($address)
			->setPhone($phone)
			->setHash($hash);
	}
}