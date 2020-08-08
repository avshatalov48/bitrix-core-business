<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main\Security;

class SecretField extends CryptoField
{
	protected $secretLength = 20;

	/**
	 * SecretField constructor.
	 * @param string $name
	 * @param array $parameters Can contain 'secret_length'.
	 */
	public function __construct($name, $parameters = [])
	{
		//the order of modifiers is important
		$this->addSaveDataModifier([$this, 'encode']);

		parent::__construct($name, $parameters);

		$this->addFetchDataModifier([$this, 'decode']);

		if(isset($parameters['secret_length']))
		{
			$this->configureSecretLength($parameters['secret_length']);
		}

		$this->configureDefaultValue([$this, 'getRandomBytes']);
	}

	/**
	 * @param int $length The length of the secret
	 */
	public function configureSecretLength($length)
	{
		if($length > 0)
		{
			$this->secretLength = (int)$length;
		}
	}

	/**
	 * Encodes binary data before save into DB.
	 * @param string $data
	 * @return string
	 */
	public function encode($data)
	{
		return base64_encode($data);
	}

	/**
	 * Decodes into binary data from DB.
	 * @param $data
	 * @return false|string
	 */
	public function decode($data)
	{
		return base64_decode($data);
	}

	/**
	 * @return string
	 */
	public function getRandomBytes()
	{
		return Security\Random::getBytes($this->secretLength);
	}
}
