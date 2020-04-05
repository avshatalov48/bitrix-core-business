<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main\Security;

class CryptoField extends TextField
{
	protected $cryptoKey;

	// we might want to use different algorithms, so single var might not be enough
	/** @var Security\Cipher */
	protected static $cipher;

	/**
	 * CryptoField constructor.
	 * @param string $name
	 * @param array $parameters Can contain the 'crypto_key' parameter, otherwise the key is taken from .settings.php as
	 *  'crypto' => array('value' =>
     *		array (
	 * 			'crypto_key' => 'mysupersecretphrase',
     *	))
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		$enabled = true;
		if(isset($parameters['crypto_enabled']))
		{
			$enabled = (bool)$parameters['crypto_enabled'];
		}

		if($enabled)
		{
			if(isset($parameters['crypto_key']) && $parameters['crypto_key'] <> '')
			{
				$this->cryptoKey = $parameters['crypto_key'];
			}
			else
			{
				// get the key from the settings
				if(($key = static::getDefaultKey()) <> '')
				{
					$this->cryptoKey = $key;
				}
			}

			if(static::cryptoAvailable($this->cryptoKey))
			{
				$this->addSaveDataModifier(array($this, 'encrypt'));
				$this->addFetchDataModifier(array($this, 'decrypt'));
			}
			else
			{
				throw new \Bitrix\Main\NotSupportedException("Crypto is not available.");
			}
		}
	}

	/**
	 * Checks availability of the crypto class and the crypto key.
	 * @param string $key
	 * @return bool
	 */
	public static function cryptoAvailable($key = '')
	{
		if($key == '')
		{
			// get the key from the settings
			$key = static::getDefaultKey();
		}

		if($key <> '')
		{
			if(static::$cipher === null)
			{
				try
				{
					static::$cipher = new Security\Cipher();
				}
				catch(Security\SecurityException $e)
				{
					static::$cipher = false;
				}
			}
		}

		return ($key <> '' && static::$cipher);
	}

	public static function getDefaultKey()
	{
		// get the key from the settings
		$options = \Bitrix\Main\Config\Configuration::getValue("crypto");
		if(isset($options["crypto_key"]))
		{
			return $options["crypto_key"];
		}
		return '';
	}

	public function encrypt($data)
	{
		if($data == '')
		{
			//is empty data still a secret?
			return $data;
		}
		try
		{
			//encrypt the data
			$value = static::$cipher->encrypt($data, $this->cryptoKey);
			return base64_encode($value);
		}
		catch(Security\SecurityException $e)
		{
			trigger_error("Error on encrypting the field {$this->getEntity()->getName()}.{$this->getName()}: {$e->getMessage()}", E_USER_WARNING);
			return null;
		}
	}

	public function decrypt($data)
	{
		if($data == '')
		{
			//is empty data still a secret?
			return $data;
		}
		try
		{
			//decrypt the data
			$value = base64_decode($data);
			$value = static::$cipher->decrypt($value, $this->cryptoKey);
			return $value;
		}
		catch(Security\SecurityException $e)
		{
			trigger_error("Error on decrypting the field {$this->getEntity()->getName()}.{$this->getName()}: {$e->getMessage()}", E_USER_WARNING);
			return null;
		}
	}
}
