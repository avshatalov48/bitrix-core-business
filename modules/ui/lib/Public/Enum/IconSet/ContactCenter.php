<?php

declare(strict_types=1);

namespace Bitrix\Ui\Public\Enum\IconSet;

enum ContactCenter: string
{
	case DIAL_5 = 'dial-5';
	case DIAL_10 = 'dial-10';
	case CALL_FORWARDING = 'call-forwarding';
	case MOBILE_STORE = 'mobile-store';
	case MAIL_SENT = 'mail-sent';
	case INCOMING_CALL_SOUND_ON = 'incoming-call-sound-on';
	case SEND_ATTACH_FILE = 'send-attach-file';
}
