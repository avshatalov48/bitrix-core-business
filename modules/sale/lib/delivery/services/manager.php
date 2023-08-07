<?php
namespace Bitrix\Sale\Delivery\Services;

use Bitrix\Main\Config\Option;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Sale\Internals\Pool;
use Bitrix\Sale\Result;
use Bitrix\Main\IO\File;
use Bitrix\Sale\Shipment;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Delivery\ExtraServices;
use Bitrix\Sale\Internals\ShipmentTable;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\Delivery\Services\Base;

Loc::loadMessages(__FILE__);

/**
 * Class Manager
 * Helps to manage delivery services.
 * @package Bitrix\Sale\Delivery\Services
 */
class Manager
{
	const SKIP_PROFILE_PARENT_CHECK = 0;
	const SKIP_CHILDREN_PARENT_CHECK = 1;

	protected static $handlers = null;
	protected static $restHandlers = [];
	protected static $cachedFields = array();

	/* Directories where we can found handlers */
	protected static $handlersDirectories = array();
	/** @var null|Pool  */
	protected static $objectsPool = null;

	/**
	 * Returns service field, caches results during hit.
	 * @param int $deliveryId
	 * @return array Service fields
	 * @throws SystemException
	 */
	public static function getById($deliveryId)
	{
		$id = intval($deliveryId);

		if($id <= 0)
			throw new SystemException("deliveryId");

		if(!isset(self::$cachedFields[$deliveryId]) || !is_array(self::$cachedFields[$deliveryId]))
		{
			self::$cachedFields[$deliveryId] = array();

			$resSrvParams = Table::getList(array(
				'filter' => array("=ID" =>  $deliveryId)
			));

			if($srvParams = $resSrvParams->fetch())
				self::$cachedFields[$srvParams["ID"]] = $srvParams;
		}

		return self::$cachedFields[$deliveryId];
	}

	/**
	 * @param array $params
	 * @return \Bitrix\Main\ORM\Query\Result
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function getList(array $params = array()): \Bitrix\Main\ORM\Query\Result
	{
		return Table::getList($params);
	}

	/**
	 * @param Shipment $shipment.
	 * @param int $restrictionMode MODE_CLIENT or MODE_MANAGER from Restrictions\Manager.
	 * @return Base[] delivery services objects
	 */
	public static function getRestrictedObjectsList(Shipment $shipment, $restrictionMode = Restrictions\Manager::MODE_CLIENT)
	{
		$result = array();
		$services = self::getRestrictedList($shipment, $restrictionMode);

		foreach($services as $srvParams)
		{
			if($srvParams["CLASS_NAME"]::canHasProfiles())
				continue;

			if(is_callable($srvParams["CLASS_NAME"]."::canHasChildren") && $srvParams["CLASS_NAME"]::canHasChildren())
				continue;

			$service = self::getPooledObject($srvParams);

			if(!$service)
				continue;

			if(!$service->isCompatible($shipment))
				continue;

			if($shipment->getCurrency() != $service->getCurrency())
			{
				$service->getExtraServices()->setOperationCurrency(
					$shipment->getCurrency()
				);
			}

			$result[$srvParams["ID"]] = $service;
		}

		return $result;
	}

	/**
	 * @param $deliveryId
	 * @return bool is service exists or not
	 * @throws SystemException
	 */
	public static function isServiceExist($deliveryId)
	{
		if(intval($deliveryId) <= 0)
			return false;

		$srv = self::getById($deliveryId);
		return !empty($srv);
	}

