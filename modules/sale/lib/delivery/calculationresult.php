<?php
namespace Bitrix\Sale\Delivery;

use Bitrix\Sale;

class CalculationResult extends Sale\ResultSerializable
{
	const PERIOD_TYPE_MIN = "MIN";
	const PERIOD_TYPE_HOUR = "H";
	const PERIOD_TYPE_DAY = "D";
	const PERIOD_TYPE_MONTH = "M";

	/** @var string */
	protected $description = "";
	/** @var string */
	protected $periodDescription = "";
	/** @var int  */
	protected $periodFrom = null;
	/** @var int  */
	protected $periodTo = null;
	/** @var string  */
	protected $periodType = self::PERIOD_TYPE_DAY;
	/** @var bool */
	protected $nextStep = false;
	/** @var int */
	protected $packsCount = 0;
	/** @var float */
	protected $extraServicesPrice = 0;
	/** @var float */
	protected $deliveryPrice = 0;
	/** @var string $tmpData */
	protected $tmpData = "";

	public function __construct() { parent::__construct(); }

	/**	@return float */
	public function getDeliveryPrice() { return $this->deliveryPrice; }

	/** @param float $price */
	public function setDeliveryPrice($price) { $this->deliveryPrice = $price; }

	/** @return float  */
	public function getExtraServicesPrice() { return $this->extraServicesPrice; }

	/** @param float $price */
	public function setExtraServicesPrice($price) { $this->extraServicesPrice = $price; }

	/**	@return float */
	public function getPrice() { return $this->deliveryPrice + $this->extraServicesPrice;	}

	/** @param string $description */
	public function setDescription($description) { $this->description = $description; }

	/** @return string */
	public function getDescription() { return $this->description; }

	/** @param string $description */
	public function setPeriodDescription($description) { $this->periodDescription = $description; }

	/** @return string */
	public function getPeriodDescription() { return $this->periodDescription; }

	public function setAsNextStep() { $this->nextStep = true; }

	/** @return string */
	public function isNextStep() { return $this->nextStep; }

	/**	@return int */
	public function getPacksCount() { return $this->packsCount; }

	/** @param int $count */
	public function setPacksCount($count) { $this->packsCount = $count; }

	/**	@return int */
	public function getTmpData() { return $this->tmpData; }

	/** @param string $data */
	public function setTmpData($data) { $this->tmpData = $data; }

	/** @return int */
	public function getPeriodFrom() { return $this->periodFrom; }

	/** @param int $periodFrom */
	public function setPeriodFrom($periodFrom) { $this->periodFrom = intval($periodFrom); }

	/** @return int */
	public function getPeriodTo() { return $this->periodTo; }

	/** @param int $periodTo */
	public function setPeriodTo($periodTo) { $this->periodTo = intval($periodTo); }

	/** @return int */
	public function getPeriodType() { return $this->periodType; }

	/** @param int $periodType */
	public function setPeriodType($periodType)
	{
		if(in_array($periodType, array(self::PERIOD_TYPE_DAY, self::PERIOD_TYPE_HOUR, self::PERIOD_TYPE_MIN, self::PERIOD_TYPE_MONTH)))
			$this->periodType = $periodType;
	}
}