<?php

namespace Bitrix\Forum\Provider;

class Mobile
{
	/**
	 * Checks if the mobile app installed for the logged-in user
	 * @return bool
	 */
	public function isMobileAppInstalled(): bool
	{
		if (\CUserOptions::GetOption('mobile', 'iOsLastActivityDate') !== false)
		{
			return true;
		}

		if (\CUserOptions::GetOption('mobile', 'AndroidLastActivityDate') !== false)
		{
			return true;
		}

		return false;
	}
}