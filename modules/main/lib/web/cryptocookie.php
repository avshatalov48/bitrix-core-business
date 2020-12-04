<?php
namespace Bitrix\Main\Web;

class CryptoCookie extends Cookie
{
	public function copyAttributesTo(Cookie $cookie): void
	{
		$cookie
			->setDomain($this->getDomain())
			->setExpires($this->getExpires())
			->setHttpOnly($this->getHttpOnly())
			->setSpread($this->getSpread())
			->setPath($this->getPath())
			->setSecure($this->getSecure())
		;
	}
}
