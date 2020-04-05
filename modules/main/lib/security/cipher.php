<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Security;

use Bitrix\Main\Text\BinaryString;

class Cipher
{
	protected $cipherAlgorithm;
	protected $hashAlgorithm;
	protected $ivLength;
	protected $calculateHash;

	/**
	 * Cipher constructor. Aes-256-ctr and sha256 are the best currently known methods.
	 * @param string $cipherAlgorithm
	 * @param string $hashAlgorithm
	 * @param bool $calculateHash
	 * @throws SecurityException
	 */
	public function __construct($cipherAlgorithm = 'aes-256-ctr', $hashAlgorithm = 'sha256', $calculateHash = true)
	{
		if(!function_exists('openssl_get_cipher_methods'))
		{
			throw new SecurityException("Openssl extension is not available.");
		}
		if(!in_array($cipherAlgorithm, openssl_get_cipher_methods(true)))
		{
			throw new SecurityException("Unknown cipher algorithm {$cipherAlgorithm}.");
		}
		if(!in_array($hashAlgorithm, openssl_get_md_methods(true)))
		{
			throw new SecurityException("Unknown hash algorithm {$hashAlgorithm}.");
		}

		$this->cipherAlgorithm = $cipherAlgorithm;
		$this->hashAlgorithm = $hashAlgorithm;
		$this->ivLength = openssl_cipher_iv_length($cipherAlgorithm);
		$this->calculateHash = (bool)$calculateHash;
	}

	/**
	 * Encrypts the data by key (symmetric cipher).
	 * @param string $data
	 * @param string $key
	 * @return string Base64 encoded.
	 * @throws SecurityException
	 */
	public function encrypt($data, $key)
	{
		// Initialisation vector: it MUST be different every time
		$iv = openssl_random_pseudo_bytes($this->ivLength, $strong);
		if(!$strong)
		{
			throw new SecurityException("Not a strong initialisation vector.");
		}

		// Hash the key: we shouldn't use the password itself, it can be weak
		$keyHash = openssl_digest($iv.$key, $this->hashAlgorithm, true);

		if($this->calculateHash)
		{
			//store the hash to check on reading
			$dataHash = openssl_digest($data, $this->hashAlgorithm, true);
			$data = $dataHash.$data;
		}

		// Encrypt the data
		$encrypted = openssl_encrypt($data, $this->cipherAlgorithm, $keyHash, OPENSSL_RAW_DATA, $iv);
		if($encrypted === false)
		{
			throw new SecurityException("Encryption failed: ".openssl_error_string());
		}

		// Store IV with encrypted data to use it for decryption
		$res = $iv.$encrypted;

		return $res;
	}

	/**
	 * Decrypts the data by key (symmetric cipher).
	 * @param string $data Base64 encoded
	 * @param string $key
	 * @return string
	 * @throws SecurityException
	 */
	public function decrypt($data, $key)
	{
		// Extract the initialisation vector and encrypted data
		$iv = BinaryString::getSubstring($data, 0, $this->ivLength);
		$raw = BinaryString::getSubstring($data, $this->ivLength);

		// Hash the key
		$keyHash = openssl_digest($iv.$key, $this->hashAlgorithm, true);

		// Decrypt
		$result = openssl_decrypt($raw, $this->cipherAlgorithm, $keyHash, OPENSSL_RAW_DATA, $iv);
		if($result === false)
		{
			throw new SecurityException("Decryption failed: ".openssl_error_string());
		}

		if($this->calculateHash)
		{
			//extract the hash and decrypted data
			$length = BinaryString::getLength($keyHash);
			$hash = BinaryString::getSubstring($result, 0, $length);
			$result = BinaryString::getSubstring($result, $length);

			//check the hash: may be the crypto key has changed? It shouldn't.
			$dataHash = openssl_digest($result, $this->hashAlgorithm, true);
			if($dataHash !== $hash)
			{
				throw new SecurityException("The hash is incorrect: the data was corrupted or a wrong key was supplied.");
			}
		}
		return $result;
	}
}
