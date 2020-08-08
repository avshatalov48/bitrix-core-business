<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;

interface IOrderBuilderDelegate
{
	/**
	 * IOrderBuilderDelegate constructor.
	 * @param OrderBuilder $builder
	 */
	public function __construct(OrderBuilder $builder);

	/**
	 * @param array $data
	 * @return mixed
	 */
	public function createOrder(array $data);

	/**
	 * @return mixed
	 */
	public function setUser();

	/**
	 * @return mixed
	 */
	public function buildBasket();

	/**
	 * @param Shipment $shipment
	 * @param array $fields
	 * @return mixed
	 */
	public function setShipmentPriceFields(Shipment $shipment, array $fields);

	/**
	 * @param int $orderPropsCountBefore
	 * @param Order $order
	 */
	public function recalculateDeliveryPrice(int $orderPropsCountBefore, Order $order);
}
