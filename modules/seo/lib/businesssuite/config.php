<?php

namespace Bitrix\Seo\BusinessSuite;


use Bitrix\Seo\Retargeting;
use Bitrix\Seo\BusinessSuite\Exception;
use Bitrix\Seo\BusinessSuite\Configuration;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;

abstract class Config extends AbstractBase
{
	/**
	 * set config
	 * @param Configuration\IConfig $config
	 *
	 * @return Retargeting\Response|null
	 * @throws Exception\UnresolvedDependencyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function set(Configuration\IConfig $config) : ?Retargeting\Response
	{
		if (
			$config instanceof Facebook\Config &&
			($setup = Facebook\Setup::load()) &&
			($managerId = Facebook\Installs::load()->getBusinessManager())
		)
		{
			return $this->getRequest()->send([
				'methodName' => $this->getMethodName('config.change'),
				'parameters' => [
					'fbe_external_business_id' => $setup->get(Facebook\Setup::BUSINESS_ID) ,
					'business_config' => $config->toArray(),
					'business_id' => $managerId
				]]);
		}
		return null;
	}

	/**
	 * get config
	 * @return Retargeting\Response|null
	 * @throws \Bitrix\Main\SystemException
	 */
	public function get() : ?Retargeting\Response
	{
		if($setup = Facebook\Setup::load())
		{
			return $this->getRequest()->send([
				'methodName' => $this->getMethodName('config.get'),
				'parameters' => [
					'fbe_external_business_id' => $setup->get(Facebook\Setup::BUSINESS_ID)
				]]);
		}
		return null;
	}
}