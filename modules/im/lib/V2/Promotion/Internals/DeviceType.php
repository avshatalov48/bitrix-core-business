<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion\Internals;

enum DeviceType: string
{
	case WEB = 'web';
	case BROWSER = 'browser';
	case DESKTOP = 'desktop';
	case MOBILE = 'mobile';
	case ALL = 'all';

	public function isDeviceTypeAvailable(string $type): bool
	{
		$availableTypes = $this->getTypesMap()[$this->value];

		return in_array(self::tryFrom($type), $availableTypes, true);
	}

	private function getTypesMap(): array
	{
		return [
			self::WEB->value => [self::ALL, self::BROWSER, self::WEB, self::DESKTOP],
			self::BROWSER->value => [self::ALL, self::BROWSER, self::WEB],
			self::DESKTOP->value => [self::ALL, self::WEB, self::DESKTOP],
			self::MOBILE->value => [self::ALL, self::MOBILE],
			self::ALL->value => [self::ALL, self::BROWSER, self::WEB, self::DESKTOP, self::MOBILE],
		];
	}
}
