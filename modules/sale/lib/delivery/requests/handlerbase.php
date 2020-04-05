<?
namespace Bitrix\Sale\Delivery\Requests;

use Bitrix\Main;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Shipment;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class HandlerBase
 * @package Bitrix\Sale\Delivery\Requests
 * Base class for delivery requests handlers
 */
abstract class HandlerBase
{
	/** @var Delivery\Services\Base */
	protected $deliveryService = null;

	/**
	 * Base constructor.
	 * @param Delivery\Services\Base $deliveryService
	 */
	public function __construct(Delivery\Services\Base $deliveryService)
	{
		$this->deliveryService = $deliveryService;
	}

	/**
	 * Creates delivery request.
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Result
	 */
	public function create(array $shipmentIds, array $additional = array())
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_CREATE_NOT_SUPPORT')));
		return $result;
	}

	/**
	 * @param int $requestId
	 * @return array Actions list.
	 * For example array( 'ACTION1' => 'ACTION1_NAME', 'ACTION2' => 'ACTION2_NAME').
	 */
	public function getActions($requestId)
	{
		return array();
	}

	/**
	 * @param Shipment $shipment
	 * @return array Actions list
	 * For example array( 'ACTION1' => 'ACTION1_NAME', 'ACTION2' => 'ACTION2_NAME').
	 */
	public function getShipmentActions(Shipment $shipment)
	{
		return array();
	}

	/**
	 * Executes delivery request action
	 * @param int $requestId
	 * @param string $actionType
	 * @param array $additional
	 * @return Result
	 */
	public function executeAction($requestId, $actionType, array $additional)
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_ACTIONS_NOT_SUPPORT')));
		return $result;
	}

	/**
	 * Executes action for shipment from delivery request
	 * @param int $requestId
	 * @param int $shipmentId
	 * @param string $actionType
	 * @param array $additional
	 * @return Result
	 */
	public function executeShipmentAction($requestId, $shipmentId, $actionType, array $additional)
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_SHIPMENT_ACTIONS_NOT_SUPPORT')));
		return $result;
	}

	/**
	 * Add shipments to delivery request
	 * @param int $requestId
	 * @param int[] $shipmentIds
	 * @return Result
	 */
	public function addShipments($requestId, $shipmentIds)
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_SHIPMENT_ADD_NOT_SUPPORT')));
		return $result;
	}

	/**
	 * Delete delivery request
	 * @param int $requestId
	 * @return Result
	 */
	public function delete($requestId)
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_DELETE_NOT_SUPPORT')));
		return $result;
	}

	/**
	 * Delete shipment from delivery request
	 * @param int $requestId
	 * @param int[] $shipmentIds
	 * @return Result
	 */
	public function deleteShipments($requestId, array $shipmentIds = array())
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_SHIPMENT_DELETE_NOT_SUPPORT')));
		return $result;
	}

	/**
	 * Update shipment from delivery request
	 * @param int $requestId
	 * @param int[] $shipmentIds
	 * @return Result
	 */
	public function updateShipments($requestId, array $shipmentIds = array())
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_SHIPMENT_UPDATE_NOT_SUPPORTED')));
		return $result;
	}

	/**
	 * Returns array of fields if we need some additional information
	 * during creation, or action execution, etc.
	 * @param string $formFieldsType (FORM_FIELDS_TYPE_CREATE | FORM_FIELDS_TYPE_ADD | FORM_FIELDS_TYPE_ACTION)
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return array
	 */
	public function getFormFields($formFieldsType, array $shipmentIds, array $additional = array())
	{
		return array();
	}

	/**
	 * Returns content of delivery request
	 * @param int $requestId
	 * @return Result
	 */
	public function getContent($requestId)
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_VIEW_NOT_SUPPORT')));
		return $result;
	}

	/**
	 * Returns content of shipment from delivery request
	 * @param int $requestId
	 * @param int $shipmentId
	 * @return Result. Data contain array of shipment fields on delivery side
	 */
	public function getShipmentContent($requestId, $shipmentId)
	{
		$result = new Result();
		$result->addError(new Main\Error(Loc::getMessage('SALE_DLVR_REQ_BASE_SHIPMENT_VIEW_NOT_SUPPORT')));
		return $result;
	}

	/**
	 * Returns id of delivery service witch actually handles delivery requests
	 * @return int
	 */
	public function getHandlingDeliveryServiceId()
	{
		return $this->deliveryService->getId();
	}
}