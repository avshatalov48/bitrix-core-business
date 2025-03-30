<?php

namespace Bitrix\Rest\Entity\Collection;

use Bitrix\Rest\Entity\App;

/**
 * @extends BaseCollection<App>
 */
class AppCollection extends BaseCollection
{
	protected static function getItemClassName(): string
	{
		return App::class;
	}

	public function getAppIds(): array
	{
		return $this->map(function ($application) {
			return $application->getId();
		});
	}

	public function getAppCodes(): array
	{
		return $this->map(function ($application) {
			return $application->getCode();
		});
	}
}