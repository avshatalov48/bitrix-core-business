<?php

namespace Bitrix\Main\Security\Sign;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config;

/**
 * Class Signer
 * @since 14.0.7
 * @package Bitrix\Main\Security\Sign
 */
class Signer
{
	/** @var SigningAlgorithm Signing algorithm */
	protected $algorithm = null;
	protected $separator = '.';
	/** @var string Secret key */
	protected $key = null;

	/**
	 * Creates new Signer object. If you want to use your own signing algorithm - you can do this.
	 *
	 * @param SigningAlgorithm|null $algorithm Custom signing algorithm.
	 */
	public function __construct(SigningAlgorithm $algorithm = null)
	{
		if ($algorithm !== null)
		{
			$this->algorithm = $algorithm;
		}
		else
		{
			$this->algorithm = new HmacAlgorithm();
		}
	}

	/**
	 * Sets key for signing.
	 *
	 * @param string $value Key.
	 * @return $this
	 * @throws ArgumentTypeException
	 */
	public function setKey($value)
	{
		if (!is_string($value))
		{
			throw new ArgumentTypeException('value', 'string');
		}

		$this->key = $value;
		return $this;
	}

	/**
	 * Returns separator, used for packing/unpacking.
	 *
	 * @return string
	 */
	public function getSeparator()
	{
		return $this->separator;
	}

	/**
	 * Sets separator, used for packing/unpacking.
	 *
	 * @param string $value Separator.
	 * @return $this
	 * @throws ArgumentTypeException
	 */
	public function setSeparator($value)
	{
		if (!is_string($value))
		{
			throw new ArgumentTypeException('value', 'string');
		}

		$this->separator = $value;
		return $this;
	}

	/**
	 * Returns message signature.
	 *
	 * @param string $value Message.
	 * @param string|null $salt Salt.
	 * @return string
	 * @throws ArgumentTypeException
	 */
	public function getSignature($value, $salt = null)
	{
		if (!is_string($value))
		{
			throw new ArgumentTypeException('value', 'string');
		}

		$key = $this->getKey($salt);
		$signature = $this->algorithm->getSignature($value, $key);
		$signature = $this->encodeSignature($signature);
		return $signature;
	}

	/**
	 * Signs message, returns string in format "{message}{separator}{signature}".
	 *
	 * Simple example:
	 * <code>
	 *  // If salt needed
	 *  $foo = (new Signer)->sign('test', 'my_salt');
	 *
	 *  // Otherwise
	 *  $bar = (new Signer)->sign('test');
	 * </code>
	 *
	 * @param string $value Message for signing.
	 * @param string|null $salt Salt, if needed.
	 * @return string
	 * @throws ArgumentTypeException
	 */
	public function sign($value, $salt = null)
	{
		if (!is_string($value))
		{
			throw new ArgumentTypeException('value', 'string');
		}

		$signature = $this->getSignature($value, $salt);
		return $this->pack([$value, $signature]);
	}

	/**
	 * Checks message signature and return original message.
	 *
	 * Simple example:
	 * <code>
	 *  $signer = new Signer;
	 *
	 *  // Sign message
	 *  $signedValue = $signer->sign('test');
	 *
	 *  // Get original message with checking
	 *  echo $signer->unsign($signedValue);
	 *  // Output: 'test'
	 *
	 *  // Try to unsigning not signed value
	 *  echo $signer->unsign('test');
	 *  //throw BadSignatureException with message 'Separator not found in value'
	 *
	 *  // Or with invalid sign
	 *  echo $signer->unsign('test.invalid_sign');
	 *
	 *  // Or invalid salt
	 *  //throw BadSignatureException with message 'Signature does not match'
	 *  echo $signer->unsign($signedValue, 'invalid_salt');
	 *
	 * </code>
	 *
	 * @param string $signedValue Signed value, must be in format "{message}{separator}{signature}".
	 * @param string|null $salt Salt, if used while signing.
	 * @return string
	 * @throws BadSignatureException
	 * @throws ArgumentTypeException
	 */
	public function unsign($signedValue, $salt = null)
	{
		if (!is_string($signedValue))
		{
			throw new ArgumentTypeException('signedValue', 'string');
		}

		[$value, $signature] = $this->unpack($signedValue);
		if (!$this->verifySignature($value, $signature, $salt))
		{
			throw new BadSignatureException('Signature does not match');
		}

		return $value;
	}

