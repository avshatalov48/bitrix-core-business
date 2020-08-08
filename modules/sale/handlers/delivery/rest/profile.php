<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main,
	Bitrix\Main\ArgumentNullException,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Web\HttpClient,
	Bitrix\Main\Web\Json,
	Bitrix\Sale,
	Bitrix\Sale\Delivery\CalculationResult,
	Bitrix\Sale\Delivery\Services\Base,
	Bitrix\Sale\Delivery\Services\Manager;

Loc::loadMessages(__FILE__);

class RestProfile extends Base
{
	/** @var RestHandler Parent service. */
	protected $restHandler = null;
	/** @var string Service type */
	protected $profileType = '';

	protected static $whetherAdminExtraServicesShow = true;

	/** @var bool This handler is profile */
	protected static $isProfile = true;

	/**
	 * @param array $initParams
	 * @throws ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function __construct(array $initParams)
	{
		if (empty($initParams['PARENT_ID']))
		{
			throw new ArgumentNullException('initParams[PARENT_ID]');
		}

		parent::__construct($initParams);
		$this->restHandler = Manager::getObjectById($this->parentId);

		if (!($this->restHandler instanceof RestHandler))
		{
			throw new ArgumentNullException('this->restHandler is not instance of RestHandler');
		}

		if (!empty($initParams['PROFILE_ID']))
		{
			$this->profileType = $initParams['PROFILE_ID'];
		}
		elseif (!empty($this->config['MAIN']['PROFILE_TYPE']))
		{
			$this->profileType = $this->config['MAIN']['PROFILE_TYPE'];
		}

		if ($this->profileType)
		{
			$profileParams = $this->getProfileParams();
			if (!empty($profileParams) && $this->id <= 0)
			{
				$this->name = $profileParams['NAME'];
				$this->description = $profileParams['DESCRIPTION'];
			}
		}
	}

	/**
	 * @return string
	 */
	public static function getClassTitle(): string
	{
		return Loc::getMessage('SALE_DELIVERY_REST_PROFILE_NAME');
	}

	/**
	 * @return string
	 */
	public static function getClassDescription(): string
	{
		return Loc::getMessage('SALE_DELIVERY_REST_PROFILE_DESCRIPTION');
	}

