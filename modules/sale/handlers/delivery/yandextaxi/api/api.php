<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\CancelInfoResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal\EventBuilder;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\PhoneResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\PriceResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\SingleClaimResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Tariff;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\TariffsResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ClaimReader\ClaimReader;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Estimation;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\TariffsOptions;
use Sale\Handlers\Delivery\YandexTaxi\Api\Transport;
use Sale\Handlers\Delivery\YandexTaxi\Common\Logger;

/**
 * Class Api
 * @package Sale\Handlers\Delivery\YandexTaxi\Api
 * @internal
 */
final class Api
{
	private const SINGLE_POINT_API_VERSION = 1;
	private const MULTI_POINT_API_VERSION = 2;

	private const LOG_SOURCE = 'api';

	/** @var Transport\Client */
	private $transport;

	/** @var ClaimReader */
	private $claimReader;

	/** @var EventBuilder */
	private $eventBuilder;

	/** @var Logger */
	private $logger;

	/**
	 * Api constructor.
	 * @param Transport\Client $transport
	 * @param ClaimReader $claimReader
	 * @param EventBuilder $eventBuilder
	 * @param Logger $logger
	 */
	public function __construct(
		Transport\Client $transport,
		ClaimReader $claimReader,
		EventBuilder $eventBuilder,
		Logger $logger
	)
	{
		$this->transport = $transport;
		$this->claimReader = $claimReader;
		$this->eventBuilder = $eventBuilder;
		$this->logger = $logger;
	}

	/**
	 * @param Estimation $estimation
	 * @return PriceResult
	 */
	public function checkPrice(Estimation $estimation): PriceResult
	{
		$result = new PriceResult();

		try
		{
			$response = $this->transport->request(
				self::SINGLE_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'check-price',
				null,
				$estimation
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'check_price', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'check_price');
		}

		if (
			!isset($body['price'])
			|| !isset($body['currency_rules']['code'])
			|| (float)$body['price'] <= 0
		)
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_RATE_CALCULATE_ERROR')));
		}

		$result
			->setPrice((float)$body['price'])
			->setCurrency((string)$body['currency_rules']['code']);

		if (isset($body['eta']))
		{
			$result->setEta((int)$body['eta']);
		}

