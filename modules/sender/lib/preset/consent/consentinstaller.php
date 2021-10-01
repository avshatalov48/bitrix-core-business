<?php

namespace Bitrix\Sender\Preset\Consent;


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserConsent\Agreement;
use Bitrix\Main\UserConsent\Internals\AgreementTable;
Loc::loadMessages(__FILE__);

class ConsentInstaller
{
	public static function run(string $lang): string
	{
		self::createConsent('sender_approve_confirmation_', $lang);

		return '';
	}

	private static function createConsent(string $code, string $lang)
	{
		$preparedCode = $code.$lang;
		$existed = AgreementTable::getList(array(
			'select' => ['ID'],
			'filter' => [
				'=CODE' => $preparedCode,
				'=LANGUAGE_ID' => $lang,
			],
			'limit' => 1
		));


		if ($existed->fetch())
		{
			return '';
		}

		$title = Loc::getMessage($code.'title', null, $lang);
		if (!$title)
		{
			return '\\Bitrix\\Sender\\Preset\\Consent\\ConsentInstaller::run(\''.$lang.'\')';
		}

		AgreementTable::add(array(
			"CODE" => $preparedCode,
			"NAME" => $title,
			"TYPE" => Agreement::TYPE_CUSTOM,
			"LANGUAGE_ID" => $lang,
			"AGREEMENT_TEXT" => Loc::getMessage($code . 'text',null, $lang),
			"LABEL_TEXT" => Loc::getMessage($code . 'label', null, $lang),
		));
	}
}