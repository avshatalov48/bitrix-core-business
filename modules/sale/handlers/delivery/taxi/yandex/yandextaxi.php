<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\NewOrderListenerContract;
use Bitrix\Sale\Internals\LocalDeliveryRequestTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\SendTaxiRequestResult;
use Sale\Handlers\Delivery\Taxi\TaxiDeliveryServiceContract;
use Sale\Handlers\Delivery\Taxi\Yandex\Crm;
use Sale\Handlers\Delivery\Taxi\Yandex\EventJournal\Process;

Loc::loadMessages(__FILE__);

/**
 * Class YandexTaxi
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class YandexTaxi extends Base implements TaxiDeliveryServiceContract, NewOrderListenerContract
{
	const SERVICE_CODE = 'YANDEX_TAXI';

	const NEW_ORDER_EVENT_CODE = 'OnNewOrder';
	const CLAIM_CREATED_EVENT_CODE = 'OnClaimCreated';
	const CLAIM_CANCELLED_EVENT_CODE = 'OnClaimCancelled';

	/** @var RateCalculator */
	private $rateCalculator;

	/** @var ClaimCreator */
	private $claimCreator;

	/** @var ClaimCanceler */
	private $claimCanceler;

	/** @var Process */
	private $eventJournalReadProcess;

	/** @var Installator */
	private $installator;

	/**
	 * @inheritdoc
	 */
	public function __construct(array $initParams)
	{
		parent::__construct($initParams);

		if (!Loader::includeModule('location'))
		{
			throw new SystemException('location module is not installed');
		}

		if (isset($initParams['CONFIG']['MAIN']['OAUTH_TOKEN']))
		{
			ServiceContainer::getOauthTokenProvider()->setToken($initParams['CONFIG']['MAIN']['OAUTH_TOKEN']);
		}

		ServiceContainer::getContextFactory()->makeContext()->setDeliveryService($this)->subscribeToEvents();

		$this->rateCalculator = ServiceContainer::getRateCalculator();
		$this->claimCreator = ServiceContainer::getClaimCreator();
		$this->claimCanceler = ServiceContainer::getClaimCanceler();
		$this->eventJournalReadProcess = ServiceContainer::getEventJournalReadProcess();
		$this->installator = ServiceContainer::getInstallator();
	}

	/**
	 * @inheritdoc
	 */
	protected function getConfigStructure()
	{
		return [
			'MAIN' => [
				'TITLE' => Loc::getMessage('SALE_YANDEX_TAXI_AUTH'),
				'DESCRIPTION' => Loc::getMessage('SALE_YANDEX_TAXI_AUTH'),
				'ITEMS' => [
					'OAUTH_TOKEN' => [
						'TYPE' => 'STRING',
						'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_AUTH_TOKEN'),
						"REQUIRED" => true,
					],
					'CURSOR' => [
						'TYPE' => 'STRING',
						'NAME' => 'History Journal Cursor',
						'REQUIRED' => false,
						'HIDDEN' => true,
					]
				]
			]
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_YANDEX_TAXI_TITLE');
	}

	/**
	 * @inheritdoc
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_YANDEX_TAXI_TITLE');
	}

	/**
	 * @inheritdoc
	 */
	protected function calculateConcrete(Shipment $shipment)
	{
		$result = new CalculationResult();

		$calculateRateResult = $this->rateCalculator->calculateRate($shipment);
		if (!$calculateRateResult->isSuccess())
		{
			return $result->addErrors(
				$this->getFormattedErrors($calculateRateResult->getErrors())
			);
		}

		$result->setDeliveryPrice($calculateRateResult->getRate());

		return $result;
	}

	/**
	 * @param array $errors
	 * @return Error[]
	 */
	private function getFormattedErrors(array $errors)
	{
		$result = [];

		foreach ($errors as $error)
		{
			$result[] = new Error($error->getMessage(), 'DELIVERY_CALCULATION');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public static function isHandlerCompatible()
	{
		return (
			ServiceContainer::getContextFactory()->makeContext() instanceof Crm\Context
			&& ServiceContainer::getRegionalPolicy()->isAvailableInCurrentRegion()
		);
	}

	/**
	 * @inheritdoc
	 */
	public function isCompatible(Shipment $shipment)
	{
		$isSoaContext = class_exists('\SaleOrderAjax');

		return !$isSoaContext;
	}

	/**
	 * @inheritdoc
	 */
	public static function whetherAdminExtraServicesShow()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function onNewOrder(Order $order)
	{
		(new \Bitrix\Main\Event(
			'sale',
			static::NEW_ORDER_EVENT_CODE,
			['ORDER' => $order]
		))->send();
	}

	/**
	 * @inheritdoc
	 */
	public function sendTaxiRequest(Shipment $shipment): SendTaxiRequestResult
	{
		$result = new SendTaxiRequestResult();

		$claimCreateResult = $this->claimCreator->createClaim($shipment);
		if (!$claimCreateResult->isSuccess())
		{
			return $result->addErrors($claimCreateResult->getErrors());
		}

		$persistResult = LocalDeliveryRequestTable::add(
			[
				'DELIVERY_SERVICE_ID' => $shipment->getDeliveryId(),
				'SHIPMENT_ID' => $shipment->getId(),
				'CREATED_AT' => new DateTime(),
				'EXTERNAL_ID' => $claimCreateResult->getRequestId(),
			]
		);
		if (!$persistResult->isSuccess())
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_PERSIST_ERROR')));
		}

		$result
			->setStatus($claimCreateResult->getStatus())
			->setRequestId($persistResult->getId());

		(new Event(
			'sale',
			static::CLAIM_CREATED_EVENT_CODE,
			[
				'SHIPMENT' => $shipment,
				'RESULT' => $result,
			]
		))->send();

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function cancelTaxiRequest(int $requestId): CancellationResult
	{
		$result = new CancellationResult();

		$request = LocalDeliveryRequestTable::getById($requestId)->fetch();
		if (!$request)
		{
			return $result->addError(new Error('Request has not been found'));
		}

		$result = $this->claimCanceler->cancelClaim($request['EXTERNAL_ID']);

		(new Event(
			'sale',
			static::CLAIM_CANCELLED_EVENT_CODE,
			[
				'REQUEST' => $request,
				'CANCELLATION_RESULT' => $result,
			]
		))->send();

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public static function onBeforeAdd(array &$fields = array()): Result
	{
		$result = new Result();

		if (!ModuleManager::isModuleInstalled('location'))
		{
			return $result->addError(
				new Error(
					Loc::getMessage('SALE_YANDEX_TAXI_LOCATION_MODULE_REQUIRED')
				)
			);
		}

		$fields['CODE'] = static::SERVICE_CODE;

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public static function onAfterAdd($serviceId, array $fields = [])
	{
		$instance = static::upInstance($serviceId);
		if (!$instance)
		{
			return false;
		}

		return $instance->installator->install($serviceId)->isSuccess();
	}

	/**
	 * @param int $serviceId
	 * @return string|null
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function readEventsJournal(int $serviceId)
	{
		$agent = static::getAgentName($serviceId);

		$instance = static::upInstance($serviceId);
		if (!$instance)
		{
			return $agent;
		}

		$configValues = $instance->getConfigValues();
		$prevCursor = isset($configValues['MAIN']['CURSOR']) && !empty($configValues['MAIN']['CURSOR'])
			? $configValues['MAIN']['CURSOR']
			: null;

		$hasMore = $instance->eventJournalReadProcess->run($serviceId, $prevCursor);

		if ($hasMore === false)
		{
			return null;
		}

		return $agent;
	}

	/**
	 * @inheritDoc
	 */
	public static function onAfterDelete($serviceId)
	{
		\CAgent::RemoveAgent(
			static::getAgentName((int)$serviceId),
			'sale'
		);

		return true;
	}

	/**
	 * @param int $serviceId
	 * @return string
	 */
	private static function getAgentName(int $serviceId): string
	{
		return '\\' . static::class . sprintf('::readEventsJournal(%s);', $serviceId);
	}

	/**
	 * @param int $serviceId
	 * @return YandexTaxi|null
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private static function upInstance(int $serviceId)
	{
		/** @var YandexTaxi|null $self */
		$self = Manager::getObjectById($serviceId);
		if (is_null($self))
		{
			return null;
		}

		return $self;
	}
}
