import { Loc, Tag } from 'main.core';
import {UI} from 'ui.notification';

export class Item
{
	#text;
	#active = false;
	#id;
	#href;
	#bindingType;
	#wait = false;
	#node;
	#messageId;
	#messageSimpleId;
	#createHref;
	#waitCSSClassName = 'ui-btn-wait';
	#errorType;

	#phrases = {
		'crm' : 'MAIL_BINDING_CRM_',
		'chat' : 'MAIL_BINDING_CHAT_',
		'task' : 'MAIL_BINDING_TASK_',
		'post' : 'MAIL_BINDING_POST_',
		'meeting' : 'MAIL_BINDING_MEETING_'
	}

	static #errorPhrases = {
		'crm-install-error' : 'MAIL_BINDING_CRM_ERROR',
		'calendar-install-error' : 'MAIL_BINDING_MEETING_ERROR_MSGVER_1',
		'tasks-install-error' : 'MAIL_BINDING_TASK_ERROR',
		'chat-install-error' : 'MAIL_BINDING_CHAT_ERROR_MSGVER_1',
		'socialnetwork-install-error' : 'MAIL_BINDING_POST_ERROR_MSGVER_1',
		'crm-install-permission-error' : 'MAIL_BINDING_CRM_PERMISSION_SAVE_ERROR',
		'crm-install-permission-open-error' : 'MAIL_BINDING_CRM_PERMISSION_OPEN_ERROR',
		'crm-install-permission-working-error' :'MAIL_BINDING_CRM_PERMISSION_WORKING_ERROR',
	}

	#phrasesFull = {
		'crm' : 'MAIL_BINDING_CRM_TITLE',
		'chat' : 'MAIL_BINDING_CHAT_TITLE',
		'task' : 'MAIL_BINDING_TASK_TITLE',
		'post' : 'MAIL_BINDING_POST_TITLE',
		'meeting' : 'MAIL_BINDING_MEETING_TITLE'
	}

	#classes = {
		'crm' : 'mail-binding-crm',
		'chat' : 'mail-binding-chat',
		'task' : 'mail-binding-task',
		'post' : 'mail-binding-post',
		'meeting' : 'mail-binding-meeting'
	}

	isError(errorKey)
	{
		if(Item.#errorPhrases[errorKey] !== undefined)
		{
			return true;
		}

		return false;
	}

	isActive()
	{
		return this.#active;
	}

	getId()
	{
		return this.#id;
	}

	getMessageId(simple = false)
	{
		if(!simple)
		{
			return this.#messageId;
		}
		else
		{
			return this.#messageSimpleId;
		}
	}

	constructor(config = {
		type: '',
		id: '',
	})
	{
		this.#errorType = config['errorType'];
		this.#messageId = config['messageId'];
		this.#id = config['id'];
		this.#href = config['href'];
		this.#bindingType = config['type'];
		this.#messageSimpleId = config['messageSimpleId'];
		this.#createHref =  config['createHref'];

		if(this.#id)
		{
			this.#active = true;
		}

		if(this.isActive())
		{
			this.#text = Loc.getMessage(this.#phrases[this.#bindingType]+'ACTIVE');
		}
		else
		{
			this.#text = Loc.getMessage(this.#phrases[this.#bindingType]+'NOT_ACTIVE' + this.getVersionNotActivePhrase());
		}
	}

	getType()
	{
		return this.#bindingType;
	}

	static showError(key)
	{
		UI.Notification.Center.notify({
			content: Loc.getMessage(Item.#errorPhrases[key]),
		});
	}

	onClick(event)
	{
		if (this.isError(this.#errorType))
		{
			Item.showError(this.#errorType);
			return;
		}

		if (this.isActive())
		{
			switch (this.getType())
			{
				//to join the chat if you left it
				case 'chat':
					BX.Mail.Secretary.getInstance(this.getMessageId(true)).openChat();
					break;
				case 'task':
					BX.Mail.Secretary.getInstance(this.getMessageId(true)).onTaskAction('task_view', 'view_button');
					break;
			}
		}
		else if (!this.#wait)
		{
			switch (this.getType())
			{
				case 'crm':
					this.startWait();
					BX.Mail.Client.Message.List["mail-client-list-manager"].onCrmClick(this.getMessageId());
					break;
				case 'chat':
					BX.Mail.Secretary.getInstance(this.getMessageId(true)).openChat();
					break;
				case 'task':
					const uri = BX.Uri.addParam(this.#createHref, {
						ta_sec: 'mail',
						ta_el: 'create_button',
					});
					top.BX.SidePanel.Instance.open(uri);
					break;
				case 'post':
					top.BX.SidePanel.Instance.open(this.#createHref);
					break;
				case 'meeting':
					BX.Mail.Secretary.getInstance(this.getMessageId(true)).openCalendarEvent();
					break;
			}
		}
	}

	getHref()
	{
		return this.#href;
	}

	setText(text)
	{
		this.#node.textContent = text;
	}

	getNode()
	{
		return this.#node;
	}

	startWait()
	{
		this.#wait = true;
		this.getNode().classList.add(this.#waitCSSClassName);
	}

	stopWait()
	{
		this.#wait = false;
		this.getNode().classList.remove(this.#waitCSSClassName);
	}

	setActive(href)
	{
		this.stopWait();
		this.getNode().classList.remove("mail-ui-not-active");
		this.getNode().classList.add("mail-ui-active");
		this.setText(Loc.getMessage(this.#phrases[this.getType()]+'ACTIVE'));
		this.getNode().setAttribute("href", href);
		this.#active = true;
		this.updateTitle();
	}

	deactivation()
	{
		this.stopWait();
		this.getNode().classList.add("mail-ui-not-active");
		this.getNode().classList.remove("mail-ui-active");
		this.setText(Loc.getMessage(`${this.#phrases[this.getType()]}NOT_ACTIVE${this.getVersionNotActivePhrase()}`));
		this.getNode().removeAttribute("href");
		this.#active = false;
		this.updateTitle();
	}

	getTitle()
	{
		return Loc.getMessage(this.#phrasesFull[this.getType()]+(this.isActive() ? '_ACTIVE' :''));
	}

	updateTitle()
	{
		this.getNode().removeAttribute("title");
		this.getNode().setAttribute("title", this.getTitle());
	}

	render()
	{
		const activeClass = this.isActive() ? 'mail-ui-active' : 'mail-ui-not-active';
		const item = Tag.render`
			<a class="mail-ui-binding ui-btn-light-border ui-btn ui-btn-xs ui-btn-round ui-btn-no-caps ${this.#classes[this.getType()]} ${activeClass} js-bind-${this.getMessageId(true)}">
				${this.#text}
			</a>`

		this.#node = item;
		this.#node.object = this;

		this.updateTitle();

		item.onclick = function()
		{
			this.object.onClick();
		};

		item.ondblclick = event => {
			event.stopPropagation();
		};

		item.setActive = function(href)
		{
			this.object.setActive(href);
		};

		item.deactivation = function()
		{
			this.object.deactivation();
		};

		item.startWait = function()
		{
			this.object.startWait();
		};

		item.stopWait = function()
		{
			this.object.stopWait();
		};

		if(this.#errorType === 'crm-install-permission-error' && this.getHref())
		{
			this.#errorType = 'crm-install-permission-open-error';
		}

		if(this.isActive() && !this.isError(this.#errorType))
		{
			item.setAttribute("href", this.getHref());
		}

		return item;
	}

	getVersionNotActivePhrase()
	{
		return {
			'meeting': '_MSG_1',
		}[this.getType()] || '';
	}
}

