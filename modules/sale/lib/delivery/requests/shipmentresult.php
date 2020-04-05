<?
namespace Bitrix\Sale\Delivery\Requests;

/**
 * Class ShipmentResult
 * @package Bitrix\Sale\Delivery\Requests
 */
class ShipmentResult extends Result
{
	protected $externalId = '';
	protected $internalId = 0;
	protected $trackingNumber = '';
	protected $deliveryDocNum = '';
	protected $deliveryDocDate = '';

	/**
	 * ShipmentResult constructor.
	 * @param int $internalId
	 * @param string $externalId
	 */
	public function __construct($internalId, $externalId = '')
	{
		$this->setInternalId($internalId);
		$this->setExternalId($externalId);
		parent::__construct();
	}

	/**
	 * @return string
	 */
	public function getDeliveryDocNum()
	{
		return $this->deliveryDocNum;
	}

	/**
	 * @param string $deliveryDocNum
	 */
	public function setDeliveryDocNum($deliveryDocNum)
	{
		$this->deliveryDocNum = $deliveryDocNum;
	}

	/**
	 * @return string
	 */
	public function getDeliveryDocDate()
	{
		return $this->deliveryDocDate;
	}

	/**
	 * @param string $deliveryDocDate
	 */
	public function setDeliveryDocDate($deliveryDocDate)
	{
		$this->deliveryDocDate = $deliveryDocDate;
	}

	/**
	 * @return string
	 */
	public function getTrackingNumber()
	{
		return $this->trackingNumber;
	}

	/**
	 * @param string $trackingNumber
	 */
	public function setTrackingNumber($trackingNumber)
	{
		$this->trackingNumber = $trackingNumber;
	}

	/**
	 * @return string
	 */
	public function getExternalId()
	{
		return $this->externalId;
	}

	/**
	 * @param string $externalId
	 */
	public function setExternalId($externalId)
	{
		$this->externalId = $externalId;
	}

	/**
	 * @return int
	 */
	public function getInternalId()
	{
		return $this->internalId;
	}

	/**
	 * @param int $internalId
	 */
	public function setInternalId($internalId)
	{
		$this->internalId = $internalId;
	}
}