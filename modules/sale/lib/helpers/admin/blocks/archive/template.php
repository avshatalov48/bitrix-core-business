<?php
namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive;

use Bitrix\Sale\Order;

abstract class Template
{
	/** @var  Order $order*/
	protected $order;
	protected $name = "";

	/**
	 * Template constructor.
	 */
	function __construct()
	{
	}

	/**
	 * @param Order $order
	 * 
	 * @return void
	 */
	public function setOrder(Order $order)
	{
		$this->order = $order;
	}

	/**
	 * @return string
	 * 
	 * @return void
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Build block's html.
	 */
	abstract public function buildBlock();
}