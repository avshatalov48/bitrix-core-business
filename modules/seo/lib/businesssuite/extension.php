<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Seo\Retargeting;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;

abstract class Extension extends AbstractBase
{
	/**
	 * get installs FBE
	 * @return Retargeting\Response|null
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getInstalls(): ?Retargeting\Response
	{
		if ($setup = Facebook\Setup::load())
		{
			return $installs = $this->getRequest()->send(['methodName' => $this->getMethodName('installs.get'),
				'parameters' => [
					'fbe_external_business_id' => $setup->get(Facebook\Setup::BUSINESS_ID)
				]
			]);
		}
		return null;
	}

	/**
	 * uninstall FBE
	 * @return Retargeting\Response|null
	 * @throws \Bitrix\Main\SystemException
	 */
	public function uninstall() : ?Retargeting\Response
	{
		if ($setup = Facebook\Setup::load())
		{
			return $this->getRequest()->send([
				'methodName' => $this->getMethodName('installs.delete'),
				'parameters' => [
					'fbe_external_business_id' => $setup->get(Facebook\Setup::BUSINESS_ID)
				]
			]);
		}
		return null;
	}
}