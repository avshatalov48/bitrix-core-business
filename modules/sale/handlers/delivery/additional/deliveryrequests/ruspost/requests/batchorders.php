<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;

Loc::loadMessages(__FILE__);

/**
 * Class BatchOrders
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Receives data about shipments from the batch
 * https://otpravka.pochta.ru/specification#/batches-get_info_about_orders_in_batch
 */
class BatchOrders extends Base
{
	protected $path = "/1.0/batch/{name}/shipment";
	protected $type = HttpClient::HTTP_GET;

	/**
	 * @param array $requestData
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function send(array $requestData = array(), array $additional = array())
	{
		if(empty($requestData['BATCH_NAME']))
		{
			$result = new Requests\Result();
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCO_01')));
			return $result;
		}

		$this->path = str_replace('{name}', $requestData['BATCH_NAME'], $this->path);
		unset($requestData['BATCH_NAME']);
		return parent::send($requestData, $additional);
	}
}