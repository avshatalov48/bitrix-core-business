<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\CheckPriceResult;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\Journal\EventBuilder;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\MultiClaimResult;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\PhoneResult;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\SingleClaimResult;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ClaimReader\ClaimReader;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Estimation;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\SearchOptions;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Transport;
use Sale\Handlers\Delivery\Taxi\Yandex\Logger;

/**
 * Class Api
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api
 */
class Api
{
	const LOG_SOURCE = 'api';

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
	 * @return CheckPriceResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function checkPrice(Estimation $estimation): CheckPriceResult
	{
		$result = new CheckPriceResult();

		try
		{
			$response = $this->transport->post(
				'check-price',
				$estimation,
				[
					'base_uri_template' => 'https://b2b.taxi%s.yandex.net/b2b/cargo-matcher/v%s/',
				]
			);
		}
		catch (Transport\Exception $requestException)
		{
			return $this->respondTransportError($result, $requestException);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'status_code', $statusCode);
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
			return $this->respondTransportError($result, $requestException);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'status_code', $statusCode);
			return $this->respondStatusError($result, $statusCode, 'create');
		}

		$claimReadResult = $this->claimReader->readFromRawJsonResponse($body);
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
			return $this->respondTransportError($result, $requestException);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'status_code', $statusCode);
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
			return $this->respondTransportError($result, $requestException);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'status_code', $statusCode);
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
			return $this->respondTransportError($result, $requestException);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'status_code', $statusCode);
			return $this->respondStatusError($result, $statusCode, 'info');
		}

		$claimReadResult = $this->claimReader->readFromRawJsonResponse($body);
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
			return $this->respondTransportError($result, $requestException);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'status_code', $statusCode);
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
					$this->logger->log(static::LOG_SOURCE, 'claim_structure', $statusCode);
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
			return $this->respondTransportError($result, $requestException);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'status_code', $statusCode);
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
			return $this->respondTransportError($result, $requestException);
		}

		$statusCode = $response->getStatus();
		$body = $response->getBody();

		if ($statusCode != 200)
		{
			$this->logger->log(static::LOG_SOURCE, 'status_code', $statusCode);
			return $this->respondStatusError($result, $statusCode, 'journal');
		}

		if (!is_array($body) || !isset($body['cursor']) || !isset($body['events']))
		{
			$this->logger->log(static::LOG_SOURCE, 'response_structure', $statusCode);

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
	 * @param Transport\Exception $transportException
	 * @return mixed
	 */
	private function respondTransportError($result, Transport\Exception $transportException)
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
