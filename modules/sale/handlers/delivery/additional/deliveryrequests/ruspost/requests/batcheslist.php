<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Delivery\Requests;

/**
 * Class BatchesList
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Searching of all batches
 * https://otpravka.pochta.ru/specification#/batches-search_all_batches
 */
class BatchesList extends Base
{
	protected $path = "/1.0/batch";
	protected $type = HttpClient::HTTP_GET;

	/**
	 * @param array $rawData
	 * @param array $requestData
	 * @return Requests\Result
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function convertResponse($rawData, $requestData)
	{
		$result = new Requests\Result();

		/** @var Requests\RequestResult[] $batchesResults */
		$batchesResults = array();
		$externalIds = array();

		if(is_array($rawData) && !empty($rawData))
		{
			foreach($rawData as $batch)
			{
				$externalId = $batch['batch-name'];
				$externalIds[] = $externalId;
				$batchesResults[$externalId] = new Requests\RequestResult();
				$batchesResults[$externalId]->setExternalId($externalId);
				$batchesResults[$externalId]->setData($batch);
			}
		}

		if(!empty($externalIds))
		{
			$dbRes = Requests\RequestTable::getList(array(
				'filter' => array(
					'=EXTERNAL_ID' => $externalIds
				)
			));

			while($row = $dbRes->fetch())
				if(isset($batchesResults[$row['EXTERNAL_ID']]))
					$batchesResults[$row['EXTERNAL_ID']]->setInternalId($row['ID']);
		}

		if(!empty($batchesResults))
			$result->setData($batchesResults);

		return $result;
	}
}