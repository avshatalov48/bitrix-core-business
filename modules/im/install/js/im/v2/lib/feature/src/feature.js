import { Core } from 'im.v2.application.core';

import { ChatHistoryManager } from './classes/chat-history';

export const Feature = {
	chatV2: 'chatV2',
	openLinesV2: 'openLinesV2',
	chatDepartments: 'chatDepartments',
	copilotActive: 'copilotActive',
	copilotAvailable: 'copilotAvailable',
	sidebarLinks: 'sidebarLinks',
	sidebarFiles: 'sidebarFiles',
	sidebarBriefs: 'sidebarBriefs',
	zoomActive: 'zoomActive',
	zoomAvailable: 'zoomAvailable',
	giphyAvailable: 'giphyAvailable',
	collabAvailable: 'collabAvailable',
	collabCreationAvailable: 'collabCreationAvailable',
	inviteByLinkAvailable: 'inviteByLinkAvailable',
	inviteByPhoneAvailable: 'inviteByPhoneAvailable',
	documentSignAvailable: 'documentSignAvailable',
	intranetInviteAvailable: 'intranetInviteAvailable',
	voteCreationAvailable: 'voteCreationAvailable',
};

export const FeatureManager = {
	chatHistory: ChatHistoryManager,

	isFeatureAvailable(featureName: $Values<typeof Feature>): boolean
	{
		const { featureOptions = {} } = Core.getApplicationData();

		return featureOptions[featureName] ?? false;
	},
};
