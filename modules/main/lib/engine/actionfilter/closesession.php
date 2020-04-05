<?php

namespace Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main\Event;

/**
 * Class CloseSession
 * Be careful by using this feature. You will close session and code below can't work with it until session will be open.
 * @package Bitrix\Main\Engine\ActionFilter
 */
final class CloseSession extends Base
{
	/**
	 * @var bool
	 */
	private $enabled;

	/**
	 * Close session constructor.
	 * @param bool $enabled
	 */
	public function __construct($enabled = true)
	{
		$this->enabled = $enabled;

		parent::__construct();
	}

	public function onBeforeAction(Event $event)
	{
		if (!$this->enabled)
		{
			return;
		}

		session_write_close();
	}
}