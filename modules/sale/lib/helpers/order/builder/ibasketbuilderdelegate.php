<?
namespace Bitrix\Sale\Helpers\Order\Builder;

interface IBasketBuilderDelegate
{
	public function __construct(BasketBuilder $builder);
	public function getItemFromBasket($basketCode, $productData);
	public function setItemData($basketCode, &$productData, &$item);
	public function finalActions();
}