<?
namespace Bitrix\Sale\Helpers;

use Bitrix\Main\Config\Option;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Update\Stepper;
use Bitrix\Sale;

class ReservedProductCleaner extends Stepper
{
	protected static $moduleId = "sale";

	public function execute(array &$result)
	{
		$className = get_class($this);
		$option = Option::get("sale", $className, 0);
		$result["steps"] = $option;

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$limit = 100;
		$result["steps"] = $result["steps"] ?? 0;
		$selectedRowsCount = 0;

		$days_ago = (int) Option::get("sale", "product_reserve_clear_period");

		if ($days_ago > 0)
		{
			global $USER;

			if (!is_object($USER))
			{
				$USER = new \CUser;
			}

			$date = new DateTime();
			$parameters = [
				'select' => [
					'ORDER_ID' => 'ORDER.ID',
					'ID',
					'BASKET_ID'
				],
				'filter' => [
					'>QUANTITY' => 0,
					'<=DATE_RESERVE_END' => $date,
					'=ORDER.PAYED' => 'N',
					'=ORDER.CANCELED' => 'N',
				],
				'runtime' => [
					new Main\Entity\ReferenceField(
						'BASKET',
						Sale\Internals\BasketTable::class,
						[
							'=this.BASKET_ID' => 'ref.ID',
						],
						['join_type' => 'inner']
					),
					new Main\Entity\ReferenceField(
						'ORDER',
						Sale\Internals\OrderTable::class,
						[
							'=this.BASKET.ORDER_ID' => 'ref.ID',
						],
						['join_type' => 'inner']
					),
				],
				'count_total' => true,
				'limit' => $limit,
				'offset' => $result["steps"]
			];

			$orderList = [];
			$res = Sale\ReserveQuantityCollection::getList($parameters);
			$selectedRowsCount = $res->getCount();
			while ($data = $res->fetch())
			{
				if (!isset($orderList[$data['ORDER_ID']]))
				{
					$orderList[$data['ORDER_ID']] = [];
				}

				if (!isset($orderList[$data['ORDER_ID']][$data['BASKET_ID']]))
				{
					$orderList[$data['ORDER_ID']][$data['BASKET_ID']] = [];
				}

				$orderList[$data['ORDER_ID']][$data['BASKET_ID']][] = $data['ID'];
			}

			foreach ($orderList as $orderId => $basketItemIds)
			{
				$orderSaved = false;

				$order = $orderClass::load($orderId);
				if (!$order)
				{
					continue;
				}

				$basket = $order->getBasket();
				foreach ($basketItemIds as $basketItemId => $reserveIds)
				{
					/** @var Sale\BasketItem $basketItem */
					$basketItem = $basket->getItemById($basketItemId);
					if (!$basketItem)
					{
						continue;
					}

					foreach ($reserveIds as $reserveId)
					{
						$reserve = $basketItem->getReserveQuantityCollection()->getItemById($reserveId);
						if (!$reserve)
						{
							continue;
						}

						$reserve->delete();
					}
				}

				$r = $order->save();
				if ($r->isSuccess())
				{
					$orderSaved = true;
				}
				else
				{
					$errors = $r->getErrorMessages();
				}

				if (!$orderSaved && !empty($errors))
				{
					$oldErrorText = $order->getField('REASON_MARKED');
					foreach($errors as $error)
					{
						$oldErrorText .= (strval($oldErrorText) != '' ? "\n" : ""). $error;
					}

					Sale\Internals\OrderTable::update($order->getId(), [
						"MARKED" => "Y",
						"REASON_MARKED" => $oldErrorText
					]);
				}
			}

			// crutch for #120087
			if (!is_object($USER) || $USER->GetID() <= 0)
			{
				ORM\Entity::destroy(Sale\Internals\OrderTable::getEntity());
			}
		}

		if($selectedRowsCount < $limit)
		{
			Option::delete("sale", array("name" => $className));
			return false;
		}
		else
		{
			$result["steps"] = $result["steps"] + $selectedRowsCount;
			$option = $result["steps"];
			Option::set("sale", $className, $option);
			return true;
		}
	}
}