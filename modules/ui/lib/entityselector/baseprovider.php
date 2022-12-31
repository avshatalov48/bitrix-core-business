<?
namespace Bitrix\UI\EntitySelector;

abstract class BaseProvider
{
	protected $options = [];

	protected function __construct()
	{
		// You have to validate $options in a derived class constructor
	}

	public abstract function isAvailable(): bool;

	/**
	 * @param array $ids
	 *
	 * @return Item[]
	 */
	public abstract function getItems(array $ids): array;

	/**
	 * @param array $ids
	 *
	 * @return Item[]
	 */
	public function getPreselectedItems(array $ids): array
	{
		return $this->getSelectedItems($ids);
	}

	/**
	 * @param array $ids
	 * @return Item[]
	 *@deprecated
	 * @see getPreselectedItems
 */
	public function getSelectedItems(array $ids): array
	{
		return $this->getItems($ids);
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function getOption(string $option, $defaultValue = null)
	{
		return array_key_exists($option, $this->options) ? $this->options[$option] : $defaultValue;
	}

	public function fillDialog(Dialog $dialog): void
	{

	}

	public function getChildren(Item $parentItem, Dialog $dialog): void
	{

	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{

	}

	public function handleBeforeItemSave(Item $item): void
	{

	}
}