<?php

namespace Bitrix\Mail\Disk\Security;

use Bitrix\Main;
use Bitrix\Mail;
use Bitrix\Disk;

if (!Main\Loader::includeModule('disk'))
{
	return false;
}

class MailSecurityContext extends Disk\Security\SecurityContext
{

	/**
	 * @param $targetId
	 * @return bool
	 */
	public function canAdd($targetId)
	{
		return true;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canChangeRights($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canChangeSettings($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canCreateWorkflow($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canDelete($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canMarkDeleted($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @param $targetId
	 * @return bool
	 */
	public function canMove($objectId, $targetId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canRead($objectId)
	{
		global $DB;

		$message = $DB->query(sprintf(
			'SELECT ID, MAILBOX_ID FROM b_mail_message WHERE ID IN (
				SELECT MESSAGE_ID FROM b_mail_msg_attachment WHERE FILE_ID = (
					SELECT FILE_ID FROM b_disk_object WHERE ID = %u
				)
			)',
			$objectId
		))->fetch();

		return Mail\Helper\Message::hasAccess($message, $this->userId);
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canRename($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canRestore($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canShare($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canUpdate($objectId)
	{
		return false;
	}

	/**
	 * @param $objectId
	 * @return bool
	 */
	public function canStartBizProc($objectId)
	{
		return false;
	}

	public function getSqlExpressionForList($columnObjectId, $columnCreatedBy)
	{
		return '1 = 0';
	}

}
