<?php

namespace Bitrix\Sender\Integration\Sender\Mail;

use Bitrix\Main\ArgumentException;
use Bitrix\Sender\Consent\AbstractConsentResponse;
use Bitrix\Sender\Consent\Consent;
use Bitrix\Sender\Consent\iConsentResponse;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Internals\Model\Posting\RecipientTable;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Transport\iBase;

final class ConsentResponseMail extends AbstractConsentResponse
{
	const CODE = iBase::CODE_MAIL;
	private $fields;

	/**
	 * load data from string tag or array
	 * @param array|string $data
	 *
	 * @return ConsentBuilderMail
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\Security\Sign\BadSignatureException
	 */
	public function loadData($data): iConsentResponse
	{
		if (is_string($data))
		{
			$this->fields = Consent::decodeTag($data);
		} elseif (is_array($data))
		{
			$this->fields = $data;
		} else
		{
			throw new ArgumentException("");
		}
		return $this;
	}

	protected function isContactUpdated($apply)
	{
		$typeId = Recipient\Type::detect($this->fields['CODE']);
		$code = Recipient\Normalizer::normalize($this->fields['CODE'], $typeId);

		$contact = ContactTable::getRow([
			'select' => ['CODE', 'CONSENT_STATUS'],
			'filter' => [
				'=ID' => $this->getContactId(),
				'=TYPE_ID' => $typeId,
				'=CODE' => $code,
			]
		]);
		$currentStatus = ($apply ? ContactTable::CONSENT_STATUS_ACCEPT : ContactTable::CONSENT_STATUS_DENY);

		return (
			isset($contact) &&
			$contact['CODE'] === $this->fields['CODE'] &&
			$contact['CONSENT_STATUS'] !== $currentStatus
		);
	}

	protected function getContactId()
	{
		return $this->fields['CONTACT'];
	}

	protected function getConsentId()
	{
		return $this->fields['CONSENT'];
	}

	protected function getPostingId()
	{
		return $this->fields['POSTING'];
	}
}