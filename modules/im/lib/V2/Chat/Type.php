<?php

namespace Bitrix\Im\V2\Chat;

enum Type: string
{
	case Private = 'PRIVATE';
	case Chat = 'CHAT';
	case OpenChat = 'OPEN';
	case General = 'GENERAL';
	case Channel = 'CHANNEL';
	case OpenChannel = 'OPEN_CHANNEL';
	case GeneralChannel = 'GENERAL_CHANNEL';
	case Comment = 'COMMENT';
	case Copilot = 'COPILOT';
	case Collab = 'COLLAB';
	case Announcement = 'ANNOUNCEMENT';
	case Videoconference = 'VIDEOCONF';
	case Support24Notifier = 'SUPPORT24_NOTIFIER';
	case Support24Question = 'SUPPORT24_QUESTION';
	case NetworkDialog = 'NETWORK_DIALOG';
	case Calendar = 'CALENDAR';
	case Mail = 'MAIL';
	case Crm = 'CRM';
	case Sonet = 'SONET_GROUP';
	case Tasks = 'TASKS';
	case Call = 'CALL';
	case Lines = 'LINES';
}
