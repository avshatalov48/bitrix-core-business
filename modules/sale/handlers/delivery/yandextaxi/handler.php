<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Crm\Timeline\DeliveryCategoryType;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Delivery\Services\Crm\Activity;
use Bitrix\Sale\Delivery\Services\Crm\EstimationMessage;
use Bitrix\Sale\Delivery\Services\Crm\ICrmActivityProvider;
use Bitrix\Sale\Delivery\Services\Crm\ICrmEstimationMessageProvider;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Taxi\CancellationRequestResult;
use Bitrix\Sale\Delivery\Services\Taxi\CreationExternalRequestResult;
use Bitrix\Sale\Delivery\Services\Taxi\StatusDictionary;
use Bitrix\Sale\Delivery\Services\Taxi\Taxi;
use Bitrix\Sale\Shipment;
use Bitrix\Voximplant\Security\Helper;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\YandexTaxi\Api\Tariffs\Repository;
use Sale\Handlers\Delivery\YandexTaxi\ClaimBuilder\ClaimBuilder;
use Sale\Handlers\Delivery\YandexTaxi\Common\ShipmentDataExtractor;
use Sale\Handlers\Delivery\YandexTaxi\Common\StatusMapper;
use Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm;
use Sale\Handlers\Delivery\YandexTaxi\EventJournal\JournalProcessor;
use Sale\Handlers\Delivery\YandexTaxi\Installator\Installator;
use Sale\Handlers\Delivery\YandexTaxi\Internals\ClaimsTable;
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
final class YandextaxiHandler extends Taxi implements ICrmActivityProvider, ICrmEstimationMessageProvider
{
	public const SERVICE_CODE = 'YANDEX_TAXI';

	/** @var bool */
	protected static $canHasProfiles = true;

	/** @var Api */
	private $api;

	/** @var ClaimBuilder */
	private $claimBuilder;

	/** @var StatusMapper */
	private $statusMapper;

	/** @var JournalProcessor */
	private $journalProcessor;

	/** @var Installator */
	private $installator;

	/** @var ShipmentDataExtractor */
	private $extractor;

	/** @var Crm\BindingsMaker */
	private $crmBindingsMaker;

	/** @var Repository */
	private $tariffsRepository;

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

		ServiceContainer::getListenerRegisterer()
			->registerOnClaimCreated($this)
			->registerOnClaimCancelled($this)
			->registerOnClaimUpdated($this)
			->registerOnNeedContactTo($this);

		$this->api = ServiceContainer::getApi();
		$this->claimBuilder = ServiceContainer::getClaimBuilder();
		$this->statusMapper = ServiceContainer::getStatusMapper();
		$this->journalProcessor = ServiceContainer::getJournalProcessor();
		$this->installator = ServiceContainer::getInstallator();
		$this->extractor = ServiceContainer::getShipmentDataExtractor();
		$this->crmBindingsMaker = ServiceContainer::getCrmBindingsMaker();
		$this->tariffsRepository = ServiceContainer::getTariffsRepository();
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
	 * @inheritDoc
	 */
	protected function createTaxiExternalRequest(Shipment $shipment): CreationExternalRequestResult
	{
		$result = new CreationExternalRequestResult();

		$claimBuildingResult = $this->claimBuilder->build($shipment);

		if (!$claimBuildingResult->isSuccess())
		{
			return $result->addErrors($claimBuildingResult->getErrors());
		}

		/** @var Claim $claim */
		$claim = $claimBuildingResult->getData()['RESULT'];

		$claimCreationResult = $this->api->createClaim($claim);

		if (!$claimCreationResult->isSuccess())
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_CREATE_ERROR')));
		}

