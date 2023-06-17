<?
namespace Bitrix\Sale\Services\Base;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem\Logger;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\SystemException;
use Bitrix\Main\EventResult;
use Bitrix\Sale\Result;

class RestrictionManager
{
	protected static $classNames;
	protected static $cachedFields = array();

	const ON_STARTUP_SERVICE_RESTRICTIONS_EVENT_NAME = "onStartupServiceRestrictions";

	const MODE_CLIENT = 1;
	const MODE_MANAGER = 2;

	const SEVERITY_NONE = 0;
	const SEVERITY_SOFT = 1;
	const SEVERITY_STRICT = 2;

	const SERVICE_TYPE_SHIPMENT = 0;
	const SERVICE_TYPE_PAYMENT = 1;
	const SERVICE_TYPE_COMPANY = 2;
	const SERVICE_TYPE_CASHBOX = 3;

	protected static function init()
	{
		if(static::$classNames != null)
		{
			return;
		}

		$classes = static::getBuildInRestrictions();

		Loader::registerAutoLoadClasses('sale', $classes);

		/**
		 * @var Restriction $class
		 * @var string $path
		 */
		foreach ($classes as $class => $path)
		{
			if (!$class::isAvailable())
			{
				unset($classes[$class]);
			}
		}

		$event = new Event('sale', static::getEventName());
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			$customClasses = array();

			foreach ($resultList as $eventResult)
			{
				/** @var  EventResult $eventResult*/
				if ($eventResult->getType() != EventResult::SUCCESS)
					throw new SystemException("Can't add custom restriction class successfully");

				$params = $eventResult->getParameters();

				if(!empty($params) && is_array($params))
					$customClasses = array_merge($customClasses, $params);
			}

			if(!empty($customClasses))
			{
				Loader::registerAutoLoadClasses(null, $customClasses);
				$classes = array_merge($customClasses, $classes);
			}
		}

