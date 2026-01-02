<?php

namespace Bitrix\Im\V2\Message\Delete;

use JsonSerializable;

enum MessageType: string
{
	case OwnMessageUnread = 'OWN_MESSAGE_UNREAD';
	case OwnMessageRead = 'OWN_MESSAGE_READ';
	case OtherMessage = 'OTHER_MESSAGE';
}
