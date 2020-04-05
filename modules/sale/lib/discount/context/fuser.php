<?php

namespace Bitrix\Sale\Discount\Context;

use Bitrix\Sale\Discount\RuntimeCache;

class Fuser extends BaseContext
{
	protected $fuserId;

	/**
	 * FUser constructor.
	 *
	 * @param int $fuserId
	 */
	public function __construct($fuserId)
	{
		$this->fuserId = $fuserId;
		$this->userId = RuntimeCache\FuserCache::getInstance()->getUserIdById($this->fuserId);
		$this->setUserGroups(\CUser::getUserGroup($this->userId));
	}
}