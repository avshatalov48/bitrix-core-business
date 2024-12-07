<?php

namespace Bitrix\Main\Web;

/**
 * Class CryptoCookie
 * Declares the class for working with cookies which values are encrypted.
 */
class CryptoCookie extends Cookie
{
	/**
	 * Copies attributes from another cookie to this one.
	 * @param Cookie $cookie Cookie to copy attributes from.
	 * @return void
	 */
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
