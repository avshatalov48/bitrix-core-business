<?php

namespace Bitrix\Sale\Link\Html;

use Bitrix\Crm\Service\Container;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\HtmlFilter;
use CCrmOwnerType;

Loader::requireModule('crm');

/**
 * HTML link to entity detail page.
 */
class EntityLink
{
	private int $entityTypeId;
	private int $entityId;
	private string $href;

	/**
	 * @param int $entityTypeId
	 * @param int $entityId
	 * @param string $href
	 */
	public function __construct(int $entityTypeId, int $entityId, string $href)
	{
		$this->entityTypeId = $entityTypeId;
		$this->entityId = $entityId;
		$this->href = $href;
	}

	/**
	 * Create instance from order.
	 *
	 * By id of order finds entity binding, and read `entityTypeId` and `entityId`.
	 * If order not found, type set as `Undefined`.
	 * If order found, but not found binding, type set as `Order`.
	 *
	 * @param int $orderId
	 * @param string $link
	 *
	 * @return self
	 */
	public static function createByOrder(int $orderId, string $link): self
	{
		$entityId = $orderId;
		$entityTypeId = CCrmOwnerType::Undefined;

		$order = \Bitrix\Crm\Order\Order::load($orderId);
		if ($order)
		{
			$binding = $order->getEntityBinding();
			if ($binding)
			{
				$entityId = $binding->getOwnerId();
				$entityTypeId = $binding->getOwnerTypeId();
			}
			else
			{
				$entityId = $orderId;
				$entityTypeId = CCrmOwnerType::Order;
			}
		}

		return new static($entityTypeId, $entityId, $link);
	}

	/**
	 * Link label
	 *
	 * @return string
	 */
	private function getLabel(): string
	{
		// we process orders separately, because the factory does not know how to work with items.
		if ($this->entityTypeId === CCrmOwnerType::Order)
		{
			$description = CCrmOwnerType::GetDescription($this->entityTypeId);

			return "{$description} #{$this->entityId}";
		}

		$factory = Container::getInstance()->getFactory($this->entityTypeId);
		if ($factory)
		{
			$item = $factory->getItem($this->entityId);
			if ($item)
			{
				$title = $item->getHeading();
				if ($title)
				{
					return $title;
				}
			}
		}

		return $this->entityId;
	}

	/**
	 * Get HTML string
	 *
	 * @return string
	 */
	public function render(): string
	{
		$href = HtmlFilter::encode($this->href);

		$sanitizer = new \CBXSanitizer();
		$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_HIGH);
		$sanitizer->ApplyDoubleEncode(false);

		$label = $sanitizer->SanitizeHtml($this->getLabel());

		unset($sanitizer);

		if (!$href)
		{
			return $label;
		}

		return '<a href="' . $href . '">' . $label . '</a>';
	}
}
