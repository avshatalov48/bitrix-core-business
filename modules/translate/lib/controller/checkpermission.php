<?php
namespace Bitrix\Translate\Controller;

use Bitrix\Translate;
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;


class CheckPermission extends Main\Engine\ActionFilter\Base
{
	const ERROR_ACCESS_DENIED = 'access_denied';

	/**
	 * @var string
	 */
	private $checkLevel;

	/**
	 * Permission constructor.
	 *
	 * @param string $checkLevel Level checking for.
	 */
	public function __construct($checkLevel = 'auth')
	{
		$this->checkLevel = $checkLevel;
		parent::__construct();
	}

	/**
	 * Performs permissions checking.
	 *
	 * @param Main\Event $event Event object.
	 *
	 * @return Main\EventResult|null
	 */
	public function onBeforeAction(Main\Event $event)
	{
		/** @var Main\Engine\CurrentUser $user */
		$user = $this->action->getCurrentUser();

		$denied = false;
		$accessMessage = '';
		if (!($user instanceof Main\Engine\CurrentUser) || !$user->getId())
		{
			$denied = true;
			$accessMessage = Loc::getMessage('TRANSLATE_FILTER_ERROR_ACCESS_DENIED');
		}

		if (!$denied)
		{
			switch ($this->checkLevel)
			{
				case Translate\Permission::SOURCE:
					if (!Translate\Permission::canEditSource($user))
					{
						$denied = true;
						$accessMessage = Loc::getMessage('TRANSLATE_FILTER_ERROR_WRITING_RIGHTS');
					}
					break;

				case Translate\Permission::WRITE:
					if (!Translate\Permission::canEdit($user))
					{
						$denied = true;
						$accessMessage = Loc::getMessage('TRANSLATE_FILTER_ERROR_WRITING_RIGHTS');
					}
					break;

				case Translate\Permission::READ:
					if (!Translate\Permission::canView($user))
					{
						$denied = true;
						$accessMessage = Loc::getMessage('TRANSLATE_FILTER_ERROR_ACCESS_DENIED');
					}
					break;
			}
		}

		if ($denied)
		{
			Context::getCurrent()->getResponse()->setStatus(401);
			$this->errorCollection[] = new Main\Error($accessMessage, self::ERROR_ACCESS_DENIED);

			return new Main\EventResult(Main\EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}