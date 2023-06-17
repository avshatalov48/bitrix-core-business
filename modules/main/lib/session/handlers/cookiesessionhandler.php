<?php

namespace Bitrix\Main\Session\Handlers;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Request;
use Bitrix\Main\Web\Cookie;
use Bitrix\Main\Web\CryptoCookie;
use Bitrix\Main\Web\Json;

class CookieSessionHandler implements \SessionHandlerInterface
{
	/** @var \Bitrix\Main\Request  */
	private $request;
	/** @var \Bitrix\Main\HttpResponse */
	private $response;
	/** @var int */
	private $lifetime;

	public function __construct(int $lifetime, Request $request = null)
	{
		$this->request = $request ?: Context::getCurrent()->getRequest();
		$this->lifetime = $lifetime;
	}

	public function close(): bool
	{
		return true;
	}

	private function setSecureAttribute(Cookie $cookie): Cookie
	{
		$context = Context::getCurrent();
		if (!$context)
		{
			return $cookie;
		}

		$request = $context->getRequest();
		$secure = (Option::get('main', 'use_secure_password_cookies', 'N') === 'Y' && $request->isHttps());
		$cookie
			->setHttpOnly(true)
			->setSecure($secure)
		;

		return $cookie;
	}

	public function destroy($sessionId): bool
	{
		$cookie = new Cookie($sessionId, null, -2628000);
		$cookie = $this->setSecureAttribute($cookie);

		$this->getResponse()->addCookie($cookie);

		return true;
	}

	public function gc($maxlifetime): int
	{
		return 0;
	}

	public function open($savePath, $name): bool
	{
		return true;
	}

	#[\ReturnTypeWillChange]
	public function read($sessionId)
	{
		$value = $this->request->getCookie($sessionId) ?: '';
		if (!$value)
		{
			return '';
		}

		try
		{
			$decoded = Json::decode($value);
		}
		catch (ArgumentException $exception)
		{
			return '';
		}

		if (is_array($decoded))
		{
			if (!isset($decoded['expires']))
			{
				return $decoded['data'];
			}
			if (time() <= $decoded['expires'])
			{
				return $decoded['data'];
			}
		}

		return '';
	}

	public function write($sessionId, $sessionData): bool
	{
		$expires = $this->lifetime ? (time() + $this->lifetime) : 0;

		$value = Json::encode([
			'data' => $sessionData,
			'createdAt' => time(),
			'expires' => $expires?: null,
		]);

		$cookie = new CryptoCookie($sessionId, $value, $expires);
		$cookie = $this->setSecureAttribute($cookie);

		$this->getResponse()->addCookie($cookie);

		return true;
	}

	/**
	 * @return \Bitrix\Main\HttpResponse
	 */
	public function getResponse(): \Bitrix\Main\HttpResponse
	{
		return $this->response?: Context::getCurrent()->getResponse();
	}

	/**
	 * @param \Bitrix\Main\HttpResponse $response
	 * @return $this
	 */
	public function setResponse($response)
	{
		$this->response = $response;

		return $this;
	}
}