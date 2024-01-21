<?php

namespace Bitrix\Im\V2\Settings\Entity;

use Bitrix\Im\Configuration\Notification;

class Notify extends BaseSettings
{

	public static function getRestEntityName(): string
	{
		return 'notify';
	}

	public function toRestFormat(array $option = []): array
	{
		if (!$this->isLoad())
		{
			$this->load($this->groupId);
		}

		$notifyNames = Notification::getEventNames();
		$result = [];
		foreach ($this->settings as $moduleId => $moduleConfig)
		{
			$newModuleConfig = [
				'id' => $moduleId,
				'label' => $notifyNames[$moduleId]['NAME'],
				'notices' => [],
			];

			$notices = [];
			foreach ($moduleConfig['NOTIFY'] as $eventName => $eventConfig)
			{
				$notify = [
					'id' => $eventName,
					'label' => $eventConfig['NAME'],
					'site' => $eventConfig['SITE'],
					'mail' => $eventConfig['MAIL'],
					'push' => $eventConfig['PUSH'],
					'disabled' => [],
				];

				$disabled = [];
				foreach ($eventConfig['DISABLED'] as $disableType => $value)
				{
					if ($disableType === 'XMPP')
					{
						continue;
					}
					if ($value)
					{
						$disabled[] = mb_strtolower($disableType);
					}
				}
				$notify['disabled'] = $disabled;

				$notices[] = $notify;
			}
			$newModuleConfig['notices'] = $notices;
			$result[] = $newModuleConfig;
		}

		return $result;
	}

	/**
	 * @param array{moduleId: string, name: string, type:string, value: bool} $settingConfiguration
	 * @return void
	 */
	public function updateSetting(array $settingConfiguration)
	{
		$updatingSetting = [
			$settingConfiguration['moduleId'] => [[
				$settingConfiguration['name'] => [
					$settingConfiguration['type'] => $settingConfiguration['value'],
				],
			]],
		];

		Notification::updateGroupSettings($this->groupId, $updatingSetting);
	}

	public function updateSimpleSettings(array $simpleSchema): Notify
	{
		$this->settings = Notification::getSimpleNotifySettings($simpleSchema);

		Notification::updateGroupSettings($this->groupId, $this->settings);

		return $this;
	}

	/**
	 * @param bool $isSimpleSchema
	 * @param array{} $simpleSchema
	 * @return $this
	 */
	public function fillDataBase(bool $isSimpleSchema = false, array $simpleSchema = []): BaseSettings
	{
		if ($isSimpleSchema)
		{
			$this->settings = Notification::getSimpleNotifySettings($simpleSchema);
		}
		else
		{
			$this->settings = Notification::getDefaultSettings();
		}
		Notification::setSettings($this->groupId, $this->settings);

		return $this;
	}

	public function load($source): BaseSettings
	{
		if (is_int($source))
		{
			$this->settings = Notification::getGroupSettings($this->groupId);
			$this->isLoad = true;

			return $this;
		}

		if (is_array($source) && !empty($source))
		{
			$this->settings =
				array_replace_recursive(Notification::getDefaultSettings(), $source)
			;
			$this->isLoad = true;

			return $this;
		}

		return $this;
	}
}