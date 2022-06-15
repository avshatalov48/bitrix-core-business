<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal\EventBuilder;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\MultiClaimResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\PhoneResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\PriceResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\SingleClaimResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Tariff;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\TariffsResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\ClaimReader\ClaimReader;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Estimation;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\SearchOptions;
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
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function checkPrice(Estimation $estimation): PriceResult
	{
		$result = new PriceResult();

		try
		{
			$response = $this->transport->post('check-price', $estimation);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'check_price', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'check_price');
		}

		if (!isset($body['price'])
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
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getTariffs(TariffsOptions $tariffsOptions): TariffsResult
	{
		$result = new TariffsResult();

		try
		{
			$response = $this->transport->post('tariffs', $tariffsOptions);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
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
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function createClaim(Claim $claim): SingleClaimResult
	{
		$result = new SingleClaimResult();

		try
		{
			$response = $this->transport->post(
				sprintf(
					'claims/create?%s',
					http_build_query(['request_id' => uniqid('', true)])
				),
				$claim
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
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
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function acceptClaim(string $claimId, int $version): Result
	{
		$result = new Result();

		try
		{
			$response = $this->transport->post(
				sprintf(
					'claims/accept?%s',
					http_build_query(['claim_id' => $claimId])
				),
				['version' => $version]
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();

		if ($statusCode != 200)
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
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function cancelClaim(string $claimId, int $version, string $cancelState): Result
	{
		$result = new Result();

		try
		{
			$response = $this->transport->post(
				sprintf(
					'claims/cancel?%s',
					http_build_query(['claim_id' => $claimId])
				),
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

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'cancel_claim', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'cancel');
		}

		return $result;
	}

	/**
	 * @param string $claimId
	 * @return SingleClaimResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getClaim(string $claimId): SingleClaimResult
	{
		$result = new SingleClaimResult();

		try
		{
			$response = $this->transport->post(
				sprintf(
					'claims/info?%s',
					http_build_query(['claim_id' => $claimId])
				)
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
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
	 * @param SearchOptions $searchOptions
	 * @param bool $onlyActive
	 * @return MultiClaimResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function searchClaims(SearchOptions $searchOptions, $onlyActive = false): MultiClaimResult
	{
		$result = new MultiClaimResult();

		try
		{
			$uri = $onlyActive ? 'claims/search' : 'claims/search/active';

			$response = $this->transport->post($uri, $searchOptions);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'search_claims_1', $response->toString());
			return $this->respondStatusError($result, $statusCode, 'info');
		}

		if (isset($body['claims']) && is_array($body['claims']))
		{
			foreach ($body['claims'] as $responseClaim)
			{
				$claimReadResult = $this->claimReader->readFromArray($responseClaim);
				if ($claimReadResult->isSuccess())
				{
					$result->addClaim($claimReadResult->getClaim());
				}
				else
				{
					$this->logger->log(static::LOG_SOURCE, 'search_claims_2', $response->toString());
					return $result->addErrors($claimReadResult->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param SearchOptions $searchOptions
	 * @return MultiClaimResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function searchActiveClaims(SearchOptions $searchOptions): MultiClaimResult
	{
		return $this->searchClaims($searchOptions, true);
	}

	/**
	 * @param string $claimId
	 * @return PhoneResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getPhone(string $claimId): PhoneResult
	{
		$result = new PhoneResult();

		try
		{
			$response = $this->transport->post('driver-voiceforwarding', ['claim_id' => $claimId]);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
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
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getJournalRecords($cursor): ApiResult\Journal\Result
	{
		$result = new ApiResult\Journal\Result();

		try
		{
			$options = is_null($cursor) ? new \stdClass() : ['cursor' => $cursor];
			$response = $this->transport->post('claims/journal', $options);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
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
