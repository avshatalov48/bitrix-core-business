import { ajax as Ajax } from "main.core";
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Picker } from 'ai.picker';
import 'calendar.sliderloader';

import { runAction } from 'im.v2.lib.rest';
import { Core } from 'im.v2.application.core';
import { EventType, RestMethod } from 'im.v2.const';

import type { RestClient } from 'rest.client';

const CALENDAR_ON_ENTRY_SAVE_EVENT = 'BX.Calendar:onEntrySave';

export class EntityCreator
{
	#chatId: number = 0;
	#restClient: RestClient;

	#onCalendarEntrySaveHandler: ?Function;

	constructor(chatId: number)
	{
		this.#restClient = Core.getRestClient();
		this.#chatId = chatId;
	}

	createAiTextForChat(startMessage): void
	{
		this.#createAiText(startMessage);
	}

	createTaskForChat(): Promise
	{
		return this.#createTask();
	}

	createTaskForMessage(messageId: number): Promise
	{
		return this.#createTask(messageId);
	}

	createMeetingForChat(): Promise
	{
		return this.#createMeeting();
	}

	createMeetingForMessage(messageId: number): Promise
	{
		return this.#createMeeting(messageId);
	}

	#createMeeting(messageId?: number): Promise
	{
		const queryParams = { CHAT_ID: this.#chatId };
		if (messageId)
		{
			queryParams.MESSAGE_ID = messageId;
		}

		return this.#requestPreparedParams(RestMethod.imChatCalendarPrepare, queryParams).then((sliderParams) => {
			const { params } = sliderParams;

			this.#onCalendarEntrySaveHandler = this.#onCalendarEntrySave.bind(this, params.sliderId, messageId);
			EventEmitter.subscribeOnce(CALENDAR_ON_ENTRY_SAVE_EVENT, this.#onCalendarEntrySaveHandler);

			return this.#openCalendarSlider(params);
		});
	}

	#createAiText(startMessage: ''): Promise
	{
		const picker = new Picker({
			startMessage,
			moduleId: 'im',
			contextId: 'im_menu_plus',
			history: true,
			onSelect: (item) => {
				EventEmitter.emit(EventType.textarea.insertText, {
					text: item.data,
					replace: true,
				});
			},
		});
		picker
			.setLangSpace(Picker.LangSpace.text)
			.text()
		;
	}

	#createTask(messageId?: number): Promise
	{
		const config = {
			data: { chatId: this.#chatId },
		};
		if (messageId)
		{
			config.data.messageId = messageId;
		}

		return runAction(RestMethod.imV2ChatTaskPrepare, config).then((sliderParams) => {
			const { link, params } = sliderParams;

			return this.#openTaskSlider(link, params);
		});
	}

	#requestPreparedParams(requestMethod: string, query: Object): Promise
	{
		return this.#restClient.callMethod(requestMethod, query).then((result) => {
			return result.data();
		}).catch((error) => {
			console.error(error);
		});
	}

	#openTaskSlider(sliderUri: string, sliderParams: Object)
	{
		BX.SidePanel.Instance.open(sliderUri, {
			requestMethod: 'post',
			requestParams: sliderParams,
			cacheable: false,
		});
	}

	#openCalendarSlider(sliderParams: Object)
	{
		new (window.top.BX || window.BX).Calendar.SliderLoader(0, sliderParams).show();
	}

	#onCalendarEntrySave(sliderId: string, messageId: ?number, event: BaseEvent)
	{
		const eventData = event.getData();
		if (eventData.sliderId !== sliderId)
		{
			return;
		}

		const queryParams = {
			CALENDAR_ID: eventData.responseData.entryId,
			CHAT_ID: this.#chatId,
		};

		if (messageId)
		{
			queryParams.MESSAGE_ID = messageId;
		}

		return this.#restClient.callMethod(RestMethod.imChatCalendarAdd, queryParams).catch((error) => {
			console.error(error);
		});
	}
}
