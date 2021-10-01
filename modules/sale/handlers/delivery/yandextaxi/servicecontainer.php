<?php

namespace Sale\Handlers\Delivery\YandexTaxi;

use Bitrix\Main\Config\Option;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal\EventBuilder;
use Sale\Handlers\Delivery\YandexTaxi\Api\ClaimReader\ClaimReader;
use Sale\Handlers\Delivery\YandexTaxi\Api\Transport\Client;
use Sale\Handlers\Delivery\YandexTaxi\Api\Transport\OauthTokenProvider;
use Sale\Handlers\Delivery\YandexTaxi\Api\Tariffs\Repository;
use Sale\Handlers\Delivery\YandexTaxi\ClaimBuilder\ClaimBuilder;
use Sale\Handlers\Delivery\YandexTaxi\Common\Logger;
use Sale\Handlers\Delivery\YandexTaxi\Common\ReferralSourceBuilder;
use Sale\Handlers\Delivery\YandexTaxi\Common\RegionCoordinatesMapper;
use Sale\Handlers\Delivery\YandexTaxi\Common\RegionFinder;
use Sale\Handlers\Delivery\YandexTaxi\Common\TariffNameBuilder;
use Sale\Handlers\Delivery\YandexTaxi\EventJournal\JournalProcessor;
use Sale\Handlers\Delivery\YandexTaxi\EventJournal\EventProcessor;
use Sale\Handlers\Delivery\YandexTaxi\EventJournal\EventReader;
use Sale\Handlers\Delivery\YandexTaxi\Installer\Installer;

/**
 * Class ServiceLocator
 * @package Sale\Handlers\Delivery\YandexTaxi
 * @internal
 */
final class ServiceContainer
{
	/** @var Api */
	private static $api;

	/** @var OauthTokenProvider */
	private static $oauthTokenProvider;

	/** @var ClaimReader */
	private static $claimReader;

	/** @var EventBuilder */
	private static $eventJournalBuilder;

	/** @var Logger */
	private static $logger;

	/** @var Client */
	private static $transport;

	/** @var JournalProcessor */
	private static $journalProcessor;

	/** @var EventReader */
	private static $eventReader;

	/** @var EventProcessor */
	private static $eventProcessor;

	/** @var RateCalculator */
	private static $rateCalculator;

	/** @var TariffsChecker */
	private static $tariffsChecker;

	/** @var Installer */
	private static $installer;

	/** @var ClaimBuilder */
	private static $claimBuilder;

	/** @var RegionFinder */
	private static $regionFinder;

	/** @var RegionCoordinatesMapper */
	private static $regionCoordinatesMapper;

	/** @var Repository */
	private static $tariffsRepository;

	/** @var ReferralSourceBuilder */
	private static $referralSourceBuilder;

	/** @var TariffNameBuilder */
	private static $tariffNameBuilder;

	/**
	 * @return Logger
	 */
	private static function getLogger(): Logger
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
	private static function getClaimReader(): ClaimReader
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
	private static function getEventJournalBuilder(): EventBuilder
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
	private static function getTransport(): Client
	{
		if (is_null(static::$transport))
		{
			static::$transport = new Client(
				static::getOauthTokenProvider(),
				static::getLogger(),
				static::getReferralSourceBuilder()
			);

			if (
				(int)Option::get('sale', 'delivery_service_yandex_taxi_sandbox', 0) == 1
				|| (
					defined('BITRIX_SALE_HANDLERS_YANDEX_TAXI_TEST_ENVIRONMENT')
					&& BITRIX_SALE_HANDLERS_YANDEX_TAXI_TEST_ENVIRONMENT === true
				)
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
	 * @return JournalProcessor
	 */
	public static function getJournalProcessor(): JournalProcessor
	{
		if (is_null(static::$journalProcessor))
		{
			static::$journalProcessor = new JournalProcessor(
				static::getEventReader(),
				static::getEventProcessor()
			);
		}

		return static::$journalProcessor;
	}

	/**
	 * @return EventReader
	 */
	private static function getEventReader(): EventReader
	{
		if (is_null(static::$eventReader))
		{
			static::$eventReader = new EventReader(static::getApi());
		}

		return static::$eventReader;
	}

	/**
	 * @return EventProcessor
	 */
	private static function getEventProcessor(): EventProcessor
	{
		if (is_null(static::$eventProcessor))
		{
			static::$eventProcessor = new EventProcessor(static::getApi());
		}

		return static::$eventProcessor;
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
				static::getClaimBuilder(),
				static::getTariffsChecker()
			);
		}

		return static::$rateCalculator;
	}

	/**
	 * @return TariffsChecker
	 */
	public static function getTariffsChecker(): TariffsChecker
	{
		if (is_null(static::$tariffsChecker))
		{
			static::$tariffsChecker = new TariffsChecker(static::getApi());
		}

		return static::$tariffsChecker;
	}

	/**
	 * @return Installer
	 */
	public static function getInstaller(): Installer
	{
		if (is_null(static::$installer))
		{
			static::$installer = new Installer(
				static::getTariffsRepository(),
				static::getTariffNameBuilder()
			);
		}

		return static::$installer;
	}

	/**
	 * @return ClaimBuilder
	 */
	public static function getClaimBuilder(): ClaimBuilder
	{
		if (is_null(static::$claimBuilder))
		{
			static::$claimBuilder = new ClaimBuilder(
				static::getTariffsRepository(),
				static::getReferralSourceBuilder()
			);
		}

		return static::$claimBuilder;
	}

	/**
	 * @return RegionFinder
	 */
	public static function getRegionFinder(): RegionFinder
	{
		if (is_null(static::$regionFinder))
		{
			static::$regionFinder = new RegionFinder();
		}

		return static::$regionFinder;
	}

	/**
	 * @return RegionCoordinatesMapper
	 */
	public static function getRegionCoordinatesMapper(): RegionCoordinatesMapper
	{
		if (is_null(static::$regionCoordinatesMapper))
		{
			static::$regionCoordinatesMapper = new RegionCoordinatesMapper();
		}

		return static::$regionCoordinatesMapper;
	}

	/**
	 * @return Repository
	 */
	public static function getTariffsRepository(): Repository
	{
		if (is_null(static::$tariffsRepository))
		{
			static::$tariffsRepository = new Repository();
		}

		return static::$tariffsRepository;
	}

	/**
	 * @return ReferralSourceBuilder
	 */
	public static function getReferralSourceBuilder(): ReferralSourceBuilder
	{
		if (is_null(static::$referralSourceBuilder))
		{
			static::$referralSourceBuilder = new ReferralSourceBuilder(
				static::getRegionFinder()
			);
		}

		return static::$referralSourceBuilder;
	}

	/**
	 * @return TariffNameBuilder
	 */
	public static function getTariffNameBuilder(): TariffNameBuilder
	{
		if (is_null(static::$tariffNameBuilder))
		{
			static::$tariffNameBuilder = new TariffNameBuilder(
				static::getRegionFinder()
			);
		}

		return static::$tariffNameBuilder;
	}
}
