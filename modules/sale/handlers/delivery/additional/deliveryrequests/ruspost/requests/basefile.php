<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;

Loc::loadMessages(__FILE__);

/**
 * Class BaseFile
 * Base class for downloading requests.
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 */
abstract class BaseFile extends Base
{
	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\ResultFile
	 */
	public function process(array $shipmentIds, array $additional = array())
	{
		$result = new Requests\ResultFile();

		if(count($shipmentIds) !== 1)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASEF_01')));
			return $result;
		}

		$shipmentId = current($shipmentIds);

		if(intval($shipmentId) <= 0)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASEF_02')));
			return $result;
		}

		$res = Requests\ShipmentTable::getList(array(
			'filter' => array(
				'=SHIPMENT_ID' => $shipmentId,
				'=REQUEST.DELIVERY_ID' => $this->deliveryService->getId()
			)
		));

		$row = $res->fetch();

		if(!$row || $row['EXTERNAL_ID'] == '')
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_DLVRS_ADD_DREQ_RBASEF_03',
						array('#SHIPMENT_LINK#' => Requests\Helper::getShipmentEditLink($shipmentId))
			)));

			return $result;
		}

		$this->path = str_replace('{id}', $row['EXTERNAL_ID'], $this->path);
		return $this->send();
	}

	/**
	 * @param array $requestData
	 * @param array $additional
	 * @return Requests\ResultFile
	 */
	public function send(array $requestData = array(), array $additional = array())
	{
		$result = new Requests\ResultFile();
		$httpRes = false;

		if(@$this->httpClient->query($this->type, $this->getUrl()))
			$httpRes = $this->httpClient->getResult();

		$errors = $this->httpClient->getError();

		if (!$httpRes && !empty($errors))
		{
			$result = new Requests\Result();

			foreach($errors as $errorCode => $errMes)
			{
				if($errMes == 'Socket connection error.')
				{
					$errMes =  Loc::getMessage(
						'SALE_DLVRS_ADD_DREQ_RBASE_SEND_ERROR',
						array('#URL#' => $this->url)
					);
				}

				$result->addError(new Main\Error($errMes, $errorCode));
			}
		}
		else
		{
			$status = $this->httpClient->getStatus();

			if ($status != 200)
			{
				if($status == 403)
					$errorMsg = Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASEF_04');
				elseif($status == 404)
					$errorMsg = Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASEF_05');
				elseif($status == 500)
					$errorMsg = Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBASEF_06');
				else
					$errorMsg = 'Http status: '.$status;

				$result->addError(new Main\Error($errorMsg,'STATUS_'.$status));
			}
		}

		if($result->isSuccess())
		{
			$headers = $this->httpClient->getHeaders();
			$fileName = $headers->getFilename();

			if($fileName <> '')
			{
				$ext = '';
				$contentType = $headers->getContentType();

				if($contentType == 'application/zip')
				{
					$ext = 'zip';
				}
				elseif($contentType == 'application/pdf')
				{
					$ext = 'pdf';
				}

				if($ext <> '')
				{
					$fileName .= '.'.$ext;
				}

				$result->setFileName($fileName);
				$result->setFileContent($httpRes);
			}
		}

		return $result;
	}
}