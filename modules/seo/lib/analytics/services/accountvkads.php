<?

namespace Bitrix\Seo\Analytics\Services;

use Bitrix\Main\Context;
use Bitrix\Seo\Analytics\Internals\Expenses;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Retargeting\Services\ResponseVkads;

class AccountVkads extends \Bitrix\Seo\Analytics\Account
{
	const TYPE_CODE = 'vkads';

	const MAX_ADS_EDIT = 20;
	const CURRENCY_CODE = 'RUB';

	protected static $listRowMap = array(
		'ID' => 'ACCOUNT_ID',
		'NAME' => 'NAME',
	);

	public function getList()
	{
		$result =  $this->getRequest()->send([
			'methodName' => 'analytics.account.list.full',
		]);
		if ($result->isSuccess())
		{
			$list = [];
			while ($item = $result->fetch())
			{
				if (
					(
						isset($item['CLIENT_NAME'])
						|| isset($item['NAME'])
					)
					&& isset($item['ID'])
				)
				{
					$list[] = [
						'NAME' => !empty(trim($item['CLIENT_NAME'])) ? $item['CLIENT_NAME'] : $item['NAME'],
						'ACCOUNT_ID' => $item['ID'],
					];
				}
			}
			$result->setData($list);
		}

		return $result;
	}

	public function getProfile()
	{
		$response = $this->getRequest()->send([
			'methodName' => 'analytics.profile',
			'parameters' => []
		]);

		if ($response->isSuccess() && $data = $response->fetch())
		{
			$result = [
				'ID' => $data['ID'],
				'NAME' => $data['FIRST_NAME'] . ' ' . $data['LAST_NAME'],
				'LINK' => 'https://ads.vk.com/hq/budget/transactions/',
			];

			$result['PICTURE'] = (Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http')
				. '://'
				.  Context::getCurrent()->getServer()->getHttpHost() . '/bitrix/images/seo/integration/vklogo.svg';

			return $result;
		}

		return null;
	}

	public function getExpenses($accountId, Date $dateFrom = null, Date $dateTo = null)
	{
		$result = new ResponseVkads();
		$params = [
			'id' => $accountId,
		];

		if($dateFrom && $dateTo)
		{
			$params['period'] = 'day';
			$params['date_from'] = $dateFrom->format('Y-m-d');
			$params['date_to'] = $dateTo->format('Y-m-d');
		}
		else
		{
			$params['period'] = 'summary';
			$params['date_from'] = '0';
			$params['date_to'] = '0';
		}
		$response = $this->getRequest()->send([
			'methodName' => 'analytics.expenses.get',
			'parameters' => $params,
		]);
		if($response->isSuccess())
		{
			$data = $response->getData();
			$expenses = new Expenses();
			$expenses->add([
				'impressions' => $data['shows'],
				'clicks' => $data['clicks'],
				'actions' => $data['clicks'],
				'spend' => $data['spent'],
				'currency' => static::CURRENCY_CODE,
			]);
			$result->setData(['expenses' => $expenses]);
		}
		else
		{
			$result->addErrors($response->getErrors());
		}

		return $result;
	}

	/**
	 * @param $accountId
	 * @param array $params
	 * @param array $publicPageIds
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateAnalyticParams($accountId, array $params, array $publicPageIds = [])
	{
		return new ResponseVkads();
	}
}
