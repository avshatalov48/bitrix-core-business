<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Config\Option;
use Bitrix\SalesCenter\Driver;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Api;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\Journal\EventBuilder;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ClaimReader\ClaimReader;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Transport\Client;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Transport\OauthTokenProvider;
use Sale\Handlers\Delivery\Taxi\Yandex\Crm\BindingsMaker;
use Sale\Handlers\Delivery\Taxi\Yandex\EventJournal\Process;
use Sale\Handlers\Delivery\Taxi\Yandex\EventJournal\Processor;
use Sale\Handlers\Delivery\Taxi\Yandex\EventJournal\Reader;
use \Sale\Handlers\Delivery\Taxi\Yandex\Crm;
use \Sale\Handlers\Delivery\Taxi\Yandex\SiteManager;

/**
 * Class ServiceLocator
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class ServiceContainer
{
	/** @var Api */
	private static $api;

	/** @var OauthTokenProvider */
	private static $oauthTokenProvider;

	/** @var ClaimReader */
	private static $claimReader;

	/** @var EventBuilder */
	private static $eventJournalBuilder;

	/** @var EntityProvider */
	private static $entityProvider;

	/** @var Logger */
	private static $logger;

	/** @var Client */
	private static $transport;

	/** @var Process */
	private static $eventJournalReadProcess;

	/** @var Reader */
	private static $eventJournalReader;

	/** @var Processor */
	private static $eventJournalProcessor;

	/** @var RateCalculator */
	private static $rateCalculator;

	/** @var Installator */
	private static $installator;

	/** @var ClaimBuilder */
	private static $claimBuilder;

	/** @var ClaimCreator */
	private static $claimCreator;

	/** @var ClaimCanceler */
	private static $claimCanceler;

	/** @var ContextFactory */
	private static $contextFactory;

	/** @var Crm\Context */
	private static $crmContext;

	/** @var Crm\NewOrderListener */
	private static $crmNewOrderListener;

	/** @var Crm\ClaimCreatedListener */
	private static $crmClaimCreatedListener;

	/** @var Crm\ClaimCancelledListener */
	private static $crmClaimCancelledListener;

	/** @var Crm\ClaimUpdatesListener */
	private static $crmClaimUpdatesListener;

	/** @var Crm\NeedContactToListener */
	private static $crmNeedContactToListener;

	/** @var Crm\ActivityManager */
	private static $crmActivityManager;

	/** @var StatusMapper */
	private static $statusMapper;

	/** @var SiteManager\Context */
	private static $siteManagerContext;

	/** @var ShipmentDataExtractor */
	private static $shipmentDataExtractor;

	/** @var RegionalPolicy */
	private static $regionalPolicy;

	/** @var BindingsMaker */
	private static $crmBindingsMaker;

	/**
	 * @return EntityProvider
	 */
	public static function getEntityProvider(): EntityProvider
	{
		if (is_null(static::$entityProvider))
		{
			static::$entityProvider = new EntityProvider();
		}

		return static::$entityProvider;
	}

	/**
	 * @return Logger
	 */
	public static function getLogger(): Logger
	{
		if (is_null(static::$logger))
		{
			static::$logger = new Logger();
		}

		return static::$logger;
	}

	/**
	 * @return ClaimReader
	 */
	public static function getClaimReader(): ClaimReader
	{
		if (is_null(static::$claimReader))
		{
			static::$claimReader = new ClaimReader();
		}

		return static::$claimReader;
	}

	/**
	 * @return EventBuilder
	 */
	public static function getEventJournalBuilder(): EventBuilder
	{
		if (is_null(static::$eventJournalBuilder))
		{
			static::$eventJournalBuilder = new EventBuilder();
		}

		return static::$eventJournalBuilder;
	}

	/**
	 * @return Client
	 */
	public static function getTransport(): Client
	{
		if (is_null(static::$transport))
		{
			static::$transport = new Client(static::getOauthTokenProvider());

			if (
				(int)Option::get('sale', 'delivery_service_yandex_taxi_sandbox', 0) == 1
				|| defined('BITRIX_SALE_HANDLERS_YANDEX_TAXI_TEST_ENVIRONMENT') && BITRIX_SALE_HANDLERS_YANDEX_TAXI_TEST_ENVIRONMENT === true
			)
			{
				static::$transport->setIsTestEnvironment(true);
			}
		}

		return static::$transport;
	}

	/**
	 * @return OauthTokenProvider
	 */
	public static function getOauthTokenProvider(): OauthTokenProvider
	{
		if (is_null(static::$oauthTokenProvider))
		{
			static::$oauthTokenProvider = new OauthTokenProvider();
		}

		return static::$oauthTokenProvider;
	}

	/**
	 * @return Api
	 */
	public static function getApi(): Api
	{
		if (is_null(static::$api))
		{
			static::$api = new Api(
				static::getTransport(),
				static::getClaimReader(),
				static::getEventJournalBuilder(),
				static::getLogger()
			);
		}

		return static::$api;
	}

	/**
	 * @return Process
	 */
	public static function getEventJournalReadProcess(): Process
	{
		if (is_null(static::$eventJournalReadProcess))
		{
			static::$eventJournalReadProcess = new Process(
				static::getEventJournalReader(),
				static::getEventJournalProcessor()
			);
		}

		return static::$eventJournalReadProcess;
	}

	/**
	 * @return Reader
	 */
	public static function getEventJournalReader(): Reader
	{
		if (is_null(static::$eventJournalReader))
		{
			static::$eventJournalReader = new Reader(static::getApi());
		}

		return static::$eventJournalReader;
	}

	/**
	 * @return Processor
	 */
	public static function getEventJournalProcessor(): Processor
	{
		if (is_null(static::$eventJournalProcessor))
		{
			static::$eventJournalProcessor = new Processor(static::getApi());
		}

		return static::$eventJournalProcessor;
	}

	/**
	 * @return RateCalculator
	 */
	public static function getRateCalculator(): RateCalculator
	{
		if (is_null(static::$rateCalculator))
		{
			static::$rateCalculator = new RateCalculator(
				static::getApi(),
				static::getClaimBuilder()
			);
		}

		return static::$rateCalculator;
	}

	/**
	 * @return Installator
	 */
	public static function getInstallator(): Installator
	{
		if (is_null(static::$installator))
		{
			static::$installator = new Installator(static::getEntityProvider());
		}

		return static::$installator;
	}

	/**
	 * @return ClaimBuilder
	 */
	public static function getClaimBuilder(): ClaimBuilder
	{
		if (is_null(static::$claimBuilder))
		{
			static::$claimBuilder = new ClaimBuilder(
				static::getEntityProvider(),
				static::getShipmentDataExtractor()
			);
		}

		return static::$claimBuilder;
	}

	/**
	 * @return ClaimCreator
	 */
	public static function getClaimCreator(): ClaimCreator
	{
		if (is_null(static::$claimCreator))
		{
			static::$claimCreator = new ClaimCreator(
				static::getApi(),
				static::getClaimBuilder(),
				static::getStatusMapper()
			);
		}

		return static::$claimCreator;
	}

	/**
	 * @return ClaimCanceler
	 */
	public static function getClaimCanceler(): ClaimCanceler
	{
		if (is_null(static::$claimCanceler))
		{
			static::$claimCanceler = new ClaimCanceler(
				static::getApi()
			);
		}

		return static::$claimCanceler;
	}

	/**
	 * @return ContextFactory
	 */
	public static function getContextFactory(): ContextFactory
	{
		if (is_null(static::$contextFactory))
		{
			static::$contextFactory = new ContextFactory(
				static::getCrmContext(),
				static::getSiteManagerContext()
			);
		}

		return static::$contextFactory;
	}

	/**
	 * @return ContextContract
	 */
	public static function getCrmContext(): ContextContract
	{
		if (is_null(static::$crmContext))
		{
			static::$crmContext = new Crm\Context(
				static::getCrmNewOrderListener(),
				static::getCrmClaimCreatedListener(),
				static::getCrmClaimCancelledListener(),
				static::getCrmClaimUpdatesListener(),
				static::getCrmNeedContactToListener()
			);
		}

		return static::$crmContext;
	}

	/**
	 * @return Crm\NewOrderListener
	 */
	public static function getCrmNewOrderListener(): Crm\NewOrderListener
	{
		if (is_null(static::$crmNewOrderListener))
		{
			static::$crmNewOrderListener = new Crm\NewOrderListener(
				static::getCrmClaimActivityManager(),
				static::getShipmentDataExtractor(),
				static::getCrmBindingsMaker()
			);
		}

		return static::$crmNewOrderListener;
	}

	/**
	 * @return Crm\ClaimCreatedListener
	 */
	public static function getCrmClaimCreatedListener(): Crm\ClaimCreatedListener
	{
		if (is_null(static::$crmClaimCreatedListener))
		{
			static::$crmClaimCreatedListener = new Crm\ClaimCreatedListener(
				static::getCrmClaimActivityManager(),
				static::getShipmentDataExtractor(),
				static::getRateCalculator(),
				static::getCrmBindingsMaker()
			);
		}

		return static::$crmClaimCreatedListener;
	}

	/**
	 * @return Crm\ClaimCancelledListener
	 */
	public static function getCrmClaimCancelledListener(): Crm\ClaimCancelledListener
	{
		if (is_null(static::$crmClaimCancelledListener))
		{
			static::$crmClaimCancelledListener = new Crm\ClaimCancelledListener(
				static::getCrmClaimActivityManager(),
				static::getShipmentDataExtractor(),
				static::getCrmBindingsMaker()
			);
		}

		return static::$crmClaimCancelledListener;
	}

	/**
	 * @return Crm\ClaimUpdatesListener
	 */
	public static function getCrmClaimUpdatesListener(): Crm\ClaimUpdatesListener
	{
		if (is_null(static::$crmClaimUpdatesListener))
		{
			static::$crmClaimUpdatesListener = new Crm\ClaimUpdatesListener(
				static::getCrmClaimActivityManager(),
				static::getStatusMapper(),
				static::getApi(),
				static::getShipmentDataExtractor(),
				static::getCrmBindingsMaker()
			);
		}

		return static::$crmClaimUpdatesListener;
	}

	/**
	 * @return Crm\NeedContactToListener
	 */
	public static function getCrmNeedContactToListener(): Crm\NeedContactToListener
	{
		if (is_null(static::$crmNeedContactToListener))
		{
			static::$crmNeedContactToListener = new Crm\NeedContactToListener();
		}

		return static::$crmNeedContactToListener;
	}

	/**
	 * @return Crm\ActivityManager
	 */
	public static function getCrmClaimActivityManager(): Crm\ActivityManager
	{
		if (is_null(static::$crmActivityManager))
		{
			static::$crmActivityManager = new Crm\ActivityManager(
				static::getCrmBindingsMaker()
			);
		}

		return static::$crmActivityManager;
	}

	/**
	 * @return StatusMapper
	 */
	public static function getStatusMapper(): StatusMapper
	{
		if (is_null(static::$statusMapper))
		{
			static::$statusMapper = new StatusMapper();
		}

		return static::$statusMapper;
	}

	/**
	 * @return ContextContract
	 */
	public static function getSiteManagerContext(): ContextContract
	{
		if (is_null(static::$siteManagerContext))
		{
			static::$siteManagerContext = new SiteManager\Context();
		}

		return static::$siteManagerContext;
	}

	/**
	 * @return ShipmentDataExtractor
	 */
	public static function getShipmentDataExtractor(): ShipmentDataExtractor
	{
		if (is_null(static::$shipmentDataExtractor))
		{
			static::$shipmentDataExtractor = new ShipmentDataExtractor(
				static::getEntityProvider()
			);
		}

		return static::$shipmentDataExtractor;
	}

	/**
	 * @return RegionalPolicy
	 */
	public static function getRegionalPolicy(): RegionalPolicy
	{
		if (is_null(static::$regionalPolicy))
		{
			static::$regionalPolicy = new RegionalPolicy();
		}

		return static::$regionalPolicy;
	}

	/**
	 * @return BindingsMaker
	 */
	public static function getCrmBindingsMaker(): BindingsMaker
	{
		if (is_null(static::$crmBindingsMaker))
		{
			static::$crmBindingsMaker = new BindingsMaker();
		}

		return static::$crmBindingsMaker;
	}
}
