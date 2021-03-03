<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Seo\Retargeting;

abstract class Extension extends AbstractBase
{
	/**
	 * @return Retargeting\Response|null
	 */
	abstract function getInstalls() : ?Retargeting\Response;

	/**
	 * @return Retargeting\Response|null
	 */
	abstract function uninstall() : ?Retargeting\Response;
}