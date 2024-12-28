<?php

namespace Bitrix\Im\V2\Permission;

enum GlobalAction: string
{
	case GetChannels = 'GET_CHANNELS';
	case GetMarket = 'GET_MARKET';
	case GetOpenlines = 'GET_OPENLINES';
	case CreateChat = 'CREATE_CHAT';
	case CreateConference = 'CREATE_CONFERENCE';
	case CreateChannel = 'CREATE_CHANNEL';
	case CreateCollab = 'CREATE_COLLAB';
	case CreateCopilot = 'CREATE_COPILOT';
	case LeaveCollab = 'LEAVE_COLLAB';
}