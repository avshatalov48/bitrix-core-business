<?php

namespace Bitrix\Sender\Integration\Sender\Mail;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\IO\File;
use Bitrix\Main\SiteTable;
use Bitrix\Sender\Consent\Consent;
use Bitrix\Sender\Integration\Bitrix24\Service;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Consent\AbstractConsentMessageBuilder;
use Bitrix\Sender\Transport\iBase;

class ConsentBuilderMail extends AbstractConsentMessageBuilder
{
	const CODE = iBase::CODE_MAIL;
	const REQUIRED_FIELDS = ['RECIPIENT_ID', 'CONTACT_ID', 'CONTACT_CODE', 'SITE_ID'];
	const CONSENT_EVENT = "SENDER_CONSENT";
	protected const APPLY = 1;
	protected const REJECT = 2;
	protected const URI = "/pub/mail/consent.php";

	protected static function getFilter(): \Closure
	{
		return function ($item, $key)
		{
			return isset($item, $key) && in_array($key, static::REQUIRED_FIELDS);
		};
	}

	protected static function filterFields(array $fieldForConsent): array
	{
		return array_filter($fieldForConsent, static::getFilter(), ARRAY_FILTER_USE_BOTH);
	}

	protected static function checkRequireFields($fields): bool
	{
		foreach (static::REQUIRED_FIELDS as $field)
		{
			if (!array_key_exists($field, $fields))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * @return array for \Bitrix\Main\Mail\Event
	 */
	public function buildMessage(): array
	{
		['CONTACT_CODE' => $code, 'SITE_ID' => $siteId] = $this->fields;
		$typeId = Recipient\Type::detect($code);
		$code = Recipient\Normalizer::normalize($code, $typeId);
		return [
			"EVENT_NAME" => static::CONSENT_EVENT,
			"C_FIELDS" => [
				"EMAIL" => $code,
				"SENDER_CONSENT_APPLY" => $this->buildLinkApply(),
				"SENDER_CONSENT_REJECT" => $this->buildLinkReject(),
			],
			"LID" => is_array($siteId) ? implode(",", $siteId) : $siteId,
		];
	}

	protected function buildLinkApply(): string
	{
		return static::buildLink([
			'CODE' => $this->fields['CONTACT_CODE'] ?? '',
			'CONTACT' => $this->fields['CONTACT_ID'] ?? '',
			'RECIPIENT' => $this->fields['RECIPIENT_ID'] ?? '',
			'POSTING' => $this->fields['POSTING_ID'] ?? '',
			'CONSENT' => $this->fields['CONSENT_ID'] ?? '',
		], $this->fields['SITE_ID'] ?? '', static::APPLY);
	}

	protected function buildLinkReject(): string
	{
		return static::buildLink([
			'CODE' => $this->fields['CONTACT_CODE'] ?? '',
			'CONTACT' => $this->fields['CONTACT_ID'] ?? '',
			'RECIPIENT' => $this->fields['RECIPIENT_ID'] ?? '',
			'POSTING' => $this->fields['POSTING_ID'] ?? '',
			'CONSENT' => $this->fields['CONSENT_ID'] ?? '',
		], $this->fields['SITE_ID'], static::REJECT);
	}

	protected static function buildQuery(array $fields): string
	{
		return http_build_query($fields);
	}

	protected static function buildLink($fields, $siteId, $type): string
	{
		$tag = Consent::encodeTag($fields);
		$dir = static::getLink($siteId);
		switch ($type)
		{
			case static::APPLY:
				$result = $dir . '?' . static::buildQuery(['consent' => 'apply', 'type' => static::CODE, 'tag' => $tag]);
				break;
			case static::REJECT:
				$result = $dir . '?' . static::buildQuery(['consent' => 'reject', 'type' => static::CODE, 'tag' => $tag]);
				break;
			default:
				throw new \InvalidArgumentException("Type is out of range");
		}
		return $result;
	}

	protected static function checkUri($siteId): bool
	{
		return $siteId && File::isFileExists(SiteTable::getDocumentRoot($siteId) . DIRECTORY_SEPARATOR . static::URI);
	}

	protected static function getLink($siteId): ?string
	{
		$uri = null;
		if (static::checkUri($siteId))
		{
			$uri = static::URI;
			if (Service::isCloud() && !in_array(mb_substr(BX24_HOST_NAME, -7), ['.com.br', '.com.de'])) // exclude com.br & com.de domains
			{
				$domain = BX24_HOST_NAME;
				if (!\CBitrix24::isCustomDomain())
				{
					$domain = preg_replace('/^([-\.\w]+)\.bitrix24\.([-\.\w]+)/', '$2.$1', $domain);
					$domain = "mailinternetsub.com/" . $domain;
				}
				$uri = "https://$domain$uri";
			}
		}
		return $uri;
	}
}