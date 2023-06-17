<?php

namespace Bitrix\Im\V2\Settings\Entity;

use Bitrix\Im\Common;
use Bitrix\Im\V2\Settings\Preset\Preset;
use Bitrix\Pull\Event;
use CModule;

class General extends BaseSettings
{
	public const SIMPLE_SITE = 'notifySchemeSendSite';
	public const SIMPLE_MAIL = 'notifySchemeSendEmail';
	public const SIMPLE_PUSH = 'notifySchemeSendPush';
	public const SCHEME = 'notifyScheme';

	public const PRIVACY_SEARCH = 'privacySearch';
	public const OPEN_DESKTOP_FROM_PANEL = 'openDesktopFromPanel';


	public static function getRestEntityName(): string
	{
		return 'general';
	}

	public function toRestFormat(array $option = []): array
	{
		return $this->settings;
	}

	/**
	 * @param array{name: string, value: string|bool} $settingConfiguration
	 * @return void
	 * @throws \Exception
	 */
	public function updateSetting(array $settingConfiguration): void
	{
		$updatingSetting = [
			$settingConfiguration['name'] => $settingConfiguration['value']
		];
		$this->settings[$settingConfiguration['name']] = $settingConfiguration['value'];

		\Bitrix\Im\Configuration\General::updateGroupSettings($this->groupId, $updatingSetting);
	}

	public function shouldUpdateSimpleNotifySettings(array $settingsConfiguration): bool
	{
		if ($settingsConfiguration['name'] === self::SCHEME && $settingsConfiguration['value'] === 'simple')
		{
			return true;
		}

		if ($this->getValue(self::SCHEME) === 'simple')
		{
			return in_array(
				$settingsConfiguration['name'],
				[
					self::SIMPLE_SITE,
					self::SIMPLE_MAIL,
					self::SIMPLE_PUSH
				],
				true
			);
		}

		return false;
	}

	/**
	 * @param int|array $source
	 * @return $this
	 */
	public function load($source): BaseSettings // TODO return int|array
	{
		if (is_int($source))
		{
			$this->settings = \Bitrix\Im\Configuration\General::getGroupSettings($this->groupId);
			$this->isLoad = true;

			return $this;
		}

		if (is_array($source) && !empty($source))
		{
			$this->settings =
				array_replace_recursive(\Bitrix\Im\Configuration\General::getDefaultSettings(), $source)
			;
			$this->isLoad = true;

			return $this;
		}

		return $this;
	}

	protected function getValue(string $name, $defaultValue = null)
	{
		if (!$this->isLoad())
		{
			$this->load($this->groupId);
		}

		return $this->settings[$name] ?? $defaultValue;
	}

	public function fillDataBase(): BaseSettings
	{
		\Bitrix\Im\Configuration\General::setSettings($this->groupId, [], true);

		$this->settings = \Bitrix\Im\Configuration\General::getDefaultSettings();

		return $this;
	}

	public function isSimpleNotifySchema(): bool
	{
		return $this->getValue(static::SCHEME) === 'simple';
	}

	public function getSimpleNotifyScheme(): array
	{
		return [
			static::SIMPLE_SITE => $this->getValue(static::SIMPLE_SITE),
			self::SIMPLE_MAIL => $this->getValue(self::SIMPLE_MAIL),
			self::SIMPLE_PUSH => $this->getValue(self::SIMPLE_PUSH),
		];
	}
}