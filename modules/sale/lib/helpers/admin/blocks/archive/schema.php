<?php
namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive;

use Bitrix\Sale;

abstract class Schema
{
	protected $order;

	/**
	 * Schema constructor.
	 */
	function __construct()
	{
	}

	/**
	 * Collect blocks into array.
	 * 
	 * @param Sale\Order $order
	 *
	 * @return array $html
	 */
	public function getBlocks(Sale\Order $order)
	{
		$html = array();
		$blockList = $this->collectBlocks();

		/** @var Template $block */
		foreach ($blockList as $blockName)
		{
			$block = new $blockName;
			$block->setOrder($order);
			$html[$block->getName()] = $block->buildBlock();
			unset($block);
		}
		return $html;
	}

	/**
	 * Return list of blocks's names.
	 */
	abstract protected function collectBlocks();
}