		static::$classNames = array_keys($classes);
	}

	/**
	 * @return string
	 * @throws NotImplementedException
	 */
	public static function getEventName()
	{
		throw new NotImplementedException;
	}

	/**
	 * @return array
	 * @throws SystemException
	 */
	public static function getClassesList()
	{
		if (static::$classNames === null)
			self::init();

		return static::$classNames;
	}

	/**
	 * @param $serviceId
	 * @param Entity $entity
	 * @param int $mode
	 * @return int
	 * @throws SystemException
	 */
	public static function checkService($serviceId, Entity $entity, $mode = self::MODE_CLIENT)
	{
		if(intval($serviceId) <= 0)
			return self::SEVERITY_NONE;

		self::init();
		$result = self::SEVERITY_NONE;
		$restrictions = static::getRestrictionsList($serviceId);

		foreach($restrictions as $rstrParams)
		{
			if(!$rstrParams['PARAMS'])
				$rstrParams['PARAMS'] = array();

			$res = $rstrParams['CLASS_NAME']::checkByEntity($entity, $rstrParams['PARAMS'], $mode, $serviceId);

			if($res == self::SEVERITY_STRICT)
				return $res;

			if($res == self::SEVERITY_SOFT && $result != self::SEVERITY_SOFT)
				$result = self::SEVERITY_SOFT;
		}

		return $result;
	}

	/**
	 * @return int
	 * @throws NotImplementedException
	 */
	protected static function getServiceType()
	{
		throw new NotImplementedException;
	}

	/**
	 * @param $serviceId
	 * @return array
	 */
	public static function getRestrictionsList($serviceId)
	{
		if ((int)$serviceId <= 0)
			return array();

		$serviceType = static::getServiceType();

		if (!isset(static::$cachedFields[$serviceType]))
		{
			$result = array();
			$dbRes = ServiceRestrictionTable::getList(array(
				'filter' => array(
					'=SERVICE_TYPE' => $serviceType,
				),
				'order' => array('SORT' => 'ASC'),
			));

			while($restriction = $dbRes->fetch())
			{
				if (!isset($result[$restriction['SERVICE_ID']]))
					$result[$restriction['SERVICE_ID']] = array();

				$result[$restriction['SERVICE_ID']][$restriction["ID"]] = $restriction;
			}

			static::$cachedFields[$serviceType] = $result;
		}

		if (!isset(static::$cachedFields[$serviceType][$serviceId]))
			return array();

		return static::$cachedFields[$serviceType][$serviceId];
	}

	/**
	 * @param $id
	 * @return array Sites from restrictions.
	 */
	public static function getSitesByServiceId($id)
	{
		if($id <= 0)
			return array();

		$result = array();

		foreach(static::getRestrictionsList($id) as $fields)
		{
			if($fields['CLASS_NAME'] == '\Bitrix\Sale\Delivery\Restrictions\BySite')
			{
				if(!empty($fields["PARAMS"]["SITE_ID"]))
				{
					if(is_array($fields["PARAMS"]["SITE_ID"]))
						$result = $fields["PARAMS"]["SITE_ID"];
					else
						$result = array($fields["PARAMS"]["SITE_ID"]);
				}

				break;
			}
		}

		return $result;
	}

	/**
	 * @param array $servicesIds
	 * @throws NotImplementedException
	 * @throws \Bitrix\Main\ArgumentException
	 * @internal
	 */
	public static function prepareData(array $servicesIds, array $fields = array())
	{
		if(empty($servicesIds))
			return;

		$serviceType = static::getServiceType();
		$cachedServices =
			isset(static::$cachedFields[$serviceType]) && is_array(static::$cachedFields[$serviceType])
				? array_keys(static::$cachedFields[$serviceType])
				: []
		;
		$ids = array_diff($servicesIds, $cachedServices);
		$idsForDb = array_diff($ids, array_keys($fields));

		if(!empty($idsForDb))
		{
			$dbRes = ServiceRestrictionTable::getList(array(
				'filter' => array(
					'=SERVICE_ID' => $idsForDb,
					'=SERVICE_TYPE' => $serviceType,
				),
				'order' => array('SORT' =>'ASC'),
			));

			while($restriction = $dbRes->fetch())
				self::setCache($restriction["SERVICE_ID"], $serviceType, $restriction);
		}

		foreach($fields as $serviceId => $serviceRestrictions)
		{
			if(is_array($serviceRestrictions))
			{
				foreach($serviceRestrictions as $restrId => $restrFields)
					self::setCache($serviceId, $serviceType, $restrFields);
			}
		}

		foreach($ids as $serviceId)
			self::setCache($serviceId, $serviceType);

		/** @var \Bitrix\Sale\Services\Base\Restriction  $className */
		foreach(static::getClassesList() as $className)
			$className::prepareData($ids);
	}

	/**
	 * @param int $serviceId
	 * @param int $serviceType
	 * @param array $fields
	 * @throws ArgumentNullException
	 */
	protected static function setCache($serviceId, $serviceType, array $fields = array())
	{
		if(intval($serviceId) <= 0)
			throw new  ArgumentNullException('serviceId');

		if(!isset(static::$cachedFields[$serviceType]))
			static::$cachedFields[$serviceType] = array();

		if(!isset(static::$cachedFields[$serviceType][$serviceId]))
			static::$cachedFields[$serviceType][$serviceId] = array();

		if(!empty($fields))
			static::$cachedFields[$serviceType][$serviceId][$fields["ID"]] = $fields;
	}

	/**
	 * @param int $serviceId
	 * @param int $serviceType
	 * @return array
	 * @throws ArgumentNullException
	 */
	protected static function getCache($serviceId, $serviceType)
	{
		$result = array();

		if(intval($serviceId) > 0)
		{
			if(isset(static::$cachedFields[$serviceType][$serviceId]))
				$result = static::$cachedFields[$serviceType][$serviceId];
		}
		else
		{
			if(isset(static::$cachedFields[$serviceType]))
				$result = static::$cachedFields[$serviceType];
		}

		return $result;
	}

	/**
	 * @throws NotImplementedException
	 * @return array
	 */
	protected static function getBuildInRestrictions()
	{
		throw new NotImplementedException;
	}

	/**
	 * @param array $params
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getList(array $params)
	{
		if (!$params['filter'])
			$params['filter'] = array();

		$params['filter']['SERVICE_TYPE'] = static::getServiceType();

		return ServiceRestrictionTable::getList($params);
	}

	/**
	 * @param $id
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getById($id)
	{
		return ServiceRestrictionTable::getById($id);
	}

	/**
	 * @param string $restrictionCode
	 * @return string|null Restriction classname
	 * @throws SystemException
	 */
	protected static function getRestriction(string $restrictionCode): ?string
	{
		foreach (static::getClassesList() as $className)
		{
			if (
				self::isRestrictionClassname($className)
				&& $className::isMyCode($restrictionCode)
			)
			{
				return $className;
			}
		}

		return null;
	}

	private static function isRestrictionClassname(string $className): bool
	{
		try
		{
			$restrictionClass = new \ReflectionClass($className);
		}
		catch (\ReflectionException $e)
		{
			return false;
		}

		return $restrictionClass->isSubclassOf(Restriction::class);
	}

	/**
	 * Apply restriction to the service
	 *
	 * @param int $serviceId
	 * @param RestrictionInfo $restrictionInfo information about applicable restriction
	 * @return Result
	 * @throws SystemException
	 */
	public static function applyRestriction(int $serviceId, RestrictionInfo $restrictionInfo): Result
	{
		$result = new Result();

		$restriction = static::getRestriction($restrictionInfo->getType());

		$reflectionClass = new \ReflectionClass(static::class);
		$methodPath = $reflectionClass->getName() . "::applyRestriction";

		if (!$restriction)
		{
			Logger::addError("[{$methodPath}] restriction '{$restrictionInfo->getType()}' not found.");

			$publicErrorMessage = Loc::getMessage('SALE_BASE_RSTR_MANAGER_FIND_RSTR_ERROR', [
				'#RSTR_CLASSNAME#' => htmlspecialcharsbx($restrictionInfo->getType()),
			]);
			$result->addError(new \Bitrix\Main\Error($publicErrorMessage));

			return $result;
		}

		/**
		 *	@var \Bitrix\Main\Entity\Result $restrictionApplyResult
		 *	@var Restriction $restriction
		 */
		$restrictionApplyResult = $restriction::save([
			'SERVICE_ID' => $serviceId,
			'SERVICE_TYPE' => static::getServiceType(),
			'PARAMS' => $restrictionInfo->getOptions(),
		]);

		if (!$restrictionApplyResult->isSuccess())
		{
			foreach ($restrictionApplyResult->getErrors() as $error)
			{
				Logger::addError("[{$methodPath}] " . $error->getMessage());
			}

			$publicErrorMessage = $restriction::getOnApplyErrorMessage();
			$result->addError(new Error($publicErrorMessage));
		}

		return $result;
	}

	/**
	 * Apply to service his default restrictions
	 *
	 * @param RestrictableService $service
	 * @return Result
	 * @throws SystemException
	 */
	public static function setupDefaultRestrictions(RestrictableService $service): Result
	{
		$result = new Result();

		$startupRestrictions = $service->getStartupRestrictions();

		(new Event(
			'sale',
			static::ON_STARTUP_SERVICE_RESTRICTIONS_EVENT_NAME,
			[
				'STARTUP_RESTRICTIONS_COLLECTION' => $startupRestrictions,
				'SERVICE_ID' => $service->getServiceId(),
			]
		))->send();

		self::clearAlreadyUsedByServiceRestrictions($service->getServiceId(), $startupRestrictions);

		/** @var RestrictionInfo $restrictionInfo */
		foreach ($startupRestrictions as $restrictionInfo)
		{
			$applyResult = static::applyRestriction($service->getServiceId(), $restrictionInfo);
			$result->addErrors($applyResult->getErrors());
		}

		return $result;
	}

	private static function clearAlreadyUsedByServiceRestrictions(int $serviceId, RestrictionInfoCollection $collection): void
	{
		$serviceRestrictions = array_column(static::getRestrictionsList($serviceId), 'CLASS_NAME');

		foreach ($serviceRestrictions as $restrictionClassName)
		{
			if (self::isRestrictionClassname($restrictionClassName))
			{
				/** @var Restriction $restrictionClassName */
				$collection->delete($restrictionClassName::getCode());
			}
		}
	}
}