<?php

namespace Bitrix\Sale\Delivery\Rest;

use Bitrix\Main;
use Bitrix\Sale\Delivery\Requests;
use Bitrix\Sale\Delivery;
use Bitrix\Rest\RestException;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

class RequestService extends BaseService
{
	private const ERROR_CODE_ADDRESSEE_IS_NOT_SPECIFIED = 'ADDRESSEE_IS_NOT_SPECIFIED';
	private const ERROR_CODE_ADDRESSEE_UNEXPECTED_VALUE = 'ADDRESSEE_UNEXPECTED_VALUE';
	private const ERROR_CODE_MESSAGE_NOT_SPECIFIED = 'MESSAGE_NOT_SPECIFIED';
	private const ERROR_CODE_MESSAGE_STATUS_NOT_SPECIFIED = 'MESSAGE_STATUS_NOT_SPECIFIED';
	private const ERROR_CODE_MESSAGE_STATUS_SEMANTIC_NOT_SPECIFIED = 'MESSAGE_STATUS_SEMANTIC_NOT_SPECIFIED';
	private const ERROR_CODE_UNEXPECTED_MESSAGE_STATUS_SEMANTIC = 'UNEXPECTED_MESSAGE_STATUS_SEMANTIC';
	private const ERROR_CODE_DELIVERY_ID_NOT_SPECIFIED = 'DELIVERY_ID_NOT_SPECIFIED';
	private const ERROR_CODE_DELIVERY_NOT_FOUND = 'DELIVERY_NOT_FOUND';
	private const ERROR_CODE_REQUEST_ID_NOT_SPECIFIED = 'REQUEST_ID_NOT_SPECIFIED';
	private const ERROR_CODE_REQUEST_NOT_FOUND = 'REQUEST_NOT_FOUND';
	private const ERROR_CODE_REQUEST_SHIPMENT_NOT_FOUND = 'REQUEST_SHIPMENT_NOT_FOUND';
	private const ERROR_CODE_DELETE_REQUEST_INTERNAL_ERROR = 'DELETE_REQUEST_INTERNAL_ERROR';
	private const ERROR_CODE_PROPERTIES_UNEXPECTED_FORMAT = 'PROPERTIES_UNEXPECTED_FORMAT';
	private const ERROR_CODE_PROPERTY_VALUE_UNEXPECTED_FORMAT = 'PROPERTY_VALUE_UNEXPECTED_FORMAT';
	private const ERROR_CODE_PROPERTY_VALUE_TAGS_UNEXPECTED_FORMAT = 'PROPERTY_VALUE_TAGS_UNEXPECTED_FORMAT';
	private const ERROR_CODE_PROPERTY_VALUE_TAG_UNEXPECTED_FORMAT = 'PROPERTY_VALUE_TAG_UNEXPECTED_FORMAT';
	private const ERROR_CODE_UNEXPECTED_REQUEST_FINALIZE_INDICATOR_VALUE = 'UNEXPECTED_REQUEST_FINALIZE_INDICATOR_VALUE';
	private const ERROR_CODE_UNEXPECTED_OVERWRITE_PROPERTIES_VALUE = 'UNEXPECTED_OVERWRITE_PROPERTIES_VALUE';
	private const ERROR_CODE_EMPTY_UPDATE_PAYLOAD = 'EMPTY_UPDATE_PAYLOAD';
	private const ERROR_CODE_UPDATE_REQUEST_INTERNAL_ERROR = 'UPDATE_REQUEST_INTERNAL_ERROR';
	private const ERROR_CODE_STATUS_UNEXPECTED_FORMAT = 'STATUS_UNEXPECTED_FORMAT';
	private const ERROR_CODE_STATUS_TEXT_NOT_SPECIFIED = 'STATUS_TEXT_NOT_SPECIFIED';
	private const ERROR_CODE_STATUS_SEMANTIC_NOT_SPECIFIED = 'STATUS_SEMANTIC_NOT_SPECIFIED';
	private const ERROR_CODE_DATE_VALUE_UNEXPECTED_FORMAT = 'DATE_VALUE_UNEXPECTED_FORMAT';

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function updateRequest($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareIncomingParams($query);

		$requestId = self::getRequestId(
			self::getDeliveryId($params, 'DELIVERY_ID'),
			$params,
			'REQUEST_ID'
		);

		$fields = [];

		if (isset($params['FINALIZE']))
		{
			if (!in_array($params['FINALIZE'], ['Y', 'N'], true))
			{
				throw new RestException(
					'Unexpected parameter FINALIZE value: Y, N expected',
					self::ERROR_CODE_UNEXPECTED_OVERWRITE_PROPERTIES_VALUE
				);
			}

			if ($params['FINALIZE'] === 'Y')
			{
				$fields['STATUS'] = Requests\Manager::STATUS_PROCESSED;
			}
		}

		$requestStatus = self::getRequestStatus($params, 'STATUS');
		if (!is_null($requestStatus))
		{
			$fields['EXTERNAL_STATUS'] = $requestStatus['TEXT'];
			$fields['EXTERNAL_STATUS_SEMANTIC'] = $requestStatus['SEMANTIC'];
		}

		$properties = self::getRequestProperties($params, 'PROPERTIES');
		if (!is_null($properties))
		{
			$fields['EXTERNAL_PROPERTIES'] = $properties;
		}

		$overwriteProperties = false;
		if (isset($params['OVERWRITE_PROPERTIES']))
		{
			if (!in_array($params['OVERWRITE_PROPERTIES'], ['Y', 'N'], true))
			{
				throw new RestException(
					'Unexpected parameter OVERWRITE_PROPERTIES value: Y, N expected',
					self::ERROR_CODE_UNEXPECTED_OVERWRITE_PROPERTIES_VALUE
				);
			}
			$overwriteProperties = $params['OVERWRITE_PROPERTIES'] === 'Y';
		}

		if (empty($fields))
		{
			throw new RestException(
				'Empty update payload',
				self::ERROR_CODE_EMPTY_UPDATE_PAYLOAD
			);
		}

		$updateResult = Requests\Manager::updateDeliveryRequest(
			$requestId,
			$fields,
			$overwriteProperties
		);
		if (!$updateResult->isSuccess())
		{
			throw new RestException('Internal error', self::ERROR_CODE_UPDATE_REQUEST_INTERNAL_ERROR);
		}

		return true;
	}

