<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;

enum ExtendedType: string
{
	protected const INTERNAL_TYPES = [
		Chat::IM_TYPE_PRIVATE => self::Private,
		Chat::IM_TYPE_OPEN_LINE => self::Lines,
		Chat::IM_TYPE_CHANNEL => self::Channel,
		Chat::IM_TYPE_OPEN_CHANNEL => self::OpenChannel,
		Chat::IM_TYPE_COMMENT => self::Comment,
		Chat::IM_TYPE_COLLAB => self::Collab,
		Chat::IM_TYPE_COPILOT => self::Copilot,
		Chat::IM_TYPE_OPEN => self::OpenChat,
		Chat::IM_TYPE_CHAT => self::Chat,
	];

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

	public function isInternal(): bool
	{
		return in_array($this, self::INTERNAL_TYPES, true);
	}

	public static function tryFromEntityType(string $entityType): ?self
	{
		$extendedType = self::tryFrom($entityType);

		return $extendedType?->isInternal() ? null : $extendedType;
	}

	public static function tryFromTypeLiteral(string $typeLiteral): self
	{
		return self::INTERNAL_TYPES[$typeLiteral] ?? self::Chat;
	}
}
