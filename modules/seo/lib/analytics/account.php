<?php

namespace Bitrix\Seo\Analytics;

use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Retargeting;

abstract class Account extends Retargeting\Account
{
	protected const LOAD_DAILY_EXPENSES_TIMEOUT = 60;

	/**
	 * Get expenses.
	 *
	 * @param $accountId
	 * @param Date|null $dateFrom
	 * @param Date|null $dateTo
	 * @return Retargeting\Response
	 */
	abstract public function getExpenses($accountId, Date $dateFrom = null, Date $dateTo = null);


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
		throw new NotImplementedException();
	}


	/**
	 * Return true if it has expenses report.
	 *
	 * @return bool
	 */
	public function hasExpensesReport()
	{
		return false;
	}

	/**
	 * Return true if it has daily expenses report
	 *
	 * @return bool
	 */
	public function hasDailyExpensesReport(): bool
	{
		return false;
	}

	/**
	 * Get expenses report by day
	 *
	 * @return Result
	 */
	public function getDailyExpensesReport(?string $accountId, ?Date $dateFrom, ?Date $dateTo): Result
	{
		throw new NotImplementedException();
	}

	/**
	 * Return true if it has accounts.
	 *
	 * @return bool
	 */
	public function hasAccounts()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function hasPublicPages()
	{
		return false;
	}

	/**
	 * @param $accountId
	 * @param array $params
	 * @param array $publicPageIds
	 * @return Result
	 */
	abstract public function updateAnalyticParams($accountId, array $params, array $publicPageIds = []);

	/**
	 * Get public pages.
	 *
	 * @param string $accountId Account ID.
	 * @return Result
	 */
	public function getPublicPages($accountId)
	{
		return new Result();
	}

	/**
	 * Manage activity of keyword.
	 *
	 * @param string $accountId Account ID.
	 * @param string $groupId Group ID.
	 * @param string $id ID.
	 * @param bool $active Active.
	 * @return Result
	 * @throws NotImplementedException
	 */
	public function manageAdKeyword($accountId, $groupId, $id, $active = true)
	{
		if (!$this->hasExpensesReport())
		{
			throw new NotSupportedException('Not supported.');
		}

		$response = $this->getRequest()->send([
			'methodName' => 'analytics.keyword.manage',
			'parameters' => [
				'accountId' => $accountId,
				'groupId' => $groupId,
				'id' => $id,
				'active' => $active ? 1 : 0,
			]
		]);

		return $response;
	}

	/**
	 * Manage activity of ad group.
	 *
	 * @param string $accountId Account ID.
	 * @param string $id ID.
	 * @param bool $active Active.
	 * @return Result
	 * @throws NotImplementedException
	 */
	public function manageAdGroup($accountId, $id, $active = true)
	{
		if (!$this->hasExpensesReport())
		{
			throw new NotSupportedException('Not supported.');
		}

		$response = $this->getRequest()->send([
			'methodName' => 'analytics.group.manage',
			'parameters' => [
				'accountId' => $accountId,
				'id' => $id,
				'active' => $active ? 1 : 0,
			]
		]);

		return $response;
	}

	/**
	 * Manage activity of campaign.
	 *
	 * @param string $accountId Account ID.
	 * @param string $id ID.
	 * @param bool $active Active.
	 * @return Result
	 * @throws NotImplementedException
	 */
	public function manageAdCampaign($accountId, $id, $active = true)
	{
		if (!$this->hasExpensesReport())
		{
			throw new NotSupportedException('Not supported.');
		}

		$response = $this->getRequest()->send([
			'methodName' => 'analytics.campaign.manage',
			'parameters' => [
				'accountId' => $accountId,
				'id' => $id,
				'active' => $active ? 1 : 0,
			]
		]);

		return $response;
	}
}