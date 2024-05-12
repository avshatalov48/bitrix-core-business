<?php

namespace Bitrix\Im\V2\Settings;

use Bitrix\Im\Common;
use Bitrix\Im\Configuration\Configuration;
use Bitrix\Im\Configuration\General;
use Bitrix\Im\Model\OptionUserTable;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Settings\Preset\Preset;
use Bitrix\Im\V2\Settings\Preset\PresetError;
use Bitrix\Pull\Event;
use CModule;

class UserConfiguration
{

	protected Preset $notifyPreset;
	protected Preset $generalPreset;

	private ?int $userId;

	public function __construct(?int $userId = null)
	{
		if ($userId !== null)
		{
			$this->load($userId);
		}
	}

	public function load(int $userId): Result
	{
		$this->userId = $userId;

		$result = new Result();
		$cache = CacheManager::getUserCache($userId);
		$bindings = $cache->getValue();
		if (
			!empty($bindings[CacheManager::GENERAL_PRESET])
			&& !empty($bindings[CacheManager::NOTIFY_PRESET])
		)
		{
			$this->generalPreset = Preset::getInstance($bindings[CacheManager::GENERAL_PRESET]);
			$this->notifyPreset = Preset::getInstance($bindings[CacheManager::NOTIFY_PRESET]);

			return $result->setResult(true);
		}

		$bindingsUser =
			OptionUserTable::query()
				->addSelect('NOTIFY_GROUP_ID')
				->addSelect('GENERAL_GROUP_ID')
				->where('USER_ID', $userId)
				->setLimit(1)
		 		->fetchObject()
		;
		if ($bindingsUser)
		{
			$bindings = [
				CacheManager::GENERAL_PRESET => $bindingsUser->getGeneralGroupId(),
				CacheManager::NOTIFY_PRESET => $bindingsUser->getNotifyGroupId(),
			];
		}

		if (!$bindings)
		{
			$presetId = Configuration::restoreBindings($userId);

			$bindings = [
				CacheManager::GENERAL_PRESET => $presetId,
				CacheManager::NOTIFY_PRESET => $presetId,
			];
		}

		$this->generalPreset = Preset::getInstance($bindings[CacheManager::GENERAL_PRESET]);
		$this->notifyPreset = Preset::getInstance($bindings[CacheManager::NOTIFY_PRESET]);

		$cache->setValue([
			CacheManager::NOTIFY_PRESET => $this->notifyPreset->getId(),
			CacheManager::GENERAL_PRESET => $this->generalPreset->getId(),
		]);

		return $result->setResult(true);
	}

	public function updateGeneralSetting(array $settingsConfiguration)
	{
		$settingsBeforeUpdate = ($settingsConfiguration['name'] === 'pinnedChatSort')
			? $this->getGeneralSettings()
			: null
		;

		if (!$this->generalPreset->isPersonal($this->userId))
		{
			$personalPreset = Preset::getPersonal($this->userId);

			if ($personalPreset->isExist())
			{
				$this->generalPreset = $personalPreset;
			}
			else
			{
				$personalPreset = Preset::getInstance();
				$personalPreset->initPersonal($this->userId);
				$this->generalPreset = $personalPreset;
			}

			$this->generalPreset->bindToUser($this->userId, [Preset::BIND_GENERAL]);
			CacheManager::getUserCache($this->userId)->clearCache();
		}

		$this->generalPreset->general->updateSetting($settingsConfiguration);
		$this->perfomSideEffect($settingsConfiguration, $settingsBeforeUpdate);

		if (!$this->generalPreset->general->shouldUpdateSimpleNotifySettings($settingsConfiguration))
		{
			CacheManager::getPresetCache($this->generalPreset->getId())->clearCache();

			return;
		}
		if (!$this->notifyPreset->isPersonal($this->userId))
		{
			$this->notifyPreset = $this->generalPreset;
			$this->notifyPreset->bindToUser($this->userId, [Preset::BIND_NOTIFY]);
			CacheManager::getUserCache($this->userId)->clearCache();
		}

		$simpleSchema = $this->generalPreset->general->getSimpleNotifyScheme();
		$this->notifyPreset->notify->updateSimpleSettings($simpleSchema);

		CacheManager::getPresetCache($this->notifyPreset->getId())->clearCache();
	}

