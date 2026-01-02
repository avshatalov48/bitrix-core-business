<?php

declare(strict_types = 1);

namespace Bitrix\Main\Web\UserAgent;

enum Platform: string
{
	case Android = 'Android';
	case Ios = 'iOS';
	case Windows = 'Windows';
	case Macos = 'macOS';
	case LinuxRpm = 'Linux RPM';
	case LinuxDeb = 'Linux DEB';
	case Unknown = 'Unknown';

	public function isMobile(): bool
	{
		return in_array($this, [self::Android, self::Ios], true);
	}

	public function isDesktop(): bool
	{
		return in_array($this, [self::Windows, self::Macos, self::LinuxRpm, self::LinuxDeb], true);
	}

	public function isLinux(): bool
	{
		return in_array($this, [self::LinuxRpm, self::LinuxDeb], true);
	}

	public static function fromUserAgent(string $userAgent): self
	{
		$userAgent = strtolower($userAgent);

		if (str_contains($userAgent, 'bitrixmobile') || str_contains($userAgent, 'bitrix24/'))
		{
			if (
				str_contains($userAgent, 'iphone')
				|| str_contains($userAgent, 'ipad')
				|| str_contains($userAgent, 'darwin')
			)
			{
				return self::Ios;
			}

			return self::Android;
		}

		if (self::isWindowsByUserAgent($userAgent))
		{
			return self::Windows;
		}

		if (self::isMacosByUserAgent($userAgent))
		{
			return self::Macos;
		}

		if (self::isLinuxByUserAgent($userAgent))
		{
			return self::getLinuxPlatform($userAgent);
		}

		if (self::isIosByUserAgent($userAgent))
		{
			return self::Ios;
		}

		if (self::isAndroidByUserAgent($userAgent) || str_contains($userAgent, 'carddavbitrix24'))
		{
			return self::Android;
		}

		return self::Unknown;
	}

	private static function isWindowsByUserAgent(string $userAgent): bool
	{
		return str_contains($userAgent, 'windows')
			|| str_contains($userAgent, 'win32')
			|| str_contains($userAgent, 'win64');
	}

	private static function isLinuxByUserAgent(string $userAgent): bool
	{
		return str_contains($userAgent, 'linux') && !self::isAndroidByUserAgent($userAgent);
	}

	private static function isIosByUserAgent(string $userAgent): bool
	{
		return str_contains($userAgent, 'iphone')
			|| str_contains($userAgent, 'ipad')
			|| str_contains($userAgent, 'ipod')
			|| (str_contains($userAgent, 'ios') && !str_contains($userAgent, 'windows'));
	}

	private static function isMacosByUserAgent(string $userAgent): bool
	{
		return (str_contains($userAgent, 'mac os') && !str_contains($userAgent, 'like mac os x'))
			|| str_contains($userAgent, 'macintosh');
	}

	private static function isAndroidByUserAgent(string $userAgent): bool
	{
		return str_contains($userAgent, 'android');
	}

	private static function getLinuxPlatform(string $userAgent): self
	{
		if (
			str_contains($userAgent, 'rpm')
			|| str_contains($userAgent, 'fedora')
			|| str_contains($userAgent, 'centos')
			|| str_contains($userAgent, 'rhel')
		)
		{
			return self::LinuxRpm;
		}

		return self::LinuxDeb;
	}
}
