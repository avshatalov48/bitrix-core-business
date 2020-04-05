<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Internals\EntityCollection;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Internals\PaySystemRestHandlersTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Services\PaySystem\Restrictions;

Loc::loadMessages(__FILE__);

/**
 * Class PaySystemManager
 * @package Bitrix\Sale\Payment
 */
final class Manager
{
	const HANDLER_AVAILABLE_TRUE = true;
	const HANDLER_AVAILABLE_FALSE = false;

	const EVENT_ON_GET_HANDLER_DESC = 'OnSaleGetHandlerDescription';
	const CACHE_ID = "BITRIX_SALE_INNER_PS_ID";
	const TTL = 31536000;
	/**
	 * @var array
	 */
	private static $handlerDirectories = array(
		'CUSTOM' => '',
		'LOCAL' => '/local/php_interface/include/sale_payment/',
		'SYSTEM' => '/bitrix/modules/sale/handlers/paysystem/',
		'SYSTEM_OLD' => '/bitrix/modules/sale/payment/'
	);

	/**
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function getHandlerDirectories()
	{
		$handlerDirectories = self::$handlerDirectories;
		$handlerDirectories['CUSTOM'] = Option::get("sale", "path2user_ps_files", BX_PERSONAL_ROOT."/php_interface/include/sale_payment/");

		if (IsModuleInstalled('intranet'))
		{
			unset($handlerDirectories['SYSTEM_OLD']);
		}

		return $handlerDirectories;
	}

	/**
	 * @param array $params
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getList(array $params = array())
	{
		return PaySystemActionTable::getList($params);
	}

	/**
	 * @param $id
	 * @return array|false
	 */
	public static function getById($id)
	{
		if ($id <= 0)
			return false;

		$params = array(
			'select' => array('*'),
			'filter' => array('ID' => $id)
		);

		$dbRes = self::getList($params);
		return $dbRes->fetch();
	}

