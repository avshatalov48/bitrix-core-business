<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\ClaimReader;

use Bitrix\Main\Error;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Address;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Contact;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\ErrorMessage;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Offer;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\PerformerInfo;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Pricing;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\RoutePoint;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\RoutePoints;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\ShippingItem;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\ShippingItemSize;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\TransportClassification;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Warning;

/**
 * Class ClaimReader
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\ClaimReader
 */
class ClaimReader
{
	/**
	 * @param array $response
	 * @return Result
	 */
	public function readFromRawJsonResponse(array $response): Result
	{
		if (!is_array($response))
		{
			return (new Result())->addError(new Error('unexpected_response'));
		}

		return $this->readFromArray($response);
	}

	/**
	 * @param array $response
	 * @return Result
	 */
	public function readFromArray(array $response): Result
	{
		$result = new Result();

		/**
		 * Check if expected fields are present in the response
		 */
		$requiredFields = ['id', 'version', 'status'];
		foreach ($requiredFields as $requiredField)
		{
			if (isset($response[$requiredField]))
			{
				continue;
			}

			return $result->addError(new Error(sprintf('expected_field_missing: %s', $requiredField)));
		}

		$claim = new Claim();

		if (isset($response['id']))
		{
			$claim->setId($response['id']);
		}
		if (isset($response['corp_client_id']))
		{
			$claim->setCorpClientId($response['corp_client_id']);
		}
		if (isset($response['status']))
		{
			$claim->setStatus($response['status']);
		}
		if (isset($response['version']))
		{
			$claim->setVersion($response['version']);
		}
		if (isset($response['skip_client_notify']))
		{
			$claim->setSkipClientNotify($response['skip_client_notify']);
		}
		if (isset($response['skip_emergency_notify']))
		{
			$claim->setSkipEmergencyNotify($response['skip_emergency_notify']);
		}
		if (isset($response['skip_door_to_door']))
		{
			$claim->setSkipDoorToDoor($response['skip_door_to_door']);
		}
		if (isset($response['optional_return']))
		{
			$claim->setOptionalReturn($response['optional_return']);
		}
		if (isset($response['comment']))
		{
			$claim->setComment($response['comment']);
		}
		if (isset($response['created_ts']))
		{
			$claim->setCreatedTs($response['created_ts']);
		}
		if (isset($response['updated_ts']))
		{
			$claim->setUpdatedTs($response['updated_ts']);
		}
		if (isset($response['available_cancel_state']))
		{
			$claim->setAvailableCancelState($response['available_cancel_state']);
		}
		if (isset($response['items']) && is_array($response['items']))
		{
			foreach ($response['items'] as $item)
			{
				$shippingItem = new ShippingItem();

				if (isset($item['title']))
				{
					$shippingItem->setTitle($item['title']);
				}
				if (isset($item['weight']))
				{
					$shippingItem->setWeight($item['weight']);
				}
				if (isset($item['quantity']))
				{
					$shippingItem->setQuantity($item['quantity']);
				}
				if (isset($item['cost_value']))
				{
					$shippingItem->setCostValue($item['cost_value']);
				}
				if (isset($item['cost_currency']))
				{
					$shippingItem->setCostCurrency($item['cost_currency']);
				}
				if (isset($item['size']))
				{
					$shippingItemSize = new ShippingItemSize();

					if (isset($item['size']['length']))
					{
						$shippingItemSize->setLength($item['size']['length']);
					}
					if (isset($item['size']['width']))
					{
						$shippingItemSize->setWidth($item['size']['width']);
					}
					if (isset($item['size']['height']))
					{
						$shippingItemSize->setHeight($item['size']['height']);
					}

					$shippingItem->setSize($shippingItemSize);
				}

				$claim->addItem($shippingItem);
			}
		}
		if (isset($response['route_points']))
		{
			$routePoints = new RoutePoints();

			if (isset($response['route_points']['source']))
			{
				$routePoints->setSource(
					$this->buildRoutePoint($response['route_points']['source'])
				);
			}
			if (isset($response['route_points']['destination']))
			{
				$routePoints->setDestination(
					$this->buildRoutePoint($response['route_points']['destination'])
				);
			}
			if (isset($response['route_points']['return']))
			{
				$routePoints->setReturn(
					$this->buildRoutePoint($response['route_points']['return'])
				);
			}

			$claim->setRoutePoints($routePoints);
		}
		if (isset($response['emergency_contact']))
		{
			$claim->setEmergencyContact(
				$this->buildContact($response['emergency_contact'])
			);
		}
		if (isset($response['pricing']))
		{
			$pricing = new Pricing();

			if (isset($response['pricing']['currency']))
			{
				$pricing->setCurrency($response['pricing']['currency']);
			}
			if (isset($response['pricing']['final_price']))
			{
				$pricing->setFinalPrice($response['pricing']['final_price']);
			}
			if (isset($response['pricing']['offer']))
			{
				$offer = new Offer();

				if (isset($response['pricing']['offer']['offer_id']))
				{
					$offer->setOfferId($response['pricing']['offer']['offer_id']);
				}
				if (isset($response['pricing']['offer']['price']))
				{
					$offer->setPrice($response['pricing']['offer']['price']);
				}

				$pricing->setOffer($offer);
			}

			$claim->setPricing($pricing);
		}
		if (isset($response['client_requirements']))
		{
			$claim->setClientRequirements(
				$this->buildTransportClassification($response['client_requirements'])
			);
		}
		if (isset($response['matched_cars']) && is_array($response['matched_cars']))
		{
			foreach ($response['matched_cars'] as $matchedCar)
			{
				$claim->addMatchedCar(
					$this->buildTransportClassification($matchedCar)
				);
			}
		}
		if (isset($response['performer_info']))
		{
			$performerInfo = new PerformerInfo();

			if (isset($response['performer_info']['courier_name']))
			{
				$performerInfo->setCourierName($response['performer_info']['courier_name']);
			}
			if (isset($response['performer_info']['legal_name']))
			{
				$performerInfo->setLegalName($response['performer_info']['legal_name']);
			}

			if (isset($response['performer_info']['car_model']))
			{
				$performerInfo->setCarModel($response['performer_info']['car_model']);
			}

			if (isset($response['performer_info']['car_number']))
			{
				$performerInfo->setCarNumber($response['performer_info']['car_number']);
			}

			$claim->setPerformerInfo($performerInfo);
		}
		if (isset($response['error_messages']) && is_array($response['error_messages']))
		{
			foreach ($response['error_messages'] as $errorMessage)
			{
				$claim->addErrorMessage($this->buildErrorMessage($errorMessage));
			}
		}
		if (isset($response['warnings']) && is_array($response['warnings']))
		{
			foreach ($response['warnings'] as $warning)
			{
				$claim->addWarning($this->buildWarning($warning));
			}
		}

		return $result->setClaim($claim);
	}

