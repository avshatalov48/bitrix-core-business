<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Main;

class UserBudgetPool
{
	private const STATUS_LOCKED_NOW = 1;
	private const STATUS_LOCKED_EARLIER = -1;
	private const STATUS_NOT_LOCKED = 0;

	private $statusLock = self::STATUS_NOT_LOCKED;
	private $userId;

	protected static $userBudgetPool = [];

	protected $items = [];

	const BUDGET_TYPE_ORDER_CANCEL_PART = 'ORDER_CANCEL_PART';
	const BUDGET_TYPE_ORDER_UNPAY = 'ORDER_UNPAY';
	const BUDGET_TYPE_ORDER_PART_RETURN = 'ORDER_PART_RETURN';
	const BUDGET_TYPE_OUT_CHARGE_OFF = 'OUT_CHARGE_OFF';
	const BUDGET_TYPE_EXCESS_SUM_PAID = 'EXCESS_SUM_PAID';
	const BUDGET_TYPE_MANUAL = 'MANUAL';
	const BUDGET_TYPE_ORDER_PAY = 'ORDER_PAY';
	const BUDGET_TYPE_ORDER_PAY_PART = 'ORDER_PAY_PART';

	protected function __construct($userId)
	{
		$this->userId = $userId;
	}

	/**
	 * @param $sum
	 * @param $budgetType
	 * @param Sale\Order $order
	 * @param Sale\Payment|null $payment
	 */
	public function add($sum, $budgetType, Sale\Order $order, Sale\Payment $payment = null)
	{
		if (!$this->isLocked())
		{
			$this->lock();
		}

		if ($this->isStatusLockEarlier())
		{
			return;
		}

		$fields = [
			"SUM" => $sum,
			"CURRENCY" => $order->getCurrency(),
			"TYPE" => $budgetType,
			"ORDER" => $order,
		];

		if ($payment !== null)
		{
			$fields['PAYMENT'] = $payment;
		}

		$this->items[] = $fields;

	}

	/**
	 * @return void
	 */
	protected function lock(): void
	{
		if ($this->statusLock === self::STATUS_NOT_LOCKED)
		{
			$connection = Main\Application::getConnection();
			if (!$connection->lock($this->getUniqLockName()))
			{
				$this->statusLock = self::STATUS_LOCKED_EARLIER;

				return;
			}

			$this->statusLock = self::STATUS_LOCKED_NOW;
		}
	}

	private function getUniqLockName() : string
	{
		return "user_budget_{$this->userId}";
	}

	/**
	 * @return void
	 */
	protected function unlock(): void
	{
		if ($this->statusLock === self::STATUS_LOCKED_NOW)
		{
			$connection = Main\Application::getConnection();
			$connection->unlock($this->getUniqLockName());
			unset($connection);

			$this->statusLock = self::STATUS_NOT_LOCKED;
		}
	}

	protected function isLocked(): bool
	{
		return
			$this->statusLock === self::STATUS_LOCKED_EARLIER
			|| $this->statusLock === self::STATUS_LOCKED_NOW
		;
	}

	protected function isStatusLockEarlier(): bool
	{
		return $this->statusLock === self::STATUS_LOCKED_EARLIER;
	}

	/**
	 * @return array|false
	 */
	public function get()
	{
		if (isset($this->items))
		{
			return $this->items;
		}

		return false;
	}

	/**
	 * @param $index
	 * @return bool
	 * @throws Main\Db\SqlQueryException
	 */
	public function delete($index)
	{
		if (isset($this->items) && isset($this->items[$index]))
		{
			unset($this->items[$index]);
			if (count($this->items) === 0)
			{
				$this->unlock();
			}

			return true;
		}

		return false;
	}

	/**
	 * @param $userId
	 * @return UserBudgetPool
	 */
	public static function getUserBudgetPool($userId)
	{
		if (!isset(static::$userBudgetPool[$userId]))
		{
			static::$userBudgetPool[$userId] = new static($userId);
		}

		return static::$userBudgetPool[$userId];
	}