	/**
	 * @param array $params
	 * @param string $key
	 * @return array|null
	 * @throws RestException
	 */
	private static function getRequestStatus(array $params, string $key): ?array
	{
		if (!isset($params[$key]))
		{
			return null;
		}

		if (!is_array($params[$key]))
		{
			throw new RestException(
				sprintf('Unexpected status (%s) format: array expected', $key),
				self::ERROR_CODE_STATUS_UNEXPECTED_FORMAT
			);
		}

		if (empty($params[$key]['TEXT']))
		{
			throw new RestException(
				'Status text has not been specified',
				self::ERROR_CODE_STATUS_TEXT_NOT_SPECIFIED
			);
		}

		if (empty($params[$key]['SEMANTIC']))
		{
			throw new RestException(
				'Status semantic has not been specified',
				self::ERROR_CODE_STATUS_SEMANTIC_NOT_SPECIFIED
			);
		}

		if (!in_array($params[$key]['SEMANTIC'], Requests\Manager::getRequestStatusSemantics(), true))
		{
			throw new RestException(
				sprintf('Unexpected request status semantic: %s', $params[$key]['SEMANTIC']),
				self::ERROR_CODE_STATUS_SEMANTIC_NOT_SPECIFIED
			);
		}

		return [
			'TEXT' => $params[$key]['TEXT'],
			'SEMANTIC' => $params[$key]['SEMANTIC'],
		];
	}

