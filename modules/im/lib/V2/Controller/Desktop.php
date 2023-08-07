<?php

namespace Bitrix\Im\V2\Controller;

class Desktop extends BaseController
{
	/**
	 * @restMethod im.v2.Desktop.logout
	 */
	public function logoutAction(): ?array
	{
		\CIMMessenger::SetDesktopStatusOffline();
		\CIMContactList::SetOffline();

		return [];
	}
}