	/**
	 * @param array $node
	 * @return ErrorMessage
	 */
	private function buildErrorMessage(array $node): ErrorMessage
	{
		$result = new ErrorMessage();

		if (isset($node['code']))
		{
			$result->setCode($node['code']);
		}
		if (isset($node['message']))
		{
			$result->setMessage($node['message']);
		}

		return $result;
	}

	/**
	 * @param array $node
	 * @return Warning
	 */
	private function buildWarning(array $node): Warning
	{
		$result = new Warning();

		if (isset($node['source']))
		{
			$result->setSource($node['source']);
		}
		if (isset($node['code']))
		{
			$result->setCode($node['code']);
		}
		if (isset($node['message']))
		{
			$result->setMessage($node['message']);
		}

		return $result;
	}

	/**
	 * @param array $node
	 * @return RoutePoint
	 */
	private function buildRoutePoint(array $node): RoutePoint
	{
		$result = new RoutePoint();

		if (isset($node['skip_confirmation']))
		{
			$result->setSkipConfirmation($node['skip_confirmation']);
		}
		if (isset($node['contact']))
		{
			$result->setContact(
				$this->buildContact($node['contact'])
			);
		}
		if (isset($node['address']))
		{
			$result->setAddress(
				$this->buildAddress($node['address'])
			);
		}

		return $result;
	}

	/**
	 * @param array $node
	 * @return Contact
	 */
	private function buildContact(array $node): Contact
	{
		$result = new Contact();

		if (isset($node['name']))
		{
			$result->setName($node['name']);
		}
		if (isset($node['phone']))
		{
			$result->setPhone($node['phone']);
		}
		if (isset($node['email']))
		{
			$result->setEmail($node['email']);
		}

		return $result;
	}

	/**
	 * @param array $node
	 * @return Address
	 */
	private function buildAddress(array $node): Address
	{
		$result = new Address();

		if (isset($node['fullname']))
		{
			$result->setFullName($node['fullname']);
		}
		if (isset($node['country']))
		{
			$result->setCountry($node['country']);
		}
		if (isset($node['city']))
		{
			$result->setCity($node['city']);
		}
		if (isset($node['street']))
		{
			$result->setStreet($node['street']);
		}
		if (isset($node['building']))
		{
			$result->setBuilding($node['building']);
		}
		if (isset($node['porch']))
		{
			$result->setPorch($node['porch']);
		}
		if (isset($node['coordinates']))
		{
			$result->setCoordinates($node['coordinates']);
		}

		return $result;
	}

	/**
	 * @param array $node
	 * @return TransportClassification
	 */
	private function buildTransportClassification(array $node): TransportClassification
	{
		$result = new TransportClassification();

		if (isset($node['taxi_class']))
		{
			$result->setTaxiClass($node['taxi_class']);
		}
		if (isset($node['cargo_type']))
		{
			$result->setCargoType($node['cargo_type']);
		}
		if (isset($node['cargo_loaders']))
		{
			$result->setCargoLoaders($node['cargo_loaders']);
		}
		
		return $result;
	}
}
