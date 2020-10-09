<?

namespace Bitrix\Seo\Analytics;

use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Retargeting;

abstract class Account extends Retargeting\Account
{
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
	 * @param $accountId
	 * @return Result
	 */
	public function getPublicPages($accountId)
	{
		return new Result();
	}
}