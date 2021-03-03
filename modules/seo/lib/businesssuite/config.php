<?php

namespace Bitrix\Seo\BusinessSuite;


use Bitrix\Seo\Retargeting;
use Bitrix\Seo\BusinessSuite\Configuration;

abstract class Config extends AbstractBase
{
	/**
	 * @param Configuration\IConfig $config
	 *
	 * @return Retargeting\Response|null
	 */
	abstract function set(Configuration\IConfig $config) : ?Retargeting\Response;

	/**
	 * @return Retargeting\Response|null
	 */
	abstract function get() : ?Retargeting\Response;
}