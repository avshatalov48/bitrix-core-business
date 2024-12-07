<?php
namespace Bitrix\Main\Controller\Security;

use Bitrix\Main\Engine;
use Bitrix\Main\Security\Notifications\VendorNotificationSignTable;

class VendorNotification extends Engine\Controller
{
	public function signAction(string $notificationId): array
	{
		global $USER;

		VendorNotificationSignTable::signOrIgnore($notificationId, $USER->getId());

		return [];
	}
}
