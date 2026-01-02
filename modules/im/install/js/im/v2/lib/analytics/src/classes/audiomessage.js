import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { AudioPlaybackRate, TranscriptionStatus } from 'im.v2.const';

import { AnalyticsCategory, AnalyticsEvent, AnalyticsTool } from '../const';
import { getChatType } from '../helpers/get-chat-type';

import type { ImModelChat } from 'im.v2.model';
import type { ExtendedChatType } from '../helpers/get-chat-type';

export class AudioMessage
{
	onViewTranscription(chatId: number, status: $Values<typeof TranscriptionStatus>): void
	{
		const chatType = this.#getTypeByChatId(chatId);
		const normalizedStatus = status.toLowerCase();

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.audiomessage,
			event: AnalyticsEvent.viewTranscription,
			status: normalizedStatus,
			p1: `chatType_${chatType}`,
		});
	}

	onPlay(chatId: number): void
	{
		const chatType = this.#getTypeByChatId(chatId);

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.audiomessage,
			event: AnalyticsEvent.play,
			p1: `chatType_${chatType}`,
		});
	}

	onPause(chatId: number): void
	{
		const chatType = this.#getTypeByChatId(chatId);

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.audiomessage,
			event: AnalyticsEvent.pause,
			p1: `chatType_${chatType}`,
		});
	}

	onChangeRate(chatId: number, rate: $Values<typeof AudioPlaybackRate>): void
	{
		const chatType = this.#getTypeByChatId(chatId);

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.audiomessage,
			event: AnalyticsEvent.changeSpeed,
			p1: `chatType_${chatType}`,
			p2: `speed_${rate}`,
		});
	}

	#getTypeByChatId(chatId: number): ExtendedChatType
	{
		const chat: ImModelChat = Core.getStore().getters['chats/getByChatId'](chatId);

		return getChatType(chat);
	}
}