	/**
	 * Prepares and caches data during the hit
	 * @param bool $calculatingOnly If we need absolutely all, or calculating items only.
	 * @param array $restrictedIds If we have list of services ids, successful checked restrictions.
	 * @return array Array of active delivery services fields.
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getActiveList($calculatingOnly = false, $restrictedIds = null)
	{
		//If we alredy got all active services
		static $isDataPrepared = false;
		static $activeIds = array();
		static $canHasProfiles = array();
		static $canHasChildren = array();
		static $DontHaveRestrictionsIds = array();

		if(is_array($restrictedIds))
		{
			$unPreparedRestrictedIds = array_diff_key(
				array_flip(
					$restrictedIds
				),
				self::$cachedFields
			);

			$unPreparedRestrictedIds = array_keys($unPreparedRestrictedIds);
		}
		else
		{
			$unPreparedRestrictedIds = array();
		}

		if(!$isDataPrepared || !empty($unPreparedRestrictedIds))
		{
			$result = array();
			self::initHandlers();

			$filter = array(
				'=ACTIVE' => 'Y'
			);

			$params =  array(
				'order' => array('SORT' =>'ASC', 'NAME' => 'ASC'),
				'filter' => $filter
			);

			$params['runtime'] = array(
				new \Bitrix\Main\Entity\ExpressionField(
					'RESTRICTIONS_EXIST',
					'CASE WHEN EXISTS('.
					'SELECT 1 FROM b_sale_service_rstr SSR WHERE SSR.SERVICE_ID = %s AND SSR.SERVICE_TYPE = '.Restrictions\Manager::SERVICE_TYPE_SHIPMENT.') THEN 1 ELSE 0 END',
					'ID'
				)
			);

			$params['select'] = array('*', 'RESTRICTIONS_EXIST');

			if(!empty($unPreparedRestrictedIds))
			{
				if($isDataPrepared)
				{
					$params['filter']['ID'] = $unPreparedRestrictedIds;
				}
				else
				{
					$params['filter'][] = array(
						"LOGIC" => "OR",
						"ID" => $unPreparedRestrictedIds,
						'RESTRICTIONS_EXIST' => 0
					);
				}
			}

			$dbRes = Table::getList($params);
			$profilesParentsIds = array();

			while($service = $dbRes->fetch())
			{
				if(!is_subclass_of($service["CLASS_NAME"], 'Bitrix\Sale\Delivery\Services\Base'))
					continue;

				if(!$service['RESTRICTIONS_EXIST'])
					$DontHaveRestrictionsIds[] = $service['ID'];

				if($service["CLASS_NAME"]::canHasChildren())
					$canHasChildren[$service["ID"]] = true;

				if ($service['CLASS_NAME']::canHasProfiles())
					$canHasProfiles[$service["ID"]] = true;

				if($service["PARENT_ID"] != 0)
					$profilesParentsIds[$service["PARENT_ID"]] = $service['ID'];

				$result[$service["ID"]] = $service;

				if($service['ACTIVE'] == 'Y')
					$activeIds[$service["ID"]] = $service["ID"];
			}

			foreach(array_diff_key($canHasProfiles, $profilesParentsIds) as $id => $tmp)
				unset($result[$id]);

			self::$cachedFields = self::$cachedFields + $result;
			Restrictions\Manager::prepareData(array_keys($result));
			ExtraServices\Manager::prepareData(array_keys($result));

			//selected all active
			if(!$isDataPrepared && empty($unPreparedRestrictedIds))
				$isDataPrepared = true;
		}

		if(is_array($restrictedIds) && !empty($restrictedIds))
			$storedIds = array_diff($restrictedIds, $unPreparedRestrictedIds);
		else
			$storedIds = array();

		if(!empty($storedIds) && is_array($storedIds))
		{
			foreach($storedIds as $storedId)
			{
				if(empty(self::$cachedFields[$storedId]))
					continue;

				$service = self::$cachedFields[$storedId];

				if(!class_exists($service["CLASS_NAME"]))
					continue;

				if($service["CLASS_NAME"]::canHasChildren())
					$canHasChildren[$storedId] = true;

				if ($service['CLASS_NAME']::canHasProfiles())
					$canHasProfiles[$storedId] = true;

				if($service['ACTIVE'] == 'Y')
					$activeIds[$storedId] = $storedId;
			}
		}

		$active = array_intersect_key(self::$cachedFields, array_flip($activeIds));

		if(is_array($restrictedIds))
		{
			$result = array_intersect_key($active, array_flip($DontHaveRestrictionsIds));

			if(!empty($restrictedIds))
				$result = $result + array_intersect_key($active, array_flip($restrictedIds));
		}
		else
		{
			$result = $active;
		}

		/*
		 * Clean children if parent is not present in result.
		 * We mean that it doesn't pass restrictions checks.
		 * Or it is not active.
		 */

		foreach($result as $id => $fields)
		{
			if(intval($fields['PARENT_ID']) <= 0)
				continue;

			if(empty($result[$fields['PARENT_ID']]))
				unset($result[$id]);
		}

		if($calculatingOnly)
			$result = array_diff_key($result, $canHasChildren, $canHasProfiles);