	/**
	 * @param array $params
	 * @param string $key
	 * @return array|null
	 * @throws RestException
	 */
	private static function getRequestProperties(array $params, string $key): ?array
	{
		if (!isset($params[$key]))
		{
			return null;
		}

		if (!is_array($params[$key]))
		{
			throw new RestException(
				sprintf('Unexpected properties (%s) format: array expected', $key),
				self::ERROR_CODE_PROPERTIES_UNEXPECTED_FORMAT
			);
		}

		$result = [];
		foreach ($params[$key] as $propertyKey => $propertyValue)
		{
			$isExpectedFormat = (
				is_array($propertyValue)
				&& isset($propertyValue['NAME'])
				&& is_string($propertyValue['NAME'])
				&& !empty($propertyValue['NAME'])
				&& isset($propertyValue['VALUE'])
				&& is_string($propertyValue['VALUE'])
			);
			if (!$isExpectedFormat)
			{
				throw new RestException(
					sprintf('Unexpected property value (%s.%s) format', $key, $propertyKey),
					self::ERROR_CODE_PROPERTY_VALUE_UNEXPECTED_FORMAT
				);
			}
			$resultItem = [
				'NAME' => $propertyValue['NAME'],
				'VALUE' => $propertyValue['VALUE'],
			];

			if (isset($propertyValue['TAGS']))
			{
				if (!is_array($propertyValue['TAGS']))
				{
					throw new RestException(
						sprintf(
							'Unexpected property value\'s tags format (%s.%s) format: array expected',
							$key,
							$propertyKey
						),
						self::ERROR_CODE_PROPERTY_VALUE_TAGS_UNEXPECTED_FORMAT
					);
				}

				foreach ($propertyValue['TAGS'] as $tag)
				{
					if (!is_string($tag))
					{
						throw new RestException(
							sprintf(
								'Property value (%s.%s) tag must be of string type',
								$key,
								$propertyKey
							),
							self::ERROR_CODE_PROPERTY_VALUE_TAG_UNEXPECTED_FORMAT
						);
					}
				}

				$resultItem['TAGS'] = $propertyValue['TAGS'];
			}

			$result[] = $resultItem;
		}

		return $result;
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function deleteRequest($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareIncomingParams($query);

		$deliveryId = self::getDeliveryId($params, 'DELIVERY_ID');
		$requestId = self::getRequestId($deliveryId, $params, 'REQUEST_ID');

		$deleteResult = Requests\Manager::deleteDeliveryRequest($requestId);
		if (!$deleteResult->isSuccess())
		{
			throw new RestException('Internal error', self::ERROR_CODE_DELETE_REQUEST_INTERNAL_ERROR);
		}

		return true;
	}

	/**
	 * @param $query
	 * @param $n
	 * @param \CRestServer $server
	 * @return bool
	 * @throws RestException
	 */
	public static function sendMessage($query, $n, \CRestServer $server): bool
	{
		self::checkDeliveryPermission();
		$params = self::prepareIncomingParams($query);

		$delivery = self::getDelivery($params, 'DELIVERY_ID');

		$requestId = self::getRequestId($delivery->getId(), $params, 'REQUEST_ID');

		Requests\Manager::sendMessage(
			self::getAddressee($params, 'ADDRESSEE'),
			self::getMessage($params, 'MESSAGE')->setCurrency($delivery->getCurrency()),
			$requestId,
			self::getShipmentId($requestId, $params, 'SHIPMENT_REQUEST_ID')
		);

		return true;
	}

	/**
	 * @param array $params
	 * @param string $key
	 * @return int
	 */
	private static function getDeliveryId(array $params, string $key): int
	{
		$delivery = self::getDelivery($params, $key);

		return $delivery->getId();
	}

	/**
	 * @param array $params
	 * @param string $key
	 * @return Delivery\Services\Base
	 * @throws RestException
	 */
	private static function getDelivery(array $params, string $key): Delivery\Services\Base
	{
		if (!isset($params[$key]))
		{
			throw new RestException(
				sprintf('Parameter %s is not specified', $key),
				self::ERROR_CODE_DELIVERY_ID_NOT_SPECIFIED
			);
		}

		/** @var Delivery\Services\Base $delivery */
		$delivery = Delivery\Services\Manager::getObjectById((int)$params[$key]);
		if (!$delivery)
		{
			throw new RestException(
				'Delivery service has not been found',
				self::ERROR_CODE_DELIVERY_NOT_FOUND
			);
		}

		return $delivery;
	}

	/**
	 * @param array $params
	 * @param string $key
	 * @return string
	 * @throws RestException
	 */
	private static function getAddressee(array $params, string $key): string
	{
		if (empty($params[$key]))
		{
			throw new RestException(
				sprintf('Parameter %s is not specified', $key),
				self::ERROR_CODE_ADDRESSEE_IS_NOT_SPECIFIED
			);
		}

		if (!in_array($params[$key], Requests\Manager::getMessageAddressees(), true))
		{
			throw new RestException(
				sprintf('Unexpected %s parameter value', $key),
				self::ERROR_CODE_ADDRESSEE_UNEXPECTED_VALUE
			);
		}

		return $params[$key];
	}

	/**
	 * @param int $deliveryId
	 * @param array $params
	 * @param string $key
	 * @return int
	 * @throws RestException
	 */
	private static function getRequestId(int $deliveryId, array $params, string $key): int
	{
		if (empty($params[$key]))
		{
			throw new RestException(
				sprintf('Parameter %s is not specified', $key),
				self::ERROR_CODE_REQUEST_ID_NOT_SPECIFIED
			);
		}

		$requestList = Requests\RequestTable::getList([
			'filter' => [
				'=DELIVERY_ID' => $deliveryId,
				'=EXTERNAL_ID' => $params[$key],
			]
		]);
		$request = $requestList->fetch();

		if (!$request)
		{
			throw new RestException(
				'Request has not been found',
				self::ERROR_CODE_REQUEST_NOT_FOUND
			);
		}

		return (int)$request['ID'];
	}

	/**
	 * @param int $requestId
	 * @param array $params
	 * @param string $key
	 * @return int
	 * @throws RestException
	 */
	private static function getShipmentId(int $requestId, array $params, string $key): int
	{
		$requestShipmentFilter = ['=REQUEST_ID' => $requestId];
		if (isset($params[$key]))
		{
			$requestShipmentFilter['=EXTERNAL_ID'] = (int)$params[$key];
		}
		$requestShipment = Requests\ShipmentTable::getList(['filter' => $requestShipmentFilter])->fetch();
		if (!$requestShipment)
		{
			throw new RestException(
				'Shipment has not been found',
				self::ERROR_CODE_REQUEST_SHIPMENT_NOT_FOUND
			);
		}

		return (int)$requestShipment['SHIPMENT_ID'];
	}

	/**
	 * @param array $params
	 * @param string $key
	 * @return Requests\Message\Message
	 * @throws RestException
	 */
	private static function getMessage(array $params, string $key): Requests\Message\Message
	{
		if (
			!isset($params[$key])
			|| !is_array($params[$key])
			|| (
				(!is_string($params[$key]['SUBJECT']) || empty($params[$key]['SUBJECT']))
				&& (!is_string($params[$key]['BODY']) || empty($params[$key]['BODY']))
			)
		)
		{
			throw new RestException(
				sprintf('Parameter %s is not specified', $key),
				self::ERROR_CODE_MESSAGE_NOT_SPECIFIED
			);
		}

		$message = new Requests\Message\Message();

		if (!empty($params[$key]['SUBJECT']))
		{
			$message->setSubject($params[$key]['SUBJECT']);
		}
		if (!empty($params[$key]['BODY']))
		{
			$message->setBody($params[$key]['BODY']);
		}

		if (isset($params[$key]['MONEY_VALUES']) && is_array($params[$key]['MONEY_VALUES']))
		{
			foreach ($params[$key]['MONEY_VALUES'] as $key => $moneyValue)
			{
				$message->addMoneyValue(
					(string)$key,
					(float)$moneyValue
				);
			}
		}

		if (isset($params[$key]['DATE_VALUES']) && is_array($params[$key]['DATE_VALUES']))
		{
			foreach ($params[$key]['DATE_VALUES'] as $key => $dateValue)
			{
				if (!isset($dateValue['VALUE']) || !isset($dateValue['FORMAT']))
				{
					throw new RestException(
						'Unexpected date value format',
						self::ERROR_CODE_DATE_VALUE_UNEXPECTED_FORMAT
					);
				}

				$message->addDateValue(
					(string)$key,
					(int)$dateValue['VALUE'],
					(string)$dateValue['FORMAT']
				);
			}
		}

		if (isset($params[$key]['STATUS']))
		{
			if (!is_string($params[$key]['STATUS']['MESSAGE']) || empty($params[$key]['STATUS']['MESSAGE']))
			{
				throw new RestException(
					'Status message is not specified',
					self::ERROR_CODE_MESSAGE_STATUS_NOT_SPECIFIED
				);
			}

			if (!is_string($params[$key]['STATUS']['SEMANTIC']) || empty($params['MESSAGE']['STATUS']['SEMANTIC']))
			{
				throw new RestException(
					'Message status semantic is not specified',
					self::ERROR_CODE_MESSAGE_STATUS_SEMANTIC_NOT_SPECIFIED
				);
			}

			if (!in_array($params[$key]['STATUS']['SEMANTIC'], Requests\Message\Status::getAvailableSemantics(), true))
			{
				throw new RestException(
					sprintf('Unexpected message status semantic: %s', $params['MESSAGE']['STATUS']['SEMANTIC']),
					self::ERROR_CODE_UNEXPECTED_MESSAGE_STATUS_SEMANTIC
				);
			}

			$message->setStatus(
				new Requests\Message\Status(
					$params[$key]['STATUS']['MESSAGE'],
					$params[$key]['STATUS']['SEMANTIC']
				)
			);
		}

		return $message;
	}
}
