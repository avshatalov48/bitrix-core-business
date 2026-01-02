<?php

namespace Bitrix\Im\V2\Permission;

enum Action: string
{
	case Call = 'CALL';
	case Mute = 'MUTE';
	case Leave = 'LEAVE';
	case LeaveOwner = 'LEAVE_OWNER';
	case Send = 'SEND';
	case UserList = 'USER_LIST';
	case ChangeAvatar = 'AVATAR';
	case Rename = 'RENAME';
	case Extend = 'EXTEND';
	case Kick = 'KICK';
	case ChangeColor = 'COLOR';
	case ChangeDescription = 'DESCRIPTION';
	case ChangeRight = 'RIGHT';
	case ChangeOwner = 'CHANGE_OWNER';
	case ChangeManagers = 'CHANGE_MANAGERS';
	case PinMessage = 'PIN_MESSAGE';
	case CreateTask = 'CREATE_TASK';
	case CreateMeeting = 'CREATE_MEETING';
	case DeleteOthersMessage = 'DELETE_OTHERS_MESSAGE';
	case Update = 'UPDATE';
	case Delete = 'DELETE';
	case UpdateInviteLink = 'UPDATE_INVITE_LINK';
	case CreateDocumentSign = 'CREATE_DOCUMENT_SIGN';
	case CreateCalendarSlots = 'CREATE_CALENDAR_SLOTS';
	case ChangeMessagesAutoDeleteDelay = 'CHANGE_MESSAGES_AUTO_DELETE_DELAY';
	case PinChat = 'PIN_CHAT';
	case HideChat = 'HIDE_CHAT';

	public function getRelatedGlobalAction(): ?GlobalAction
	{
		return match ($this)
		{
			self::ChangeMessagesAutoDeleteDelay => GlobalAction::ChangeMessagesAutoDeleteDelay,
			default => null
		};
	}
}
