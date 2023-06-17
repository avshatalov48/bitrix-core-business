<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Internals\PaySystemRestHandlersTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\Internals\BusinessValueTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Services\Base\Restriction;
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
	const EVENT_ON_PAYSYSTEM_UPDATE = 'OnSalePaySystemUpdate';

	const CACHE_ID = "BITRIX_SALE_INNER_PS_ID";
	const TTL = 31536000;
	/**
	 * @var array
	 */
	private static $handlerDirectories = array(
		'CUSTOM' => '',
		'LOCAL' => '/local/php_interface/include/sale_payment/',
		'SYSTEM' => '/bitrix/modules/sale/handlers/paysystem/',

		/**
		 * @deprecated
		 * The directory /bitrix/modules/sale/payment/ is not supported since version 22.200.0
		 * Key SYSTEM_OLD is left for compatibility
		 */
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
		if ((int)$id <= 0)
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
	 * @return \Bitrix\Main\ORM\Data\UpdateResult
	 * @throws \Exception
	 */
	public static function update($primary, array $data): \Bitrix\Main\ORM\Data\UpdateResult
	{
		$oldFields = PaySystemActionTable::getByPrimary($primary)->fetch();
		if ($oldFields)
		{
			$newFields = array_merge($oldFields, $data);
			$data['PS_CLIENT_TYPE'] = (new Service($newFields))->getClientTypeFromHandler();
		}

		$updateResult = PaySystemActionTable::update($primary, $data);
		if ($oldFields && $updateResult->isSuccess())
		{
			$oldFields = array_intersect_key($oldFields, $data);
			$eventParams = [
				'PAY_SYSTEM_ID' => $primary,
				'OLD_FIELDS' => $oldFields,
				'NEW_FIELDS' => $data,
			];
			$event = new Event('sale', self::EVENT_ON_PAYSYSTEM_UPDATE, $eventParams);
			$event->send();
		}

		return $updateResult;
	}

	/**
	 * @param array $data
	 * @return \Bitrix\Main\ORM\Data\AddResult
	 * @throws \Exception
	 */
	public static function add(array $data): \Bitrix\Main\ORM\Data\AddResult
	{
		$data['PS_CLIENT_TYPE'] = (new Service($data))->getClientTypeFromHandler();
		
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
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function searchByRequest(Request $request)
	{
		$documentRoot = Application::getDocumentRoot();

		$items = self::getList([
			'select' => ['*'],
			'filter' => [
				'ACTIVE' => 'Y',
			],
		]);

		while ($item = $items->fetch())
		{
			$name = $item['ACTION_FILE'];

			foreach (self::getHandlerDirectories() as $type => $path)
			{
				$className = '';
				if (File::isFileExists($documentRoot.$path.$name.'/handler.php'))
				{
					[$className] = self::includeHandler($item['ACTION_FILE']);
				}

				if (class_exists($className) && is_callable(array($className, 'isMyResponse')))
				{
					if ($className::isMyResponse($request, $item['ID']))
					{
						return $item;
					}
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
		$pos = mb_strrpos($className, '\\');
		if ($pos !== false)
			$className = mb_substr($className, $pos + 1);

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
	public static function getIdsByPayment($paymentId, $registryType = Registry::REGISTRY_TYPE_ORDER): array
	{
		if (empty($paymentId))
		{
			return [0, 0];
		}

		$params = [
			'select' => ['ID', 'ORDER_ID'],
		];

		if (intval($paymentId).'|' == $paymentId.'|')
		{
			$params['filter']['=ID'] = $paymentId;
		}
		else
		{
			$params['filter']['=ACCOUNT_NUMBER'] = $paymentId;
		}

		$registry = Registry::getInstance($registryType);

		/** @var Payment $paymentClassName */
		$paymentClassName = $registry->getPaymentClassName();
		$result = $paymentClassName::getList($params);
		$data = $result->fetch() ?: [];

		return [(int)$data['ORDER_ID'], (int)$data['ID']];
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
			$data = self::getHandlerDescription($item['ACTION_FILE'], $item['PS_MODE']);
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
	 * @param Order $order
	 * @param float|null $sum
	 * @param int $mode
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\NotSupportedException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getListWithRestrictionsByOrder(Order $order, float $sum = null, int $mode = Restrictions\Manager::MODE_CLIENT): array
	{
		/** @var Order $orderClone */
		$orderClone = $order->createClone();

		$orderPrice = $orderClone->getPrice();
		$paymentSum = $orderPrice;
		if ($sum && $sum >= 0 && $sum <= $orderPrice)
		{
			$paymentSum = $sum;
		}

		$paymentCollection = $orderClone->getPaymentCollection();
		$payment = $paymentCollection->createItem();
		$payment->setFields([
			'SUM' => $paymentSum,
		]);

		return self::getListWithRestrictions($payment, $mode);
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

		$filter = [
			'=ACTIVE' => 'Y',
			'=ENTITY_REGISTRY_TYPE' => $payment::getRegistryType(),
		];
		
		$bindingPaySystemIds = [];
		if ($mode == Restrictions\Manager::MODE_CLIENT)
		{
			$bindingPaySystemIds = PaymentAvailablesPaySystems::getAvailablePaySystemIdsByPaymentId($payment->getId());
			if ($bindingPaySystemIds)
			{
				$filter['=ID'] = $bindingPaySystemIds;
			}
		}

		$dbRes = self::getList([
			'filter' => $filter,
			'order' => [
				'SORT' => 'ASC',
				'NAME' => 'ASC',
			],
		]);

		while ($paySystem = $dbRes->fetch())
		{
			if ($bindingPaySystemIds)
			{
				$result[$paySystem['ID']] = $paySystem;
			}
			elseif ($mode == Restrictions\Manager::MODE_MANAGER)
			{
				$checkServiceResult = Restrictions\Manager::checkService($paySystem['ID'], $payment, $mode);
				if ($checkServiceResult != Restrictions\Manager::SEVERITY_STRICT)
				{
					if ($checkServiceResult == Restrictions\Manager::SEVERITY_SOFT)
					{
						$paySystem['RESTRICTED'] = $checkServiceResult;
					}
					$result[$paySystem['ID']] = $paySystem;
				}
			}
			elseif ($mode == Restrictions\Manager::MODE_CLIENT)
			{
				if (Restrictions\Manager::checkService($paySystem['ID'], $payment, $mode) === Restrictions\Manager::SEVERITY_NONE)
				{
					$result[$paySystem['ID']] = $paySystem;
				}
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

						if (mb_strpos($item->getName(), '.description') !== false)
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
							$group = (mb_strpos($type, 'SYSTEM') !== false) ? 'SYSTEM' : 'USER';

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
					$group = (mb_strpos($type, 'SYSTEM') !== false) ? 'SYSTEM' : 'USER';
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
		$pos = mb_strrpos($path, '/');

		if ($pos == mb_strlen($path))
		{
			$path = mb_substr($path, 0, $pos - 1);
			$pos = mb_strrpos($path, '/');
		}

		if ($pos !== false)
			$path = mb_substr($path, $pos + 1);

		return "Sale\\Handlers\\PaySystem\\".$path.'Handler';
	}

	/**
	 * @param $handler
	 * @param null $psMode
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function getHandlerDescription($handler, $psMode = null)
	{
		$service = new Service(array('ACTION_FILE' => $handler, 'PS_MODE' => $psMode));
		$data = $service->getHandlerDescription();

		$eventParams = array('handler' => $handler);
		$event = new Event('sale', self::EVENT_ON_GET_HANDLER_DESC, $eventParams);
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() !== EventResult::ERROR)
			{
				$codes = $eventResult->getParameters();
				if ($codes && is_array($codes))
				{
					if (!isset($data['CODES']) || !is_array($data['CODES']))
					{
						$data['CODES'] = [];
					}

					$data['CODES'] = array_merge($data['CODES'], $codes);
				}
			}
		}

		if (isset($data['CODES']) && is_array($data['CODES']))
		{
			uasort(
				$data['CODES'],
				function ($a, $b)
				{
					$sortA = $a['SORT'] ?? 0;
					$sortB = $b['SORT'] ?? 0;

					return ($sortA < $sortB) ? -1 : 1;
				}
			);
		}

		return $data;
	}

	/**
	 * @param $folder
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function getPathToHandlerFolder($folder): ?string
	{
		if (!$folder)
		{
			return null;
		}

		$documentRoot = Application::getDocumentRoot();
		$dirs = self::getHandlerDirectories();

		if (mb_strpos($folder, DIRECTORY_SEPARATOR) !== false)
		{
			$folderWithoutHandlerName = array_slice(explode(DIRECTORY_SEPARATOR, $folder), 1, -1);
			$folderWithoutHandlerName = implode(DIRECTORY_SEPARATOR, $folderWithoutHandlerName);

			$handlersDirectory = new Directory($folderWithoutHandlerName);
			$handlersDirectoryPhysicalPath = DIRECTORY_SEPARATOR.$handlersDirectory->getPhysicalPath().DIRECTORY_SEPARATOR;

			foreach ($dirs as $dir)
			{
				if ($documentRoot.$dir !== $documentRoot.$handlersDirectoryPhysicalPath)
				{
					continue;
				}

				return Directory::isDirectoryExists($documentRoot.$folder) ? $folder : null;
			}
		}
		else
		{
			foreach ($dirs as $dir)
			{
				$path = $dir.$folder;
				if (!Directory::isDirectoryExists($documentRoot.$path))
				{
					continue;
				}

				return $path;
			}
		}

		return null;
	}

	/**
	 * @return int
	 */
	public static function getInnerPaySystemId() : int
	{
		$id = 0;
		$cacheManager = Application::getInstance()->getManagedCache();

		if ($cacheManager->read(self::TTL, self::CACHE_ID))
		{
			$id = $cacheManager->get(self::CACHE_ID);
		}

		if ($id <= 0)
		{
			$data = PaySystemActionTable::getRow(
				[
					'select' => ['ID'],
					'filter' => ['ACTION_FILE' => 'inner']
				]
			);
			if ($data === null)
			{
				$id = self::createInnerPaySystem();
			}
			else
			{
				$id = $data['ID'];
			}

			$cacheManager->set(self::CACHE_ID, $id);
		}

		return (int)$id;
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
		if ((int)$id <= 0)
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
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
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
				$actionFile = self::getFolderFromClassName(self::getClassNameFromPath($path));
				[$className] = self::includeHandler($actionFile);
				if (class_exists($className) && is_subclass_of($className, IPayable::class))
				{
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
		return [
			'CONNECT_SETTINGS_ALFABANK' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_ALFABANK'), 'SORT' => 100],
			'CONNECT_SETTINGS_AUTHORIZE' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_AUTHORIZE'), 'SORT' => 100],
			'CONNECT_SETTINGS_YANDEX' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_YANDEX'), 'SORT' => 100],
			'CONNECT_SETTINGS_YANDEX_INVOICE' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_YANDEX_INVOICE'), 'SORT' => 100],
			'CONNECT_SETTINGS_WEBMONEY' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_WEBMONEY'), 'SORT' => 100],
			'CONNECT_SETTINGS_ASSIST' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_ASSIST'), 'SORT' => 100],
			'CONNECT_SETTINGS_ROBOXCHANGE' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_ROBOXCHANGE'), 'SORT' => 100],
			'CONNECT_SETTINGS_QIWI' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_QIWI'), 'SORT' => 100],
			'CONNECT_SETTINGS_PAYPAL' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_PAYPAL'), 'SORT' => 100],
			'CONNECT_SETTINGS_PAYMASTER' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_PAYMASTER'), 'SORT' => 100],
			'CONNECT_SETTINGS_LIQPAY' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_LIQPAY'), 'SORT' => 100],
			'CONNECT_SETTINGS_BILL' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILL'), 'SORT' => 100],
			'CONNECT_SETTINGS_BILLDE' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILLDE'), 'SORT' => 100],
			'CONNECT_SETTINGS_BILLEN' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILLEN'), 'SORT' => 100],
			'CONNECT_SETTINGS_BILLUA' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILLUA'), 'SORT' => 100],
			'CONNECT_SETTINGS_BILLLA' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BILLLA'), 'SORT' => 100],
			'CONNECT_SETTINGS_SBERBANK' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_SBERBANK'), 'SORT' => 100],
			'GENERAL_SETTINGS' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_GENERAL_SETTINGS'), 'SORT' => 100],
			'COLUMN_SETTINGS' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_COLUMN'), 'SORT' => 100],
			'VISUAL_SETTINGS' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_VISUAL'), 'SORT' => 100],
			'PAYMENT' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_PAYMENT'), 'SORT' => 200],
			'PAYSYSTEM' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_PAYSYSTEM'), 'SORT' => 500],
			'PS_OTHER' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_PS_OTHER'), 'SORT' => 10000],
			'CONNECT_SETTINGS_UAPAY' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_UAPAY'), 'SORT' => 100],
			'CONNECT_SETTINGS_ADYEN' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_ADYEN'), 'SORT' => 100],
			'CONNECT_SETTINGS_APPLE_PAY' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_APPLE_PAY'), 'SORT' => 200],
			'CONNECT_SETTINGS_SKB' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_SINARA'), 'SORT' => 100],
			'CONNECT_SETTINGS_BEPAID' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_BEPAID'), 'SORT' => 100],
			'CONNECT_SETTINGS_WOOPPAY' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_WOOPPAY'), 'SORT' => 100],
			'CONNECT_SETTINGS_PLATON' => ['NAME' => Loc::getMessage('SALE_PS_MANAGER_GROUP_CONNECT_SETTINGS_PLATON'), 'SORT' => 100],
		];
	}

	/**
	 * @param $primary
	 * @return \Bitrix\Main\Entity\DeleteResult|\Bitrix\Main\ORM\Data\DeleteResult
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function delete($primary)
	{
		if (empty($primary))
		{
			throw new ArgumentException('Parameter $primary can\'t be empty');
		}

		$paySystemInfo = PaySystemActionTable::getRowById($primary);
		if ($paySystemInfo['LOGOTIP'])
		{
			\CFile::Delete($paySystemInfo['LOGOTIP']);
		}

		self::deleteRestrictions($primary);

		$service = Manager::getObjectById($primary);
		if ($service)
		{
			self::deleteBusinessValues($service);
		}

		$deleteResult = PaySystemActionTable::delete($primary);
		if ($deleteResult->isSuccess())
		{
			if ($service && $service->isSupportPrintCheck())
			{
				$onDeletePaySystemResult = Cashbox\EventHandler::onDeletePaySystem($service);
				if (!$onDeletePaySystemResult->isSuccess())
				{
					$deleteResult->addErrors($onDeletePaySystemResult->getErrors());
				}
			}
		}

		return $deleteResult;
	}

	/**
	 * Deletes restrictions
	 *
	 * @param int $paySystemId
	 * @return void
	 */
	private static function deleteRestrictions(int $paySystemId): void
	{
		$restrictionList =  Restrictions\Manager::getRestrictionsList($paySystemId);
		if ($restrictionList)
		{
			Restrictions\Manager::getClassesList();

			foreach ($restrictionList as $restriction)
			{
				/** @var Restriction $className */
				$className = $restriction['CLASS_NAME'];
				if (is_subclass_of($className, Restriction::class))
				{
					$className::delete($restriction['ID'], $paySystemId);
				}
			}
		}
	}

	/**
	 * Deletes business value with prefix
	 * Also if there is only 1 paysystem (before delete), deletes all business value including COMMON
	 *
	 * @param Service $service
	 * @return void
	 */
	private static function deleteBusinessValues(Service $service): void
	{
		BusinessValue::delete(Service::PAY_SYSTEM_PREFIX . $service->getField('ID'));

		$paySystemCount = PaySystemActionTable::getList([
			'select' => ['ID'],
			'filter' => [
				'ACTION_FILE' => $service->getField('ACTION_FILE'),
			],
			'count_total' => true,
		])->getCount();
		if ($paySystemCount === 1)
		{
			$handlerDescription = $service->getHandlerDescription();
			$handlerDescriptionCodes = array_keys($handlerDescription['CODES'] ?? []);
			foreach ($handlerDescriptionCodes as $code)
			{
				BusinessValueTable::deleteByCodeKey($code);
			}
		}
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
	 * @return Payment|null
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\NotSupportedException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getPaymentObjectByData(array $data)
	{
		$context = Application::getInstance()->getContext();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		/** @var Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		/** @var Order $order */
		$order = $orderClass::create($context->getSite());
		$order->setPersonTypeId($data['PERSON_TYPE_ID']);

		/** @var Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$basket = $basketClass::create($context->getSite());
		$order->setBasket($basket);

		$collection = $order->getPaymentCollection();
		if ($collection)
		{
			return $collection->createItem();
		}

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
		static $result = [];

		if (isset($result[$handler]))
		{
			return $result[$handler];
		}

		$handlerData = PaySystemRestHandlersTable::getList([
			'filter' => ['=CODE' => $handler],
			'limit' => 1,
		])->fetch();
		$result[$handler] = (bool)$handlerData;

		return $result[$handler] ?? false;
	}

	/**
	 * @param $actionFile
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function includeHandler($actionFile): array
	{
		$className = '';
		$handlerType = '';

		if ($name = self::getFolderFromClassName($actionFile))
		{
			$documentRoot = Application::getDocumentRoot();
			foreach (self::getHandlerDirectories() as $type => $path)
			{
				if (File::isFileExists($documentRoot.$path.$name.'/handler.php'))
				{
					$className = self::getClassNameFromPath($actionFile);
					if (!class_exists($className))
						require_once($documentRoot.$path.$name.'/handler.php');

					if (class_exists($className))
					{
						$handlerType = $type;
						break;
					}

					$className = '';
				}
			}
		}

		if ($className === '')
		{
			if (self::isRestHandler($actionFile))
			{
				$className = '\Bitrix\Sale\PaySystem\RestHandler';
				if (!class_exists($actionFile))
				{
					class_alias($className, $actionFile);
				}
			}
			else
			{
				$className = '\Bitrix\Sale\PaySystem\CompatibilityHandler';
			}
		}

		return [
			$className,
			$handlerType,
		];
	}
}