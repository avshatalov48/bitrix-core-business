<?php

namespace Bitrix\Catalog\v2;

use Bitrix\Catalog\v2\Fields\FieldStorage;

trait HasSettingsTrait
{
	protected $settings;

	private function getStorage(): FieldStorage
	{
		if ($this->settings === null)
		{
			$this->settings = new FieldStorage();
		}

		return $this->settings;
	}

	/**
	 * @param array $settings
	 * @return static
	 */
	public function setSettings(array $settings): self
	{
		$this->getStorage()->initFields($settings);

		return $this;
	}

	public function getSettings(): array
	{
		return $this->getStorage()->toArray();
	}

	public function getSetting(string $name)
	{
		return $this->getStorage()->getField($name);
	}
}