<?php

namespace Bitrix\Forum\Comments;

final class TimemanEntryEntity extends Entity
{
	const ENTITY_TYPE = 'tm';
	const MODULE_ID = 'timeman';
	const XML_ID_PREFIX = 'TIMEMAN_ENTRY_';

	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canRead($userId)
	{
		return true;
	}
	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canAdd($userId)
	{
		return $this->canRead($userId);
	}

	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canEditOwn($userId)
	{
		return true;
	}

	/**
	 * @var integer $userId User Id.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		return false;
	}
}