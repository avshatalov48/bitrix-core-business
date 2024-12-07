<?php

namespace Bitrix\Socialnetwork\Integration\Intranet;

use Bitrix\Intranet\Integration\Templates\Bitrix24;
use Bitrix\Main\Loader;

class ThemePicker
{
	public static function applyUserTheme(): void
	{
		if (!self::isAvaliable())
		{
			return;
		}

		$themePicker = self::getUserThemePicker();

		self::applyTheme($themePicker);
	}

	public static function applyGroupTheme(int $groupId): void
	{
		if (!self::isAvaliable())
		{
			return;
		}

		$themePicker = self::getGroupThemePicker($groupId);

		self::applyTheme($themePicker);
	}

	public static function getUserTheme(): array
	{
		if (!self::isAvaliable())
		{
			return [];
		}

		return self::getUserThemePicker()->getCurrentTheme();
	}

	public static function getGroupTheme(int $groupId): array
	{
		if (!self::isAvaliable())
		{
			return [];
		}

		return self::getGroupThemePicker($groupId)->getCurrentTheme();
	}

	protected static function getUserThemePicker(): Bitrix24\ThemePicker
	{
		return new Bitrix24\ThemePicker(
			SITE_TEMPLATE_ID,
			SITE_ID,
			self::getUserId(),
			Bitrix24\ThemePicker::ENTITY_TYPE_USER,
			self::getUserId(),
		);
	}

	protected static function getGroupThemePicker(int $groupId): Bitrix24\ThemePicker
	{
		return new Bitrix24\ThemePicker(
			SITE_TEMPLATE_ID,
			SITE_ID,
			self::getUserId(),
			Bitrix24\ThemePicker::ENTITY_TYPE_SONET_GROUP,
			$groupId,
		);
	}

	protected static function isAvaliable(): bool
	{
		return Loader::includeModule('intranet') && Bitrix24\ThemePicker::isAvailable();
	}

	protected static function applyTheme(Bitrix24\ThemePicker $themePicker)
	{
		$themePicker->showHeadAssets();
		$themePicker->showBodyAssets();

		$baseTheme = $themePicker->getCurrentBaseThemeId();
		echo <<<JS
<script>
	BX.ready(() => {
		document.body.className = document.body.className.replace(/bitrix24-[^\s]*-theme/, '');
		document.body.classList.add("bitrix24-$baseTheme-theme");
	});
</script>
JS;
	}

	protected static function getUserId(): int
	{
		global $USER;

		return (int)$USER->getId();
	}
}