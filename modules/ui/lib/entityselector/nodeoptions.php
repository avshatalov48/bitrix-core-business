<?

namespace Bitrix\UI\EntitySelector;

class NodeOptions implements \JsonSerializable
{
	protected $itemOrder = [];
	protected $open = false;
	protected $dynamic = false;

	public function __construct(array $options)
	{
		$this->setItemOrder($options['itemOrder'] ?? []);
		$this->setOpen($options['open'] ?? false);
		$this->setDynamic($options['dynamic'] ?? false);
	}

	public function setOpen(bool $open = true)
	{
		$this->open = $open;
	}

	public function isOpen()
	{
		return $this->open;
	}

	public function setDynamic(bool $dynamic = true)
	{
		$this->dynamic = $dynamic;
	}

	public function isDynamic()
	{
		return $this->dynamic;
	}

	public function setItemOrder(array $order)
	{
		$this->itemOrder = $order;
	}

	public function getItemOrder()
	{
		return $this->itemOrder;
	}

	public function jsonSerialize()
	{
		return [
			'itemOrder' => $this->getItemOrder(),
			'open' => $this->isOpen(),
			'dynamic' => $this->isDynamic()
		];
	}
}