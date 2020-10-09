<?php

namespace Bitrix\Main\Web;

use Bitrix\Main\Config;
use Bitrix\Main\Security\Cipher;
use Bitrix\Main\Security\SecurityException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\BinaryString;

final class CookiesCrypter
{
	public const COOKIE_MAX_SIZE              = 4096;
	public const COOKIE_RESERVED_SUFFIX_BYTES = 3;

	private const CIPHER_KEY_SUFFIX = 'cookiecrypter';

	/** @var string */
	protected $cipherKey;
	/** @var Cipher */
	protected $cipher;

	public function __construct()
	{}

	public function hasEncryptedCookiesInSettings(): bool
	{
		return !empty($this->getListToProcess());
	}

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

	public function encrypt(Cookie ...$cookies): iterable
	{
		$result = [];
		foreach ($cookies as $cookie)
		{
			if ($this->shouldEncrypt($cookie->getOriginalName()))
			{
				foreach($this->packCookie($cookie) as $partCookie)
				{
					$result[] = $partCookie;
				}
			}
			else
			{
				$result[] = $cookie;
			}
		}

		return $result;
	}

	public function decrypt(iterable $cookies): iterable
	{
		$result = [];
		foreach ($cookies as $name => $value)
		{
			if ($this->shouldDecrypt($name))
			{
				try
				{
					$result[$name] = $this->unpackCookie($value, $cookies);
				}
				catch (SecurityException $e)
				{
					//just skip cookies which we can't decrypt.
				}

			}
			else
			{
				$result[$name] = $value;
			}
		}

		return $result;
	}

	/**
	 * @param Cookie $cookie
	 * @return iterable|Cookie[]
	 */
	protected function packCookie(Cookie $cookie): iterable
	{
		$encryptedValue = $this->encryptValue($cookie->getValue());
		$length = BinaryString::getLength($encryptedValue);
		$maxContentLength = static::COOKIE_MAX_SIZE - static::COOKIE_RESERVED_SUFFIX_BYTES - BinaryString::getLength($cookie->getName());

		$i = 0;
		$parts = ($length / $maxContentLength);
		$pack = [];
		do
		{
			$startPosition = $i * $maxContentLength;
			$partCookie = clone $cookie;
			$partCookie->setName("{$cookie->getName()}_{$i}");
			$partCookie->setValue(BinaryString::getSubstring($encryptedValue, $startPosition, $maxContentLength));
			$pack["{$cookie->getOriginalName()}_{$i}"] = $partCookie;

			$i++;
		}
		while($parts > $i);

		$mainCookie = clone $cookie;
		$mainCookie->setValue(implode(',', array_keys($pack)));

		array_unshift($pack, $mainCookie);

		return $pack;
	}

	protected function unpackCookie(string $mainCookie, iterable $cookies): string
	{
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

	public function shouldEncrypt($cookieName): bool
	{
		return in_array($cookieName, $this->getListToProcess(), true);
	}

	public function shouldDecrypt($cookieName): bool
	{
		return $this->shouldEncrypt($cookieName);
	}

	public function getCipherKey(): string
	{
		return $this->cipherKey;
	}

	public function getListToProcess(): array
	{
		$configuration = Config\Configuration::getInstance();

		return $configuration->get('cookies')['encrypted'] ?? [];
	}
}