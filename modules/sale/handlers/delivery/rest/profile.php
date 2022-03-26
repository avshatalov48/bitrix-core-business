<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\Services\Manager;
use Sale\Handlers\Delivery\Rest\DataProviders;
use Sale\Handlers\Delivery\Rest\RequestHandler;

Loc::loadMessages(__FILE__);

/**
 * Class RestProfile
 * @package Sale\Handlers\Delivery
 */
class RestProfile extends Base
{
	private const HANDLER_CODE_PREFIX = 'BITRIX_REST_';

	/** @var RestHandler Parent service. */
	protected $restHandler;

	/** @var string Service type */
	protected $profileType = '';

	/** @var bool */
	protected static $whetherAdminExtraServicesShow = true;

	/** @var bool This handler is profile */
	protected static $isProfile = true;

	/**
	 * RestProfile constructor.
	 * @param array $initParams
	 * @throws ArgumentNullException
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
	 */
	protected function calculateConcrete(Sale\Shipment $shipment): CalculationResult
	{
		$result = new CalculationResult;

		$handlerParams = $this->getHandlerParams();
		if (
			!isset($handlerParams['SETTINGS']['CALCULATE_URL'])
			|| !is_string($handlerParams['SETTINGS']['CALCULATE_URL'])
			|| empty($handlerParams['SETTINGS']['CALCULATE_URL'])
		)
		{
			return $result->addError(new Main\Error('Calculate URL is not specified'));
		}

		$sendRequestResult = Sale\Helpers\Rest\Http::sendRequest(
			$handlerParams['SETTINGS']['CALCULATE_URL'],
			[
				'SHIPMENT' => DataProviders\Shipment::getData($shipment),
			],
			[
				'JSON_REQUEST' => true,
			]
		);
		if ($sendRequestResult->isSuccess())
		{
			$calculatedData = $sendRequestResult->getData();

			if (!(isset($calculatedData['SUCCESS']) && $calculatedData['SUCCESS'] === 'Y'))
			{
				$errorText = (
					isset($calculatedData['REASON']['TEXT'])
					&& is_string($calculatedData['REASON']['TEXT'])
					&& !empty($calculatedData['REASON']['TEXT'])
				)
					? $calculatedData['REASON']['TEXT']
					: Loc::getMessage('SALE_DELIVERY_REST_PROFILE_PRICE_CALCULATION_ERROR');

				$result->addError(new Main\Error($errorText, 'DELIVERY_CALCULATION'));
			}

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
	 * @inheritDoc
	 */
	protected function getProfileType(): string
	{
		return (string)$this->profileType;
	}

	/**
	 * @return mixed
	 */
	private function getProfileParams()
	{
		$handlerParams = $this->getHandlerParams();
		$type = $this->getProfileType();

		return $handlerParams['PROFILES'][$type];
	}

	/**
	 * @return mixed
	 */
	private function getHandlerParams()
	{
		$handlerList = Manager::getRestHandlerList();
		$code = str_replace(self::HANDLER_CODE_PREFIX, '', $this->restHandler->getHandlerCode());

		return $handlerList[$code];
	}

	/**
	 * @return array Handler's configuration
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

	/**
	 * @inheritDoc
	 */
	public function getDeliveryRequestHandler()
	{
		$handlerParams = $this->getHandlerParams();

		if (
			!isset($handlerParams['SETTINGS']['CREATE_DELIVERY_REQUEST_URL'])
			|| !is_string($handlerParams['SETTINGS']['CREATE_DELIVERY_REQUEST_URL'])
			|| empty($handlerParams['SETTINGS']['CREATE_DELIVERY_REQUEST_URL'])
		)
		{
			return null;
		}

		$handler = (new RequestHandler($this))
			->setCreateRequestUrl($handlerParams['SETTINGS']['CREATE_DELIVERY_REQUEST_URL']);

		if (
			isset($handlerParams['SETTINGS']['CANCEL_DELIVERY_REQUEST_URL'])
			&& is_string($handlerParams['SETTINGS']['CANCEL_DELIVERY_REQUEST_URL'])
			&& !empty($handlerParams['SETTINGS']['CANCEL_DELIVERY_REQUEST_URL'])
		)
		{
			$handler->setCancelRequestUrl($handlerParams['SETTINGS']['CANCEL_DELIVERY_REQUEST_URL']);
		}

		if (
			isset($handlerParams['SETTINGS']['CANCEL_ACTION_NAME'])
			&& is_string($handlerParams['SETTINGS']['CANCEL_ACTION_NAME'])
			&& !empty($handlerParams['SETTINGS']['CANCEL_ACTION_NAME'])
		)
		{
			$handler->setCancelActionName($handlerParams['SETTINGS']['CANCEL_ACTION_NAME']);
		}

		if (
			isset($handlerParams['SETTINGS']['DELETE_DELIVERY_REQUEST_URL'])
			&& is_string($handlerParams['SETTINGS']['DELETE_DELIVERY_REQUEST_URL'])
			&& !empty($handlerParams['SETTINGS']['DELETE_DELIVERY_REQUEST_URL'])
		)
		{
			$handler->setDeleteRequestUrl($handlerParams['SETTINGS']['DELETE_DELIVERY_REQUEST_URL']);
		}

		if (
			isset($handlerParams['SETTINGS']['HAS_CALLBACK_TRACKING_SUPPORT'])
			&& $handlerParams['SETTINGS']['HAS_CALLBACK_TRACKING_SUPPORT'] === 'Y'
		)
		{
			$handler->setHasCallbackTrackingSupport(true);
		}

		return $handler;
	}
}
