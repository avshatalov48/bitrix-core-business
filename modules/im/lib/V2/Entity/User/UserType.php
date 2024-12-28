<?php

namespace Bitrix\Im\V2\Entity\User;

enum UserType: string
{
	case USER = 'user';
	case BOT = 'bot';
	case EXTRANET = 'extranet';
	case COLLABER = 'collaber';
}
