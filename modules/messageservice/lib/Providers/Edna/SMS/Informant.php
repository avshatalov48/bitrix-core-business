<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\Main\Localization\Loc;

class Informant extends \Bitrix\MessageService\Providers\Base\Informant
{

	public function isConfigurable(): bool
	{
		return true;
	}

	public function getId(): string
	{
		return Constants::ID;
	}

	public function getName(): string
	{
		return Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSEDNARU_NAME');
	}

	public function getShortName(): string
	{
		return 'sms.edna.ru';
	}
}