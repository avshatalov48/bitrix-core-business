<?php

namespace Bitrix\Calendar\Sync\Office365\Dto;

use Bitrix\Calendar\Internals;

class Dto extends Internals\Dto
{
	private array $metaFields = [];

	/**
	 * @return array
	 */
	public function getMetaFields(): array
	{
		return $this->metaFields;
	}

	protected function checkConstructException($key, $value): bool
	{
		if (strpos($key, '@') !== false)
		{
			$this->metaFields[$key] = $value;
			return true;
		}

		return parent::checkConstructException($key, $value);
	}

	protected function checkPrepareToArrayException($key, $value): bool
	{
		if (strpos($key, 'metaFields') !== false)
		{
			return true;
		}
		if ($key === 'etag')
		{
			return true;
		}
		return parent::checkPrepareToArrayException($key, $value);
	}
}
