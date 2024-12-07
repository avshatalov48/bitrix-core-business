<?php

namespace Bitrix\Calendar\OpenEvents\Controller;

use Bitrix\Main\Engine\Controller;

trait FeatureTrait
{
	protected function getDefaultPreFilters(): array
	{
		return [
			...$this->getParentPreFilters(),
			// adds +prefilter for all actions
			new Filter\Feature(),
		];
	}

	private function getParentPreFilters(): array
	{
		return is_subclass_of(__CLASS__, Controller::class)
			? parent::getDefaultPreFilters()
			: [];
	}
}
