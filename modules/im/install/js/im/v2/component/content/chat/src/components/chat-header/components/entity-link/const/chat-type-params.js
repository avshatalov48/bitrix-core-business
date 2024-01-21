import { ChatType } from 'im.v2.const';
import { Loc } from 'main.core';

type ChatTypeParams = {
	[chatType: $Values<typeof ChatType>]: {
		className: string,
		loc: string,
	}
};

export const ParamsByChatType: ChatTypeParams = {
	[ChatType.tasks]: {
		className: '--task',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_TASK'),
	},
	[ChatType.calendar]: {
		className: '--calendar',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MEETING'),
	},
	[ChatType.sonetGroup]: {
		className: '--group',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_GROUP'),
	},
	[ChatType.crm]: {
		className: '--crm',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_CRM'),
	},
	[ChatType.mail]: {
		className: '--mail',
		loc: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MAIL'),
	},
};

const CrmEntityType = {
	lead: 'LEAD',
	deal: 'DEAL',
	contact: 'CONTACT',
	company: 'COMPANY',
};

export const CrmLinkTextByEntity: {[entityType: $Values<typeof CrmEntityType>]: string} = {
	[CrmEntityType.lead]: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_LEAD'),
	[CrmEntityType.deal]: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_DEAL'),
	[CrmEntityType.contact]: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_CONTACT'),
	[CrmEntityType.company]: Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_COMPANY'),
};