	/**
	 * @param $code
	 * @return array|bool
	 */
	public static function getByCode($code)
	{
		$params = array(
			'select' => array('*'),
			'filter' => array('CODE' => $code)
		);

		$dbRes = self::getList($params);
		return $dbRes->fetch();
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return \Bitrix\Main\Entity\UpdateResult
	 * @throws \Exception
	 */
	public static function update($primary, array $data)
	{
		return PaySystemActionTable::update($primary, $data);
	}

	/**
	 * @param array $data
	 * @return \Bitrix\Main\Entity\AddResult
	 */
	public static function add(array $data)
	{
		return PaySystemActionTable::add($data);
	}

	/**
	 * @return string
	 */
	public static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * @param Request $request
	 * @return array|false
	 */
	public static function searchByRequest(Request $request)
	{
		$documentRoot = Application::getDocumentRoot();

		$items = self::getList(array('select' => array('*')));

		while ($item = $items->fetch())
		{
			$name = $item['ACTION_FILE'];

			foreach (self::getHandlerDirectories() as $type => $path)
			{
				$className = '';

				if (File::isFileExists($documentRoot.$path.$name.'/handler.php'))
				{
					$className = static::getClassNameFromPath($item['ACTION_FILE']);
					if (!class_exists($className))
						require_once($documentRoot.$path.$name.'/handler.php');
				}
				else if (static::isRestHandler($name))
				{
					$className = '\Bitrix\Sale\PaySystem\RestHandler';
				}

				if (class_exists($className) && is_callable(array($className, 'isMyResponse')))
				{
					if ($className::isMyResponse($request, $item['ID']))
						return $item;
				}
			}
		}

		return false;
	}

	/**
	 * @param string $className
	 * @return mixed|string
	 */
	public static function getFolderFromClassName($className)
	{
		$pos = strrpos($className, '\\');
		if ($pos !== false)
			$className = substr($className, $pos + 1);

		$folder = str_replace('Handler', '', $className);
		$folder = self::sanitize($folder);
		$folder = ToLower($folder);

		return $folder;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public static function sanitize($name)
	{
		return preg_replace("/[^a-z0-9._]/i", "", $name);
	}

	/**
	 * @param $paymentId
	 * @param string $registryType
	 * @return array
	 */
	public static function getIdsByPayment($paymentId, $registryType = Registry::REGISTRY_TYPE_ORDER)
	{
		if (empty($paymentId))
		{
			return array(0, 0);
		}

		$params = array(
			'select' => array('ID', 'ORDER_ID')
		);

		if (intval($paymentId).'|' == $paymentId.'|')
		{
			$params['filter']['ID'] = $paymentId;
		}
		else
		{
			$params['filter']['ACCOUNT_NUMBER'] = $paymentId;
		}

		$registry = Registry::getInstance($registryType);

		/** @var Payment $paymentClassName */
		$paymentClassName = $registry->getPaymentClassName();
		$result = $paymentClassName::getList($params);
		$data = $result->fetch() ?: array();

		return array((int)$data['ORDER_ID'], (int)$data['ID']);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getConsumersList()
	{
		$result = array();

		$items = self::getList();

		while ($item = $items->fetch())
		{
			$data = self::getHandlerDescription($item['ACTION_FILE']);
			$data['NAME'] = $item['NAME'];
			$data['GROUP'] = 'PAYSYSTEM';
			$data['PROVIDERS'] = [
				'VALUE', 'COMPANY', 'ORDER', 'USER', 'PROPERTY',
				'PAYMENT', 'BANK_DETAIL', 'MC_BANK_DETAIL',
				'REQUISITE', 'MC_REQUISITE', 'CRM_COMPANY',
				'CRM_MYCOMPANY', 'CRM_CONTACT'
			];

			$result['PAYSYSTEM_'.$item['ID']] = $data;
		}

		return $result;
	}

	/**
	 * @param $paySystemId
	 * @return string
	 */
	public static function getPsType($paySystemId)
	{
		$params = array(
			'select' => array('IS_CASH'),
			'filter' => array('ID' => $paySystemId)
		);

		$dbRes = self::getList($params);
		$data = $dbRes->fetch();

		return $data['IS_CASH'];
	}

	/**
	 * @param Payment $payment
	 * @param int $mode
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getListWithRestrictions(Payment $payment, $mode = Restrictions\Manager::MODE_CLIENT)
	{
		$result = array();

		$dbRes = self::getList(array(
			'filter' => array('ACTIVE' => 'Y', 'ENTITY_REGISTRY_TYPE' => $payment::getRegistryType()),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
		));

		while ($paySystem = $dbRes->fetch())
		{
			if ($mode == Restrictions\Manager::MODE_MANAGER)
			{
				$checkServiceResult = Restrictions\Manager::checkService($paySystem['ID'], $payment, $mode);
				if ($checkServiceResult != Restrictions\Manager::SEVERITY_STRICT)
				{
					if ($checkServiceResult == Restrictions\Manager::SEVERITY_SOFT)
						$paySystem['RESTRICTED'] = $checkServiceResult;
					$result[$paySystem['ID']] = $paySystem;
				}
			}
			else if ($mode == Restrictions\Manager::MODE_CLIENT)
			{
				if (Restrictions\Manager::checkService($paySystem['ID'], $payment, $mode) === Restrictions\Manager::SEVERITY_NONE)
					$result[$paySystem['ID']] = $paySystem;
			}
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	public static function getHandlerList()
	{
		$documentRoot = Application::getDocumentRoot();
		$result = array(
			'SYSTEM' => array(),
			'USER' => array()
		);

		foreach (self::getHandlerDirectories() as $type => $dir)
		{
			if (!Directory::isDirectoryExists($documentRoot.$dir))
			{
				continue;
			}

			$directory = new Directory($documentRoot.$dir);
			foreach ($directory->getChildren() as $handler)
			{
				if (!$handler->isDirectory())
				{
					continue;
				}

				$isDescriptionExist = false;
				/** @var Directory $handler */
				foreach ($handler->getChildren() as $item)
				{
					if ($item->isFile())
					{
						$data = array();
						$psTitle = '';
						$isAvailable = null;

						if (strpos($item->getName(), '.description') !== false)
						{
							$handlerName = $handler->getName();

							include $item->getPath();

							if (array_key_exists('NAME', $data))
							{
								$psTitle = $data['NAME'].' ('.$handlerName.')';
								if (isset($data['IS_AVAILABLE']))
								{
									$isAvailable = $data['IS_AVAILABLE'];
								}
							}
							else
							{
								if ($psTitle == '')
								{
									$psTitle = $handlerName;
								}
								else
								{
									$psTitle .= ' ('.$handlerName.')';
								}

								$handlerName = str_replace(Path::normalize($documentRoot), '', $handler->getPath());
							}
							$group = (strpos($type, 'SYSTEM') !== false) ? 'SYSTEM' : 'USER';

							if (!isset($result[$group][$handlerName]))
							{
								if ($isAvailable !== null
									&& $isAvailable === static::HANDLER_AVAILABLE_FALSE
								)
								{
									continue(2);
								}

								$result[$group][$handlerName] = $psTitle;
							}
							$isDescriptionExist = true;
							continue;
						}
					}
				}

				if (!$isDescriptionExist)
				{
					$group = (strpos($type, 'SYSTEM') !== false) ? 'SYSTEM' : 'USER';
					$handlerName = str_replace($documentRoot, '', $handler->getPath());
					$result[$group][$handlerName] = $handler->getName();
				}
			}
		}

		$result['USER'] = array_merge(static::getRestHandlers(), $result['USER']);

		return $result;
	}

