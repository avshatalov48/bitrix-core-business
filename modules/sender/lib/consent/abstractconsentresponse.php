<?php

namespace Bitrix\Sender\Consent;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Internals\Model\Posting\RecipientTable;
use Bitrix\Sender\Internals\SqlBatch;

abstract class AbstractConsentResponse implements iConsentResponse
{
	const APPLY_ACTION = true;
	const REJECT_ACTION = false;
	const ORIGINATOR_ID = 'sender';

	/**
	 * load data for reject/apply actions
	 * @param $data
	 *
	 * @return iConsentResponse
	 */
	public abstract function loadData($data) : iConsentResponse;

	/**
	 * deny contact to send marketing messages
	 * @return bool
	 */
	public function reject()
	{
		return $this->updateContact(static::REJECT_ACTION);
	}

	/**
	 * apply contact to send marketing messages
	 * @return bool
	 */
	public function apply()
	{
		\Bitrix\Main\UserConsent\Consent::addByContext($this->getConsentId(), self::ORIGINATOR_ID, $this->getPostingId());
		return $this->updateContact(static::APPLY_ACTION);
	}

	/**
	 * return Contact ID
	 * @return mixed
	 */
	protected abstract function getContactId();

	/**
	 * @return mixed
	 */
	protected abstract function getConsentId();

	/**
	 * @return mixed
	 */
	protected abstract function getPostingId();

	/**
	 * @param $apply
	 * @return mixed
	 */
	protected abstract function isContactUpdated($apply);

	/**
	 * @param $apply
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function updateContact($apply)
	{
		$result = false;
		if ($this->isContactUpdated($apply))
		{
			$contactId = $this->getContactId();
			$type = ($apply? ContactTable::CONSENT_STATUS_ACCEPT : ContactTable::CONSENT_STATUS_DENY);
			$isUnsub = ($apply? 'N':'Y');
			$result = ContactTable::update($contactId,[
				'CONSENT_STATUS' => $type,
				'IS_UNSUB' => $isUnsub,
				'DATE_UPDATE' => new DateTime()
			])->isSuccess();
			$recipients = RecipientTable::getList([
				'select' => ['ID','STATUS'],
				'filter' => [
					'=CONTACT_ID' => $contactId,
					'@STATUS' => [RecipientTable::SEND_RESULT_NONE, RecipientTable::SEND_RESULT_WAIT_ACCEPT]
				]
			])->fetchAll();
			if(!empty($recipients))
			{
				SqlBatch::update(RecipientTable::getTableName(),array_map(
					function($recipient) use ($isUnsub)
					{
						$changeStatus = $recipient['STATUS'] === RecipientTable::SEND_RESULT_WAIT_ACCEPT;
						return [
							'ID' => $recipient['ID'],
							'STATUS' => ($changeStatus? RecipientTable::SEND_RESULT_NONE: $recipient['STATUS']),
							'IS_UNSUB' => $isUnsub
						];
					},
					$recipients
				));
			}
		}
		return $result;
	}
}