	/**
	 * @param Sale\Shipment $shipment
	 * @return CalculationResult
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function calculateConcrete(Sale\Shipment $shipment): CalculationResult
	{
		$result = new CalculationResult;

		$handlerParams = $this->getHandlerParams();
		$calculateUrl = $handlerParams['SETTINGS']['CALCULATE_URL'];
		$requestParams = $this->getRequestParams($shipment);

		$sendRequestResult = $this->sendRequest($calculateUrl, $requestParams);
		if ($sendRequestResult->isSuccess())
		{
			$calculatedData = $sendRequestResult->getData();

			if (!empty($calculatedData['PRICE']))
			{
				$result->setDeliveryPrice($calculatedData['PRICE']);
			}

			if (!empty($calculatedData['PERIOD_DESCRIPTION']))
			{
				$result->setPeriodDescription($calculatedData['PERIOD_DESCRIPTION']);
			}

			if (!empty($calculatedData['PERIOD_FROM']))
			{
				$result->setPeriodFrom($calculatedData['PERIOD_FROM']);
			}

			if (!empty($calculatedData['PERIOD_TO']))
			{
				$result->setPeriodTo($calculatedData['PERIOD_TO']);
			}

			if (!empty($calculatedData['PERIOD_TYPE']))
			{
				$result->setPeriodType($calculatedData['PERIOD_TYPE']);
			}

			if (!empty($calculatedData['DESCRIPTION']))
			{
				$result->setDescription($calculatedData['DESCRIPTION']);
			}
		}

		return $result;
	}

	/**
	 * @param Sale\Shipment $shipment
	 * @return array
	 * @throws ArgumentNullException
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getRequestParams(Sale\Shipment $shipment): array
	{
		$basketItems = [];
		foreach ($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			if ($basketItem = $shipmentItem->getBasketItem())
			{
				$dimension = $basketItem->getField('DIMENSIONS');
				if($dimension && is_string($dimension) && \CheckSerializedData($dimension))
				{
					$dimension = unserialize($dimension, ['allowed_classes' => false]);
				}

				$basketItems[] = [
					'PRICE' => $basketItem->getPrice(),
					'WEIGHT' => $basketItem->getWeight(),
					'CURRENCY' => $basketItem->getCurrency(),
					'QUANTITY' => $shipmentItem->getQuantity(),
					'DIMENSIONS' => $dimension,
				];
			}
		}

		$extraServiceManager = new Sale\Delivery\ExtraServices\Manager($shipment->getDeliveryId());
		$extraServiceManager->setOperationCurrency($shipment->getField('CURRENCY'));
		$extraServiceManager->setValues($shipment->getExtraServices());
		$extraServiceList = [];
		foreach ($extraServiceManager->getItems() as $extraService)
		{
			$extraServiceList[] = [
				'CODE' => $extraService->getCode(),
				'NAME' => $extraService->getName(),
				'VALUE' => $extraService->getValue(),
				'PRICE' => $extraService->getPriceShipment(),
			];
		}

		$orderProps = [];
		if ($order = $shipment->getOrder())
		{
			$propertyCollection = $order->getPropertyCollection();
			if (($address = $propertyCollection->getAddress())
				&& $address->getValue()
			)
			{
				$orderProps['ADDRESS'] = $address->getValue();
			}

			if (($deliveryLocation = $propertyCollection->getDeliveryLocation())
				&& $deliveryLocation->getValue()
			)
			{
				$res = Sale\Location\LocationTable::getList(array(
					'filter' => array(
						'=CODE' => $deliveryLocation->getValue(),
						'=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
						'=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
					),
					'select' => array(
						'LOCATION' => 'PARENTS.NAME.NAME',
						'TYPE_CODE' => 'PARENTS.TYPE.CODE',
						'TYPE_NAME' => 'PARENTS.TYPE.NAME.NAME'
					),
					'order' => array(
						'PARENTS.DEPTH_LEVEL' => 'asc'
					)
				));
				while($item = $res->fetch())
				{
					$orderProps['DELIVERY_LOCATION'][] = $item;
				}
			}

			if (($deliveryLocationZip = $propertyCollection->getDeliveryLocationZip())
				&& $deliveryLocationZip->getValue()
			)
			{
				$orderProps['DELIVERY_LOCATION_ZIP'] = $deliveryLocationZip->getValue();
			}
		}

		$deliverConfig = [];
		$delivery = Sale\Delivery\Services\Manager::getObjectById($shipment->getDeliveryId());
		if ($delivery)
		{
			$deliverConfig['PROFILE_CONFIG'] = $delivery->getConfigValues();
			$parentDelivery = $delivery->getParentService();
			if ($parentDelivery)
			{
				$deliverConfig['PARENT_CONFIG'] = $parentDelivery->getConfigValues();
			}
		}

		return [
			'PRICE' => $shipment->getShipmentItemCollection()->getPrice(),
			'CURRENCY' => $shipment->getCurrency(),
			'WEIGHT' => $shipment->getWeight(),
			'BASKET_ITEMS' => $basketItems,
			'EXTRA_SERVICES' => $extraServiceList,
			'ORDER_PROPS' => $orderProps,
			'DELIVERY_CONFIG' => $deliverConfig,
		];
	}

	/**
	 * @param string $url
	 * @param array $params
	 * @return Sale\Result
	 */
	private function sendRequest(string $url, array $params): Sale\Result
	{
		$result = new Sale\Result();
		$httpClient = new HttpClient();

		$response = $httpClient->post($url, $params);
		if ($response === false)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Main\Error($message, $code));
			}

			return $result;
		}

		$httpStatus = $httpClient->getStatus();
		if ($httpStatus === 200)
		{
			try
			{
				$response = Json::decode($response);
				$response = array_change_key_case($response, CASE_UPPER);
				$response = Main\Text\Encoding::convertEncoding($response, 'UTF-8', LANG_CHARSET);
			}
			catch (Main\ArgumentException $exception)
			{
				$response = [];
				$result->addError(
					new Main\Error('Response decoding error', 'RESPONSE_DECODING_ERROR')
				);
			}

			$result->setData($response);
		}

		return $result;
	}

	/**
	 * @return mixed|string
	 */
	private function getProfileType()
	{
		return $this->profileType;
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getProfileParams()
	{
		$handlerParams = $this->getHandlerParams();
		$type = $this->getProfileType();

		return $handlerParams['PROFILES'][$type];
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getHandlerParams()
	{
		$handlerList = Manager::getRestHandlerList();
		$code = $this->restHandler->getHandlerCode();

		return $handlerList[$code];
	}

	/**
	 * @return bool
	 */
	public function isCalculatePriceImmediately(): bool
	{
		return $this->restHandler->isCalculatePriceImmediately();
	}

	/**
	 * @return array Handler's configuration
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getConfigStructure(): array
	{
		$result = [];
		$configParams = $this->getProfileParams();

		if (!empty($configParams['CONFIG']))
		{
			$result['MAIN'] = [
				'TITLE' => $configParams['CONFIG']['TITLE'],
				'DESCRIPTION' => $configParams['CONFIG']['DESCRIPTION'],
				'ITEMS' => $configParams['CONFIG']['ITEMS'],
			];
		}
		else
		{
			$result['MAIN'] = [
				'TITLE' => Loc::getMessage('SALE_DELIVERY_REST_PROFILE_SETTING_TITLE'),
				'DESCRIPTION' => Loc::getMessage('SALE_DELIVERY_REST_PROFILE_SETTING_DESCRIPTION'),
			];
		}

		$result['MAIN']['ITEMS']['PROFILE_TYPE'] = [
			'TYPE' => 'STRING',
			'NAME' => 'PROFILE_TYPE',
			'HIDDEN' => true,
			'DEFAULT' => $this->getProfileType(),
		];

		return $result;
	}

	public function getParentService()
	{
		return $this->restHandler;
	}

	/**
	 * @return bool
	 */
	public static function isProfile(): bool
	{
		return self::$isProfile;
	}

	/**
	 * @return bool
	 */
	public static function whetherAdminExtraServicesShow(): bool
	{
		return self::$whetherAdminExtraServicesShow;
	}
}
