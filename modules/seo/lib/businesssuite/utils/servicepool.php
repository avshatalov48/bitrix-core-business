<?php

namespace Bitrix\Seo\BusinessSuite\Utils;

use Bitrix\Seo\BusinessSuite\ServiceAdapter;
use Bitrix\Seo\BusinessSuite\ServiceMetaData;
use Bitrix\Seo\BusinessSuite\ServiceWrapper;

final class ServicePool
{
	private static function buildService($type, $clientId, $serviceType) : ?ServiceWrapper
	{
		if (is_string($type) && $clientId && is_string($serviceType))
		{
			return ServiceAdapter::createServiceWrapperContainer()->setMeta(
				ServiceMetaData::create()
					->setType($type)
					->setEngineCode($serviceType)
					->setClientId($clientId)
					->setService(ServiceFactory::getServiceByEngineCode($serviceType))
			);
		}
		return null;
	}

	/**
	 * build service Wrapper
	 * @param array|string $type
	 *
	 * @return ServiceWrapper|null
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getService($type) : ?ServiceWrapper
	{
		$types = (is_array($type)? $type : [$type]);
		foreach ($types as $type)
		{
			while ($data = ServiceQueue::getInstance($type)->getHead())
			{
				try
				{
					$wrapper = static::buildService($data['TYPE'],$data['CLIENT_ID'],$data['SERVICE_TYPE']);
				}
				finally
				{
					if ($wrapper && $wrapper::getAuthAdapter($type)->hasAuth())
					{
						return $wrapper;
					}
					ServiceQueue::getInstance($type)->removeHead();
				}
			}
		}
		return null;
	}

}