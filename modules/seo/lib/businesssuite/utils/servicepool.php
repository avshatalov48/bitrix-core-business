<?php

namespace Bitrix\Seo\BusinessSuite\Utils;

use Bitrix\Main;
use Bitrix\Seo\BusinessSuite\ServiceAdapter;
use Bitrix\Seo\BusinessSuite\ServiceWrapper;
use Bitrix\Seo\Retargeting\IMultiClientService;
use Bitrix\Seo\Retargeting\IService;

final class ServicePool
{
	/** @var self[] */
	private static $pool = [];

	/**@var string*/
	private $type;

	/** @var IService|null $current */
	private $current;

	public static function getInstance(string $type) : self
	{
		if(!array_key_exists($type,static::$pool))
		{
			static::$pool[$type] = new self($type);
		}
		return static::$pool[$type];
	}

	private function __construct(string $type)
	{
		$this->type = $type;
	}

	public function getService()
	{
		return $this->current = $this->current ?? static::getNextService($this->type);
	}

	private static function getNextService($type) : ?ServiceWrapper
	{
		while ($data = ServiceQueue::getInstance($type)->getHead())
		{
			try
			{
				['TYPE' => $type, 'CLIENT_ID' => $id, 'SERVICE_TYPE' => $serviceType] = $data;
				if (isset($id, $type, $serviceType))
				{
					$wrapper =
						ServiceAdapter::createServiceWrapperContainer()
							->getInstance()
							->setService(ServiceFactory::getServiceByEngineCode($serviceType))
							->setClientId($id);

					if ($wrapper::getAuthAdapter($type)->hasAuth())
					{
						return $wrapper;
					}
				}
			}
			catch (\Throwable $exception)
			{}

			ServiceQueue::getInstance($type)->removeHead();
		}
		return null;
	}

}