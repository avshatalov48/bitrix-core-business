<?php

namespace Bitrix\Im\V2\Settings\Entity;

use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Settings\SettingsError;

abstract class BaseSettings implements RestConvertible
{
	protected array $settings = [];

	protected ?int $groupId;

	protected bool $isLoad = false;

	/**
	 * @param int|null $groupId
	 */
	public function __construct(?int $groupId)
	{
		$this->groupId = $groupId;
	}

	abstract public function updateSetting(array $settingConfiguration);
	abstract public function fillDataBase(): BaseSettings;

	// TODO return int|array
	abstract public function load($source): BaseSettings;

	/**
	 * @return Result<array>
	 */
	public function getSettings(): Result
	{
		$result = new Result();
		if ($this->groupId === null)
		{
			return $result->addError(new SettingsError(SettingsError::UNDEFINED_GROUP_ID));
		}

		if (!$this->isLoad())
		{
			$this->load($this->groupId);
		}

		return $result->setResult($this->settings);
	}

	public function isLoad(): bool
	{
		return $this->isLoad;
	}
}