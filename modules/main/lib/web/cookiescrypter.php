<?php

namespace Bitrix\Main\Web;

use Bitrix\Main\Config;
use Bitrix\Main\Security\Cipher;
use Bitrix\Main\Security\SecurityException;
use Bitrix\Main\SystemException;

final class CookiesCrypter
{
	public const COOKIE_MAX_SIZE              = 4096;
	public const COOKIE_RESERVED_SUFFIX_BYTES = 3;

	private const SIGN_PREFIX       = '-crpt-';
	private const CIPHER_KEY_SUFFIX = 'cookiecrypter';

	/** @var string */
	protected $cipherKey;
	/** @var Cipher */
	protected $cipher;

	public function __construct()
	{}

	protected function buildCipher(): self
	{
		if ($this->cipher)
		{
			return $this;
		}

		$configuration = Config\Configuration::getInstance();

		$this->cipher = new Cipher();
		$this->cipherKey = $configuration->get('crypto')['crypto_key'] ?? null;
		if (!$this->cipherKey)
		{
			throw new SystemException('There is no crypto[crypto_key] in .settings.php. Generate it.');
		}
		$this->cipherKey = $this->prependSuffixToKey($this->cipherKey);

		return $this;
	}

	protected function prependSuffixToKey(string $key): string
	{
		return $key . self::CIPHER_KEY_SUFFIX;
	}

	/**
	 * @param CryptoCookie $cookie
	 * @return iterable|Cookie[]
	 */
	public function encrypt(CryptoCookie $cookie): iterable
	{
		$result = [];
		$encryptedValue = $this->encryptValue($cookie->getValue());
		foreach ($this->packCookie($cookie, $encryptedValue) as $partCookie)
		{
			$result[] = $partCookie;
		}

		return $result;
	}

	public function decrypt(string $name, string $value, iterable $cookies): string
	{
		if (!$this->shouldDecrypt($name, $value))
		{
			return $value;
		}

		try
		{
			return $this->unpackCookie($value, $cookies);
		}
		catch (SecurityException $e)
		{
			//just skip cookies which we can't decrypt.
		}

		return '';
	}

	/**
	 * @param CryptoCookie $cookie
	 * @param string       $encryptedValue
	 * @return iterable|Cookie[]
	 */
	protected function packCookie(CryptoCookie $cookie, string $encryptedValue): iterable
	{
		$length = strlen($encryptedValue);
		$maxContentLength = static::COOKIE_MAX_SIZE - static::COOKIE_RESERVED_SUFFIX_BYTES - strlen($cookie->getName());

		$i = 0;
		$parts = ($length / $maxContentLength);
		$pack = [];
		do
		{
			$startPosition = $i * $maxContentLength;
			$partCookie = new Cookie("{$cookie->getName()}_{$i}", substr($encryptedValue, $startPosition, $maxContentLength));
			$cookie->copyAttributesTo($partCookie);
			$pack["{$cookie->getOriginalName()}_{$i}"] = $partCookie;

			$i++;
		}
		while($parts > $i);

		$mainCookie = new Cookie($cookie->getName(), $this->prependSign(implode(',', array_keys($pack))));
		$cookie->copyAttributesTo($mainCookie);

		array_unshift($pack, $mainCookie);

		return $pack;
	}

	protected function unpackCookie(string $mainCookie, iterable $cookies): string
	{
		$mainCookie = $this->removeSign($mainCookie);
		$packedNames = array_flip(array_filter(explode(',', $mainCookie)));
		$parts = [];

		foreach ($cookies as $name => $value)
		{
			if (!isset($packedNames[$name]))
			{
				continue;
			}

			$parts[$packedNames[$name]] = $value;
			if (count($parts) === count($packedNames))
			{
				break;
			}
		}
		ksort($parts);
		$encryptedValue = implode('', $parts);

		return $this->decryptValue($encryptedValue);
	}

	protected function encryptValue(string $value): string
	{
		$this->buildCipher();
		if (function_exists('gzencode'))
		{
			$value = gzencode($value);
		}

		return $this->encodeUrlSafeB64($this->cipher->encrypt($value, $this->getCipherKey()));
	}

	protected function decryptValue(string $value): string
	{
		$this->buildCipher();

		$value = $this->cipher->decrypt($this->decodeUrlSafeB64($value), $this->getCipherKey());
		if (function_exists('gzdecode'))
		{
			$value = gzdecode($value);
		}

		return $value;
	}

	private function decodeUrlSafeB64($input)
	{
		$padLength = 4 - strlen($input) % 4;
		$input .= str_repeat('=', $padLength);

		return base64_decode(strtr($input, '-_', '+/'));
	}

	private function encodeUrlSafeB64($input)
	{
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}

	public function shouldEncrypt(Cookie $cookie): bool
	{
		return $cookie instanceof CryptoCookie;
	}

	public function shouldDecrypt(string $cookieName, string $cookieValue): bool
	{
		return strpos($cookieValue, self::SIGN_PREFIX) === 0;
	}

	protected function prependSign(string $value): string
	{
		return self::SIGN_PREFIX . $value;
	}

	protected function removeSign(string $value): string
	{
		return substr($value, strlen(self::SIGN_PREFIX));
	}

	public function getCipherKey(): string
	{
		return $this->cipherKey;
	}
}