		if(!empty($result))
			sortByColumn($result, array("SORT" => SORT_ASC, "NAME" => SORT_ASC), '', null, true);

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @param int $restrictionMode MODE_CLIENT or MODE_MANAGER from Restrictions\Manager.
	 * @param array $skipChecks self::SKIP_CHILDREN_PARENT_CHECK || self::SKIP_PROFILE_PARENT_CHECK
	 * @return array Array of active delivery services fields filtered by restrictions.
	 */
	public static function getRestrictedList(Shipment $shipment = null, $restrictionMode, array $skipChecks = array())
	{
		$result = array();

		if(empty($skipChecks))
		{
			$skipChecks = array(
				self::SKIP_CHILDREN_PARENT_CHECK,
				self::SKIP_PROFILE_PARENT_CHECK
			);
		}

		//Have restrictions and this restrictions successfully checked
		$restrictedSrvIds = Restrictions\Manager::getRestrictedIds($shipment, $restrictionMode);
		//Don't have any restrictions + successfully checked
		$services = self::getActiveList(false, array_keys($restrictedSrvIds));

		foreach($services as $srvParams)
		{
			$srvParams["RESTRICTED"] = false;

			if(!in_array(self::SKIP_PROFILE_PARENT_CHECK, $skipChecks))
				if($srvParams["CLASS_NAME"]::canHasProfiles())
					continue;

			if(!in_array(self::SKIP_CHILDREN_PARENT_CHECK, $skipChecks))
				if(is_callable($srvParams["CLASS_NAME"]."::canHasChildren") && $srvParams["CLASS_NAME"]::canHasChildren())
					continue;

			if(isset($restrictedSrvIds[$srvParams["ID"]]) &&  $restrictedSrvIds[$srvParams["ID"]] == Restrictions\Manager::SEVERITY_SOFT)
				$srvParams["RESTRICTED"] = true;

			$result[$srvParams["ID"]] = $srvParams;
		}

		return $result;
	}

	/**
	 * @param array $srvParams Delivery service fields from DataBase to construct service object.
	 * @return Base|null Delivery service object
	 * All errors it writes to system log.
	 * It's better to use \Bitrix\Sale\Delivery\Services\Manager::getPooledObject for performance purposes
	 */
	public static function createObject(array $srvParams)
	{
		self::initHandlers();
		$errorMsg = '';
		$service = null;

		if(!isset($srvParams['PARENT_ID']))
		{
			$srvParams['PARENT_ID'] = 0;
		}

		if(class_exists($srvParams['CLASS_NAME']))
		{
			if(is_subclass_of($srvParams['CLASS_NAME'], Base::class))
			{
				try
				{
					$service = new $srvParams['CLASS_NAME']($srvParams);
				}
				catch (SystemException $err)
				{
					$errorMsg = $err->getMessage();
				}
			}
			else
			{
				$errorMsg = 'Class "' . $srvParams['CLASS_NAME'] . '" is not subclass of of Bitrix\Sale\Delivery\Services\Base';
			}
		}
		else
		{
			$errorMsg = 'Can\'t create delivery object. Class "'.$srvParams['CLASS_NAME']. '" does not exist.';
		}

		if($errorMsg !== '')
		{
			static::log(
				[
				'SEVERITY' => \CEventLog::SEVERITY_ERROR,
				'AUDIT_TYPE_ID' => 'SALE_DELIVERY_CREATE_OBJECT_ERROR',
				'MODULE_ID' => 'sale',
				'ITEM_ID' => 'createObject()',
				'DESCRIPTION' => $errorMsg.' Fields: '.serialize($srvParams),
				]
			);

			return null;
		}

		return $service;
	}

	protected static function log(array $params): void
	{
		\CEventLog::Add($params);
	}

	public static function getPooledObject(array $fields)
	{
		if(self::$objectsPool === null)
			self::$objectsPool = new ObjectPool();

		return self::$objectsPool->getObject($fields);
	}

	/**
	 * @param int $deliveryId Delivery service id
	 * @return Base Delivery service object
	 * @throws ArgumentNullException
	 * @throws SystemException
	 */
	public static function getObjectById($deliveryId)
	{
		if(intval($deliveryId) <= 0 )
			throw new ArgumentNullException("deliveryId");

		$srvParams = self::getById($deliveryId);

		if(empty($srvParams))
			return null;

		return self::getPooledObject($srvParams);
	}

