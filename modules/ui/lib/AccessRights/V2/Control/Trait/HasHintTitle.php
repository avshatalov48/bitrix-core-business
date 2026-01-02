<?php

namespace Bitrix\UI\AccessRights\V2\Control\Trait;

use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;

trait HasHintTitle
{
	protected ?string $hintTitle = null;

	public function hintTitle(string $hintTitle): static
	{
		$this->hintTitle = $hintTitle;

		return $this;
	}

	public function configureRightItemByHintTitle(RightItem $rightItem): void
	{
		$rightItem->setHintTitle($this->hintTitle);
	}
}