	/**
	 * @param $path
	 * @return string
	 */
	public static function getClassNameFromPath($path)
	{
		$pos = strrpos($path, '/');

		if ($pos == strlen($path))
		{
			$path = substr($path, 0, $pos - 1);
			$pos = strrpos($path, '/');
		}

		if ($pos !== false)
			$path = substr($path, $pos+1);

		return "Sale\\Handlers\\PaySystem\\".$path.'Handler';
	}

	/**
	 * @param $handler
	 * @return array
	 */
	public static function getHandlerDescription($handler)
	{
		$service = new Service(array('ACTION_FILE' => $handler));
		$data = $service->getHandlerDescription();

		$eventParams = array('handler' => $handler);
		$event = new Event('sale', self::EVENT_ON_GET_HANDLER_DESC, $eventParams);
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if($eventResult->getType() !== EventResult::ERROR)
				$data['CODES'] = array_merge($data['CODES'], $eventResult->getParameters());
		}

		if (isset($data['CODES']) && is_array($data['CODES']))
			uasort($data['CODES'], function ($a, $b) { return ($a['SORT'] < $b['SORT']) ? -1 : 1;});

		return $data;
	}

	/**
	 * @param $folder
	 * @return null|string
	 */
	public static function getPathToHandlerFolder($folder)
	{
		$documentRoot = Application::getDocumentRoot();

		if (strpos($folder, '/') !== false)
		{
			return $folder;
		}
		else
		{
			$dirs = self::getHandlerDirectories();

			foreach ($dirs as $dir)
			{
				$path = $dir.$folder;
				if (!Directory::isDirectoryExists($documentRoot.$path))
					continue;

				return $path;
			}
		}

		return null;
	}

	/**
	 * @return int
	 */
	public static function getInnerPaySystemId()
	{
		$id = 0;
		$cacheManager = Application::getInstance()->getManagedCache();

		if($cacheManager->read(self::TTL, self::CACHE_ID))
			$id = $cacheManager->get(self::CACHE_ID);

		if ($id <= 0)
		{
			$data = PaySystemActionTable::getRow(
				array(
					'select' => array('ID'),
					'filter' => array('ACTION_FILE' => 'inner')
				)
			);
			if ($data === null)
				$id = self::createInnerPaySystem();
			else
				$id = $data['ID'];

			$cacheManager->set(self::CACHE_ID, $id);
		}

		return $id;
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	private static function createInnerPaySystem()
	{
		$paySystemSettings = array(
			'NAME' => Loc::getMessage('SALE_PS_MANAGER_INNER_NAME'),
			'PSA_NAME' => Loc::getMessage('SALE_PS_MANAGER_INNER_NAME'),
			'ACTION_FILE' => 'inner',
			'ACTIVE' => 'Y',
			'ENTITY_REGISTRY_TYPE' => Registry::REGISTRY_TYPE_ORDER,
			'NEW_WINDOW' => 'N'
		);

		$imagePath = Application::getDocumentRoot().'/bitrix/images/sale/sale_payments/inner.png';
		if (File::isFileExists($imagePath))
		{
			$paySystemSettings['LOGOTIP'] = \CFile::MakeFileArray($imagePath);
			$paySystemSettings['LOGOTIP']['MODULE_ID'] = "sale";
			\CFile::SaveForDB($paySystemSettings, 'LOGOTIP', 'sale/paysystem/logotip');
		}

		$result = PaySystemActionTable::add($paySystemSettings);

		if ($result->isSuccess())
			return $result->getId();

		return 0;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public static function isExist($id)
	{
		return (bool)self::getById($id);
	}

	/**
	 * @param $id
	 * @return Service|null
	 */
	public static function getObjectById($id)
	{
		if ($id <= 0)
			return null;

		$data = Manager::getById($id);

		if (is_array($data) && $data)
			return new Service($data);

		return null;
	}

	/**
	 * @param $folder
	 * @param int $paySystemId
	 * @return array|mixed
	 */
	public static function getTariff($folder, $paySystemId = 0)
	{
		$documentRoot = Application::getDocumentRoot();
		$result = array();

		$path = self::getPathToHandlerFolder($folder);
		if ($path !== null)
		{
			if (File::isFileExists($documentRoot.$path.'/handler.php'))
			{
				require_once $documentRoot.$path.'/handler.php';

				$className = self::getClassNameFromPath($folder);
				if (class_exists($className))
				{
					$interfaces = class_implements($className);
					if (array_key_exists('Bitrix\Sale\PaySystem\IPayable', $interfaces))
						$result = $className::getStructure($paySystemId);
				}
			}
		}
		else
		{
			$result = \CSalePaySystemsHelper::getPaySystemTarif($folder, $paySystemId);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public static function getBusValueGroups()
	{
		return array(
			'CONNECT_SETTINGS_ALFABANK' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_ALFABANK'), 'SORT' => 100),
			'CONNECT_SETTINGS_AUTHORIZE' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_AUTHORIZE'), 'SORT' => 100),
			'CONNECT_SETTINGS_YANDEX' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_YANDEX'), 'SORT' => 100),
			'CONNECT_SETTINGS_YANDEX_INVOICE' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_YANDEX_INVOICE'), 'SORT' => 100),
			'CONNECT_SETTINGS_WEBMONEY' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_WEBMONEY'), 'SORT' => 100),
			'CONNECT_SETTINGS_ASSIST' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_ASSIST'), 'SORT' => 100),
			'CONNECT_SETTINGS_ROBOXCHANGE' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_ROBOXCHANGE'), 'SORT' => 100),
			'CONNECT_SETTINGS_QIWI' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_QIWI'), 'SORT' => 100),
			'CONNECT_SETTINGS_PAYPAL' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_PAYPAL'), 'SORT' => 100),
			'CONNECT_SETTINGS_PAYMASTER' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_PAYMASTER'), 'SORT' => 100),
			'CONNECT_SETTINGS_LIQPAY' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_LIQPAY'), 'SORT' => 100),
			'CONNECT_SETTINGS_BILL' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILL'), 'SORT' => 100),
			'CONNECT_SETTINGS_BILLDE' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILLDE'), 'SORT' => 100),
			'CONNECT_SETTINGS_BILLEN' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILLEN'), 'SORT' => 100),
			'CONNECT_SETTINGS_BILLUA' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILLUA'), 'SORT' => 100),
			'CONNECT_SETTINGS_BILLLA' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILLLA'), 'SORT' => 100),
			'GENERAL_SETTINGS' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_GENERAL_SETTINGS'), 'SORT' => 100),
			'COLUMN_SETTINGS' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_COLUMN'), 'SORT' => 100),
			'VISUAL_SETTINGS' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_VISUAL'), 'SORT' => 100),
			'PAYMENT' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_PAYMENT'), 'SORT' => 200),
			'PAYSYSTEM' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_PAYSYSTEM'), 'SORT' => 500),
			'PS_OTHER' => array('NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_PS_OTHER'), 'SORT' => 10000)
		);
	}

	/**
	 * @param $primary
	 * @return \Bitrix\Main\Entity\DeleteResult
	 */
	public static function delete($primary)
	{
		$paySystemInfo = array();
		if ($primary)
		{
			$dbRes = PaySystemActionTable::getById($primary);
			$paySystemInfo = $dbRes->fetch();
		}

		$result = PaySystemActionTable::delete($primary);
		if ($result->isSuccess())
		{
			if ($paySystemInfo['LOGOTIP'])
				\CFile::Delete($paySystemInfo['LOGOTIP']);

			$restrictionList =  Restrictions\Manager::getRestrictionsList($primary);
			if ($restrictionList)
			{
				Restrictions\Manager::getClassesList();

				foreach ($restrictionList as $restriction)
				{
					/** @var \Bitrix\Sale\Services\Base\Restriction $className */
					$className = $restriction["CLASS_NAME"];
					if (is_subclass_of($className, '\Bitrix\Sale\Services\Base\Restriction'))
					{
						$className::delete($restriction['ID'], $primary);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param $paySystemId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getPersonTypeIdList($paySystemId)
	{
		$data = array();

		$dbRestriction = ServiceRestrictionTable::getList(array(
			'filter' => array(
				'SERVICE_ID' => $paySystemId,
				'SERVICE_TYPE' => Restrictions\Manager::SERVICE_TYPE_PAYMENT,
				'=CLASS_NAME' => '\\'.Restrictions\PersonType::class
			)
		));

		while ($restriction = $dbRestriction->fetch())
			$data = array_merge($data, $restriction['PARAMS']['PERSON_TYPE_ID']);

		return $data;
	}

	/**
	 * @param array $data
	 * @return null|EntityCollection|Payment
	 */
	public static function getPaymentObjectByData(array $data)
	{
		$context = Application::getInstance()->getContext();

		/** @var Order $order */
		$order = Order::create($context->getSite());
		$order->setPersonTypeId($data['PERSON_TYPE_ID']);

		$basket = Basket::create($context->getSite());
		$order->setBasket($basket);

		$collection = $order->getPaymentCollection();
		if ($collection)
			return $collection->createItem();

		return null;
	}

	/**
	 * @return array
	 */
	public static function getDataRefundablePage()
	{
		$paySystemList = array();
		$dbRes = static::getList();
		while ($data = $dbRes->fetch())
		{
			$service = new Service($data);
			if ($service->isRefundable())
				$paySystemList[$data['ACTION_FILE']][] = $data;
		}

		$result = array();
		foreach ($paySystemList as $handler => $data)
		{
			/* @var ServiceHandler $classHandler */
			$classHandler = static::getClassNameFromPath($handler);

			if (is_subclass_of($classHandler, '\Bitrix\Sale\PaySystem\ServiceHandler'))
			{
				$settings = $classHandler::findMyDataRefundablePage($data);
				if ($settings)
					$result = array_merge($settings, $result);
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private static function getRestHandlers()
	{
		$result = array();

		$dbRes = PaySystemRestHandlersTable::getList();
		while ($item = $dbRes->fetch())
		{
			$result[$item['CODE']] = $item['NAME'].' ('.$item['CODE'].')';
		}

		return $result;
	}

	/**
	 * @param $handler
	 * @return bool
	 */
	public static function isRestHandler($handler)
	{
		$dbRes = PaySystemRestHandlersTable::getList(array('filter' => array('CODE' => $handler)));
		return (bool)$dbRes->fetch();
	}
}