	/**
	 * Simply validation of message signature.
	 *
	 * @param string $value Message.
	 * @param string $signature Signature.
	 * @param string|null $salt Salt, if used while signing.
	 * @return bool True if OK, otherwise - false.
	 */
	public function validate($value, $signature, $salt = null)
	{
		if (is_string($value) && is_string($signature))
		{
			return $this->verifySignature($value, $signature, $salt);
		}
		return false;
	}

	/**
	 * Verifies message signature provided by hashing algorithm.
	 *
	 * @param string $value Message.
	 * @param string $sig Signature.
	 * @param string|null $salt Salt, if used while signing.
	 * @return bool
	 */
	protected function verifySignature($value, $sig, $salt = null)
	{
		$key = $this->getKey($salt);
		$signature = $this->decodeSignature($sig);
		return $this->algorithm->verify($value, $key, $signature);
	}

	/**
	 * Returns salted key for signing.
	 * If key was set by setKey - use it
	 * Otherwise - used default (if default key does not exist - automatically generate it)
	 *
	 * @param string|null $salt Salt, if needed.
	 * @return string
	 * @throws BadSignatureException
	 */
	protected function getKey($salt = null)
	{
		if ($salt !== null && !preg_match('#^[a-zA-Z0-9_.-]{3,50}$#D', $salt))
		{
			throw new BadSignatureException('Malformed salt, only [a-zA-Z0-9_.-]{3,50} characters are acceptable');
		}

		if ($this->key !== null)
		{
			$key = $this->key;
		}
		else
		{
			$key = $this->getDefaultKey();
		}

		return $salt . $key;
	}

	/**
	 * Returns default (system) key for signing or generate if it does not exist.
	 *
	 * @return string
	 */
	protected function getDefaultKey()
	{
		static $defaultKey = null;
		if ($defaultKey === null)
		{
			$defaultKey = Config\Option::get('main', 'signer_default_key', false);
			if (!$defaultKey)
			{
				$defaultKey = hash('sha512', \Bitrix\Main\Security\Random::getString(64));
				Config\Option::set('main', 'signer_default_key', $defaultKey);
			}

			$options = Config\Configuration::getValue("crypto");
			if (isset($options["crypto_key"]))
			{
				$defaultKey .= $options["crypto_key"];
			}
		}

		return $defaultKey;
	}

	/**
	 * Packs array values to single string:
	 * pack(['test', 'all', 'values']) -> 'test.all.values'
	 *
	 * @param array $values Values for packing.
	 * @return string
	 */
	public function pack(array $values)
	{
		return join($this->separator, $values);
	}

	/**
	 * Unpacks values from string (something like rsplit).
	 * Simple example for separator ".":
	 * <code>
	 *  // Unpack all values:
	 *  unpack('test.all.values', 0) -> ['test', 'all', 'values']
	 *
	 *  // Unpack 2 values (by default). First element containing the rest of string.
	 *  unpack('test.all.values') -> ['test.all', 'values']
	 *
	 *  // Exception if separator is missing
	 *  unpack('test.all values', 3) -> throws BadSignatureException
	 * </code>
	 *
	 * @param string $value String for unpacking.
	 * @param int $limit If $limit === 0 - unpack all values, default - 2.
	 * @return array
	 * @throws BadSignatureException
	 */
	public function unpack($value, $limit = 2)
	{
		// Some kind of optimization
		if ($limit === 0)
		{
			if (!str_contains($value, $this->separator))
			{
				throw new BadSignatureException('Separator not found in value');
			}

			return explode($this->separator, $value);
		}

		$result = [];
		while (--$limit > 0)
		{
			$pos = bxstrrpos($value, $this->separator);
			if ($pos === false)
			{
				throw new BadSignatureException('Separator not found in value');
			}

			$result[] = mb_substr($value, $pos + 1);
			$value = mb_substr($value, 0, $pos);
		}
		$result[] = $value;

		return array_reverse($result);
	}

	/**
	 * Returns encoded signature.
	 *
	 * @param string $value Signature in binary representation.
	 * @return string Encoded signature
	 */
	protected function encodeSignature($value)
	{
		return bin2hex($value);
	}

	/**
	 * Returns decoded signature.
	 *
	 * @param string $value Encoded signature.
	 * @return string Signature in binary representation
	 * @throws BadSignatureException
	 */
	protected function decodeSignature($value)
	{
		if (preg_match('#[^[:xdigit:]]#', $value))
		{
			throw new BadSignatureException('Signature must be hexadecimal string');
		}

		// ToDo: use hex2bin instead pack for PHP > 5.4.0
		return pack('H*', $value);
	}
}
