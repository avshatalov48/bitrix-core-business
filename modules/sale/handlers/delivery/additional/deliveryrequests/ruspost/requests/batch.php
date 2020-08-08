<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Delivery\Requests;

Loc::loadMessages(__FILE__);

/**
 * Class Batch
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Searches batch by name
 * https://otpravka.pochta.ru/specification#/batches-find_batch
 */
class Batch extends Base
{
	protected $path = "/1.0/batch/{name}";
	protected $type = HttpClient::HTTP_GET;
	protected $internalId = 0;
	protected $externalId = '';

	/**
	 * @param array $rawData
	 * @param array $requestData
	 * @return Requests\RequestResult
	 */
	protected function convertResponse($rawData, $requestData)
	{
		$result = new Requests\RequestResult();

		if(isset($rawData['batch-name']))
		{
			$result->setExternalId($rawData['batch-name']);
			$result->setInternalId($this->internalId);
			$result->setData($rawData);
		}
		else
		{
			$result->addError(
				new Error(
					Loc::getMessage(
						'SALE_DLVRS_ADD_DREQ_RBATCH_01',
						array(
							'#INTERNAL_ID#' => $this->internalId,
							'#EXTERNAL_ID#' => $this->externalId
						)
					),
					Requests\RequestResult::ERROR_NOT_FOUND
			));
		}

		return $result;
	}

	/**
	 * @param int[] $requestIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function process(array $requestIds, array $additional = array())
	{
		$result = new Requests\Result();

		if(count($requestIds) !== 1)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCH_02')));
			return $result;
		}

		$requestId = current($requestIds);

		if(intval($requestId) <= 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCH_03')));
			return $result;
		}

		$res =Requests\RequestTable::getList(array(
			'filter' => array(
				'=ID' => $requestId
			)
		));

		$row = $res->fetch();

		if(!$row || $row['EXTERNAL_ID'] == '')
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCH_04')));
			return $result;
		}

		$this->externalId = $row['EXTERNAL_ID'];
		$this->internalId= $row['INTERNAL_ID'];
		$this->path = str_replace('{name}', $this->externalId, $this->path);
		return $this->send(array(), $additional);
	}
}