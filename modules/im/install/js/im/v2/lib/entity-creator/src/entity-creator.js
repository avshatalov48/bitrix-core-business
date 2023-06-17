import {BaseEvent, EventEmitter} from 'main.core.events';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';

const REQUEST_METHODS = Object.freeze({
	task: RestMethod.imChatTaskPrepare,
	meeting: RestMethod.imChatCalendarPrepare,
});

const CALENDAR_ON_ENTRY_SAVE_EVENT = 'BX.Calendar:onEntrySave';

export class EntityCreator
{
	#chatId: number = 0;
	#restClient: RestClient;

	#onCalendarEntrySaveHandler: ?Function;
	#calendarSliderId: ?number = null;

	constructor(chatId: number)
	{
		this.#restClient = Core.getRestClient();
		this.#chatId = chatId;
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
		const queryParams = {CHAT_ID: this.#chatId};
		if (messageId)
		{
			queryParams['MESSAGE_ID'] = messageId;
		}

		return this.#requestPreparedParams(REQUEST_METHODS.meeting, queryParams).then(sliderParams => {
			const {params} = sliderParams;

			this.#onCalendarEntrySaveHandler = this.#onCalendarEntrySave.bind(this, params.sliderId, messageId);
			EventEmitter.subscribeOnce(CALENDAR_ON_ENTRY_SAVE_EVENT, this.#onCalendarEntrySaveHandler);

			return this.#openCalendarSlider(params);
		});
	}

	#createTask(messageId?: number): Promise
	{
		const queryParams = {CHAT_ID: this.#chatId};
		if (messageId)
		{
			queryParams['MESSAGE_ID'] = messageId;
		}

		return this.#requestPreparedParams(REQUEST_METHODS.task, queryParams).then(sliderParams => {
			const {link, params} = sliderParams;

			return this.#openTaskSlider(link, params);
		});
	}

	#requestPreparedParams(requestMethod: string, query: Object): Promise
	{
		return this.#restClient.callMethod(requestMethod, query).then(result => {
			return result.data();
		}).catch(error => {
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
			CHAT_ID: this.#chatId
		};

		if (messageId)
		{
			queryParams['MESSAGE_ID'] = messageId;
		}

		return this.#restClient.callMethod(RestMethod.imChatCalendarAdd, queryParams).catch((error) => {
			console.error(error);
		});
	}
}