<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Main\Engine\ActionFilter\CloseSession;

class Desktop extends BaseController
{
	public function configureActions()
	{
		return [
			'logout' => [
				'-prefilters' => [
					CloseSession::class,
				],
			],
		];
	}

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