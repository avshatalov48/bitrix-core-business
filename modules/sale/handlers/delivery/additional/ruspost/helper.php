<?
namespace Sale\Handlers\Delivery\Additional\RusPost;

use Bitrix\Main\Error;
use \Bitrix\Sale\Delivery\Services;
use Bitrix\Sale\Result;

/**
 * Class Helper
 * @package Sale\Handlers\Delivery\Additional\RusPost
 */
class Helper
{
	/**
	 * @param int $deliveryId
	 * @param bool $useCache
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getEnabledShippingPointsList($deliveryId, $useCache = true)
	{
		$deliveryId = (int)$deliveryId;

		if($deliveryId <= 0)
		{
			return [];
		}

		if(!($deliveryService = Services\Manager::getObjectById($deliveryId)))
		{
			return [];
		}

		if($useCache)
		{
			$ttl = 86400; //day
			$cacheId = "SaleHandlersDeliveryAdditionalRusPost".
				$deliveryId.
				"ShippingPoints".
				md5(serialize($deliveryService->getConfigValues())
			);

			$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();

			if($cacheManager->read($ttl, $cacheId))
			{
				return $cacheManager->get($cacheId);
			}
		}

		$res = self::getEnabledShippingPointsListResult($deliveryId);
		$result = $res->getData();

		if($useCache)
		{
			$cacheManager->set($cacheId, $result);
		}

		return $result;
	}

	/**
	 * @param int $deliveryId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getEnabledShippingPointsListResult($deliveryId)
	{
		$result = new Result();
		$deliveryId = (int)$deliveryId;

		if($deliveryId <= 0)
		{
			$result->addError(new Error('deliveryId is less than zero'));
			return $result;
		}

		if(!($deliveryService = Services\Manager::getObjectById($deliveryId)))
		{
			$result->addError(new Error('Can\t obtain delivery object'));
			return $result;
		}

		/** @var Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler $deliveryRequest*/
		if(!($deliveryRequest = $deliveryService->getDeliveryRequestHandler()))
		{
			$result->addError(new Error('Can\t obtain request handler'));
			return $result;
		}

		if(get_class($deliveryRequest) != 'Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler')
		{
			$result->addError(new Error('Can\t obtain class Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler'));
			return $result;
		}

		$points = [];
		/** @var Result $res */
		$res = $deliveryRequest->send('USER_SETTINGS', []);

		if($res->isSuccess())
		{
			$data = $res->getData();

			if(is_array($data['shipping-points']))
			{
				foreach($data['shipping-points'] as $sPoint)
				{
					if($sPoint['enabled'] == 1)
					{
						$points[$sPoint['operator-postcode']] = $sPoint;
					}
				}
			}

			$result->setData($points);
		}
		else
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}

	/**
	 * @param \Sale\Handlers\Delivery\AdditionalHandler $deliveryService
	 * @return array
	 */
	public static function getSelectedShippingPoint($deliveryService)
	{
		if(!$deliveryService)
		{
			return [];
		}

		$result = [];
		$config = $deliveryService->getConfigValues();

		if(isset($config['MAIN']['SHIPPING_POINT']['VALUE']))
		{
			$result = $config['MAIN']['SHIPPING_POINT'];
		}

		return $result;
	}
}
