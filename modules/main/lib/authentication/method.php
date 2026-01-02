<?php

namespace Bitrix\Main\Authentication;

enum Method: string
{
	case Unspecified = 'unspecified';
	case Password = 'password';
	case Otp = 'otp';
	case AppPassword = 'appPassword';
	case External = 'external';
	case LoginAs = 'loginAs';
	case Cookie = 'cookie';
	case HitHash = 'hitHash';
	case Registration = 'registration';
	case EmailCode = 'emailCode';
	case PhoneCode = 'phoneCode';
	case Controller = 'controller';
}
