<?php

namespace Bitrix\Sale\Link\Html;

/**
 * HTML link to order detail page.
 */
class OrderLink
{
	private int $orderId;
	private string $href;

	/**
	 * @param int $orderId
	 * @param string $href
	 */
	public function __construct(int $orderId, string $href)
	{
		$this->orderId = $orderId;
		$this->href = $href;
	}

	/**
	 * Link label
	 *
	 * @return string
	 */
	private function getLabel(): string
	{
		return $this->orderId;
	}

	/**
	 * Get HTML string
	 *
	 * @return string
	 */
	public function render(): string
	{
		$href = htmlspecialcharsbx($this->href);
		$label = $this->getLabel();

		if (!$href)
		{
			return $label;
		}

		return "<a href=\"{$href}\">{$label}</a>";
	}
}
