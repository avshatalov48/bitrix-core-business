<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Delivery\Requests;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Packing\Packer;
use Sale\Handlers\Delivery\AdditionalHandler;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Reference;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler;

Loc::loadMessages(__FILE__);

/**
 * Class OrderCreate
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Creates shipment
 * https://otpravka.pochta.ru/specification#/orders-creating_order
 */
class OrderCreate extends Base
{
	protected $path = "/1.0/user/backlog";
	protected $type = HttpClient::HTTP_PUT;
	/** @var AdditionalHandler */
	protected $deliveryService = null;

	/**
	 * @param array $rawData
	 * @param array $requestData
	 * @return Requests\Result
	 */
	protected function convertResponse($rawData, $requestData)
	{
		$result = new Requests\Result();
		$errorPositions = array();
		/** @var Requests\ShipmentResult[] $createResults */
		$createResults = array();

		if(is_array($rawData['errors']))
		{
			foreach($rawData['errors'] as $error)
			{
				if(!isset($requestData[$error['position']]['order-num']))
					continue;

				$internalId = $requestData[$error['position']]['order-num'];

				if(!isset($createResults[$internalId]))
					$createResults[$internalId] = new Requests\ShipmentResult($internalId, '');

				$errorPositions[] = $error['position'];

				if(is_array($error['error-codes']))
				{
					foreach($error['error-codes'] as $errorcode)
					{
						$message = Reference::getErrorDescription($errorcode['code'], 'PUT /1.0/user/backlog');

						if(!empty($errorcode['details']))
							$details = $errorcode['details'];
						elseif(!empty($errorcode['description']))
							$details = $errorcode['description'];
						else
							$details = '';

						if(!empty($details))
						{
							if($errorcode['code'] == 'ILLEGAL_MAIL_CATEGORY')
								$message = str_replace('%s', Reference::getRpoCategory($details), $message);
							else
								$message .= ' ('.$details.')';
						}

						$createResults[$internalId]->addError(new Error($message));
					}
				}
			}
		}

		$idsMap = $this->getShipmentsMap($rawData['result-ids'], $errorPositions, $requestData);
		$idsMapFlipped = array_flip($idsMap);

		if(is_array($rawData['result-ids']))
		{
			foreach($rawData['result-ids'] as $externalId)
			{
				if(!isset($idsMapFlipped[$externalId]))
					continue;

				$internalId = $idsMapFlipped[$externalId];

				if(!isset($createResults[$internalId]))
					$createResults[$internalId] = new Requests\ShipmentResult($internalId, $externalId);
			}
		}

		$result->setResults($createResults);
		return $result;
	}

	/**
	 * @param int[] $successIds
	 * @param int[] $errorPositions
	 * @param array $requestData
	 * @return array
	 */
	protected function getShipmentsMap($successIds, $errorPositions, $requestData)
	{
		$idsMap = array();

		if(empty($successIds) || !is_array($successIds))
			return array();

		$shift = 0;

		foreach($requestData as $position => $request)
		{
			if(!empty($errorPositions) && in_array($position, $errorPositions))
			{
				$shift++;
				continue;
			}

			if(!empty($requestData[$position]['order-num']) && !empty($successIds[$position - $shift]))
				$idsMap[$request['order-num']] = $successIds[$position - $shift];
		}

		return $idsMap;
	}

	/**
	 * @param array $addresses
	 * @return Requests\Result
	 */
	protected function normalizeAddresses(array $addresses)
	{
		$requestData = array();

		foreach($addresses as $id => $address)
		{
			$address = str_replace(["\n", "\t", "\r"], " ", $address);

			$requestData[] = array(
				'id' => $id,
				'original-address' => $address
			);
		}

		/** @var Handler $deliveryRequest */
		$deliveryRequest = $this->deliveryService->getDeliveryRequestHandler();
		return $deliveryRequest->send('CLEAN_ADDRESS', $requestData);
	}

	/**
	 * @param array $fios
	 * @return Requests\Result
	 */
	protected function normalizeFios(array $fios)
	{
		$requestData = array();

		foreach($fios as $id => $fio)
		{
			$requestData[] = array(
				'id' => $id,
				'original-fio' => $fio
			);
		}

		/** @var Handler $deliveryRequest */
		$deliveryRequest = $this->deliveryService->getDeliveryRequestHandler();
		return $deliveryRequest->send('NORMALIZE_FIO', $requestData);
	}

