<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */
namespace Bitrix\Main\Security;

abstract class AsymmetricCipher
{
	/**
	 * @param $data
	 * @param $key
	 * @return false|string
	 */
	public function encrypt($data, $key): bool|string
	{
		$keyInfo  = $this->getKeyInfo($key);
		$chunkSize = $keyInfo['bits']/8 - 11;

		$result = '';

		foreach (str_split($data, $chunkSize) as $chunk)
		{
			$encryptedChunk = $this->doEncrypt($chunk, $key);

			if ($encryptedChunk === false)
			{
				return false;
			}

			$result .= $encryptedChunk;
		}

		return base64_encode($result);
	}

	/**
	 * @param $data
	 * @param $key
	 * @return false|string
	 */
	public function decrypt($data, $key): bool|string
	{
		$result = '';

		$keyInfo  = $this->getKeyInfo($key);
		$blockSize = $keyInfo['bits']/8;

		foreach(str_split(base64_decode($data), $blockSize) as $chunk)
		{
			$decryptedChunk = $this->doDecrypt($chunk, $key);

			if ($decryptedChunk === false)
			{
				return false;
			}

			$result .= $decryptedChunk;
		}

		return $result;
	}

	/**
	 * @param string $data
	 * @param string $key
	 * @return string|false
	 */
	abstract protected function doEncrypt($data, $key);

	/**
	 * @param string $data
	 * @param string $key
	 * @return string|false
	 */
	abstract protected function doDecrypt($data, $key);

	/**
	 * @param $key
	 * @return array|mixed
	 */
	abstract protected function getKeyInfo($key);
}