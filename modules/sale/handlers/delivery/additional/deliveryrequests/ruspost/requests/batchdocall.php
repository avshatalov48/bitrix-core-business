<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Sale\Delivery\Requests;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;

Loc::loadMessages(__FILE__);

/**
 * Class BatchDocAll
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Generates and receives full set of documents
 * https://otpravka.pochta.ru/specification#/documents-create_all_docs
 */
class BatchDocAll extends BaseFile
{
	protected $path = "/1.0/forms/{name}/zip-all";
	protected $type = Main\Web\HttpClient::HTTP_GET;

	/**
	 * @param int[] $requestIds
	 * @param array $additional
	 * @return Requests\Result|Requests\ResultFile
	 * @throws Main\ArgumentException
	 */
	public function process(array $requestIds, array $additional = array())
	{
		$result = new Requests\Result();

		if(count($requestIds) !== 1)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHDA_01')));
			return $result;
		}

		$requestId = current($requestIds);

		if(intval($requestId) <= 0)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHDA_02')));
			return $result;
		}

		$res =Requests\RequestTable::getList(array(
			'filter' => array(
				'=ID' => $requestId
			)
		));

		$row = $res->fetch();

		if(!$row || strlen($row['EXTERNAL_ID']) <= 0)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHDA_03')));
			return $result;
		}

		$this->path = str_replace('{name}', $row['EXTERNAL_ID'], $this->path);
		$result = $this->send();

		foreach($result->getErrors() as $error)
		{
			if($error->getCode() == 'STATUS_400')
			{
				/** @var \Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler $deliveryRequest */
				$deliveryRequest = $this->deliveryService->getDeliveryRequestHandler();
				$result = $deliveryRequest->getRequestObject('BATCH_DOC_PREPARE')->process($requestIds, array());

				if($result->isSuccess())
					$result = $this->send();

				break;
			}
		}

		return $result;
	}
}