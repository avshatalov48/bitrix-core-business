<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services\Crm\ICrmActivityProvider;
use Bitrix\Sale\Delivery\Services\Crm\ICrmEstimationMessageProvider;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Taxi\CancellationRequestResult;
use Bitrix\Sale\Delivery\Services\Taxi\CreationExternalRequestResult;
use Bitrix\Sale\Delivery\Services\Taxi\Taxi;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Delivery\Services\Crm\Activity;
use Bitrix\Sale\Delivery\Services\Crm\EstimationMessage;
use Sale\Handlers\Delivery\YandexTaxi\Common\OrderEntitiesCodeDictionary;
use Sale\Handlers\Delivery\YandexTaxi\RateCalculator;
use Sale\Handlers\Delivery\YandexTaxi\ServiceContainer;
use Sale\Handlers\Delivery\YandexTaxi\TariffsChecker;

/**
 * Class YandextaxiProfile
 * @package Sale\Handlers\Delivery
 */
final class YandextaxiProfile extends Taxi implements ICrmActivityProvider, ICrmEstimationMessageProvider
{
	/** @var YandextaxiHandler */
	protected $yandextaxiHandler;

	/** @var string */
	protected $profileType;
	
	/** @var bool */
	protected static $whetherAdminExtraServicesShow = true;

	/** @var bool */
	protected static $isProfile = true;

	/** @var RateCalculator */
	private $rateCalculator;

	/** @var TariffsChecker */
	private $tariffsChecker;

	/**
	 * @inheritdoc
	 */
	public function __construct(array $initParams)
	{
		if (empty($initParams['PARENT_ID']))
		{
			throw new ArgumentNullException('initParams[PARENT_ID]');
		}

		parent::__construct($initParams);

		$this->yandextaxiHandler = Manager::getObjectById($this->parentId);
		if (!($this->yandextaxiHandler instanceof YandextaxiHandler))
		{
			throw new ArgumentNullException('this->yandextaxiHandler is not instance of YandextaxiHandler');
		}

		if (!empty($initParams['PROFILE_ID']))
		{
			$this->profileType = $initParams['PROFILE_ID'];
		}
		elseif (!empty($this->config['MAIN']['PROFILE_TYPE']))
		{
			$this->profileType = $this->config['MAIN']['PROFILE_TYPE'];
		}
		if (empty($this->profileType))
		{
			throw new ArgumentNullException('Profile type is not specified');
		}

		if ($this->id <= 0)
		{
			$this->name = $this->yandextaxiHandler->getProfilesList()[$this->profileType];
		}

		$this->rateCalculator = ServiceContainer::getRateCalculator();
		$this->tariffsChecker = ServiceContainer::getTariffsChecker();
	}

	/**
	 * @inheritdoc
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_YANDEX_TAXI_TARIFF');
	}

	/**
	 * @inheritdoc
	 */
	protected function getConfigStructure()
	{
		return [
			'MAIN' => [
				'TITLE' => Loc::getMessage('SALE_YANDEX_TAXI_TARIFF_SETTINGS'),
				'ITEMS' => [
					'PROFILE_TYPE' => [
						'TYPE' => 'STRING',
						'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_TARIFF_CODE'),
						'READONLY' => true,
						'DEFAULT' => $this->profileType,
					],
				]
			]
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function calculateConcrete(Shipment $shipment)
	{
		return $this->rateCalculator->calculateRate($shipment);
	}

	/**
	 * @inheritdoc
	 */
	protected function createTaxiExternalRequest(Shipment $shipment): CreationExternalRequestResult
	{
		return $this->yandextaxiHandler->createTaxiExternalRequest($shipment);
	}

	/**
	 * @inheritdoc
	 */
	protected function cancelTaxiExternalRequest(string $externalRequestId): CancellationRequestResult
	{
		return $this->yandextaxiHandler->cancelTaxiExternalRequest($externalRequestId);
	}

	/**
	 * @inheritdoc
	 */
	public function provideCrmActivity(\Bitrix\Crm\Order\Shipment $shipment): Activity
	{
		return $this->yandextaxiHandler->provideCrmActivity($shipment);
	}

	/**
	 * @inheritdoc
	 */
	public function provideCrmEstimationMessage(\Bitrix\Crm\Order\Shipment $shipment): EstimationMessage
	{
		return $this->yandextaxiHandler->provideCrmEstimationMessage($shipment);
	}

	/**
	 * @inheritdoc
	 */
	public function getParentService()
	{
		return $this->yandextaxiHandler;
	}

	/**
	 * @inheritDoc
	 */
	public static function whetherAdminExtraServicesShow()
	{
		return self::$whetherAdminExtraServicesShow;
	}

	/**
	 * @inheritDoc
	 */
	public static function isProfile()
	{
		return self::$isProfile;
	}

	/**
	 * @inheritDoc
	 */
	public function isCompatible(Shipment $shipment)
	{
		return (bool)$this->tariffsChecker->isTariffAvailableByShipment($this->profileType, $shipment);
	}

	/**
	 * @inheritDoc
	 */
	public function getCompatibleExtraServiceIds(Shipment $shipment): ?array
	{
		$supportedRequirements = $this->tariffsChecker->getSupportedRequirementsByTariff($this->profileType, $shipment);

		return array_column(
			array_filter(
				\Bitrix\Sale\Delivery\ExtraServices\Manager::getExtraServicesList($this->getId()),
				function ($extraService) use ($supportedRequirements)
				{
					return (
						$extraService['CODE'] === OrderEntitiesCodeDictionary::DOOR_DELIVERY_EXTRA_SERVICE_CODE
						|| in_array($extraService['CODE'], $supportedRequirements, true)
					);
				}
			),
			'ID'
		);
	}
}