	/**
	 * @param Sale\Order $order
	 * @param $value
	 * @param $type
	 * @param Sale\Payment|null $payment
	 * @throws Main\Db\SqlQueryException
	 */
	public static function addPoolItem(Sale\Order $order, $value, $type, Sale\Payment $payment = null)
	{
		if (floatval($value) == 0)
			return;

		$userId = $order->getUserId();
		$pool = static::getUserBudgetPool($userId);
		$pool->add($value, $type, $order, $payment);
	}

	/**
	 * @param $userId
	 * @return Sale\Result
	 */
	public static function onUserBudgetSave($userId)
	{
		$result = new Sale\Result();

		$pool = static::getUserBudgetPool($userId);

		if ($pool->isStatusLockEarlier())
		{
			return $result->addError(
				new Sale\ResultError(
					Loc::getMessage('SALE_PROVIDER_USER_BUDGET_LOCKED')
				)
			);
		}

		foreach ($pool->get() as $key => $budgetDat)
		{
			$orderId = null;
			$paymentId = null;

			if (isset($budgetDat['ORDER'])
				&& ($budgetDat['ORDER'] instanceof Sale\OrderBase))
			{
				$orderId = $budgetDat['ORDER']->getId();
			}

			if (isset($budgetDat['PAYMENT'])
				&& ($budgetDat['PAYMENT'] instanceof Sale\Payment))
			{
				$paymentId = $budgetDat['PAYMENT']->getId();
			}

			if (!\CSaleUserAccount::UpdateAccount($userId, $budgetDat['SUM'], $budgetDat['CURRENCY'], $budgetDat['TYPE'], $orderId, '', $paymentId))
			{
				$result->addError( new Sale\ResultError(Loc::getMessage("SALE_PROVIDER_USER_BUDGET_".$budgetDat['TYPE']."_ERROR"), "SALE_PROVIDER_USER_BUDGET_".$budgetDat['TYPE']."_ERROR") );
			}

			$pool->delete($key);
		}

		return $result;
	}

	/**
	 * @param Sale\Order $order
	 * @return int
	 */
	public static function getUserBudgetTransForOrder(Sale\Order $order)
	{
		$ignoreTypes = array(
			static::BUDGET_TYPE_ORDER_PAY
		);
		$sumTrans = 0;

		if ($order->getId() > 0)
		{
			$resTrans = \CSaleUserTransact::GetList(
				array("TRANSACT_DATE" => "DESC"),
				array(
					"ORDER_ID" => $order->getId(),
				),
				false,
				false,
				array("AMOUNT", "CURRENCY", "DEBIT")
			);
			while ($transactDat = $resTrans->Fetch())
			{
				if ($transactDat['DEBIT'] == "Y")
				{
					$sumTrans += $transactDat['AMOUNT'];
				}
				else
				{
					$sumTrans -= $transactDat['AMOUNT'];
				}
			}
		}

		if ($userBudgetPool = static::getUserBudgetPool($order->getUserId()))
		{
			foreach ($userBudgetPool->get() as $userBudgetDat)
			{
				if (in_array($userBudgetDat['TYPE'], $ignoreTypes))
					continue;

				$sumTrans += $userBudgetDat['SUM'];
			}
		}

		return $sumTrans;
	}

	/**
	 * @param Sale\Order $order
	 * @return int
	 */
	public static function getUserBudgetByOrder(Sale\Order $order)
	{
		$budget = static::getUserBudget($order->getUserId(), $order->getCurrency());
		if ($userBudgetPool = static::getUserBudgetPool($order->getUserId()))
		{
			foreach ($userBudgetPool->get() as $userBudgetDat)
			{
				$budget += $userBudgetDat['SUM'];
			}
		}

		return $budget;
	}

	/**
	 * @param $userId
	 * @param $currency
	 * @return float|null
	 */
	public static function getUserBudget($userId, $currency)
	{
		$budget = null;
		if ($userAccount = \CSaleUserAccount::GetByUserId($userId, $currency))
		{
			if ($userAccount['LOCKED'] != 'Y')
				$budget = floatval($userAccount['CURRENT_BUDGET']);
		}

		return $budget;
	}

	public function __destruct()
	{
		$this->unlock();
	}
}