	/**
	 * @param string $serviceCode Delivery service code
	 * @return Base Delivery service object.
	 * @throws ArgumentNullException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getObjectByCode($serviceCode)
	{
		if($serviceCode == '' )
			throw new ArgumentNullException("serviceCode");

		$srvParams = array();

		foreach(self::$cachedFields as $id => $srv)
		{
			if($srv['CODE'] == $serviceCode)
			{
				$srvParams = $srv;
			}
		}

		if(empty($srvParams))
		{
			$resSrvParams = Table::getList(array(
				'filter' => array('=CODE' => $serviceCode)
			));

			if(!($srvParams = $resSrvParams->fetch()))
				throw new SystemException("Can't get delivery service data with code: \"".$serviceCode."\"");

			self::$cachedFields[$srvParams['ID']] = $srvParams;
		}

		return self::getPooledObject($srvParams);
	}

	/**
	 * Gets info about existing delivery services handlers
	 * Stores this information during the hit
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected static function initHandlers()
	{
		if(self::$handlers !== null)
			return true;

		self::$handlersDirectories = array(
			'LOCAL' => '/local/php_interface/include/sale_delivery/',
			'CUSTOM' => Option::get('sale', 'delivery_handles_custom_path', BX_PERSONAL_ROOT.'/php_interface/include/sale_delivery/'),
			'SYSTEM' => '/bitrix/modules/sale/handlers/delivery/'
		);

		$result = array(
			'\Bitrix\Sale\Delivery\Services\Group' => 'lib/delivery/services/group.php',
			'\Bitrix\Sale\Delivery\Services\Automatic' => 'lib/delivery/services/automatic.php',
			'\Bitrix\Sale\Delivery\Services\Configurable' => 'lib/delivery/services/configurable.php',
			'\Bitrix\Sale\Delivery\Services\AutomaticProfile' => 'lib/delivery/services/automatic_profile.php',
			'\Bitrix\Sale\Delivery\Services\EmptyDeliveryService' => 'lib/delivery/services/emptydeliveryservice.php',
		);

		\Bitrix\Main\Loader::registerAutoLoadClasses('sale', $result);

		/*
		 *	To add you own handler you must wrote smth. like this in for example init.php:
		 *
		 *	function addCustomDeliveryServices()
		 *	{
		 *		return new \Bitrix\Main\EventResult(
		 *			\Bitrix\Main\EventResult::SUCCESS,
		 *			array(
		 *					'\Sale\Handlers\Delivery\SimpleHandler' => '/bitrix/modules/sale/handlers/delivery/simple/handler.php'
		 *			),
		 *			'sale'
		 *		);
		 *	}
		 *
		 *	$eventManager->addEventHandler('sale', 'onSaleDeliveryHandlersClassNamesBuildList', 'addCustomDeliveryServices');
		 */

		$event = new Event('sale', 'onSaleDeliveryHandlersClassNamesBuildList');
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			$customClasses = array();

			foreach ($resultList as $eventResult)
			{
				/** @var  \Bitrix\Main\EventResult $eventResult*/
				if ($eventResult->getType() != EventResult::SUCCESS)
					continue;

				$params = $eventResult->getParameters();

				if(!empty($params) && is_array($params))
					$customClasses = array_merge($customClasses, $params);
			}