	/**
	 * @param array{moduleId: string, name: string, type: string, value: bool} $settingConfiguration
	 * @return void
	 */
	public function updateNotifySetting(array $settingsConfiguration)
	{
		if (!$this->notifyPreset->isPersonal($this->userId))
		{
			$notifyPreset = Preset::getPersonal($this->userId);

			if (!$notifyPreset->isExist())
			{
				$notifyPreset = Preset::getInstance();
				$notifyPreset->initPersonal($this->userId);
			}
			$this->notifyPreset = $notifyPreset;

			$this->notifyPreset->bindToUser($this->userId, [Preset::BIND_NOTIFY]);
			CacheManager::getUserCache($this->userId)->clearCache();
		}

		$this->notifyPreset->notify->updateSetting($settingsConfiguration);

		CacheManager::getPresetCache($this->notifyPreset->getId())->clearCache();
	}

	public function updateStatus(string $status): bool
	{
		$this->updateGeneralSetting(['name' => 'status', 'value' => $status]);

		return \CIMStatus::Set($this->userId, ['STATUS' => $status]);
	}

	public function getGeneralSettings(): array
	{
		if ($this->generalPreset->getId() === null)
		{
			$this->recoveryBinding(Preset::BIND_GENERAL);
		}

		if ($this->generalPreset->general === null)
		{
			$this->generalPreset = Preset::getDefaultPreset();
		}

		return $this->generalPreset->general->toRestFormat();
	}

	public function getNotifySettings(): array
	{
		if ($this->notifyPreset->getId() === null)
		{
			$this->recoveryBinding(Preset::BIND_NOTIFY);
		}

		if ($this->notifyPreset->notify === null)
		{
			$this->notifyPreset = Preset::getDefaultPreset();
		}

		return $this->notifyPreset->notify->toRestFormat();
	}

	protected function perfomSideEffect(array $settingConfiguration, ?array $settingsBeforeUpdate)
	{
		$this->updateUserSearch($settingConfiguration);
		$this->openDesktopFromPanel($settingConfiguration);

		if (isset($settingsBeforeUpdate))
		{
			$this->updatePinSortCost($settingConfiguration, $settingsBeforeUpdate);
		}
	}

	private function updateUserSearch(array $settingsConfiguration): void
	{
		$defaultSettings = General::getDefaultSettings();

		if (
			$settingsConfiguration['name'] === Entity\General::PRIVACY_SEARCH
			&& $this->checkUserSearch($settingsConfiguration['value'])
		)
		{
			$value =
				$defaultSettings[Entity\General::PRIVACY_SEARCH] === $settingsConfiguration['value']
					? ''
					: $settingsConfiguration['value']
			;

			\Bitrix\Main\Application::getUserTypeManager()->Update(
				"USER",
				$this->userId,
				[
					'UF_IM_SEARCH' => $value
				]
			);
		}
	}

	private function checkUserSearch($settingValue): bool
	{
		return in_array($settingValue, [General::PRIVACY_RESULT_ALL, General::PRIVACY_RESULT_CONTACT], true);
	}

	private function recoveryBinding(string $toEntity)
	{
		$userPreset = Preset::getPersonal($this->userId);

		$bindingPreset =
			$userPreset->isExist()
				? $userPreset
				: Preset::getDefaultPreset()
		;
		$bindingPreset->bindToUser($this->userId, [$toEntity]);

		if ($toEntity === Preset::BIND_GENERAL)
		{
			$this->generalPreset = $bindingPreset;
		}
		else
		{
			$this->notifyPreset = $bindingPreset;
		}

		CacheManager::getUserCache($this->userId)->clearCache();
	}

	private function openDesktopFromPanel(array $settingsConfiguration): void
	{
		if (
			$settingsConfiguration['name'] === Entity\General::OPEN_DESKTOP_FROM_PANEL
			&& CModule::IncludeModule('pull')
		)
		{
			Event::add($this->userId, [
				'module_id' => 'im',
				'command' => 'settingsUpdate',
				'expiry' => 5,
				'params' => [
					'openDesktopFromPanel' => $settingsConfiguration['value'],
				],
				'extra' => Common::getPullExtra()
			]);
		}
	}

	public function checkIsPersonalGeneralPreset(): bool
	{
		return $this->generalPreset->isPersonal($this->userId);
	}

	public function getPersonalGeneralPresetId(): ?int
	{
		return $this->generalPreset->getId();
	}

	private function updatePinSortCost(array $settingsConfiguration, array $settingsBeforeUpdate): void
	{
		if ($settingsConfiguration['name'] === 'pinnedChatSort'
			&& $settingsConfiguration['value'] !== 'byDate'
		)
		{
			if ($settingsBeforeUpdate['pinnedChatSort'] !== 'byCost')
			{
				Recent::updatePinSortCost($this->userId);
			}
		}
	}
}