		return $result;
	}

	/**
	 * @param TariffsOptions $tariffsOptions
	 * @return TariffsResult
	 */
	public function getTariffs(TariffsOptions $tariffsOptions): TariffsResult
	{
		$result = new TariffsResult();

		try
		{
			$response = $this->transport->request(
				self::SINGLE_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'tariffs',
				null,
				$tariffsOptions
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'tariffs', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'tariffs');
		}

		if (isset($body['available_tariffs']) && is_array($body['available_tariffs']))
		{
			foreach ($body['available_tariffs'] as $tariff)
			{
				if (!isset($tariff['name']))
				{
					continue;
				}

				$tariffObject = new Tariff($tariff['name']);

				if (isset($tariff['supported_requirements']) && is_array($tariff['supported_requirements']))
				{
					foreach ($tariff['supported_requirements'] as $supportedRequirement)
					{
						if (!isset($supportedRequirement['name']))
						{
							continue;
						}

						if ($supportedRequirement['name'] === 'cargo_options')
						{
							if (isset($supportedRequirement['options']) && is_array($supportedRequirement['options']))
							{
								foreach ($supportedRequirement['options'] as $option)
								{
									if (isset($option['value']) && !empty($option['value']))
									{
										$tariffObject->addSupportedRequirement($option['value']);
									}
								}
							}
						}
						else
						{
							$tariffObject->addSupportedRequirement($supportedRequirement['name']);
						}
					}
				}

				$result->addTariff($tariffObject);
			}
		}

		return $result;
	}

	/**
	 * @param Claim $claim
	 * @return SingleClaimResult
	 */
	public function createClaim(Claim $claim): SingleClaimResult
	{
		$result = new SingleClaimResult();

		try
		{
			$response = $this->transport->request(
				self::MULTI_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'claims/create',
				['request_id' => uniqid('', true)],
				$claim
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'create_claim', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'create');
		}

		$claimReadResult = $this->claimReader->readFromArray($body);
		if ($claimReadResult->isSuccess())
		{
			return $result->setClaim($claimReadResult->getClaim());
		}

		return $result;
	}

	/**
	 * @param string $claimId
	 * @param int $version
	 * @return Result
	 */
	public function acceptClaim(string $claimId, int $version): Result
	{
		$result = new Result();

		try
		{
			$response = $this->transport->request(
				self::SINGLE_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'claims/accept',
				['claim_id' => $claimId],
				['version' => $version]
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'accept_claim', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'accept');
		}

		return $result;
	}

	/**
	 * @param string $claimId
	 * @param int $version
	 * @param string $cancelState
	 * @return Result
	 */
	public function cancelClaim(string $claimId, int $version, string $cancelState): Result
	{
		$result = new Result();

		try
		{
			$response = $this->transport->request(
				self::SINGLE_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'claims/cancel',
				['claim_id' => $claimId],
				[
					'version' => $version,
					'cancel_state' => $cancelState,
				]
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'cancel_claim', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'cancel');
		}

		return $result;
	}

	/**
	 * @param string $claimId
	 * @return CancelInfoResult
	 */
	public function getCancelInfo(string $claimId): CancelInfoResult
	{
		$result = new CancelInfoResult();

		try
		{
			$response = $this->transport->request(
				self::MULTI_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'claims/cancel-info',
				['claim_id' => $claimId]
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'cancel_info', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'cancel_info');
		}

		if (empty($body['cancel_state']))
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_FATAL_ERROR')));
		}

		return $result->setCancelState($body['cancel_state']);
	}

	/**
	 * @param string $claimId
	 * @return SingleClaimResult
	 */
	public function getClaim(string $claimId): SingleClaimResult
	{
		$result = new SingleClaimResult();

		try
		{
			$response = $this->transport->request(
				self::MULTI_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'claims/info',
				['claim_id' => $claimId]
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'get_claim', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'info');
		}

		$claimReadResult = $this->claimReader->readFromArray($body);
		if ($claimReadResult->isSuccess())
		{
			return $result->setClaim($claimReadResult->getClaim());
		}

		return $result;
	}

	/**
	 * @param string $claimId
	 * @return PhoneResult
	 */
	public function getPhone(string $claimId): PhoneResult
	{
		$result = new PhoneResult();

		try
		{
			$response = $this->transport->request(
				self::MULTI_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'driver-voiceforwarding',
				null,
				['claim_id' => $claimId]
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'get_phone', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'driver-voiceforwarding');
		}

		if (isset($body['phone']))
		{
			$result->setPhone($body['phone']);
		}
		if (isset($body['ext']))
		{
			$result->setExt($body['ext']);
		}
		if (isset($body['ttl_seconds']))
		{
			$result->setTtlSeconds($body['ttl_seconds']);
		}

		return $result;
	}

	/**
	 * @param $cursor
	 * @return ApiResult\Journal\Result
	 */
	public function getJournalRecords($cursor): ApiResult\Journal\Result
	{
		$result = new ApiResult\Journal\Result();

		try
		{
			$response = $this->transport->request(
				self::MULTI_POINT_API_VERSION,
				HttpClient::HTTP_POST,
				'claims/journal',
				null,
				is_null($cursor) ? new \stdClass() : ['cursor' => $cursor]
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode !== 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'get_journal_records_1', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'journal');
		}

		if (!is_array($body) || !isset($body['cursor']) || !isset($body['events']))
		{
			$this->logger->log(static::LOG_SOURCE, 'get_journal_records_2', $response->toString());

			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_NETWORK_ERROR_UNEXPECTED_ERROR')));
		}

		$result->setCursor($body['cursor']);

		if (is_array($body['events']))
		{
			foreach ($body['events'] as $event)
			{
				$event = $this->eventBuilder->build($event);
				if (is_null($event))
				{
					continue;
				}

				$result->addEvent($event);
			}
		}

		return $result;
	}

	/**
	 * @param $result
	 * @param int $statusCode
	 * @param string $method
	 * @return mixed
	 */
	private function respondStatusError($result, int $statusCode, string $method)
	{
		$error = Loc::getMessage('SALE_YANDEX_TAXI_NETWORK_ERROR_UNEXPECTED_ERROR');

		if ($statusCode == 401)
		{
			$error = Loc::getMessage('SALE_YANDEX_TAXI_AUTHENTICATION_ERROR');
		}
		elseif ($statusCode == 404)
		{
			$error = Loc::getMessage('SALE_YANDEX_TAXI_ORDER_NOT_FOUND_ERROR');
		}
		elseif ($statusCode == 409)
		{
			$error = Loc::getMessage('SALE_YANDEX_TAXI_OPERATION_REJECTED');
		}

		return $result->addError(new Error($error));
	}

	/**
	 * @param $result
	 * @return mixed
	 */
	private function respondTransportError($result)
	{
		return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_NETWORK_ERROR')));
	}

	/**
	 * @return Transport\Client
	 */
	public function getTransport(): Transport\Client
	{
		return $this->transport;
	}
}
