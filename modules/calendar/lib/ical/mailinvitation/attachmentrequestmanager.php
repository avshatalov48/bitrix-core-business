<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

/**
 * Class AttachmentRequestManager
 * @package Bitrix\Calendar\ICal\MailInvitation
 */
class AttachmentRequestManager extends AttachmentManager
{
	/**
	 * AttachmentRequestManager constructor.
	 * @param array $event
	 */
	public function __construct(array $event)
	{
		parent::__construct($event);
		$this->uid = Helper::getUniqId();
	}
}
