<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Providers\Edna\RegionHelper;

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
		return Loc::getMessage(RegionHelper::getPhrase('MESSAGESERVICE_SENDER_SMS_SMSEDNARU_NAME'));
	}

	public function getShortName(): string
	{
		if (RegionHelper::isInternational())
		{
			return 'sms.edna.io';
		}

		return 'sms.edna.ru';
	}
}