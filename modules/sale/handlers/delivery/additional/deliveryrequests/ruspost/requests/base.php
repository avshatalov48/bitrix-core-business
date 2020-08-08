<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Error;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Delivery;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;
use Sale\Handlers\Delivery\AdditionalHandler;

Loc::loadMessages(__FILE__);

/**
 * Class Base
 * Base class for requests of all types.
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 */
abstract class Base
{
	/** @var HttpClient */
	protected $httpClient = null;
	protected $url = "https://otpravka-api.pochta.ru";

	protected $path = "";
	protected $type = "";

	/** @var AdditionalHandler  */
	protected $deliveryService = null;

	/**
	 * Base constructor.
	 * @param Delivery\Services\Base $deliveryService
	 */
	public function __construct(Delivery\Services\Base $deliveryService, HttpClient $httpClient)
	{
		$this->deliveryService = $deliveryService;
		$deliveryConfig = $deliveryService->getConfigValues();

		$this->httpClient = $httpClient;

		$this->httpClient->setHeader("Authorization", "AccessToken ".$deliveryConfig['MAIN']['OTPRAVKA_AUTH_TOKEN']);
		$this->httpClient->setHeader("X-User-Authorization", "Basic ".$deliveryConfig['MAIN']['OTPRAVKA_AUTH_KEY']);
		$this->httpClient->setHeader("Content-Type", "application/json;charset=UTF-8");
	}

	/**
	 * @return string
	 */
	protected function getUrl()
	{
		return $this->url.$this->path;
	}

	/**
	 * @param array $rawData
	 * @param array $requestData
	 * @return Requests\Result
	 */
	protected function convertResponse($rawData, $requestData)
	{
		$result = new Requests\Result();
		$result->setData($rawData);
		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function process(array $shipmentIds, array $additional = array())
	{
		$bodyResult = $this->createBody($shipmentIds, $additional);

		if(!$bodyResult->isSuccess())
			return $bodyResult;

		$result = $this->send($bodyResult->getData(), $additional);
		$result->addResults($bodyResult->getResults());
		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function createBody(array $shipmentIds, array $additional = array())
	{
		return new Requests\Result();
	}

	/**
	 * @param array $requestData
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function send(array $requestData = array(), array $additional = array())
	{
		$result = new Requests\Result();
		$jsonData = !empty($requestData) ? Json::encode($requestData) : null;
		$httpRes = false;

		if(@$this->httpClient->query($this->type, $this->getUrl(), $jsonData))
		{
			$httpRes = $this->httpClient->getResult();
		}

		$errors = $this->httpClient->getError();

		if(!$httpRes && !empty($errors))
		{
			foreach($errors as $errorCode => $errMes)
			{
				if($errMes == 'Socket connection error.')
				{
					$errMes =  Loc::getMessage(
						'SALE_DLVRS_ADD_DREQ_RBASE_SEND_ERROR',
						array('#URL#' => $this->url)
					);
				}

				$result->addError(new Error($errMes, $errorCode));
			}
		}
		else
		{
			$response = array();

			try
			{
				$response = Json::decode($httpRes);
			}
			catch(\Exception $e)
			{
				$result->addError(new Error($e->getMessage()));
			}

			$status = $this->httpClient->getStatus();

			if ($status != 200)
			{
				$message = '';

				if($status == 404)
				{
					$message = Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASE_01');

					if(!empty($response))
						if($response['code'] == '1001' && $response['sub-code'] == 'RESOURCE_NOT_FOUND')
							$message = Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASE_02');
				}
				elseif($status == 401)
				{
					$message = Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASE_03');;
				}
				elseif($status == 500)
				{
					$message = Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASE_INTERNAL_ERROR');;
				}

				if($message == '')
					$message = $message.' ('.Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASE_HTTP_STATUS').' '.$status.')';

				$result->addError(new Error($message,'STATUS_'.$status));
			}
			else
			{
				$convertResult = $this->convertResponse($response, $requestData);

				if(!$convertResult->isSuccess())
					$result->addErrors($convertResult->getErrors());

				$result->addResults($convertResult->getResults());
				$result->setData($convertResult->getData());
			}
		}

		return $result;
	}

	/**
	 * @param int[] $entityIds
	 * @return array
	 */
	public function getFormFields(array $entityIds)
	{
		return array();
	}
}