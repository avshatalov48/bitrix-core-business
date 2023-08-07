<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Cashbox\Internals\CashboxConnectTable;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Bitrix\Sale\Internals\CashboxRestHandlerTable;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Result;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * Class Manager
 * @package Bitrix\Sale\Cashbox
 */
final class Manager
{
	/* ignored all errors, warnings */
	const LEVEL_TRACE_E_IGNORED = 0;
	/* trace only errors */
	const LEVEL_TRACE_E_ERROR = Errors\Error::LEVEL_TRACE;
	/* trace only errors, warnings */
	const LEVEL_TRACE_E_WARNING = Errors\Warning::LEVEL_TRACE;

	const DEBUG_MODE = false;
	const CACHE_ID = 'BITRIX_CASHBOX_LIST';
	const TTL = 31536000;
	const CHECK_STATUS_AGENT = '\Bitrix\Sale\Cashbox\Manager::updateChecksStatus();';

	private const EVENT_ON_BEFORE_CASHBOX_ADD = 'onBeforeCashboxAdd';

	/**
	 * @param CollectableEntity $entity
	 * @return array
	 */
	public static function getListWithRestrictions(CollectableEntity $entity)
	{
		$result = array();

		$dbRes = CashboxTable::getList(array(
			'select' => array('*'),
			'filter' => array('ACTIVE' => 'Y'),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
		));

		while ($cashbox = $dbRes->fetch())
		{
			if (Restrictions\Manager::checkService($cashbox['ID'], $entity) === Restrictions\Manager::SEVERITY_NONE)
				$result[$cashbox['ID']] = $cashbox;
		}

		return $result;
	}

	private static function getCashboxByPayment(Sale\Payment $payment): array
	{
		$service = $payment->getPaySystem();
		if ($service && $service->isSupportPrintCheck())
		{
			/** @var CashboxPaySystem $cashboxClass */
			$cashboxClass = $service->getCashboxClass();
			$kkm = $cashboxClass::getKkmValue($service);

			return self::getList([
				'filter' => [
					'=ACTIVE' => 'Y',
					'=HANDLER' => $cashboxClass,
					'=KKM_ID' => $kkm,
				],
			])->fetch();
		}

		return [];
	}

