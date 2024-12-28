<?php

namespace Bitrix\Seo\Analytics\Services;

use Bitrix\Main\Context;
use Bitrix\Main\Result;
use Bitrix\Seo\Analytics\Internals\Expenses;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Analytics\Internals\ExpensesCollection;
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

	public function hasDailyExpensesReport(): bool
	{
		return true;
	}

	public function getDailyExpensesReport(?string $accountId, ?Date $dateFrom, ?Date $dateTo): Result
	{
		$result = new Result();
		$params = [
			'id' => $accountId,
		];

		if ($dateFrom && $dateTo)
		{
			$params['date_from'] = $dateFrom->format('Y-m-d');
			$params['date_to'] = $dateTo->format('Y-m-d');
		}
		else
		{
			$params['date_from'] = '0';
			$params['date_to'] = '0';
		}

		$response = $this->getRequest()->send([
			'methodName' => 'analytics.campaigns.expenses.get',
			'parameters' => $params,
			'streamTimeout' => static::LOAD_DAILY_EXPENSES_TIMEOUT,
		]);

		if (!$response->isSuccess())
		{
			return $result->addErrors($response->getErrors());
		}

		$campaignList = $response->getData();
		if (!is_array($campaignList))
		{
			$result->setData(['expenses' => new ExpensesCollection()]);

			return $result;
		}

		$expensesCollection = new ExpensesCollection();
		foreach ($campaignList as $campaign)
		{
			if (!isset($campaign['rows']))
			{
				continue;
			}

			/** @var array{date: string, base: array} $row */
			foreach ($campaign['rows'] as $row)
			{
				if (isset($row['base']))
				{
					if ((float)$row['base']['spent'] > 0)
					{
						$expensesCollection->addItem(
							new Expenses([
								'date' => new Date($row['date'], 'Y-m-d'),

								'impressions' => $row['base']['shows'],
								'clicks' => $row['base']['clicks'],
								'actions' => $row['base']['clicks'],
								'spend' => $row['base']['spent'],
								'currency' => static::CURRENCY_CODE,

								'campaignName' => $campaign['name'],
								'campaignId' => $campaign['id'],
							])
						);
					}
				}
			}
		}

		$result->setData(['expenses' => $expensesCollection]);

		return $result;
	}
}