			if(!empty($customClasses))
			{
				\Bitrix\Main\Loader::registerAutoLoadClasses(null, $customClasses);
				$result = array_merge($result, $customClasses);
			}
		}

		$handlers =  self::getHandlersClasses();

		if(!empty($handlers))
		{
			\Bitrix\Main\Loader::registerAutoLoadClasses(null, $handlers);
			$result = array_merge($result, self::getHandlersClasses());
		}

		self::$handlers = array_keys($result);

		for($idx = count(self::$handlers) - 1; $idx >= 0; $idx--)
		{
			/** @var \Bitrix\Sale\Delivery\Services\Base $handler */
			$handler = self::$handlers[$idx];

			if(!$handler::isHandlerCompatible())
			{
				unset(self::$handlers[$idx]);
				continue;
			}

			$profiles = $handler::getChildrenClassNames();

			if(!empty($profiles))
			{
				self::$handlers = array_merge(self::$handlers, $profiles);
			}
		}

		return true;
	}

	/**
	 * @return array Handler Classes
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	protected static function getHandlersClasses()
	{
		$result = array();

		foreach(self::$handlersDirectories as $handlersDirectory)
		{
			$dirPath = $_SERVER['DOCUMENT_ROOT'].$handlersDirectory;

			if(!Directory::isDirectoryExists($dirPath))
				continue;

			$dir = new Directory($_SERVER['DOCUMENT_ROOT'].$handlersDirectory);

			foreach ($dir->getChildren() as $handler)
			{
				if (!$handler->isDirectory())
					continue;

				/** @var Directory $handler */

				$handlerDir = $handler->getPath();
				$handlerPath = $handlerDir.'/handler.php';

				if(!File::isFileExists($handlerPath))
					continue;

				$handlerClassName = ucfirst($handler->getName().'Handler');
				$fullClassName = '\Sale\Handlers\Delivery\\'.$handlerClassName;
				$result[$fullClassName] = $handlersDirectory.$handler->getName().'/handler.php';
				require_once($handlerPath);
			}
		}

		return $result;
	}

	/**
	 * @return array Known delivery services handlers
	 * @throws SystemException
	 */
	public static function getHandlersList()
	{
		if(self::$handlers === null)
			self::initHandlers();

		return self::$handlers;
	}

	/**
	 * @return array|null
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function getRestHandlerList(): ?array
	{
		static $init = false;
		if (!$init)
		{
			$init = true;
			$restHandlerList = \Bitrix\Sale\Delivery\Rest\Internals\DeliveryRestHandlerTable::getList([
				'select' => ['ID', 'NAME', 'CODE', 'SORT', 'DESCRIPTION', 'SETTINGS', 'PROFILES'],
			])->fetchAll();
			foreach ($restHandlerList as $restHandler)
			{
				self::$restHandlers[$restHandler['CODE']] = $restHandler;
			}
		}

		return self::$restHandlers;
	}

	/**
	 * Calculates the price of the shipment
	 * @param Shipment $shipment
	 * @param int $deliveryId optional
	 * @param array  $extraServices optional
	 * @return \Bitrix\Sale\Delivery\CalculationResult
	 * @throws ArgumentNullException
	 */
	public static function calculateDeliveryPrice(Shipment $shipment, $deliveryId = 0, $extraServices = array())
	{
		if($deliveryId <=0)
			$deliveryId = $shipment->getDeliveryId();

		$delivery = self::getObjectById($deliveryId);

		if($delivery)
		{
			$result = $delivery->calculate($shipment, $extraServices);
		}
		else
		{
			$result = new CalculationResult();
			$result->addError(new Error("Can't create delivery service object with id: \"".$deliveryId."\""));
		}

		return $result;
	}

	/**
	 * Returns id of delivery service group.
	 * Creates if such group does not exist.
	 * @param string $name Group name
	 * @return int Group id
	 * @throws SystemException
	 * @throws \Exception
	 */
	public static function getGroupId(string $name): int
	{
		$group = Table::getRow([
			'select' => ['ID'],
			'filter' => [
				'=NAME' => $name,
				'=CLASS_NAME' => '\Bitrix\Sale\Delivery\Services\Group',
			]
		]);

		if ($group)
		{
			$result = $group["ID"];
		}
		else
		{
			$res = self::add([
				'NAME' => $name,
				'CLASS_NAME' => '\Bitrix\Sale\Delivery\Services\Group',
				'ACTIVE' => 'Y',
				'CURRENCY' => Option::get('sale', 'default_currency'),
			]);

			if($res->isSuccess())
				$result = (int)$res->getId();
			else
				throw new SystemException(implode("<br>\n",$res->getErrorMessages()));
		}

		return $result;
	}

	/**
	 * @param int $parentId Delivery service parent id
	 * @return array Delivery service fields
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getByParentId($parentId)
	{
		$result = array();
		$srvList = self::getActiveList();

		foreach($srvList as $id => $srv)
		{
			if($srv['PARENT_ID'] == $parentId)
			{
				$result[$srv['ID']] = $srv;
			}
		}

		return $result;
	}

	/**
	 * Returns entity link name for connection with Locations
	 * @return string
	 */
	public static function getLocationConnectorEntityName()
	{
		return	'Bitrix\Sale\Delivery\DeliveryLocation';
	}

	/**
	 * Adds delivery service
	 * @param array $fields
	 * @return AddResult
	 * @throws SystemException
	 * @throws \Exception
	 */
	public static function add(array $fields)
	{
		self::initHandlers();

		if(!empty($fields["CLASS_NAME"]))
		{
			/** @var \Bitrix\Main\Result $onBeforeAddResult */
			$onBeforeAddResult = $fields["CLASS_NAME"]::onBeforeAdd($fields);
			if (!$onBeforeAddResult->isSuccess())
			{
				return (new AddResult())->addErrors($onBeforeAddResult->getErrors());
			}
		}

		$res = \Bitrix\Sale\Delivery\Services\Table::add($fields);

		if($res->isSuccess())
		{
			if(!empty($fields["CLASS_NAME"]))
				$fields["CLASS_NAME"]::onAfterAdd($res->getId(), $fields);

			if(!empty($fields['CODE']))
				self::setIdCodeCached($res->getId(), $fields['CODE']);
		}

		return $res;
	}

	/**
	 * @return string
	 */
	public static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * Updates delivery service
	 * @param int $id
	 * @param array $fields
	 * @return \Bitrix\Main\Entity\UpdateResult
	 * @throws SystemException
	 * @throws \Exception
	 */
	public static function update($id, array $fields)
	{
		self::initHandlers();

		if (!empty($fields["CLASS_NAME"]) && class_exists($fields["CLASS_NAME"]))
		{
			$fields["CLASS_NAME"]::onBeforeUpdate($id, $fields);
		}

		$res = \Bitrix\Sale\Delivery\Services\Table::update($id, $fields);

		if ($res->isSuccess())
		{
			if (!empty($fields["CLASS_NAME"]) && class_exists($fields["CLASS_NAME"]))
			{
				$fields["CLASS_NAME"]::onAfterUpdate($res->getId(), $fields);
			}

			if (isset($fields['CODE']))
			{
				self::cleanIdCodeCached($id);
			}

			if (Loader::includeModule('pull'))
			{
				\CPullWatch::AddToStack(
					'SALE_DELIVERY_SERVICE',
					[
						'module_id' => 'sale',
						'command' => 'onDeliveryServiceSave',
						'params' => [
							'ID' => $id,
						]
					]
				);
			}
		}

		return $res;
	}

	/**
	 * Deletes delivery service
	 * @param int $id
	 * @param bool $checkServiceUsage
	 * @return \Bitrix\Main\Result
	 * @throws ArgumentNullException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public static function delete($id, bool $checkServiceUsage = true)
	{
		if ((int)$id <= 0)
		{
			throw new ArgumentNullException('id');
		}

		if ($checkServiceUsage)
		{
			$res = self::checkServiceUsage($id);
			if (!$res->isSuccess())
			{
				return $res;
			}
		}

		self::initHandlers();

		$res = Table::getList(array(
			'filter' => array(
				'ID' => $id
			),
			'select' => array('ID', 'CLASS_NAME', 'LOGOTIP')
		));

		$className = '';
		$logotip = 0;

		if ($service = $res->fetch())
		{
			$className = $service['CLASS_NAME'];
			$logotip = intval($service['LOGOTIP']);
		}

		$res = \Bitrix\Sale\Delivery\Services\Table::delete($id);

		if ($res->isSuccess())
		{
			if (!empty($className) && class_exists($className))
			{
				$className::onAfterDelete($id);
			}

			self::deleteRelatedEntities($id, $checkServiceUsage);

			if ($logotip > 0)
			{
				\CFile::Delete($logotip);
			}
		}

		return $res;
	}

	/**
	 * @return array
	 * registerEventHandler(
	 * 	'sale', 'OnGetBusinessValueConsumers', 'sale',
	 * 	'\Bitrix\Sale\Delivery\Services\Manager',
	 * 	'onGetBusinessValueConsumers');
	 */
	public static function onGetBusinessValueConsumers()
	{
		static $result = null;

		if($result !== null)
			return $result;

		$result = array();
		$handlers = self::getHandlersList();
		$consumers = array();

		/** @var Base $handlerClassName */
		foreach($handlers as $handlerClassName)
		{
			$tmpCons = $handlerClassName::onGetBusinessValueConsumers();

			/** @var string $handlerClassName*/
			if(!empty($tmpCons))
				$consumers[$handlerClassName] = $tmpCons;
		}

		$res = Table::getList(array(
			'filter' => array(
				'=CLASS_NAME' => array_keys($consumers),
				'=ACTIVE' => 'Y'
			),
			'select' => array('ID', 'CLASS_NAME', 'NAME')
		));

		while($dlv = $res->fetch())
		{
			$result['DELIVERY_'.$dlv['ID']] = $consumers[$dlv['CLASS_NAME']];
			$result['DELIVERY_'.$dlv['ID']]['NAME'] = $dlv['NAME'];
		}

		return $result;
	}

	public static function onGetBusinessValueGroups()
	{
		$result = array(
			'DELIVERY' => array('NAME' => Loc::getMessage('SALE_DLVR_MNGR_DLV_SRVS'), 'SORT' => 100),
		);

		$handlers = self::getHandlersList();

		/** @var Base $handlerClassName */
		foreach($handlers as $handlerClassName)
			$result = array_merge($result, $handlerClassName::onGetBusinessValueGroups());

		return $result;
	}

	/**
	 * Sets fields values to all children
	 * @param int $id
	 * @param array $fields
	 * @return int count modified children
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function setChildrenFieldsValues($id, array $fields)
	{
		if(empty($fields))
			return 0;

		$counter = 0;

		$res = Table::getList(array(
			'filter' => array('PARENT_ID' => $id),
			'select' => array('ID')
		));

		while($child = $res->fetch())
		{
			$ures = self::update($child['ID'], $fields);

			if($ures->isSuccess())
				$counter++;

			$counter += self::setChildrenFieldsValues($child['ID'], $fields);
		}

		return $counter;
	}

	/**
	 * @param string $code
	 * @return int Service id
	 */
	public static function getIdByCode($code)
	{
		$result = self::getIdCodeCached($code, "code");

		if($result !== false)
			return $result;

		foreach(self::$cachedFields as $id => $srv)
		{
			if($srv['CODE'] == $code)
			{
				$result = $id;
			}
		}

		if(intval($result) <= 0)
		{
			$res = Table::getList(array(
				'filter' => array(
					'=CODE' => $code
				)
			));

			if($handler = $res->fetch())
			{
				$result = $handler["ID"];
				self::$cachedFields[$handler["ID"]] = $handler;
			}
		}

		$result = intval($result);
		self::setIdCodeCached($result, $code);
		return $result;
	}

	/**
	 * @param int $id
	 * @return string Delivery service code
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getCodeById($id)
	{
		$result = self::getIdCodeCached($id, "id");

		if($result !== false)
			return $result;

		$srv = self::getById($id);

		$result = $srv['CODE'];
		self::setIdCodeCached($id, $result);
		return $result;
	}

	/**
	 *
	 * @param $value
	 * @param $type
	 * @return int|string Id or Code of delivery service
	 */
	protected static function getIdCodeCached($value, $type)
	{
		$result = false;
		$ttl = 315360000;
		$cacheId = "SALE_DELIVERY_ID_CODE_MAP_".($type == "id" ? "I" : "C")."_".$value;
		$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();

		if($cacheManager->read($ttl, $cacheId))
			$result = $cacheManager->get($cacheId);

		return $result;
	}

	/**
	 * Saves relation between Id an code of delivery service
	 * @param int $id
	 * @param string $code
	 */
	protected static function setIdCodeCached($id, $code)
	{
		$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();
		$cacheManager->set("SALE_DELIVERY_ID_CODE_MAP_I_".$id, $code);
		$cacheManager->set("SALE_DELIVERY_ID_CODE_MAP_C_".$code, $id);
	}

	/**
	 * Cleans cache of delivery service id-code relation
	 * @param int $id
	 */
	protected static function cleanIdCodeCached($id)
	{
		$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();
		$code = self::getIdCodeCached($id, "id");
		$cacheManager->clean("SALE_DELIVERY_ID_CODE_MAP_I_".$id);

		if($code <> '')
			$cacheManager->clean("SALE_DELIVERY_ID_CODE_MAP_C_".$code);
	}

	/**
	 * Returns is delivery service is already used in shipments
	 * @param $deliveryId
	 * @return bool
	 */
	protected static function isDeliveryInShipments($deliveryId)
	{
		$res = ShipmentTable::getList(array(
			'filter' => array(
				"=DELIVERY_ID" => $deliveryId,
				"=SYSTEM" => "N"
			),
			'select' => array("ID")
		));

		if($res->fetch())
			$result = true;
		else
			$result = false;

		return $result;
	}

	/**
	 * Returns if delivery service and it's children are used in shipments
	 * @param $deliveryId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function checkServiceUsage($deliveryId)
	{
		$result = new Result();

		if(self::isDeliveryInShipments($deliveryId))
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVR_MNGR_ERR_DEL_IN_SHPMNTS_EXIST')));
		}
		else
		{
			$dbRes = Table::getList(array(
				'filter' => array(
					"PARENT_ID" => $deliveryId
				),
				'select' => array("ID")
			));

			while($child = $dbRes->fetch())
			{
				if(self::isDeliveryInShipments($child["ID"]))
				{
					$result->addError(new Error(Loc::getMessage('SALE_DLVR_MNGR_ERR_DEL_IN_SHPMNTS_EXIST_CHLD')));
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Deletes related entities
	 * @param int $deliveryId
	 * @param bool $checkServiceUsage
	 * @return bool
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * todo: restrictions, extra_services - can require some actions after deletion
	 */
	protected static function deleteRelatedEntities($deliveryId, bool $checkServiceUsage = true)
	{
		$con = \Bitrix\Main\Application::getConnection();
		$deliveryId = (int)$deliveryId;

		$con->queryExecute("
			DELETE FROM b_sale_service_rstr
			WHERE
			    SERVICE_ID = " . $deliveryId . "
			    AND SERVICE_TYPE = " . (int)Restrictions\Manager::SERVICE_TYPE_SHIPMENT . "
		");
		$con->queryExecute("DELETE FROM b_sale_delivery2location WHERE DELIVERY_ID=".$deliveryId);
		$con->queryExecute("DELETE FROM b_sale_delivery2paysystem WHERE DELIVERY_ID=".$deliveryId);
		$con->queryExecute("DELETE FROM b_sale_delivery_es WHERE DELIVERY_ID=".$deliveryId);

		$dbRes = Table::getList(array(
			'filter' => array(
				"PARENT_ID" => $deliveryId
			),
			'select' => array("ID")
		));

		while ($child = $dbRes->fetch())
		{
			self::delete($child["ID"], $checkServiceUsage);
		}

		self::cleanIdCodeCached($deliveryId);
		return true;
	}

	/**
	 * @return int Empty delivery service id
	 * @throws SystemException
	 */
	public static function getEmptyDeliveryServiceId()
	{
		self::initHandlers();
		return \Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId();
	}

	/**
	 * @internal
	 * @param int|int[] $deliveryId
	 * @param mixed $checkParams
	 * @param string $restrictionClassName
	 * @return bool|int[]
	 * @throws ArgumentNullException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public static function checkServiceRestriction($deliveryId, $checkParams, $restrictionClassName)
	{
		if(empty($deliveryId))
			throw new ArgumentNullException("deliveryId");

		if(!is_array($deliveryId))
			$deliveryId = array($deliveryId);

		$dbRstrRes = ServiceRestrictionTable::getList(array(
			'filter' => array(
				'=SERVICE_ID' => $deliveryId,
				'=SERVICE_TYPE' => Restrictions\Manager::SERVICE_TYPE_SHIPMENT,
				'=CLASS_NAME' => $restrictionClassName
			)
		));

		$tmp = array();
		/** @var Restrictions\Base $restriction */
		$restriction = static::getRestrictionObject($restrictionClassName);

		while($rstFields = $dbRstrRes->fetch())
		{
			$rstParams = is_array($rstFields["PARAMS"]) ? $rstFields["PARAMS"] : array();
			$tmp[$rstFields['SERVICE_ID']] = $restriction->check($checkParams, $rstParams, $rstFields['SERVICE_ID']);
		}

		if(count($deliveryId) == 1)
			return current($tmp);

		$result = array();

		foreach($deliveryId as $id)
			if(!isset($tmp[$id]) || (isset($tmp[$id]) && $tmp[$id] == true))
				$result[] = $id;

		return $result;
	}

	/**
	 * Check if given class is valid delivery service class
	 * inheritance of Bitrix\Sale\Delivery\Services\Base.
	 * @param string $class Checking class.
	 * @return bool
	 */
	public static function isDeliveryServiceClassValid($class)
	{
		if($class == '')
			return false;

		self::initHandlers();

		if(!class_exists($class))
			return false;

		if(!is_subclass_of($class, 'Bitrix\Sale\Delivery\Services\Base'))
			return false;

		return true;
	}

	/*
	 * Deprecated methods. Will be removed in future versions.
	 */

	/**
	 * @deprecated  use \Bitrix\Sale\Delivery\Services\Manager::calculateDeliveryPrice()
	 */
	public static function calculate(Shipment $shipment)
	{
		$delivery = self::getObjectById($shipment->getDeliveryId());
		return $delivery->calculate($shipment);
	}

	/**
	 * @deprecated will be remove in next versions
	 */
	public static function getRestrictionObject($className)
	{
		if(!class_exists($className))
			throw new SystemException("Can't find class: ".$className);

		static $cache = array();

		if(isset($cache[$className]))
			return $cache[$className];

		$restriction = new $className;

		if(!is_subclass_of($className, 'Bitrix\Sale\Services\Base\Restriction'))
			throw new SystemException('Object must be the instance of Bitrix\Sale\Services\Base\Restriction');

		$cache[$className] = $restriction;
		return  $restriction;
	}

	/**
	 * @deprecated use Restrictions\Manager::checkService()
	 */
	public static function checkServiceRestrictions($deliveryId, Shipment $shipment, $restrictionsClassesToSkip = array())
	{
		return Restrictions\Manager::checkService($deliveryId, $shipment) == Restrictions\Manager::SEVERITY_NONE;
	}

	/**
	 * @deprecated use \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList()
	 */
	public static function getServicesForShipment(Shipment $shipment)
	{
		return self::getRestrictedObjectsList($shipment);
	}

	/**
	 * @deprecated use \Bitrix\Sale\Delivery\Services\Manager::isServiceExist()
	 */
	public static function isExistService($deliveryId)
	{
		return self::isServiceExist($deliveryId);
	}

	/**
	 * @deprecated use \Bitrix\Sale\Delivery\Services\Manager::getActiveList()
	 */
	public static function getActive()
	{
		return self::getActiveList();
	}

	/**
	 * @deprecated use \Bitrix\Sale\Delivery\Services\Manager::getRestrictedList()
	 */
	public static function getServicesBriefsForShipment(Shipment $shipment = null, array $skipChecks = array(), $getAll = false)
	{
		return self::getRestrictedList($shipment, Restrictions\Manager::MODE_CLIENT, $skipChecks);
	}

	/**
	 * @deprecated use \Bitrix\Sale\Delivery\Services\Manager::createObject()
	 */
	public static function createServiceObject(array $srvParams)
	{
		return self::createObject($srvParams);
	}

	/**
	 * @deprecated use \Bitrix\Sale\Delivery\Services\Manager::getObjectById()
	 */
	public static function getService($deliveryId)
	{
		return self::getObjectById($deliveryId);
	}

	/**
	 * @deprecated use \Bitrix\Sale\Delivery\Services\Manager::getServiceByCode()
	 */
	public static function getServiceByCode($serviceCode)
	{
		return self::getObjectByCode($serviceCode);
	}

	/**
	 * @deprecated use \Bitrix\Sale\Delivery\Services\Manager::getHandlersList()
	 */
	public static function getHandlersClassNames()
	{
		return self::getHandlersList();
	}

	/**
	 * @deprecated use Restrictions\Manager::getClassesList()
	 */
	public static function getRestrictionClassNames()
	{
		return Restrictions\Manager::getClassesList();
	}

	/**
	 * @deprecated use Restrictions\Manager::getRestrictionsList()
	 */
	public static function getRestrictionsByDeliveryId($deliveryId)
	{
		return Restrictions\Manager::getRestrictionsList($deliveryId);
	}
}