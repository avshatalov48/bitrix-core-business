<?
namespace Bitrix\Sale\Helpers;

use Bitrix\Main\Config\Option;
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
		$result["steps"] = isset($result["steps"]) ? $result["steps"] : 0;
		$selectedRowsCount = 0;

		$days_ago = (int) Option::get("sale", "product_reserve_clear_period");

		if ($days_ago > 0)
		{
			global $USER;

			if (!is_object($USER))
				$USER = new \CUser;

			$date = new DateTime();
			$parameters = [
				'select' => array(
					"ORDER_ID" => "DELIVERY.ORDER.ID",
				),
				'filter' => [
					"<=DELIVERY.ORDER.DATE_INSERT" => $date->add('-'.$days_ago.' day'),
					'>RESERVED_QUANTITY' => 0,
					"=DELIVERY.DEDUCTED" => "N",
					"=DELIVERY.MARKED" => "N",
					"=DELIVERY.ALLOW_DELIVERY" => "N",
					"=DELIVERY.ORDER.PAYED" => "N",
					"=DELIVERY.ORDER.CANCELED" => "N",
				],
				'group' => ['ORDER_ID'],
				'count_total' => true,
				'limit' => $limit,
				'offset' => $result["steps"]
			];

			$res = Sale\ShipmentItem::getList($parameters);
			$selectedRowsCount = $res->getCount();
			while($data = $res->fetch())
			{
				/** @var Sale\Order $order */
				$order = $orderClass::load($data['ORDER_ID']);
				$orderSaved = false;
				$errors = array();

				try
				{
					/** @var Sale\ShipmentCollection $shipmentCollection */
					if ($shipmentCollection = $order->getShipmentCollection())
					{
						/** @var Sale\Shipment $shipment */
						foreach ($shipmentCollection as $shipment)
						{
							$r = $shipment->tryUnreserve();
							if (!$r->isSuccess())
							{
								Sale\EntityMarker::addMarker($order, $shipment, $r);
								if (!$shipment->isSystem())
								{
									$shipment->setField('MARKED', 'Y');
								}
							}
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
				}
				catch(\Exception $e)
				{
					$errors[] = $e->getMessage();
				}

				if (!$orderSaved)
				{
					if (!empty($errors))
					{
						$oldErrorText = $order->getField('REASON_MARKED');
						foreach($errors as $error)
						{
							$oldErrorText .= (strval($oldErrorText) != '' ? "\n" : ""). $error;
						}

						Sale\Internals\OrderTable::update($order->getId(), array(
							"MARKED" => "Y",
							"REASON_MARKED" => $oldErrorText
						));
					}
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