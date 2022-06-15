<?php
namespace Bitrix\Sender\Integration\Bitrix24\Limitation;

class PortalVerifyLimit extends DailyLimit
{
	/**
	 * @return integer
	 */
	public function getCurrent()
	{
		return 1;
	}

	/**
	 * @return integer
	 */
	public function getLimit()
	{
		$limit = parent::getLimit() ?: 1000;
		return $this->isVerifiedSender() ? $limit : 0;
	}

	/**
	 * @return void
	 */
	public function setLimit($limit)
	{
	}

	protected function isVerifiedSender(): bool
	{
		return Verification::isEmailConfirmed() && Verification::isPhoneConfirmed();
	}
}
