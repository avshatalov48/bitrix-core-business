<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Shipment;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;
use Bitrix\Sale\Delivery\Services;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests\Base as RequestBase;

Loc::loadMessages(__FILE__);

class Handler extends Requests\HandlerBase
{
	/** @var HttpClient */
	protected $httpClient = null;

	/**
	 * Handler constructor.
	 * @param Services\Base $deliveryService
	 */
	public function __construct(Services\Base $deliveryService)
	{
		parent::__construct($deliveryService);
		self::registerLocalClasses();
	}

	/**
	 * @return null
	 */
	protected function getHttpClient()
	{
		if($this->httpClient === null)
		{
			$this->httpClient = new HttpClient(array(
				"version" => "1.1",
				"socketTimeout" => 30,
				"streamTimeout" => 30,
				"redirect" => true,
				"redirectMax" => 5,
			));
		}

		return $this->httpClient;
	}

	/**
	 * @param HttpClient $httpClient
	 */
	public function setHttpClient(HttpClient $httpClient): void
	{
		$this->httpClient = $httpClient;
	}

	/**
	 * Register local classes.
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected static function registerLocalClasses()
	{
		static $registered = false;

		if($registered)
			return;

		$path =  'handlers/delivery/additional/deliveryrequests/ruspost/requests/';
		$namespace = __NAMESPACE__.'\Requests\\';
		$requestClasses = array(
			'Base', 'BaseFile', 'OrderCreate', 'OrderDelete', 'CleanAddress', 'NormalizeFio', 'BatchCreate',
			'OrderDocF7P', 'OrderDocForms', 'BatchOrderAdd', 'BatchesList', 'BatchOrders', 'BatchDocF103',
			'BatchDocPrepare', 'BatchDateUpdate', 'BatchDocAll', 'Batch', 'BatchOrder', 'OPS', 'OrderDocF112EK',
			'UserSettings', 'UnreliableRecipient'
		);
		$classes = array(
			__NAMESPACE__.'\Reference' => 'handlers/delivery/additional/deliveryrequests/ruspost/reference.php'
		);

		foreach($requestClasses as $className)
			$classes[$namespace.$className] = $path.mb_strtolower($className).'.php';

		Loader::registerAutoLoadClasses('sale', $classes);
		$registered = true;
	}

	/**
	 * @param int $requestId
	 * @return array
	 */
	public function getActions($requestId)
	{
		return array(
			'BATCH_DOC_PREPARE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_BATCH_DOC_PREPARE'),
			'BATCH_DOC_ALL' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_BATCH_DOC_ALL'),
			'BATCH_DOC_F103' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_BATCH_DOC_F103'),
			'BATCH_DATE_UPDATE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_BATCH_DATE_UPDATE')
		);
	}

	/**
	 * @param int $requestId
	 * @param string $actionType
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function executeAction($requestId, $actionType, array $additional)
	{
		return $this->getRequestObject($actionType)->process(array($requestId), $additional);
	}

	/**
	 * @param Shipment $shipment
	 * @return array
	 */
	public function getShipmentActions(Shipment $shipment)
	{
		if(Requests\Manager::isShipmentSent($shipment->getId()))
		{
			$result = array(
				'ORDER_DOC_CREATE' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_ORDER_DOC_CREATE'),
				'ORDER_DOC_F7P' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_ORDER_DOC_F7P'),
			);

			$delivery = Services\Manager::getObjectById($shipment->getDeliveryId());
			$deliveryConfig = $delivery ? $delivery->getConfigValues() : [];

			//cache on delivery
			if($deliveryConfig['MAIN']['CATEGORY'] == 4)
				$result['ORDER_DOC_F112EK'] = Loc::getMessage('SALE_DLVRS_ADD_DREQ_ORDER_DOC_F112EK');
		}
		else
		{
			$result = array();
		}

		return $result;
	}

	/**
	 * @param int $requestId
	 * @param int $shipmentId
	 * @param string $actionType
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function executeShipmentAction($requestId, $shipmentId, $actionType, array $additional)
	{
		return $this->getRequestObject($actionType)->process(array($shipmentId), $additional);
	}

	/**
	 * @param string $requestType
	 * @param array $requestData
	 * @return Requests\Result
	 */
	public function send($requestType, array $requestData)
	{
		$result = new Requests\Result();
		$request = $this->getRequestObject($requestType);

		if(!$request)
		{
			$result->addError(
				new Error(
					Loc::getMessage('SALE_DLVRS_ADD_DREQ_UNKNOWN_TYPE'),
					array('#REQUEST_TYPE#' => $requestType)
			));
			return $result;
		}

		return $request->send($requestData);
	}

	/**
	 * Creates delivery request
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function create(array $shipmentIds, array $additional = array())
	{
		return $this->getRequestObject('BATCH_CREATE')->process($shipmentIds, $additional);
	}

	/**
	 * @param int $requestId
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function addShipments($requestId, $shipmentIds, $additional = [])
	{
		$result = new Requests\Result();
		$request = $this->getRequestObject('BATCH_ORDER_ADD');
		$requestFields = Requests\RequestTable::getById($requestId)->fetch();

		if(empty($requestFields['EXTERNAL_ID']))
		{
			$result->addError(
				new Error(
					Loc::getMessage('SALE_DLVRS_ADD_DREQ_UNKNOWN_TYPE'),
					array('#REQUEST_ID#' => $requestId)
				));

			return $result;
		}

		$result = $request->process(
			$shipmentIds,
			array_merge(
				$additional,
				array(
					'BATCH_NAME' => $requestFields['EXTERNAL_ID'],
					'REQUEST_ID' => $requestFields['ID']
				)
			)
		);

		return $result;
	}

	/**
	 * @param int $requestId
	 * @return Requests\Result
	 */
	public function delete($requestId)
	{
		 return $this->deleteShipments($requestId);
	}

	/**
	 * @param int $formFieldsType
	 * @param int[] $entityIds
	 * @param array $additional
	 * @return array
	 */
	public function getFormFields($formFieldsType, array $entityIds, array $additional = array())
	{
		if ($formFieldsType == Requests\Manager::FORM_FIELDS_TYPE_CREATE)
		{
			$date = new Date();
			$date->add('P1D');

			$result = array(
				"DATE" => array(
					"TYPE" => "DATE",
					"TIME" => "N",
					"TITLE" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SEND_DAY'),
					"VALUE" => $date->toString(),
					"REQUIRED" => "Y",
				)
			);

			$opsData = $this->getOpsData();
			if (!is_null($opsData))
			{
				$result["OPS"] = $opsData;
			}
		}
		elseif ($formFieldsType == Requests\Manager::FORM_FIELDS_TYPE_ADD)
		{
			$result = array();

			$opsData = $this->getOpsData();
			if (!is_null($opsData))
			{
				$result["OPS"] = $opsData;
			}
		}
		elseif($formFieldsType == Requests\Manager::FORM_FIELDS_TYPE_ACTION)
		{
			$result = $this->getRequestObject($additional['ACTION_TYPE'])->getFormFields($entityIds);
		}
		else
		{
			$result = array();
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	private function getOpsData(): ?array
	{
		$opsList = array();
		$ops = $this->getDeliveryServiceOps();

		if(!empty($ops['VALUE']) && isset($ops['NAME']))
		{
			$opsList[$ops['VALUE']] = $ops['NAME'];
		}
		else
		{
			//todo: cache
			$opsRes = $this->getRequestObject('OPS')->send();

			if($opsRes->isSuccess())
			{
				foreach($opsRes->getData() as $ops)
					if($ops['enabled'] == true)
						$opsList[$ops['operator-postcode']] = '('.$ops['operator-postcode'].') '.$ops['ops-address'];
			}
		}

		if(!empty($opsList))
		{
			return array(
				"TYPE" => "ENUM",
				"TITLE" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_OPS'),
				"REQUIRED" => "Y",
				"OPTIONS" => $opsList
			);
		}

		return null;
	}
	protected function getDeliveryServiceOps()
	{
		return \Sale\Handlers\Delivery\Additional\RusPost\Helper::getSelectedShippingPoint($this->deliveryService);
	}

	/**
	 * @param string $type
	 * @return null|RequestBase
	 */
	public function getRequestObject($type)
	{
		$result = null;
		$requests = array(
			'NORMALIZE_FIO' => 'NormalizeFio',
			'CLEAN_ADDRESS' => 'CleanAddress',
			'ORDER_CREATE' => 'OrderCreate',
			'ORDER_DELETE' => 'OrderDelete',
			'BATCH_CREATE' => 'BatchCreate',
			'ORDER_DOC_F7P' => 'OrderDocF7P',
			'ORDER_DOC_CREATE' => 'OrderDocForms',
			'BATCH_ORDER_ADD' => 'BatchOrderAdd',
			'BATCHES_LIST' => 'BatchesList',
			'BATCH_ORDERS' => 'BatchOrders',
			'BATCH_DOC_F103' => 'BatchDocF103',
			'BATCH_DOC_PREPARE' => 'BatchDocPrepare',
			'BATCH_DATE_UPDATE' => 'BatchDateUpdate',
			'BATCH_DOC_ALL' => 'BatchDocAll',
			'BATCH' => 'Batch',
			'BATCHORDER' => 'BatchOrder',
			'OPS' => 'OPS',
			'USER_SETTINGS' => 'UserSettings',
			'ORDER_DOC_F112EK' => 'OrderDocF112EK',
			'UNRELIABLE_RECIPIENT' => 'UnreliableRecipient'
		);

		if(isset($requests[$type]))
		{
			$className = __NAMESPACE__.'\Requests\\'.$requests[$type];
			$result = new $className($this->deliveryService, $this->getHttpClient());
		}

		return $result;
	}

	/**
	 * @param string $field
	 * @return mixed
	 */
	protected function getContentFieldName($field)
	{
		$fields = array(
			"batch-name" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_BN'),
			"batch-status" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_BS'),
			"batch-status-date" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_BSD'),
			"delivery-notice-payment-method" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_DNPM'),
			"list-number" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_LN'),
			"list-number-date" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_LND'),
			"mail-category" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_MC'),
			"mail-category-text" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_MCT'),
			"mail-type" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_MT'),
			"mail-type-text" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_MTT'),
			"payment-method" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_MTT'),
			"postoffice-code" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_PC'),
			"postoffice-name" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_PN'),
			"shipment-count" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_SC'),
			"shipment-insure-rate-sum" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_SIRS'),
			"shipment-insure-rate-vat-sum" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_SIRVS'),
			"shipment-mass" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_SM'),
			"shipment-mass-rate-sum" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_SMRS'),
			"shipment-mass-rate-vat-sum" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_SMRVS'),
			"shipping-notice-type" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_SNT'),
			"transport-type" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_TT'),
			"postmarks" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_PM'),
		);

		return isset($fields[$field]) ? $fields[$field] : $field;
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @return string
	 */
	protected function getContentFieldValue($field, $value)
	{
		$result = $value;

		if($field == 'batch-status')
			$result = Reference::getBatchStatus($value);
		elseif($field == 'delivery-notice-payment-method')
			$result = Reference::getPaymentMethod($value);
		elseif($field == 'payment-method')
			$result = Reference::getPaymentMethod($value);
		elseif($field == 'shipping-notice-type')
			$result = Reference::getShipmentNoticeType($value);
		elseif($field == 'transport-type')
			$result = Reference::getTransportType($value);
		elseif($field == 'postmarks')
		{
			$result = implode(', ',
				array_map(
					array('\Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Reference', 'getPostmarkType'),
					$value
				)
			);
		}

		if(is_array($result))
			$result = implode(', ', $result);

		return $result;
	}

	/**
	 * @param int $requestId
	 * @return Requests\Result
	 */
	public function getContent($requestId)
	{
		$result = $this->getRequestObject('BATCH')->process(array($requestId));
		$fields = array();

		if($result->isSuccess())
		{
			foreach($result->getData() as $field => $value)
			{
				$fields[$field] = array(
					"TITLE" => self::getContentFieldName($field),
					"VALUE" => self::getContentFieldValue($field, $value)
				);
			}
		}

		$result->setData($fields);
		return $result;
	}

	/**
	 * @param int $requestId
	 * @param array $shipmentIds
	 * @return Requests\Result
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function deleteShipments($requestId, array $shipmentIds = array())
	{
		$ids = array();
		$filter = array('=REQUEST_ID' => $requestId);

		if(!empty($shipmentIds))
			$filter['=SHIPMENT_ID'] = $shipmentIds;

		$res = Requests\ShipmentTable::getList(array(
			'filter' => $filter
		));

		while($row = $res->fetch())
			if(intval($row['SHIPMENT_ID']) > 0)
				$ids[] = $row['SHIPMENT_ID'];

		if(!empty($ids))
			$result = $this->getRequestObject('ORDER_DELETE')->process($ids);
		else
			$result =  new Requests\Result();

		return $result;
	}

	/**
	 * Update shipment from delivery request
	 * @param int $requestId
	 * @param int[] $shipmentIds
	 * @return Requests\Result
	 */
	public function updateShipments($requestId, array $shipmentIds = array())
	{
		$result = $this->addShipments($requestId, $shipmentIds);

		if(!$result->isSuccess())
			return $result;

		$idsToDel = array();
		$queries = array();
		$con = Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		/** @var Requests\RequestResult $reqRes */
		foreach($result->getRequestResults() as $reqRes)
		{
			if($reqRes->isSuccess())
			{
				/** @var Requests\ShipmentResult $shpRes */
				foreach($reqRes->getShipmentResults() as $shpRes)
				{
					if($shpRes->isSuccess())
					{
						$shpInternalId = intval($shpRes->getInternalId());
						$idsToDel[] = $shpInternalId;
						$externalId = $sqlHelper->forSql($shpRes->getExternalId());
						$queries[$shpInternalId] = "UPDATE b_sale_delivery_req_shp SET EXTERNAL_ID='".$externalId."' WHERE REQUEST_ID=".intval($requestId)." AND SHIPMENT_ID=".$shpInternalId;
					}
				}
			}
		}

		$failedShipments = array();

		if(!empty($idsToDel))
		{
			$res = $this->deleteShipments($requestId, array_values($idsToDel));

			if(!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
				return $result;
			}

			foreach($res->getShipmentResults() as $shpRes)
				if(!$shpRes->isSuccess())
					$failedShipments[$shpRes->getInternalId()] = $shpRes;
		}

		if(!empty($failedShipments))
		{
			$results = $result->getRequestResults();

			foreach($results as $resId => $reqRes)
			{
				if($reqRes->isSuccess())
				{
					$shpResults = $reqRes->getShipmentResults();
					/** @var Requests\ShipmentResult $shpRes */
					foreach($shpResults as $resShpId => $shpRes)
						if(isset($failedShipments[$resShpId]))
							$shpResults[$resShpId]->addErrors($failedShipments[$resShpId]->getErrors());

					$results[$resId]->setResults($shpResults);
				}
			}

			$result->setResults($results);
		}

		if(!empty($queries))
			foreach($queries as $shpId => $query)
				if(!isset($failedShipments[$shpId]))
					$con->queryExecute($query);

		return $result;
	}

	/**
	 * @param string $field
	 * @return mixed
	 */
	protected function getShipmentContentFieldName($field)
	{
		$fields = array(
			'address-type-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ATT'),
			'area-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_AT'),
			'avia-rate' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_AR'),
			'avia-rate-with-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ARWV'),
			'avia-rate-wo-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ARWOV'),
			'barcode' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_B'),
			'batch-category' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_BC'),
			'batch-name' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_BN'),
			'batch-status' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_BS'),
			'brand-name' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_BRN'),
			'building-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_BT'),
			'comment' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_C'),
			'corpus-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_CT'),
			'delivery-time' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_DT'),
			'dimension' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_D'),
			'envelope-type' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ET'),
			'fragile-rate-with-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_FRWV'),
			'fragile-rate-wo-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_FRWOV'),
			'given-name' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_GN'),
			'ground-rate' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_GR'),
			'ground-rate-with-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_GRWV'),
			'ground-rate-wo-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_GRWOV'),
			'hotel-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_HT'),
			'house-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_HT2'),
			'human-operation-name' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_HON'),
			'id' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ID'),
			'index-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_IT'),
			'insr-rate' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_IR'),
			'insr-rate-with-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_IRWV'),
			'insr-rate-wo-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_IRWOV'),
			'insr-value' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_IV'),
			'is-deleted' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ID'),
			'last-oper-attr' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_LOA'),
			'last-oper-type' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_LOT'),
			'letter-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_LT'),
			'location-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_LOCT'),
			'mail-category' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_MC'),
			'mail-direct' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_MD'),
			'mail-rank' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_MR'),
			'mail-type' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_MT'),
			'mass' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_M'),
			'mass-rate' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_MRA'),
			'mass-rate-with-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_MRAWV'),
			'mass-rate-wo-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_MRAWOV'),
			'middle-name' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_MN'),
			'notice-rate-with-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_NRWV'),
			'notice-rate-wo-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_NRWOV'),
			'num-address-type-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_NATT'),
			'order-num' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ON'),
			'oversize-rate-with-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ORWV'),
			'oversize-rate-wo-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ORWOV'),
			'payment' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_P'),
			'payment-method' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_PM'),
			'place-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_PT'),
			'postmarks' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_POMA'),
			'postoffice-code' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_F_PC'),
			'recipient-name' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_RN'),
			'region-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_RT'),
			'room-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ROTO'),
			'slash-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_ST'),
			'street-to' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_STT'),
			'surname' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_S'),
			'tel-address' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_TA'),
			'total-rate-wo-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_TRWV'),
			'total-vat' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_TV'),
			'transport-type' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_TT'),
			'version' => Loc::getMessage('SALE_DLVRS_ADD_DREQ_SF_V')
		);

		return isset($fields[$field]) ? $fields[$field] : $field;
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @return string
	 */
	protected function getShipmentContentFieldValue($field, $value)
	{
		$result = $value;

		if($field == 'address-type-to')
			$result = Reference::getAddressType($value);
		elseif($field == 'batch-category')
			$result = Reference::getRpoCategory($value);
		elseif($field == 'batch-status')
			$result = Reference::getBatchStatus($value);
		elseif($field == 'delivery-time')
			$result = $value['max-days'].' - '.$value['min-days'];
		elseif($field == 'dimension')
			$result = $value['length'].' X '.$value['width'].' X '.$value['height'];
		elseif($field == 'envelope-type')
			$result = Reference::getEnvelopeType($value);
		elseif($field == 'last-oper-attr')
			$result = Reference::getTrackingAttr($value);
		elseif($field == 'last-oper-type')
			$result = Reference::getTrackingAttr($value);
		elseif($field == 'mail-category')
			$result = Reference::getRpoCategory($value);
		elseif($field == 'mail-rank')
			$result = Reference::getMailRank($value);
		elseif($field == 'mail-type')
			$result = Reference::getRpoKind($value);
		elseif($field == 'payment-method')
			$result = Reference::getPaymentMethod($value);
		elseif($field == 'transport-type')
			$result = Reference::getTransportType($value);
		elseif($field == 'postmarks')
		{
			$result = implode(', ',
				array_map(
					array(__NAMESPACE__.'\\Reference', 'getPostmarkType'),
					$value
				)
			);
		}

		if(is_array($result))
			$result = implode(', ', $result);

		return $result;
	}

	/**
	 * @param int $requestId
	 * @param int $shipmentId
	 * @return Requests\Result
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getShipmentContent($requestId, $shipmentId)
	{
		$result = new Requests\Result();

		if(intval($shipmentId) <= 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_ERR_SID_NULL')));
			return $result;
		}

		$res = Requests\ShipmentTable::getList(array(
			'filter' => array(
				'=SHIPMENT_ID' => $shipmentId
			),
		));

		$row = $res->fetch();

		if(!$row || (string)$row['EXTERNAL_ID'] === '')
		{
			$result->addError(
				new Error(
					Loc::getMessage(
						'SALE_DLVRS_ADD_DREQ_ERR_SHIPMENT_NOT_INCLUDED',
						array('#SHIPMENT_ID#' => $shipmentId)
			)));

			return $result;
		}

		$res = $this->getRequestObject('BATCHORDER')->process(array($shipmentId));

		$fields = array();

		if($res->isSuccess())
		{
			$data = $res->getData();

			if(!empty($data))
			{
				foreach($data as $field => $value)
				{
					$fields[$field] = array(
						"TITLE" => $this->getShipmentContentFieldName($field),
						"VALUE" => $this->getShipmentContentFieldValue($field, $value),
					);
				}

				if(!empty($fields))
					$result->setData($fields);
			}
		}
		else
		{
			$result->addErrors($res->getErrors());
		}

		return $result;
	}
}