<?php


namespace Bitrix\Main\Engine\ActionFilter;


use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class Authentication extends Base
{
	const ERROR_INVALID_AUTHENTICATION = 'invalid_authentication';

	/**
	 * @var bool
	 */
	private $enableRedirect;

	public function __construct($enableRedirect = false)
	{
		$this->enableRedirect = $enableRedirect;
		parent::__construct();
	}

	public function onBeforeAction(Event $event)
	{
		global $USER;

		if (!($USER instanceof \CAllUser) || !$USER->getId())
		{
			if ($this->enableRedirect)
			{
				LocalRedirect(
					SITE_DIR .
					'auth/?backurl=' .
					urlencode(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getRequestUri())
				);

				return new EventResult(EventResult::ERROR, null, null, $this);
			}

			$this->errorCollection[] = new Error('Need to authenticate', self::ERROR_INVALID_AUTHENTICATION);

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}