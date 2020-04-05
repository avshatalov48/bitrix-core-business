<?

namespace Bitrix\Seo\Analytics;

use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Retargeting;

abstract class Account extends Retargeting\Account
{
	/**
	 * @param $accountId
	 * @param Date|null $dateFrom
	 * @param Date|null $dateTo
	 * @return Retargeting\Response
	 */
	abstract public function getExpenses($accountId, Date $dateFrom = null, Date $dateTo = null);

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