<?php
namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Sale\Archive\Manager;

class View
{
	/** @var Sale\Archive\Order  */
	protected $order;

	/**
	 * View constructor.
	 *
	 * @param int $id
	 */
	function __construct($id)
	{
		$id = (int)$id;
		$this->order = Manager::returnArchivedOrder($id);
	}

	/**
	 * @return Sale\Order
	 */
	public function loadOrder()
	{
		return $this->order;
	}

	/**
	 * Return blocks appropriate archive version.
	 *
	 * @return array
	 *
	 * @throws Main\ObjectNotFoundException
	 */
	public function getTemplates()
	{
		$schema = $this->getSchema();
		if ($schema)
		{
			return $schema->getBlocks($this->order);
		}
		return array();
	}

	private function getSchema()
	{
		$version = $this->order->getVersion();
		if ($version === 1 || $version === 2)
		{
			return new TypeFirst\Schema();
		}

		return null;
	}
}