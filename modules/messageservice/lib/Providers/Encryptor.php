<?php

namespace Bitrix\MessageService\Providers;

use Bitrix\Main\Security\Cipher;

trait Encryptor
{
	protected static function encrypt(string $data, string $cryptoKey): string
	{
		$encryptedData = '';
		try
		{
			$cipher = new Cipher();

			$encryptedData = $cipher->encrypt($data, $cryptoKey);
			$encryptedData = \base64_encode($encryptedData);
		}
		catch (\Bitrix\Main\Security\SecurityException $e)
		{}

		return $encryptedData;
	}

	protected static function decrypt(string $encryptedData, string $cryptoKey): string
	{
		$decryptedData = '';
		try
		{
			$cipher = new Cipher();

			$decryptedData = base64_decode($encryptedData);
			$decryptedData = $cipher->decrypt($decryptedData, $cryptoKey);
		}
		catch(\Bitrix\Main\Security\SecurityException $e)
		{}

		return $decryptedData;
	}
}