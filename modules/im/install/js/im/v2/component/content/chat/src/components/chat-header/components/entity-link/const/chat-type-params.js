import { ChatType, ChatEntityLinkType } from 'im.v2.const';
import { Loc } from 'main.core';

type ChatTypeParams = {
	[chatType: $Values<typeof ChatType>]: {
		className: string,
		loc: string,
	}
};

export const ParamsByLinkType: ChatTypeParams = {
	[ChatEntityLinkType.tasks]: {
		className: '--task',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_TASK'),
	},
	[ChatEntityLinkType.calendar]: {
		className: '--calendar',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MEETING_MSGVER_1'),
	},
	[ChatEntityLinkType.sonetGroup]: {
		className: '--group',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_GROUP_MSGVER_1'),
	},
	[ChatEntityLinkType.mail]: {
		className: '--mail',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MAIL_MSGVER_1'),
	},
	[ChatEntityLinkType.contact]: {
		className: '--crm',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_CONTACT'),
	},
	[ChatEntityLinkType.deal]: {
		className: '--crm',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_DEAL'),
	},
	[ChatEntityLinkType.lead]: {
		className: '--crm',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_LEAD'),
	},
	[ChatEntityLinkType.dynamic]: {
		className: '--crm',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_DYNAMIC_ELEMENT'),
	},
};
