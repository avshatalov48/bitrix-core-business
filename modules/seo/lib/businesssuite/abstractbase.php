<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Seo\BusinessSuite;
use Bitrix\Seo\Retargeting;

abstract class AbstractBase extends Retargeting\BaseApiObject
{
	protected function getService() : IInternalService
	{
		return $this->service;
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	protected function getMethodName(string $name) : string
	{
		return $this->getService()->getMethodPrefix().'.'.$name;
	}
}