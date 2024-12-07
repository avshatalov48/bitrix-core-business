<?php

namespace Bitrix\Main\Mail\Smtp;

use Bitrix\Main\Security\Sign\Signer;

class CloudOAuthRefreshData
{
	public function __construct(
		public readonly string $uid,
		public readonly int $expires,
	) {}

	protected function getSignPayload(): string
	{
		return implode('_', [
			$this->uid,
			$this->expires,
		]);
	}

	public function getSign(): string
	{
		return $this->getSigner()->getSignature($this->getSignPayload(), static::getSignSalt());
	}

	public function isSignValid(string $sign): bool
	{
		try
		{
			return $this->getSigner()->validate($this->getSignPayload(), $sign, static::getSignSalt());
		}
		catch (\Exception $exception)
		{
			return false;
		}
	}

	protected function getSigner(): Signer
	{
		return new Signer();
	}

	protected static function getSignSalt(): string
	{
		return 'oauth_email_token_refresh';
	}
}
