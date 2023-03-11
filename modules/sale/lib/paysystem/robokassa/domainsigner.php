<?php

namespace Bitrix\Sale\PaySystem\Robokassa;

use Bitrix\Main;

class DomainSigner
{
	private string $domain;

	public function __construct(string $domain)
	{
		$this->domain = $domain;
	}

	public function signDomain(): string
	{
		if (Main\Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::RequestSign($this->domain);
		}

		$privateKey = Main\Analytics\Counter::getPrivateKey();
		return md5($this->domain . $privateKey);
	}

	public function isValidDomain(string $signedDomain): bool
	{
		return $signedDomain === $this->signDomain();
	}
}