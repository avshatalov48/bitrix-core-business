import { Core } from 'im.v2.application.core';

import { ChatHistoryManager } from './classes/chat-history';
import { MessagesAutoDelete } from './classes/messages-auto-delete';
import { CollabManager } from './classes/collab';

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
	enabledCollabersInvitation: 'enabledCollabersInvitation',
	changeInviteLanguageAvailable: 'changeInviteLanguageAvailable',
	inviteByLinkAvailable: 'inviteByLinkAvailable',
	inviteByPhoneAvailable: 'inviteByPhoneAvailable',
	documentSignAvailable: 'documentSignAvailable',
	intranetInviteAvailable: 'intranetInviteAvailable',
	voteCreationAvailable: 'voteCreationAvailable',
	defaultTabCopilotAvailable: 'defaultTabCopilotAvailable',
	messagesAutoDeleteEnabled: 'messagesAutoDeleteEnabled',
	isNotificationsStandalone: 'isNotificationsStandalone',
	isAIModelChangeAllowed: 'isCopilotSelectModelAvailable',
	teamsInStructureAvailable: 'teamsInStructureAvailable',
	isDesktopRedirectAvailable: 'isDesktopRedirectAvailable',
	aiAssistantBotAvailable: 'aiAssistantAvailable',
	aiAssistantChatAvailable: 'aiAssistantChatCreationAvailable',
	aiFileTranscriptionAvailable: 'aiFileTranscriptionAvailable',
	isTasksRecentListAvailable: 'isTasksRecentListAvailable',
};

export const FeatureManager = {
	chatHistory: ChatHistoryManager,
	messagesAutoDelete: MessagesAutoDelete,
	collab: CollabManager,

	isFeatureAvailable(featureName: $Values<typeof Feature>): boolean
	{
		const { featureOptions = {} } = Core.getApplicationData();

		return featureOptions[featureName] ?? false;
	},
};
