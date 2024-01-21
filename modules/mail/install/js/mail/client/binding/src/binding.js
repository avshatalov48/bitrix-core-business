import { Tag } from 'main.core';
import { Loc } from 'main.core';
import { Item } from './item.js';
import { EventEmitter } from "main.core.events";

export class Binding
{
	#mailboxId;

	#selectors = {
		CRM_ACTIVITY: '.mail-binding-crm',
		TASKS_TASK: '.mail-binding-task',
		IM_CHAT: '.mail-binding-chat',
		BLOG_POST: '.mail-binding-post',
		CALENDAR_EVENT: '.mail-binding-meeting',
	};

	getMailbox()
	{
		return this.#mailboxId;
	}

	constructor(mailboxId)
	{
		this.#mailboxId = mailboxId;

		this.#subscribeEvent();

		EventEmitter.subscribe('onPullEvent-mail', (event) => {

			let data = event.getData();

			if(data[0] === "messageBindingCreated" && (data[1]['mailboxId'] === this.getMailbox() || data[1]['mailboxId'] === String(this.getMailbox())))
			{
				const binding = data[1];
				const messageSimpleId = binding['messageId'];

				const bindingWrapper = document.querySelector(""+('.js-bind-' + messageSimpleId) + this.#selectors[binding['entityType']] + "");

				if (bindingWrapper)
				{
					bindingWrapper.setActive(binding['bindingEntityLink']);
				}
			}

			if(data[0] === "messageBindingDeleted" && (data[1]['mailboxId'] === this.getMailbox() || data[1]['mailboxId'] === String(this.getMailbox())))
			{
				const binding = data[1];
				const messageSimpleId = binding['messageId'];

				const bindingWrapper = document.querySelector(""+('.js-bind-' + messageSimpleId) + this.#selectors[binding['entityType']] + "");

				if (bindingWrapper)
				{
					bindingWrapper.deactivation();
				}
			}
		});
	}

	static build(config)
	{
		const item = new Item(config);
		return item.render();
	}

	static replaceElement(object)
	{
		const parent = object.parentNode;

		let newObject = this.build({
			type: object.getAttribute('bind-type'),
			id:  object.getAttribute('bind-id'),
			messageId: object.getAttribute('message-id'),
			messageSimpleId: object.getAttribute('message-simple-id'),
			href:  object.getAttribute('bind-href'),
			createHref: object.getAttribute('create-href'),
			errorType: object.getAttribute('error-type'),
		});
		parent.replaceChild(newObject,object);
	}

	static initButtons(context: HTMLElement = document.body)
	{
		const elements = Array.from(context.getElementsByClassName('mail-ui-binding-data'));
		for (let element of elements)
		{
			this.replaceElement(element);
		}
	}

	#subscribeEvent()
	{
		BX.PULL.subscribe({
			type: BX.PullClient.SubscriptionType.Server,
			moduleId: 'mail',
			command: 'unbindItem',
			callback: (data) => this.#unbindItem(data),
		});
	}

	#unbindItem(data)
	{
		const selector = `.js-bind-${data.messageId}.mail-binding-${data.type}.mail-ui-active`;
		const bindingWrapper = document.querySelector(selector);
		if (!bindingWrapper)
		{
			return;
		}
		bindingWrapper.deactivation();
		this.#updateGridByUnbindFilter();
	}

	#updateGridByUnbindFilter()
	{
		BX.Mail.Home.Grid.reloadTable();
	}
}