	/**
	 * @param Check $check
	 * @return array
	 */
	public static function getAvailableCashboxList(Check $check): array
	{
		$cashboxList = [];
		$firstIteration = true;

		$payment = CheckManager::getPaymentByCheck($check);
		if ($payment && self::canPaySystemPrint($payment))
		{
			$cashbox = self::getCashboxByPayment($payment);
			if ($cashbox)
			{
				$cashboxList[] = $cashbox;
			}
		}
		else
		{
			$entities = $check->getEntities();
			foreach ($entities as $entity)
			{
				$items = self::getListWithRestrictions($entity);
				if ($firstIteration)
				{
					$cashboxList = $items;
					$firstIteration = false;
				}
				else
				{
					$cashboxList = array_intersect_assoc($items, $cashboxList);
				}
			}

			foreach ($cashboxList as $key => $cashbox)
			{
				if (self::isPaySystemCashbox($cashbox['HANDLER']))
				{
					unset($cashboxList[$key]);
				}
			}
		}

		return $cashboxList;
	}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = [])
	{
		return CashboxTable::getList($parameters);
	}

	/**
	 * Returns a list of all the registered rest cashbox handlers
	 * Structure: handler code => handler data
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getRestHandlersList()
	{
		$result = [];

		$handlerList = CashboxRestHandlerTable::getList()->fetchAll();
		foreach ($handlerList as $handler)
		{
			$result[$handler['CODE']] = $handler;
		}

		return $result;
	}

	/**
	 * @param $id
	 * @return Cashbox|ICheckable|null
	 */
	public static function getObjectById($id)
	{
		static $cashboxObjects = array();

		if ((int)$id <= 0)
		{
			return null;
		}

		if (!isset($cashboxObjects[$id]))
		{
			$data = static::getCashboxFromCache($id);
			if ($data)
			{
				$cashbox = Cashbox::create($data);
				if ($cashbox === null)
				{
					return null;
				}

				$cashboxObjects[$id] = $cashbox;
			}
		}

		return $cashboxObjects[$id] ?? null;
	}

	/**
	 * @param $cashboxList
	 * @param Check|null $check
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public static function chooseCashbox($cashboxList)
	{
		$index = rand(0, count($cashboxList)-1);

		return $cashboxList[$index];
	}

	/**
	 * @return string
	 */
	public static function getConnectionLink()
	{
		$context = Main\Application::getInstance()->getContext();
		$scheme = $context->getRequest()->isHttps() ? 'https' : 'http';
		$server = $context->getServer();
		$domain = $server->getServerName();

		if (preg_match('/^(?<domain>.+):(?<port>\d+)$/', $domain, $matches))
		{
			$domain = $matches['domain'];
			$port   = $matches['port'];
		}
		else
		{
			$port = $server->getServerPort();
		}
		$port = in_array($port, array(80, 443)) ? '' : ':'.$port;

		return sprintf('%s://%s%s/bitrix/tools/sale_check_print.php?hash=%s', $scheme, $domain, $port, static::generateHash());
	}

	/**
	 * @return string
	 */
	private static function generateHash()
	{
		$hash = md5(base64_encode(time()));
		CashboxConnectTable::add(array('HASH' => $hash, 'ACTIVE' => 'Y'));

		return $hash;
	}

	/**
	 * @return mixed
	 */
	public static function getListFromCache()
	{
		$cacheManager = Main\Application::getInstance()->getManagedCache();

		if($cacheManager->read(self::TTL, self::CACHE_ID))
			$cashboxList = $cacheManager->get(self::CACHE_ID);

		if (empty($cashboxList))
		{
			$cashboxList = array();

			$dbRes = CashboxTable::getList();
			while ($data = $dbRes->fetch())
				$cashboxList[$data['ID']] = $data;

			$cacheManager->set(self::CACHE_ID, $cashboxList);
		}

		return $cashboxList;
	}

	/**
	 * @param $cashboxId
	 * @return array
	 */
	public static function getCashboxFromCache($cashboxId)
	{
		$cashboxList = static::getListFromCache();
		if (isset($cashboxList[$cashboxId]))
			return $cashboxList[$cashboxId];

		return array();
	}

	/**
	 * @param $cashboxId
	 * @param $id
	 * @return array
	 */
	public static function buildZReportQuery($cashboxId, $id)
	{
		$cashbox = Manager::getObjectById($cashboxId);
		if ($cashbox->getField('USE_OFFLINE') === 'Y')
			return array();

		return $cashbox->buildZReportQuery($id);
	}

	/**
	 * @param $cashboxIds
	 * @return array
	 */
	public static function buildChecksQuery($cashboxIds)
	{
		$result = array();
		$checks = CheckManager::getPrintableChecks($cashboxIds);
		foreach ($checks as $item)
		{
			$check = CheckManager::create($item);
			if ($check !== null)
			{
				$printResult = static::buildConcreteCheckQuery($check->getField('CASHBOX_ID'), $check);
				if ($printResult)
					$result[] = $printResult;
			}
		}

		return $result;
	}

	/**
	 * @param $cashboxId
	 * @param Check $check
	 * @return array
	 */
	public static function buildConcreteCheckQuery($cashboxId, Check $check)
	{
		$cashbox = static::getObjectById($cashboxId);
		if ($cashbox)
		{
			return $cashbox->buildCheckQuery($check);
		}

		return [];
	}

	/**
	 * @param array $data
	 * @return \Bitrix\Main\Entity\AddResult
	 */
	public static function add(array $data)
	{
		$event = new Main\Event('sale', self::EVENT_ON_BEFORE_CASHBOX_ADD, $data);
		$event->send();
		$eventResults = $event->getResults();
		foreach ($eventResults as $eventResult)
		{
			$data = array_merge($data, $eventResult->getParameters());
		}
		$addResult = CashboxTable::add($data);

		$cacheManager = Main\Application::getInstance()->getManagedCache();
		$cacheManager->clean(Manager::CACHE_ID);

		if (
			is_subclass_of($data['HANDLER'], ICheckable::class)
			|| (
				is_subclass_of($data['HANDLER'], ICorrection::class)
				&& $data['HANDLER']::isCorrectionOn()
			)
		)
		{
			static::addCheckStatusAgent();
		}

		return $addResult;
	}

	/**
	 * @return void
	 */
	private static function addCheckStatusAgent()
	{
		\CAgent::AddAgent(static::CHECK_STATUS_AGENT, "sale", "N", 120, "", "Y");
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return \Bitrix\Main\Entity\UpdateResult
	 */
	public static function update($primary, array $data)
	{
		$updateResult = CashboxTable::update($primary, $data);

		$cacheManager = Main\Application::getInstance()->getManagedCache();

		$service = self::getObjectById($primary);
		if ($service && self::isPaySystemCashbox($service->getField('HANDLER')))
		{
			/** @var CashboxPaySystem $cashboxClass */
			$cashboxClass = $service->getField('HANDLER');
			if ($cashboxClass::CACHE_ID)
			{
				$cacheManager->clean($cashboxClass::CACHE_ID);
			}
		}

		$cacheManager->clean(Manager::CACHE_ID);

		if (is_subclass_of($data['HANDLER'], '\Bitrix\Sale\Cashbox\ICheckable'))
		{
			static::addCheckStatusAgent();
		}

		return $updateResult;
	}

	/**
	 * @param $primary
	 * @return \Bitrix\Main\Entity\DeleteResult
	 */
	public static function delete($primary)
	{
		$service = self::getObjectById($primary);
		$deleteResult = CashboxTable::delete($primary);
		$cacheManager = Main\Application::getInstance()->getManagedCache();

		if ($primary == Cashbox1C::getId())
		{
			$cacheManager->clean(Cashbox1C::CACHE_ID);
		}

		if ($service && self::isPaySystemCashbox($service->getField('HANDLER')))
		{
			/** @var CashboxPaySystem $cashboxClass */
			$cashboxClass = $service->getField('HANDLER');
			if ($cashboxClass::CACHE_ID)
			{
				$cacheManager->clean($cashboxClass::CACHE_ID);
			}
		}

		$cacheManager->clean(Manager::CACHE_ID);

		return $deleteResult;
	}

	/**
	 * @return bool
	 */
	public static function isSupportedFFD105()
	{
		Cashbox::init();

		$cashboxList = static::getListFromCache();
		foreach ($cashboxList as $cashbox)
		{
			if ($cashbox['ACTIVE'] === 'N')
				continue;
			/** @var Cashbox $handler */
			$handler = $cashbox['HANDLER'];
			$isRestHandler = $handler === '\Bitrix\Sale\Cashbox\CashboxRest';
			if ($isRestHandler)
			{
				$handlerCode = $cashbox['SETTINGS']['REST']['REST_CODE'];
				$restHandlers = self::getRestHandlersList();
				$currentHandler = $restHandlers[$handlerCode];
				if ($currentHandler['SETTINGS']['SUPPORTS_FFD105'] !== 'Y')
				{
					return false;
				}
			}
			elseif (
				!is_callable(array($handler, 'isSupportedFFD105')) ||
				!$handler::isSupportedFFD105()
			)
			{
				return false;
			}
		}

		return true;
	}

	public static function isEnabledPaySystemPrint(): bool
	{
		Cashbox::init();

		$cashboxList = self::getListFromCache();
		foreach ($cashboxList as $cashbox)
		{
			if ($cashbox['ACTIVE'] === 'N')
			{
				continue;
			}

			if (self::isPaySystemCashbox($cashbox['HANDLER']))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param Sale\Payment $payment
	 * @return bool
	 */
	public static function canPaySystemPrint(Sale\Payment $payment): bool
	{
		$service = $payment->getPaySystem();

		return $service
			&& $service->isSupportPrintCheck()
			&& $service->canPrintCheck()
			&& $service->canPrintCheckSelf($payment)
		;
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 */
	public static function updateChecksStatus()
	{
		$cashboxList = static::getListFromCache();
		if (!$cashboxList)
		{
			return '';
		}

		$availableCashboxList = [];
		foreach ($cashboxList as $item)
		{
			$cashbox = Cashbox::create($item);
			if (
				$cashbox instanceof ICheckable
				|| $cashbox->isCorrection()
			)
			{
				$availableCashboxList[$item['ID']] = $cashbox;
			}
		}

		if (!$availableCashboxList)
		{
			return '';
		}

		$parameters = [
			'filter' => [
				'=STATUS' => 'P',
				'@CASHBOX_ID' => array_keys($availableCashboxList),
				'=CASHBOX.ACTIVE' => 'Y'
			],
			'limit' => 5
		];
		$dbRes = CheckManager::getList($parameters);
		while ($checkInfo = $dbRes->fetch())
		{
			/** @var Cashbox|ICheckable|ICorrection $cashbox */
			$cashbox = $availableCashboxList[$checkInfo['CASHBOX_ID']];
			if ($cashbox)
			{
				$check = CheckManager::getObjectById($checkInfo['ID']);

				if ($check instanceof CorrectionCheck)
				{
					$result = $cashbox->checkCorrection($check);
				}
				elseif ($check instanceof Check)
				{
					$result = $cashbox->check($check);
				}
				else
				{
					continue;
				}

				if (!$result->isSuccess())
				{
					foreach ($result->getErrors() as $error)
					{
						if ($error instanceof Errors\Warning)
						{
							Logger::addWarning($error->getMessage(), $cashbox->getField('ID'));
						}
						else
						{
							Logger::addError($error->getMessage(), $cashbox->getField('ID'));
						}
					}
				}
			}
		}

		return static::CHECK_STATUS_AGENT;
	}

	/**
	 * @param $cashboxId
	 * @param Main\Error $error
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 *
	 * @deprecated Use \Bitrix\Sale\Cashbox\Logger instead
	 */
	public static function writeToLog($cashboxId, Main\Error $error)
	{
		if ($error instanceof Errors\Warning)
		{
			Logger::addWarning($error->getMessage(), $cashboxId);
		}
		else
		{
			Logger::addError($error->getMessage(), $cashboxId);
		}
	}

	/**
	 * @param $cashboxId
	 * @return bool
	 */
	public static function isCashboxChecksExist($cashboxId): bool
	{
		$params = [
			'filter' => [
				'STATUS' => 'Y',
				'CASHBOX_ID' => $cashboxId,
			],
			'limit' => 1,
		];
		$result = CheckManager::getList($params);

		return (bool)$result->fetch();
	}

	/**
	 * @param $cashboxId
	 * @return Main\ORM\Data\UpdateResult
	 */
	public static function deactivateCashbox($cashboxId): Main\ORM\Data\UpdateResult
	{
		return self::update($cashboxId, ['ACTIVE' => 'N']);
	}

	/**
	 * @param string $cashboxClassName
	 * @return bool
	 */
	public static function isPaySystemCashbox(string $cashboxClassName): bool
	{
		return is_subclass_of($cashboxClassName, CashboxPaySystem::class);
	}
}