		$createdClaim = $claimCreationResult->getClaim();
		if (is_null($createdClaim))
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_PERSIST_ERROR')));
		}

		$addResult = ClaimsTable::add(
			[
				'SHIPMENT_ID' => $shipment->getId(),
				'CREATED_AT' => new DateTime(),
				'UPDATED_AT' => new DateTime(),
				'EXTERNAL_ID' => $createdClaim->getId(),
				'EXTERNAL_STATUS' => $createdClaim->getStatus(),
				'EXTERNAL_CREATED_TS' => $createdClaim->getCreatedTs(),
				'EXTERNAL_UPDATED_TS' => $createdClaim->getUpdatedTs(),
				'INITIAL_CLAIM' => Json::encode($createdClaim),
				'IS_SANDBOX_ORDER' => $this->api->getTransport()->isTestEnvironment() ? 'Y' : 'N',
			]
		);
		if (!$addResult->isSuccess())
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_PERSIST_ERROR')));
		}

		\CAgent::AddAgent(
			$this->journalProcessor->getAgentName($this->id),
			'sale',
			'N',
			30,
			'',
			'Y',
			'',
			100,
			false,
			false
		);

		return $result
			->setStatus($this->statusMapper->getMappedStatus($createdClaim->getStatus()))
			->setExternalRequestId($createdClaim->getId());
	}

	/**
	 * @inheritDoc
	 */
	protected function cancelTaxiExternalRequest(string $externalRequestId): CancellationRequestResult
	{
		$result = new CancellationRequestResult();

		$getClaimResult = $this->api->getClaim($externalRequestId);

		if (!$getClaimResult->isSuccess())
		{
			return $result->addErrors($getClaimResult->getErrors());
		}

		$claim = $getClaimResult->getClaim();
		if (is_null($claim))
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_TMP_ERROR'))
			);
		}

		$availableCancelState = $claim->getAvailableCancelState();
		if (!$availableCancelState)
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_TMP_ERROR'))
			);
		}

		$cancellationResult = $this->api->cancelClaim(
			$externalRequestId,
			$claim->getVersion(),
			$claim->getAvailableCancelState()
		);

		if (!$cancellationResult->isSuccess())
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_FATAL_ERROR'))
			);
		}

		return $result->setIsPaid(($availableCancelState == 'paid'));
	}

	/**
	 * @inheritDoc
	 */
	public function provideCrmActivity(\Bitrix\Crm\Order\Shipment $shipment): Activity
	{
		return (new Activity())
			->setStatus(\CCrmActivityStatus::Waiting)
			->setPriority(\CCrmActivityPriority::Medium)
			->setResponsibleId($this->extractor->getResponsibleUserId($shipment))
			->setAuthorId($this->extractor->getResponsibleUserId($shipment))
			->setSubject(Loc::getMessage('SALE_YANDEX_TAXI_ACTIVITY_NAME'))
			->setBindings($this->crmBindingsMaker->makeByShipment($shipment, 'OWNER'))
			->setFields(
				array_merge(
					[
						'STATUS' => StatusDictionary::INITIAL,
						'CAN_USE_TELEPHONY' => (
							Loader::includeModule('voximplant')
							&& Helper::canCurrentUserPerformCalls()
						),
					],
					$this->makeCrmEntitySharedFields($shipment)
				)
			);
	}

	/**
	 * @inheritDoc
	 */
	public function provideCrmEstimationMessage(\Bitrix\Crm\Order\Shipment $shipment): EstimationMessage
	{
		return (new EstimationMessage())
			->setTypeId(DeliveryCategoryType::TAXI_ESTIMATION_REQUEST)
			->setAuthorId($this->extractor->getResponsibleUserId($shipment))
			->setFields($this->makeCrmEntitySharedFields($shipment))
			->setBindings($this->crmBindingsMaker->makeByShipment($shipment));
	}

	/**
	 * @param \Bitrix\Crm\Order\Shipment $shipment
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function makeCrmEntitySharedFields(\Bitrix\Crm\Order\Shipment $shipment)
	{
		$result = [
			'SHIPMENT_ID' => $shipment->getId(),
			'DELIVERY_SYSTEM_NAME' => $this->extractor->getDeliverySystemName($shipment),
			'DELIVERY_SYSTEM_LOGO' => $this->extractor->getDeliverySystemLogo($shipment),
			'DELIVERY_METHOD' => $this->extractor->getDeliveryMethod($shipment),
			'ADDRESS_FROM' => $this->extractor->getShortenedAddressFrom($shipment),
			'ADDRESS_TO' => $this->extractor->getShortenedAddressTo($shipment),
			'DELIVERY_PRICE' => $this->extractor->getDeliveryPriceFormatted($shipment),
		];

		$calcPrice = $shipment->calculateDelivery();
		if($calcPrice->isSuccess())
		{
			$result['EXPECTED_PRICE_DELIVERY'] = SaleFormatCurrency(
				$calcPrice->getPrice(),
				$shipment->getOrder()->getCurrency()
			);
		}

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
		/** @var YandextaxiHandler $instance */
		$instance = Manager::getObjectById($serviceId);
		if (!$instance)
		{
			return false;
		}

		return $instance->installator->install($serviceId)->isSuccess();
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
		/**
		 * Region Usage Restriction
		 */
		$isAvailableInCurrentRegion = in_array(
			ServiceContainer::getRegionFinder()->getCurrentRegion(),
			['ru', 'kz', 'by']
		);

		/**
		 * Context Usage Restriction
		 */
		$isCrm = ServiceContainer::getListenerRegisterer() instanceof Crm\ListenerRegisterer;

		return ($isCrm && $isAvailableInCurrentRegion);
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

		$isByRegion = ServiceContainer::getRegionFinder()->getCurrentRegion() === 'by';
		$tariffs = $this->tariffsRepository->getTariffs();

		foreach ($tariffs as $tariff)
		{
			$code = 'SALE_YANDEX_TAXI_TARIFF_%s';
			if ($isByRegion && $tariff['name'] === 'courier')
			{
				$code .= '_BY';
			}

			$result[$tariff['name']] = Loc::getMessage(
				sprintf(
					$code,
					mb_strtoupper($tariff['name'])
				)
			);
		}

		return $result;
	}
}
