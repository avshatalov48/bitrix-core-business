<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table;
use Sale\Handlers\Delivery\YandexTaxi\Api\Tariffs\Repository;
use Sale\Handlers\Delivery\YandexTaxi\Common\TariffNameBuilder;
use Sale\Handlers\Delivery\YandexTaxi\EventJournal\JournalProcessor;
use Sale\Handlers\Delivery\YandexTaxi\Installer\Installer;
use Sale\Handlers\Delivery\YandexTaxi\ServiceContainer;

Loader::registerAutoLoadClasses(
	'sale',
	[
		__NAMESPACE__.'\YandextaxiProfile' => 'handlers/delivery/yandextaxi/profile.php',
	]
);

Loc::loadMessages(__FILE__);

/**
 * Class YandextaxiHandler
 * @package Sale\Handlers\Delivery\YandexTaxi
 */
final class YandextaxiHandler extends Base
{
	/** @var string */
	protected $handlerCode = 'BITRIX_YANDEX_TAXI';

	// @TODO get rid of the constant
	public const SERVICE_CODE = 'YANDEX_TAXI';

	/** @var bool */
	protected static $canHasProfiles = true;

	/** @var JournalProcessor */
	private $journalProcessor;

	/** @var Installer */
	private $installer;

	/** @var Repository */
	private $tariffsRepository;

	/** @var TariffNameBuilder */
	private $tariffNameBuilder;

	/**
	 * @inheritdoc
	 */
	public function __construct(array $initParams)
	{
		parent::__construct($initParams);

		if (isset($initParams['CONFIG']['MAIN']['OAUTH_TOKEN']))
		{
			ServiceContainer::getOauthTokenProvider()->setToken($initParams['CONFIG']['MAIN']['OAUTH_TOKEN']);
		}

		$this->journalProcessor = ServiceContainer::getJournalProcessor();
		$this->installer = ServiceContainer::getInstaller();
		$this->tariffsRepository = ServiceContainer::getTariffsRepository();
		$this->tariffNameBuilder = ServiceContainer::getTariffNameBuilder();
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
		/** @var YandextaxiHandler $instance */
		$instance = Manager::getObjectById($serviceId);
		if (!$instance)
		{
			return false;
		}

		return $instance->installer->install($serviceId)->isSuccess();
	}

	/**
	 * @param int $serviceId
	 * @param array $fields
	 * @return bool
	 */
	public static function onBeforeUpdate($serviceId, array &$fields = array())
	{
		$service = Table::getList([
			'filter' => ['=ID' =>  $serviceId]
		])->fetch();

		if (
			$service
			&& isset($fields['CONFIG']['MAIN']['OAUTH_TOKEN'])
			&& isset($service['CONFIG']['MAIN']['OAUTH_TOKEN'])
			&& $fields['CONFIG']['MAIN']['OAUTH_TOKEN'] !== $service['CONFIG']['MAIN']['OAUTH_TOKEN']
		)
		{
			/**
			 * Reset history cursor if oauth token has been changed
			 */
			$fields['CONFIG']['MAIN']['CURSOR'] = '';
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public static function onAfterDelete($serviceId)
	{
		/** @var YandextaxiHandler $instance */
		$instance = Manager::getObjectById($serviceId);
		if (!$instance)
		{
			return false;
		}

		\CAgent::RemoveAgent(
			$instance->journalProcessor->getAgentName($serviceId),
			'sale'
		);

		return true;
	}

	/**
	 * @return JournalProcessor
	 */
	public function getYandexTaxiJournalProcessor(): JournalProcessor
	{
		return $this->journalProcessor;
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
	public static function isHandlerCompatible()
	{
		return (
			in_array(ServiceContainer::getRegionFinder()->getCurrentRegion(), ['ru', 'kz', 'by'])
			&& ModuleManager::isModuleInstalled('crm') && Loader::includeModule('crm')
 		);
	}

	/**
	 * @inheritDoc
	 */
	public static function canHasProfiles()
	{
		return self::$canHasProfiles;
	}

	/**
	 * @inheritDoc
	 */
	public static function getChildrenClassNames(): array
	{
		return [
			'\Sale\Handlers\Delivery\YandextaxiProfile'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getProfilesList(): array
	{
		$result = [];

		$tariffs = $this->tariffsRepository->getTariffs();
		foreach ($tariffs as $tariff)
		{
			$result[$tariff['name']] = $this->tariffNameBuilder->getTariffName($tariff);
		}

		return $result;
	}
}
