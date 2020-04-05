<?

namespace Sale\Handlers\Delivery;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Sale\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services;
use Bitrix\Sale\Delivery\Tracking\StatusResult;

Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses(
	'sale',
	array(
		'Sale\Handlers\Delivery\AdditionalHandler' => 'handlers/delivery/additional/handler.php'
	)
);
/**
 * Class AdditionalTracking
 * @package \Sale\Handlers\Delivery;
 */
class AdditionalTracking extends \Bitrix\Sale\Delivery\Tracking\Base
{
	/** @var  \Sale\Handlers\Delivery\AdditionalHandler */
	protected $deliveryService;

	protected $classTitle = '';
	protected $classDescription = '';

	public function __construct(array $params, Services\Base $deliveryService)
	{
		$this->classTitle = $deliveryService->getTrackingClassTitle();
		$this->classDescription = $deliveryService->getTrackingClassDescription();

		parent::__construct($params, $deliveryService);
	}

	/**
	 * @return string
	 */
	public function getClassTitle()
	{
		return !empty($this->classTitle) ? $this->classTitle : Loc::getMessage('SALE_DLV_SRV_ADD_T_TITLE');
	}

	/**
	 * @return string
	 */
	public function getClassDescription()
	{
		return !empty($this->classDescription) ? $this->classDescription : Loc::getMessage('SALE_DLV_SRV_ADD_T_DESCR');
	}

	/**
	 * @param $trackingNumber
	 * @return StatusResult.
	 */
	public function getStatus($trackingNumber)
	{
		$trackingNumber = trim($trackingNumber);
		$results = $this->getStatuses(array($trackingNumber));

		if(!empty($results[$trackingNumber]))
		{
			$result = $results[$trackingNumber];
		}
		else
		{
			$result = new StatusResult();
			$result->addError(new Error(Loc::getMessage('SALE_DLV_SRV_ADD_T_ERROR_DATA')));
		}

		return $result;
	}

	/**
	 * @param array $trackingNumbers
	 * @return Result
	 */
	public function getStatuses(array $trackingNumbers)
	{
		/** @var AdditionalHandler $parentService */
		$parentService = $this->deliveryService->getParentService();

		if(!$parentService)
			return array();

		$statuses = $parentService->getTrackingStatuses($trackingNumbers);

		if(empty($statuses) || !is_array($statuses))
			return array();

		$resultData = array();

		foreach($statuses as $status)
		{
			$r = new StatusResult();
			$r->trackingNumber = $status['TRACKING_NUMBER'];

			if(isset($status['ERROR']))
			{
				$r->addError(new Error($status['ERROR']));
			}
			else
			{
				$r->status = $status['STATUS'];
				$r->description = $status['DESCRIPTION'];
				$r->lastChangeTimestamp = $this->translateDate($status['DATE']);
			}

			$resultData[$status['TRACKING_NUMBER']] = $r;
		}

		return $resultData;
	}


	protected static function translateDate($externalDate)
	{
		$date = new \DateTime($externalDate);
		return $date->getTimestamp();
	}

	/**
	 * @return array
	 */
	public function getParamsStructure()
	{
		return array();
	}

	/**
	 * @param string $trackingNumber
	 * @return string Url were we can see tracking information
	 */
	public function getTrackingUrl($trackingNumber = '')
	{
		$result = '';

		/** @var \Sale\Handlers\Delivery\AdditionalHandler  $parentService */
		$parentService = $this->deliveryService->getParentService();
		$trackingUrlTempl = $parentService->getTrackingUrlTempl();

		if(!empty($trackingUrlTempl))
			$result = str_replace('##TRACKING_NUMBER##', urlencode($trackingNumber), $trackingUrlTempl);

		return $result;
	}
}