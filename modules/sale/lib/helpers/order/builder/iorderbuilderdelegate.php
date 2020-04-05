<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Sale\Shipment;

interface IOrderBuilderDelegate
{
	public function __construct(OrderBuilder $builder);
	public function createOrder(array $data);
	public function setUser();
	public function buildBasket();
	public function setShipmentPriceFields(Shipment $shipment, array $fields);
}