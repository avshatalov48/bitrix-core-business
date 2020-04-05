<?
namespace Bitrix\Sale\Helpers\Order\Builder;

final class OrderBuilderSale extends OrderBuilder
{
	public function __construct(SettingsContainer $settings)
	{
		parent::__construct($settings);
		$this->setBasketBuilder(new BasketBuilderSale($this));
	}
}