<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Reference;

Loc::loadMessages(__FILE__);

/**
 * Class BatchDocPrepare
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Preparing and sending form 103
 * https://otpravka.pochta.ru/specification#/documents-checkin
 */
class BatchDocPrepare extends Base
{
	protected $path = "/1.0/batch/{name}/checkin?sendEmail=true";
	protected $type = Main\Web\HttpClient::HTTP_POST;

	/**
	 * @param array $rawData
	 * @param array $requestData
	 * @return Requests\Result
	 */
	protected function convertResponse($rawData, $requestData)
	{
		$result = new Requests\Result();

		if(!empty($rawData['error-code']))
			$result->addError(new Main\Error(Reference::getErrorDescription($rawData['error-code'], '/1.0/batch/{name}/checkin')));

		if(isset($rawData['f103-sent']) && $rawData['f103-sent'] == true)
			$result->addMessage(new Requests\Message(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDP_01')));

		return $result;
	}

	/**
	 * @param int[] $requestIds
	 * @param array $additional
	 * @return Requests\Result
	 * @throws Main\ArgumentException
	 */
	public function process(array $requestIds, array $additional = array())
	{
		$result = new Requests\Result();

		if(count($requestIds) !== 1)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDP_02')));
			return $result;
		}

		$requestId = current($requestIds);

		if(intval($requestId) <= 0)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDP_03')));
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
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDP_04')));
			return $result;
		}

		$this->path = str_replace('{name}', $row['EXTERNAL_ID'], $this->path);
		return $this->send();
	}
}