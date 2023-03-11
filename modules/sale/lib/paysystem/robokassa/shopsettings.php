<?php

namespace Bitrix\Sale\PaySystem\Robokassa;

use Bitrix\Main;
use Bitrix\Sale;

final class ShopSettings
{
	public static function getSettingsCoded(): array
	{
		return [
			'ROBOXCHANGE_SHOPLOGIN',
			'ROBOXCHANGE_SHOPPASSWORD',
			'ROBOXCHANGE_SHOPPASSWORD2',
		];
	}

	/**
	 * Adds new shop settings
	 *
	 * @param array $settings
	 * @return Main\ORM\Data\AddResult
	 */
	public function add(array $settings): Main\Result
	{
		$result = new Main\Result();

		$normalizedSettings = $this->normalizeSettings($settings);

		foreach ($normalizedSettings as $settingCode => $settingValue)
		{
			$addResult = Sale\Internals\BusinessValueTable::add([
				'CODE_KEY' => $settingCode,
				'CONSUMER_KEY' => Sale\Internals\BusinessValueTable::COMMON_CONSUMER_KEY,
				'PERSON_TYPE_ID' => Sale\Internals\BusinessValueTable::COMMON_PERSON_TYPE_ID,
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $settingValue,
			]);

			if (!$addResult->isSuccess())
			{
				$result->addErrors($addResult->getErrors());
				break;
			}
		}

		if ($result->isSuccess())
		{
			$this->emitAddEvent();
		}

		return $result;
	}

	/**
	 * Updates shop settings
	 *
	 * @param array $settings
	 * @return Main\ORM\Data\UpdateResult
	 */
	public function update(array $settings): Main\Result
	{
		$result = new Main\Result();

		$normalizedSettings = $this->normalizeSettings($settings);

		foreach ($normalizedSettings as $settingCode => $settingValue)
		{
			$primary = [
				'CODE_KEY' => $settingCode,
				'CONSUMER_KEY' => Sale\Internals\BusinessValueTable::COMMON_CONSUMER_KEY,
				'PERSON_TYPE_ID' => Sale\Internals\BusinessValueTable::COMMON_PERSON_TYPE_ID,
			];

			$updateResult = Sale\Internals\BusinessValueTable::update(
				$primary,
				[
					'PROVIDER_VALUE' => $settingValue,
				]
			);

			if (!$updateResult->isSuccess())
			{
				$result->addErrors($updateResult->getErrors());
				break;
			}
		}

		return $result;
	}

	/**
	 * Deletes settings
	 *
	 */
	public function delete(): void
	{
		if (!$this->isOnlyCommonSettingsExists())
		{
			return;
		}

		$this->deleteInternal();
	}

	private function deleteInternal(): void
	{
		foreach (self::getSettingsCoded() as $settingsCode)
		{
			Sale\Internals\BusinessValueTable::deleteByCodeKey($settingsCode);
		}
	}

	/**
	 * Gets settings
	 *
	 * @return array
	 */
	public function get(): array
	{
		$result = [];

		if ($this->isOnlyCommonSettingsExists())
		{
			$businessValues = Sale\Internals\BusinessValueTable::getList([
				'select' => ['CODE_KEY', 'PROVIDER_VALUE'],
				'filter' => [
					'@CODE_KEY' => self::getSettingsCoded(),
					'=CONSUMER_KEY' => Sale\Internals\BusinessValueTable::COMMON_CONSUMER_KEY,
					'=PERSON_TYPE_ID' => Sale\Internals\BusinessValueTable::COMMON_PERSON_TYPE_ID,
				]
			])->fetchAll();

			foreach ($businessValues as $businessValue)
			{
				$result[$businessValue['CODE_KEY']] = $businessValue['PROVIDER_VALUE'];
			}
		}

		return $result;
	}

	/**
	 * Returns true if exists only common settings without person type
	 *
	 * @return bool
	 */
	public function isOnlyCommonSettingsExists(): bool
	{
		$businessValues = Sale\Internals\BusinessValueTable::getList([
			'select' => ['CONSUMER_KEY', 'PERSON_TYPE_ID'],
			'filter' => [
				'@CODE_KEY' => self::getSettingsCoded(),
			],
			'group' => ['CONSUMER_KEY', 'PERSON_TYPE_ID'],
		])->fetchAll();

		if (!$businessValues)
		{
			return false;
		}

		$otherSettings = array_filter($businessValues, static function ($businessValue) {
			return
				$businessValue['CONSUMER_KEY'] !== null
				|| $businessValue['PERSON_TYPE_ID'] !== null
			;
		});

		if ($otherSettings)
		{
			return false;
		}

		return true;
	}

	public function isAnySettingsExists(): bool
	{
		return (bool)Sale\Internals\BusinessValueTable::getList([
			'select' => ['CODE_KEY'],
			'filter' => [
				'@CODE_KEY' => self::getSettingsCoded(),
			],
			'limit' => 1
		])->fetch();
	}

	private function normalizeSettings(array $settings): array
	{
		$settingsCodeList = self::getSettingsCoded();

		return array_filter(
			$settings,
			static function ($code) use ($settingsCodeList) {
				return in_array($code, $settingsCodeList, true);
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	private function emitAddEvent(): void
	{
		if ($this->isPushAndPullAvailable())
		{
			$handlerClassName = \Sale\Handlers\PaySystem\RoboxchangeHandler::class;
			$message = [
				'module_id' => 'sale',
				'command' => 'on_add_paysystem_settings_robokassa',
				'params' => [
					'handlerClassName' => $handlerClassName,
					'handler' => Sale\PaySystem\Manager::getFolderFromClassName($handlerClassName),
				],
			];

			\CPullWatch::AddToStack('SALE_PAYSYSTEM_ROBOKASSA_REGISTRATION', $message);
		}
	}

	private function isPushAndPullAvailable(): bool
	{
		return Main\Loader::includeModule('pull');
	}
}