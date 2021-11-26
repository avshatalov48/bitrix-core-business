<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\YandexTaxi\Common\OrderEntitiesCodeDictionary;
use Sale\Handlers\Delivery\YandexTaxi\RateCalculator;
use Sale\Handlers\Delivery\YandexTaxi\ServiceContainer;
use Sale\Handlers\Delivery\YandexTaxi\TariffsChecker;
use Sale\Handlers\Delivery\YandexTaxi\RequestHandler;

/**
 * Class YandextaxiProfile
 * @package Sale\Handlers\Delivery
 */
final class YandextaxiProfile extends Base
{
	private const PROFILE_COURIER = 'courier';
	private const PROFILE_EXPRESS = 'express';
	private const PROFILE_CARGO = 'cargo';

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

	/**
	 * @inheritDoc
	 */
	public function getDeliveryRequestHandler()
	{
		return new RequestHandler($this);
	}

	/**
	 * @inheritDoc
	 */
	public function getTags(): array
	{
		return $this->profileType === self::PROFILE_COURIER
			? [static::TAG_PROFITABLE]
			: [];
	}

	/**
	 * @inheritDoc
	 */
	protected function getProfileType(): string
	{
		return (string)$this->profileType;
	}
}
