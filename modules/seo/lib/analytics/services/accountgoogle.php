<?

namespace Bitrix\Seo\Analytics\Services;

use Bitrix\Main\NotImplementedException;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Web\Json;

use Bitrix\Seo\Analytics;
use Bitrix\Seo\Retargeting;

class AccountGoogle extends Analytics\Account
{
	const TYPE_CODE = 'google';

	public function getList()
	{
		$response = $this->request->send(array(
			'methodName' => 'analytics.account.list',
			'parameters' => array()
		));

		return $response;
	}

	public function getProfile()
	{
		$response = $this->getRequest()->getClient()->get(
			'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' .
			urlencode($this->getRequest()->getAuthAdapter()->getToken())
		);

		if ($response)
		{
			$response = Json::decode($response);
			if (is_array($response))
			{
				return array(
					'ID' => $response['id'],
					'NAME' => $response['name'],
					'LINK' => '',
					'PICTURE' => $response['picture'],
				);
			}
		}


		return null;
	}

	/**
	 * @param $accountId
	 * @param Date|null $dateFrom
	 * @param Date|null $dateTo
	 * @return Retargeting\Response
	 */
	public function getExpenses($accountId, Date $dateFrom = null, Date $dateTo = null)
	{
		$parameters = [
			'ACCOUNT_ID' => $accountId,
		];
		if($dateFrom && $dateTo)
		{
			$parameters['DATE_FROM'] = $dateFrom->format('Ymd');
			$parameters['DATE_TO'] = $dateTo->format('Ymd');
		}
		$response = $this->getRequest()->send([
			'methodName' => 'analytics.expenses.get',
			'parameters' => $parameters,
		]);

		if($response->isSuccess())
		{
			$data = $response->getData();
			$response->setData([
				'expenses' => new Analytics\Internals\Expenses([
					'impressions' => $data['Impressions'],
					'clicks' => $data['Clicks'],
					'actions' => $data['Interactions'],
					'cpc' => $data['Avg. CPC'],
					'cpm' => $data['Avg. CPM'],
					'spend' => $data['Cost'],
					'currency' => $data['Currency'],
				]),
			]);
		}

		return $response;
	}

	/**
	 * Get expenses report.
	 *
	 * @param $accountId
	 * @param Date|null $dateFrom
	 * @param Date|null $dateTo
	 * @return Result
	 * @throws NotImplementedException
	 */
	public function getExpensesReport($accountId, Date $dateFrom = null, Date $dateTo = null)
	{
		$parameters = [
			'ACCOUNT_ID' => $accountId,
		];
		if($dateFrom && $dateTo)
		{
			$parameters['DATE_FROM'] = $dateFrom->format('Ymd');
			$parameters['DATE_TO'] = $dateTo->format('Ymd');
		}
		$response = $this->getRequest()->send([
			'methodName' => 'analytics.expenses.report',
			'parameters' => $parameters,
		]);

		return $response;
	}


	/**
	 * Return true if it has expenses report.
	 *
	 * @return bool
	 */
	public function hasExpensesReport()
	{
		return true;
	}

	/**
	 * @param $accountId
	 * @param array $params
	 * @param array $publicPageIds
	 * @return Result
	 */
	public function updateAnalyticParams($accountId, array $params, array $publicPageIds = [])
	{
		$result = new Result();
		if(!isset($params['phone']) && !isset($params['url_tags']))
		{
			return $result;
		}
		$parameters = [
			'ACCOUNT_ID' => $accountId,
		];
		if(isset($params['phone']))
		{
			$phoneNumber = Parser::getInstance()->parse($params['phone']);
			$parameters['PHONE'] = ['COUNTRY_CODE' => mb_strtoupper($phoneNumber->getCountry()), 'NUMBER' => $phoneNumber->format()];
		}
		if(isset($params['url_tags']))
		{
			$parameters['URL_TAGS'] = $params['url_tags'];
		}

		return $this->getRequest()->send([
			'methodName' => 'analytics.update',
			'parameters' => $parameters,
		]);
	}
}