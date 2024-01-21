<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */
namespace Bitrix\Main\Security;

/**
 * Asymmetric cipher based on public key
 */
class PublicKeyCipher extends AsymmetricCipher
{
	/**
	 * @inheritdoc
	 */
	protected function doEncrypt($data, $key)
	{
		$success = openssl_public_encrypt($data, $out, $key);

		return $success ? $out : false;
	}

	/**
	 * @inheritdoc
	 */
	protected function doDecrypt($data, $key)
	{
		$success = openssl_public_decrypt($data, $out, $key);

		return $success ? $out : false;
	}

	/**
	 * @inheritdoc
	 */
	protected function getKeyInfo($key)
	{
		return openssl_pkey_get_details(openssl_get_publickey($key));
	}
}