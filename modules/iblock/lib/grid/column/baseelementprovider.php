<?php

namespace Bitrix\Iblock\Grid\Column;

use Bitrix\Iblock\Grid\Entity;
use Bitrix\Main\Grid\Column\DataProvider;

/**
 * @method Entity\ElementSettings getSettings()
 */
abstract class BaseElementProvider extends DataProvider
{
	public function __construct(Entity\ElementSettings $settings)
	{
		parent::__construct($settings);
	}

	protected function getIblockId(): int
	{
		return $this->getSettings()->getIblockId();
	}

	protected function isSkuSelectorEnabled(): bool
	{
		return $this->getSettings()->isSkuSelectorEnabled();
	}

	protected function isNewCardEnabled(): bool
	{
		return $this->getSettings()->isNewCardEnabled();
	}
}
