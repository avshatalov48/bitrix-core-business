<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Reference;

Loc::loadMessages(__FILE__);

/**
 * Class BatchDateUpdate
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Changes sending date
 * https://otpravka.pochta.ru/specification#/batches-sending_date
 */
class BatchDateUpdate extends Base
{
	protected $path = "/1.0/batch/{name}/sending/{year}/{month}/{dayOfMonth}";
	protected $type = HttpClient::HTTP_POST;

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

		if(empty($additional['DATE']))
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDU_01')));
			return $result;
		}

		try
		{
			$date = new Main\Type\Date($additional['DATE']);
		}
		catch (Main\ObjectException $exception)
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDU_02')));
			return $result;
		}

		if(count($requestIds) !== 1)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDU_03')));
			return $result;
		}

		$requestId = current($requestIds);

		if(intval($requestId) <= 0)
		{
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDU_04')));
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
			$result->addError( new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDU_05')));
			return $result;
		}

		$this->path = str_replace(
			array(
				'{name}',
				'{year}',
				'{month}',
				'{dayOfMonth}'
			),
			array(
				$row['EXTERNAL_ID'],
				$date->format('Y'),
				$date->format('m'),
				$date->format('d')
			),
			$this->path
		);

		return $result = $this->send();
	}

	/**
	 * @param int[] $requestIds
	 * @return array
	 */
	public function getFormFields(array $requestIds)
	{
		$date = new Main\Type\Date();
		$date->add('P1D');

		return array(
			"DATE" => array(
				"TYPE" => "DATE",
				"TIME" => "N",
				"TITLE" => Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCDU_06'),
				"VALUE" => $date->toString(),
				"REQUIRED" => "Y"
			)
		);
	}
}