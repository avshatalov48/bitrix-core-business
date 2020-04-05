<?

namespace Bitrix\Sale\Update;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Update\Stepper;
use Bitrix\Sale\Discount\Analyzer;
use Bitrix\Sale\Internals\DiscountTable;

final class DiscountExecutionModeIndex extends Stepper
{
	const CONTINUE_EXECUTING = true;
	const STOP_EXECUTING     = false;

	const PORTION = 30;
	const TMP_EXECUTE_MODE = 22;

	protected static $moduleId = 'sale';

	public function execute(array &$result)
	{
		$status = $this->loadCurrentStatus();
		if (empty($status['count']) || $status['count'] < 0)
		{
			return self::STOP_EXECUTING;
		}

		$newStatus = array(
			'count' => $status['count'],
			'steps' => $status['steps'],
		);
		$connection = Application::getConnection();
		$discountRows = DiscountTable::getList(
			array(
				'select' => array('*'),
				'filter' => array(
					'>ID' => $status['lastId'],
					'EXECUTE_MODE' => self::TMP_EXECUTE_MODE,
				),
				'order' => array('ID' => 'ASC'),
				'offset' => 0,
				'limit' => self::PORTION,
			)
		);

		foreach ($discountRows as $discountRow)
		{
			$mode = Analyzer::getInstance()->canCalculateSeparately($discountRow) ?
				DiscountTable::EXECUTE_MODE_SEPARATELY : DiscountTable::EXECUTE_MODE_GENERAL;

			$connection->queryExecute("UPDATE b_sale_discount SET EXECUTE_MODE = {$mode} WHERE ID = {$discountRow['ID']}");

			$newStatus['lastId'] = $discountRow['ID'];
			$newStatus['steps']++;
		}

		if (!empty($newStatus['lastId']))
		{
			Option::set('sale', 'discountexecutionmodeindex', serialize($newStatus));
			$result = array(
				'count' => $newStatus['count'],
				'steps' => $newStatus['steps'],
			);

			return self::CONTINUE_EXECUTING;
		}

		$canCalculateSeparately = Analyzer::getInstance()->canCalculateSeparatelyAllDiscount();
		Option::set('sale', 'discount_separately_calculation', $canCalculateSeparately? 'Y' : 'N');
		Option::delete('sale', array('name' => 'discountexecutionmodeindex'));

		return self::STOP_EXECUTING;
	}

	/**
	 * @return array
	 */
	protected function loadCurrentStatus()
	{
		$status = Option::get('sale', 'discountexecutionmodeindex', '');
		$status = ($status !== '' ? @unserialize($status) : array());
		$status = (is_array($status) ? $status : array());

		if (empty($status))
		{
			$status = array(
				'lastId' => 0,
				'steps' => 0,
				'count' => DiscountTable::getCount(
					array(
						'EXECUTE_MODE' => 22,
						'=ACTIVE' => 'Y',
					)
				)
			);
		}

		return $status;
	}
}

?>