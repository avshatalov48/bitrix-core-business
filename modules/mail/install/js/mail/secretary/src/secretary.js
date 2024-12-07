import { Type } from 'main.core';
import { sendData } from 'ui.analytics';

const ENTITY_TYPE = 'mail';

const instances = {};

/**
 * Mail Secretary
 * @see control-button.js
 */
export class Secretary
{
	#messageId: number;

	constructor(messageId: number)
	{
		this.#messageId = messageId;
		this.sliderId = `MailSecretary:${ENTITY_TYPE + this.#messageId}${Math.floor(Math.random() * 1000)}`;
		this.contextBx = (window.top.BX || window.BX);
		this.subscribe();
	}

	static getInstance(messageId: number)
	{
		if (Type.isUndefined(instances[messageId]))
		{
			instances[messageId] = new Secretary(messageId);
		}
		return instances[messageId];
	}

	openChat()
	{
		return BX.ajax.runAction('mail.secretary.createChatFromMessage',
			{data: {messageId: this.#messageId}},
		).then(
			(response) => {
				if (top.window.BXIM && response.data)
				{
					top.BXIM.openMessenger('chat' + parseInt(response.data));
				}
			},
			(response) => {
				this.#displayErrors(response.errors);
			},
		);
	}

	openCalendarEvent()
	{
		return BX.ajax.runAction('mail.secretary.getCalendarEventDataFromMessage',
			{data: {messageId: this.#messageId}}
		).then(
			(response) => {
				// let users = [];
				// if (Type.isArrayLike(response.data.userIds))
				// {
				// 	users = response.data.userIds.map((userId) => {
				// 		return {id: parseInt(userId), entityId: 'user'};
				// 	});
				// }

				if (response.data && response.data.isNewEvent)
				{
					new (window.top.BX || window.BX).Calendar.SliderLoader(
						0,
						{
							sliderId: this.sliderId,
							entryName: response.data.name,
							entryDescription: response.data.desc,
							// participantsEntityList: users,
						}
					).show();
				}
				else if (response.data && response.data.isIcal)
				{
					return BX.ajax.runComponentAction('bitrix:mail.client', 'ical', {
						mode: 'ajax',
						data: {
							messageId: this.#messageId,
							action: 'question',
						},
					});
				}
			},
			(response) => {
				this.#displayErrors(response.errors);
			},
		).then(
			(response) => {
				if (response.data && response.data.eventId)
				{
					const sliderLoader = new (window.top.BX || window.BX).Calendar.SliderLoader(
						response.data.eventId
					);
					sliderLoader.show();

					const grid = new BX.Mail.MessageGrid();
					grid.reloadTable();
				}
			}
		);
	}

	onCalendarSave(event)
	{
		if (event instanceof this.contextBx.Event.BaseEvent)
		{
			const data = event.getData();

			if (data.sliderId === this.sliderId)
			{
				BX.ajax.runAction('mail.secretary.onCalendarSave', {
					data: {
						messageId: this.#messageId,
						calendarEventId: data.responseData.entryId,
					},
				});
			}
		}
	}

	onTaskAction(event, element)
	{
		const analyticsData = {
			tool: 'tasks',
			category: 'task_operations',
			event: event,
			type: 'task',
			c_section: 'mail',
			c_element: element,
		};

		sendData(analyticsData);
	}

	subscribe()
	{
		this.contextBx.Event.EventEmitter.subscribe('BX.Calendar:onEntrySave', this.onCalendarSave.bind(this));
	}

	destroy()
	{
		this.contextBx.Event.EventEmitter.unsubscribe('BX.Calendar:onEntrySave', this.onCalendarSave);
	}

	#displayErrors(errors: Array)
	{
		if (Type.isArray(errors))
		{
			let errorMessages = [];
			errors.forEach((error) => {
				errorMessages.push(error.message);
			});
			alert(errorMessages.join("\n"));
		}
		else
		{
			alert("action can't be performed");
		}
	}
}