	/**
	 * @param array $address
	 * @return bool
	 */
	protected function isAddressGood(array $address)
	{
		$quality = $address['quality-code'] == 'GOOD'
			|| $address['quality-code'] == 'POSTAL_BOX'
			|| $address['quality-code'] == 'ON_DEMAND'
			|| $address['quality-code'] == 'UNDEF_05';

		$valid = $address['validation-code'] == 'VALIDATED'
			|| $address['validation-code'] == 'OVERRIDDEN'
			|| $address['validation-code'] == 'CONFIRMED_MANUALLY';

		return $quality && $valid;
	}

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function createBody(array $shipmentIds, array $additional = array())
	{
		$result = new Requests\Result();
		$resultData = array();
		$shipments = Requests\Helper::getShipmentsByIds($shipmentIds);
		$rpoCategory = Reference::getRpoCategoriesMap();
		$qualityCodes = Reference::getQualityCodesList();
		$addresses = array();
		$fios = array();
		$notValidShipmentIds = array();

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach($shipments as $shipment)
		{
			$shipmentParams = AdditionalHandler::getShipmentParams($shipment, $this->deliveryService->getServiceType());
			$deliveryConfig = $shipment->getDelivery() ? $shipment->getDelivery()->getConfig() : [];
			$shipmentId = $shipment->getId();

			$mailType = $deliveryConfig['MAIN']['ITEMS']['OTPRAVKA_RPO']['VALUE'];

			if($mailType == '')
			{
				$shpResult = new Requests\ShipmentResult($shipmentId);
				$shpResult->addError(
					new Error(
						Loc::getMessage(
							'SALE_DLVRS_ADD_DREQ_ROC_NOT_SUPPORTED',
							array(
								'#DELIVERY_PROFILE_LINK#' =>
									Requests\Helper::getDeliveryEditLink($shipment->getDeliveryId(), $shipmentParams['DELIVERY_SERVICE_CONFIG']['MAIN']['NAME']),
							)
				)));

				$result->addResult($shpResult);
				continue;
			}

			$shpDim = array();

			foreach($shipmentParams['ITEMS'] as $item)
			{
				if(!empty($item['DIMENSIONS']) && is_array($item['DIMENSIONS']))
				{
					if(intval($item['DIMENSIONS']['HEIGHT']) > 0
						&& intval($item['DIMENSIONS']['WIDTH']) > 0
						&& intval($item['DIMENSIONS']['LENGTH']) > 0)
					{
						for($i=0; $i < $item['QUANTITY']; $i++)
						{
							$shpDim[] = array(
								intval($item['DIMENSIONS']['LENGTH']),
								intval($item['DIMENSIONS']['WIDTH']),
								intval($item['DIMENSIONS']['HEIGHT'])
							);
						}
					}
				}
			}

			$mailCategory = $rpoCategory[$shipmentParams['DELIVERY_SERVICE_CONFIG']['MAIN']['CATEGORY']];

			if($mailCategory == '')
				$mailCategory = 'ORDINARY';

			$item = array(
				'address-type-to' => 'DEFAULT',
				//'brand-name' => ''
				//'envelope-type' =>
				'fragile' => isset($shipmentParams['EXTRA_SERVICES'][4]) && $shipmentParams['EXTRA_SERVICES'][4] =='Y', // 4 - code of extra service fragile
				'mail-category' => $mailCategory,
				'order-num' => strval($shipmentParams['SHIPMENT_ID']),
				'sms-notice-recipient' => isset($shipmentParams['EXTRA_SERVICES'][42]) && $shipmentParams['EXTRA_SERVICES'][4] == 'Y' ? 1 : 0,
				'mail-direct' => 643, //Russia
				'mail-type' => $mailType
			);

			$item['courier'] = isset($shipmentParams['EXTRA_SERVICES'][26]) && $shipmentParams['EXTRA_SERVICES'][26] == 'Y';

			if(isset($shipmentParams['EXTRA_SERVICES'][1]))
			{
				if($shipmentParams['EXTRA_SERVICES'][1] == "1")
					$item['with-simple-notice'] = true;
				elseif($shipmentParams['EXTRA_SERVICES'][1] == "2")
					$item['with-order-of-notice'] = true;
			}

			if(intval($shipmentParams['WEIGHT']) > 0)
			{
				$item['mass'] = intval($shipmentParams['WEIGHT']);
			}
			else
			{
				$dlvConfig = $this->deliveryService->getConfigValues();

				if(intval($dlvConfig['MAIN']['WEIGHT_DEFAULT']) > 0)
					$item['mass'] = intval($dlvConfig['MAIN']['WEIGHT_DEFAULT']);
			}

			if(!empty($shpDim))
			{
				$dimensions = Packer::countMinContainerSize($shpDim);

				$item['dimension'] = array(
					'length' => $dimensions[0]/10, //milimeters ->santimeters
					'width' => $dimensions[1]/10,
					'height' => $dimensions[2]/10
				);
			}
			else
			{
				$dlvConfig = $this->deliveryService->getConfigValues();

				if(intval($dlvConfig['MAIN']['LENGTH_DEFAULT']) > 0
					&& intval($dlvConfig['MAIN']['WIDTH_DEFAULT']) > 0
					&& intval($dlvConfig['MAIN']['HEIGHT_DEFAULT']) > 0)
				{
					$item['dimension'] = array(
						'length' => intval($dlvConfig['MAIN']['LENGTH_DEFAULT']/10), //milimeters ->santimeters
						'width' => intval($dlvConfig['MAIN']['WIDTH_DEFAULT']/10),
						'height' => intval($dlvConfig['MAIN']['HEIGHT_DEFAULT']/10)
					);
				}
			}

			$OVERSIZED_DIM = 60;
			$OVERSIZED_DIM_SUM = 120;

			if($item['dimension']['length'] < $OVERSIZED_DIM
				&& $item['dimension']['width'] < $OVERSIZED_DIM
				&& $item['dimension']['height'] < $OVERSIZED_DIM
				&& array_sum($item['dimension']) < $OVERSIZED_DIM_SUM
			)
			{
				unset($item['dimension']);
			}

			if(!empty($additional['OPS']))
				$item['postoffice-code'] = $additional['OPS'];

			if(!empty($shipmentParams['PHONE']))
				$item['tel-address'] = preg_replace('/[^\d]/','',$shipmentParams['PHONE']);

			$price = ($shipmentParams['PRICE'] + $shipmentParams['PRICE_DELIVERY']) * 100; //rubles -> kopeck

			if($shipmentParams['DELIVERY_SERVICE_CONFIG']['MAIN']['CATEGORY'] == 2 || $shipmentParams['DELIVERY_SERVICE_CONFIG']['MAIN']['CATEGORY'] == 4)
				$item['insr-value'] = $price; // https://www.pochta.ru/support/post-rules/valuable-departure

			if($shipmentParams['DELIVERY_SERVICE_CONFIG']['MAIN']['CATEGORY'] == 4)
				$item['payment'] = $price;

			if (!empty($shipmentParams['LOCATION_TO_TYPES']['REGION']))
			{
				$item['region-to'] = $this->convertLocationTypeToString($shipmentParams['LOCATION_TO_TYPES']['REGION']);
			}

			if (!empty($shipmentParams['LOCATION_TO_TYPES']['SUBREGION']))
			{
				$item['area-to'] = $this->convertLocationTypeToString($shipmentParams['LOCATION_TO_TYPES']['SUBREGION']);
			}

			if (!empty($shipmentParams['LOCATION_TO_TYPES']['STREET']))
			{
				$item['street-to'] = $this->convertLocationTypeToString($shipmentParams['LOCATION_TO_TYPES']['STREET']);
			}

			if (!empty($shipmentParams['LOCATION_TO_TYPES']['VILLAGE']))
			{
				$item['place-to'] = $this->convertLocationTypeToString($shipmentParams['LOCATION_TO_TYPES']['VILLAGE']);
			}
			elseif (!empty($shipmentParams['LOCATION_TO_TYPES']['CITY']))
			{
				$item['place-to'] = $this->convertLocationTypeToString($shipmentParams['LOCATION_TO_TYPES']['CITY']);
			}

			if (!empty($shipmentParams['ZIP_TO']))
			{
				$item['index-to'] = $shipmentParams['ZIP_TO'];
			}

			$address = '';
			$types = ['COUNTRY', 'REGION', 'SUBREGION', 'CITY', 'VILLAGE', 'STREET'];

			foreach($types as $type)
			{
				if (empty($shipmentParams['LOCATION_TO_TYPES'][$type]))
				{
					continue;
				}

				if ($address <> '')
				{
					$address .= ', ';
				}

				$address .= $this->convertLocationTypeToString($shipmentParams['LOCATION_TO_TYPES'][$type]);
			}

			if(!empty($shipmentParams['ADDRESS']))
			{
				if($address <> '')
					$address .= ', ';

				$address .= $shipmentParams['ADDRESS'];
			}

			$addresses[$shipment->getId()] = $address;
			$fios[$shipment->getId()] = $shipmentParams['PAYER_NAME'];
			$resultData[$shipment->getId()] = $item;
		}

		if(!empty($resultData))
		{
			$normalizeResult = $this->normalizeAddresses($addresses);

			if(!$normalizeResult->isSuccess())
			{
				$result->addErrors($normalizeResult->getErrors());
				return $result;
			}
			else
			{
				foreach($normalizeResult->getData() as $address)
				{
					$shipmentId = $address['id'];

					if(!isset($resultData[$shipmentId]))
						continue;

					if(!$this->isAddressGood($address))
					{
						$shpResult = new Requests\ShipmentResult($shipmentId);
						$shpResult->addError(new Error(
							$qualityCodes[$address['quality-code']].'. '.Loc::getMessage('SALE_DLVRS_ADD_DREQ_ROC_02').'. "'.
							print_r($address['original-address'], true).'"',
							$shipmentId
						));
						$result->addResult($shpResult);
						$notValidShipmentIds[$shipmentId] = true;
						continue;
					}

					if(!empty($address['area']) && empty($resultData[$shipmentId]['area-to']))
						$resultData[$shipmentId]['area-to'] =  $address['area'];

					if(!empty($address['house']))
						$resultData[$shipmentId]['house-to'] =  $address['house'];

					if(!empty($address['place']))
						$resultData[$shipmentId]['place-to'] =  $address['place'];

					if(!empty($address['region']) && empty($resultData[$shipmentId]['region-to']))
						$resultData[$shipmentId]['region-to'] =  $address['region'];

					if(!empty($address['room']))
						$resultData[$shipmentId]['room-to'] =  $address['room'];

					if(!empty($address['slash']))
						$resultData[$shipmentId]['slash-to'] =  $address['slash'];

					if(!empty($address['building']))
						$resultData[$shipmentId]['building-to'] =  $address['building'];

					if(!empty($address['corpus']))
						$resultData[$shipmentId]['corpus-to'] =  $address['corpus'];

					if(!empty($address['hotel']))
						$resultData[$shipmentId]['hotel-to'] =  $address['hotel'];

					if(!empty($address['letter']))
						$resultData[$shipmentId]['letter-to'] =  $address['letter'];

					if(!empty($address['location']))
						$resultData[$shipmentId]['location-to'] =  $address['location'];

					if(!empty($address['street']) && empty($resultData[$shipmentId]['street-to']))
						$resultData[$shipmentId]['street-to'] =  $address['street'];

					if(!empty($address['place']) && empty($resultData[$shipmentId]['place-to']))
						$resultData[$shipmentId]['place-to'] =  $address['place'];

					if(!empty($address['index']) && empty($resultData[$shipmentId]['index-to']))
						$resultData[$shipmentId]['index-to'] =  $address['index'];

					if(!empty($address['num-address-type']))
						$resultData[$shipmentId]['num-address-type-to'] =  $address['num-address-type'];
				}
			}

			$normalizeResult = $this->normalizeFios($fios);

			if(!$normalizeResult->isSuccess())
			{
				$result->addErrors($normalizeResult->getErrors());
				return $result;
			}

			foreach($normalizeResult->getData() as $fio)
			{
				$shipmentId = $fio['id'];

				if(!isset($resultData[$shipmentId]) || $notValidShipmentIds[$shipmentId])
					continue;

				if((!isset($fio["valid"]) || $fio["valid"] !== false) && $fio["quality-code"] != 'NOT_SURE' )
				{
					if(!empty($fio['middle-name']))
						$resultData[$shipmentId]['middle-name'] =  $fio['middle-name'];

					if(!empty($fio['surname']))
						$resultData[$shipmentId]['surname'] =  $fio['surname'];

					if(!empty($fio['name']))
						$resultData[$shipmentId]['given-name'] =  $fio['name'];
				}

				$resultData[$shipmentId]['recipient-name'] =  $fio['original-fio'];
			}
		}

		if(!empty($notValidShipmentIds))
			foreach($notValidShipmentIds as $shipmentId => $t)
				unset($resultData[$shipmentId]);

		if(!empty($resultData))
			$result->setData(array_values($resultData));
		else
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_ROC_DATA_EMPTY')));

		return $result;
	}

	/**
	 * @param array $locationTypeComponent
	 * @return string
	 */
	private function convertLocationTypeToString(array $locationTypeComponent): string
	{
		return implode(', ', $locationTypeComponent);
	}
}
