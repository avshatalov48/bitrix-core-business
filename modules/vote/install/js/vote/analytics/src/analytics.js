import { Runtime } from 'main.core';
import { sendData, type AnalyticsOptions } from 'ui.analytics';
import { AnalyticsCategories, AnalyticsEvents } from './const';

const getAnalyticsOptions = async (dialogId: string, messageId: number): Promise<Partial<AnalyticsOptions> | null> => {
	try
	{
		const { Analytics: ImAnalytics } = await Runtime.loadExtension('im.v2.lib.analytics');
		const analyticsInstance = ImAnalytics.getInstance();
		const options = analyticsInstance.vote.getAnalyticsOptions(dialogId);
		options.p3 = `pollId_${messageId}`;

		return options;
	}
	catch (ex)
	{
		console.error(ex);

		return null;
	}
};

const publishVote = (anonymousVote: boolean, messageId: number): void => {
	const { searchParams } = new URL(document.location.href);
	const options = {
		tool: searchParams.get('st[tool]'),
		event: AnalyticsEvents.publishPoll,
		type: anonymousVote ? 'anonymous' : 'public',
		category: searchParams.get('st[category]'),
		p1: searchParams.get('st[p1]'),
		p2: searchParams.get('st[p2]'),
		p3: `pollId_${messageId}`,
		p5: searchParams.get('st[p5]'),
	};
	if (searchParams.has('st[p4]'))
	{
		options.p4 = searchParams.get('st[p4]');
	}

	sendData(options);
};

const sendDataByImOptions = async (
	dialogId: string,
	messageId: number,
	event: string,
	type: ?string,
): Promise<void> => {
	const analyticsOptions = await getAnalyticsOptions(dialogId, messageId);
	if (!analyticsOptions)
	{
		return;
	}

	analyticsOptions.event = event;
	if (event === AnalyticsEvents.finishPoll)
	{
		sendData({ ...analyticsOptions, type });

		return;
	}

	const { p4, p5, ...rest } = analyticsOptions;
	if (type)
	{
		rest.type = type;
	}

	sendData(rest);
};

const downloadResult = (messageId: number): void => {
	const options = {
		tool: 'im',
		event: AnalyticsEvents.downloadResult,
		category: AnalyticsCategories.polls,
		p3: `pollId_${messageId}`,
	};
	sendData(options);
};

const setupVote = (optionValue: boolean | number, event: string, messageId: string): void => {
	let type = '';
	switch (event)
	{
		case AnalyticsEvents.isMultipleChoice:
			type = optionValue ? 'Y' : 'N';
			break;
		case AnalyticsEvents.setCancelVote:
			type = optionValue ? 'Y' : 'N';
			break;
		case AnalyticsEvents.setTimeLimit:
			type = optionValue ? 'limited' : '';
			break;
		case AnalyticsEvents.setOptions:
			type = optionValue > 2 ? 'multiple' : 'two';
			break;
		default:
			break;
	}

	const options = {
		tool: 'im',
		category: AnalyticsCategories.pollSettings,
		event,
		type,
		p3: `pollId_${messageId}`,
	};
	sendData(options);
};

export const VoteAnalytics = {
	publishVote,
	downloadResult,
	setupVote,
	vote: (dialogId: string, messageId: number): void => {
		sendDataByImOptions(dialogId, messageId, AnalyticsEvents.vote);
	},
	revokeVote: (dialogId: string, messageId: number): void => {
		sendDataByImOptions(dialogId, messageId, AnalyticsEvents.cancelVote);
	},
	completeVote: (dialogId: string, messageId: number): void => {
		sendDataByImOptions(dialogId, messageId, AnalyticsEvents.finishPoll, 'user');
	},
	copyLink: (dialogId: string, messageId: number, type: string): void => {
		sendDataByImOptions(dialogId, messageId, AnalyticsEvents.copyPollLink, type);
	},
};

export { AnalyticsEvents };
