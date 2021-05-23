<?

namespace Sale\Handlers\Delivery\Additional;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\ExtraServices\Manager;
use Bitrix\Sale\Delivery\Services\Table;
use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\ExternalTable;
use Bitrix\Sale\Location\Name\LocationTable;
use Bitrix\Sale\Result;
use Bitrix\Sale\ResultSerializable;
use Bitrix\Sale\Shipment;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Sale\Handlers\Delivery\AdditionalHandler;

Loc::loadMessages(__FILE__);

/**
 * Class RestClient
 * Allows exchanging information with server via rest
 * Caches some requests if it possible
 * @package Sale\Handlers\Delivery\Additional
 */
class RestClient extends \Bitrix\Sale\Services\Base\RestClient
{
	const WRONG_LICENSE_OPTION = 'handlers_dlv_add_rest_wrong_license';

	/**
	 * @return \Bitrix\Sale\Result
	 */
	public function getDeliveryList()
	{
		return $this->getItem('delivery.list', CacheManager::TYPE_DELIVERY_LIST);
	}

	/**
	 * @param string $serviceType
	 * @return \Bitrix\Sale\Result
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function  getDeliveryProfilesList($serviceType)
	{
		return $this->getItem(
			'delivery.profile.list',
			CacheManager::TYPE_PROFILES_LIST,
			array('serviceType' => $serviceType),
			array($serviceType)
		);
	}

	/**
	 * @param string $serviceType
	 * @param string $profileType
	 * @return \Bitrix\Sale\Result
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function getDeliveryProfileFields($serviceType, $profileType)
	{
		return $this->getItem(
			'delivery.profile.fields',
			CacheManager::TYPE_PROFILE_FIELDS,
			array('serviceType' => $serviceType, 'profileType' => $profileType),
			array($serviceType, $profileType)
		);
	}

	/**
	 * @param string $serviceType
	 * @return \Bitrix\Sale\Result
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function getDeliveryFields($serviceType)
	{
		return $this->getItem(
			'delivery.fields',
			CacheManager::TYPE_DELIVERY_FIELDS,
			array('serviceType' => $serviceType),
			array($serviceType)
		);
	}

	/**
	 * @param string $serviceType
	 * @param string $profileType
	 * @param array $serviceParams
	 * @param array $profileParams
	 * @param Shipment $shipment
	 * @return CalculationResult
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function getDeliveryPrice($serviceType, $profileType, array $serviceParams, array $profileParams, Shipment $shipment)
	{
		$params = array(
			'serviceType' => $serviceType,
			'profileType' => $profileType,
			'serviceParams' => $serviceParams,
			'profileParams' => $profileParams,
			'shipmentParams' => AdditionalHandler::getShipmentParams($shipment, $serviceType)
		);

		$hash = md5(serialize($params));

		$answer = $this->getItem(
			'delivery.price',
			CacheManager::TYPE_DELIVERY_PRICE,
			$params,
			array($hash)
		);

		if($answer->isSuccess())
		{
			$data = $answer->getData();
			$result = unserialize(rawurldecode($data['PRICE']), ['allowed_classes' => [CalculationResult::class]]);

			if(!($result instanceof CalculationResult))
			{
				$result = new CalculationResult();
				$result->addError(new Error('Error. Incorrect answer about price!'));
				return $result;
			}

			if(!$result->isSuccess() && defined('SALE_HANDLERS_DLV_ADD_LOG_PRICE_ERRORS'))
			{
				$eventLog = new \CEventLog();
				$eventLog->Add(array(
					"SEVERITY" => $eventLog::SEVERITY_ERROR,
					"AUDIT_TYPE_ID" => "SALE_HANDLERS_DLV_ADD_LOG_PRICE_ERRORS",
					"MODULE_ID" => "sale",
					"ITEM_ID" => $serviceType.'_'.$profileType,
					"DESCRIPTION" => implode(', ', $result->getErrorMessages()),
				));
			}
		}
		else
		{
			$result = new CalculationResult();
			$result->addErrors($answer->getErrors());
		}

		return $result;
	}

	public function getProfileConfig($serviceType, $profileType)
	{
		return $this->getItem(
			'delivery.profile.config',
			CacheManager::TYPE_PROFILE_CONFIG,
			array('serviceType' => $serviceType, 'profileType' => $profileType),
			array($serviceType, $profileType)
		);
	}

	public function getDeliveryExtraServices($serviceType)
	{
		return $this->getItem(
			'delivery.extraservices',
			CacheManager::TYPE_EXTRA_SERVICES,
			array('serviceType' => $serviceType),
			array($serviceType)
		);
	}

	public function getProfileExtraServices($serviceType, $profileType, array $profileParams)
	{
		return $this->getItem(
			'delivery.profile.extraservices',
			CacheManager::TYPE_EXTRA_SERVICES,
			array('serviceType' => $serviceType, 'profileType' => $profileType, 'profileParams' => $profileParams),
			array($serviceType, $profileType, $profileParams)
		);
	}

	public function getTrackingStatuses($serviceType, array $serviceParams, array $trackingNumbers = array())
	{
		return $this->getItem(
			'delivery.tracking.statuses',
			CacheManager::TYPE_NONE,
			array('serviceType' => $serviceType, 'serviceParams' => $serviceParams, 'trackingNumbers' => $trackingNumbers)
		);
	}

	protected function setCacheItem($result, $cacheType, array $cacheIds)
	{
		$cache = CacheManager::getItem($cacheType);
		$cache->set(serialize($result), $cacheIds);
	}

	protected function getItem($callMethod, $cacheType, array $callParams = array(), array $cacheIds = array())
	{
		$cache = CacheManager::getItem($cacheType);
		$isLicenseWrong = Option::get('sale', self::WRONG_LICENSE_OPTION, 'N') == 'Y';
		$skipCache = defined('SALE_HANDLERS_DLV_ADD_SKIP_CACHE');
		$result = false;

		if(!$skipCache && !$isLicenseWrong && $cache && $result = $cache->get($cacheIds))
		{
			$result = unserialize($result, ['allowed_classes' => [ResultSerializable::class]]);
		}

		if(!($result instanceof ResultSerializable))
		{
			if($isLicenseWrong && $cache)
				$cache->clean();

			$result = $this->call(static::SCOPE.'.'.$callMethod, $callParams);

			if($result->isSuccess())
			{
				$data = $result->getData();

				if(!empty($data['ACTIONS']) && is_array($data['ACTIONS']))
				{
					$this->processActions($data['ACTIONS']);
					unset($data['ACTIONS']);
					$result->setData($data);
				}

				if($cache)
					$cache->set(serialize($result), $cacheIds);
			}
			else
			{
				foreach($result->getErrors() as $error)
				{
					if($error->getCode() == self::ERROR_WRONG_LICENSE)
					{
						Option::set('sale', self::WRONG_LICENSE_OPTION, 'Y');
						$isLicenseWrong = true;
					}
				}

				if(defined('SALE_HANDLERS_DLV_ADD_LOG_REST_ERRORS'))
				{
					$eventLog = new \CEventLog();
					$eventLog->Add(array(
						"SEVERITY" => $eventLog::SEVERITY_ERROR,
						"AUDIT_TYPE_ID" => "SALE_HANDLERS_DLV_ADD_LOG_REST_ERRORS",
						"MODULE_ID" => "sale",
						"ITEM_ID" => $callMethod,
						"DESCRIPTION" => implode(', ', $result->getErrorMessages()),
					));
				}
			}
		}

		if($result->isSuccess() && $isLicenseWrong)
			Option::delete('sale', array('name' => self::WRONG_LICENSE_OPTION));

		return $result;
	}

	private function processActions(array $actions)
	{
		foreach($actions as $actType => $actParams)
		{
			$res = Action::execute($actParams);

			if(!$res->isSuccess() && defined('SALE_HANDLERS_DLV_ADD_LOG_ACTIONS_ERRORS'))
			{
				$eventLog = new \CEventLog();
				$eventLog->Add(array(
					"SEVERITY" => $eventLog::SEVERITY_ERROR,
					"AUDIT_TYPE_ID" => "SALE_HANDLERS_DLV_ADD_LOG_ACTIONS_ERRORS",
					"MODULE_ID" => "sale",
					"ITEM_ID" => $actType,
					"DESCRIPTION" => implode(', ', $res->getErrorMessages()),
				));
			}
		}
	}
}