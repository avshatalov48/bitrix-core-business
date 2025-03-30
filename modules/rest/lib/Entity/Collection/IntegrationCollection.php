<?php

namespace Bitrix\Rest\Entity\Collection;

use Bitrix\Rest\Entity\Integration;

/**
 * @extends BaseCollection<Integration>
 */
class IntegrationCollection extends BaseCollection
{
	protected static function getItemClassName(): string
	{
		return Integration::class;
	}

	public function getIntegrationIds(): array
	{
		return $this->map(function ($integration) {
			return $integration->getId();
		});
	}
}