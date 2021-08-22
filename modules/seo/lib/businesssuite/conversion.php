<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Main\Config;
use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Conversion\Facebook\Event;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;
use Bitrix\Seo\Conversion\ConversionEventInterface;

abstract class Conversion extends AbstractBase
{
	/**
	 * @param array $events
	 *
	 * @return array
	 */
	protected function getData(array $events) : array
	{
		return array_filter(array_map(
			function($item) {
				return $item instanceof Event && $item->validate()? $item->prepareData() : null;
			},
			$events
		));
	}

	/**
	 * @param ConversionEventInterface[] $events
	 *
	 * @return Retargeting\Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function fireEvents(array $events) : ?Retargeting\Response
	{
		$facade = ExtensionFacade::getInstance();
		if($facade->isInstalled() && !empty($events = $this->getData($events)))
		{

			return $this->getRequest()->send([
				'methodName' => $this->getMethodName('conversion.event.fire'),
				'parameters' => array_filter([
					'fbe_external_business_id' => $facade->getCurrentSetup()->get(Facebook\Setup::BUSINESS_ID),
					'business_manager_id' => $facade->getCurrentInstalls()->getBusinessManager(),
					'pixel_id' => $facade->getCurrentInstalls()->getPixel(),
					'test_code' => Config\Option::get('seo', 'facebook_conversion_test_code', null),
					'events' => $events
				])
			]);
		}

		return null;
	}
}