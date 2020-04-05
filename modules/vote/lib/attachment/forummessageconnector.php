<?php
namespace Bitrix\Vote\Attachment;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class ForumMessageConnector extends Connector
{
	protected static $messages = array();
	protected static $topics = array();

	private $canRead = null;


	/**
	 * @param integer $userId User ID.
	 * @return bool
	 */
	public function canRead($userId)
	{
		return $this->canRead;
	}

	/**
	 * @param integer $userId User ID.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		return $this->canRead($userId);
	}
}
