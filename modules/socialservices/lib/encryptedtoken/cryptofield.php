<?php
namespace Bitrix\Socialservices\EncryptedToken;

use Bitrix\Main\Security\SecurityException;

class CryptoField extends \Bitrix\Main\ORM\Fields\CryptoField
{
	protected $encryptionComplete = false;
	protected $ivLength;

	public function __construct($name, $parameters = array())
	{
		if (isset($parameters['encryption_complete']))
		{
			$this->encryptionComplete = !!$parameters['encryption_complete'];
			unset($parameters['encryption_complete']);
		}
		if (!static::cryptoAvailable())
		{
			$parameters['crypto_enabled'] = false;
		}
		parent::__construct($name, $parameters);
		$this->ivLength = openssl_cipher_iv_length('aes-256-ctr');
	}

	public static function cryptoAvailable($key = '')
	{
		if (!\Bitrix\Main\Config\Option::get("socialservices", "allow_encrypted_tokens", false))
			return false;

		return parent::cryptoAvailable($key);
	}

	public function decrypt($data)
	{
		if ($this->encryptionComplete)
			return parent::decrypt($data);

		if($data == '')
			return $data;

		try
		{
			$value = base64_decode($data);
			if (false === $value) // not base64 decoded so not encrypted
				return $data;

			if (mb_strlen($value, 'latin1') <= $this->ivLength) // too short to be encrypted
				return $data;

			$value = static::$cipher->decrypt($value, $this->cryptoKey);
			return $value;
		}
		catch(SecurityException $e)
		{
			return $data;
		}
	}
}