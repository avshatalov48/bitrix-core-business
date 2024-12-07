import { Core } from 'im.v2.application.core';

import { ChatHistoryManager } from './classes/chat-history';

export const Feature = {
	chatV2: 'chatV2',
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
};

export const FeatureManager = {
	chatHistory: ChatHistoryManager,

	isFeatureAvailable(featureName: $Values<typeof Feature>): boolean
	{
		const { featureOptions = {} } = Core.getApplicationData();

		return featureOptions[featureName] ?? false;
	},
};
