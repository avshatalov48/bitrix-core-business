import {Tag, Loc, Dom, Event, Type, Runtime} from 'main.core';
import {PopupManager} from 'main.popup';
import {EventEmitter} from 'main.core.events';
export class EmailSelectorControl extends EventEmitter
{
	DOM = {};
	CONFIRM_POPUP_ID = 'add_from_email';

	constructor(params)
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.EmailSelectorControl');
		this.DOM.select = params.selectNode;
		this.mailboxList = Type.isArray(params.mailboxList) ? params.mailboxList : [];
		this.DOM.componentWrap = this.DOM.select.parentNode.appendChild(Tag.render`<div style="display: none;"></div>`);
		this.allowAddNewEmail = params.allowAddNewEmail;
		this.checkValueDebounce = Runtime.debounce(this.checkValue, 50, this);
		this.create();
	}

	create()
	{
		this.setSelectValues();
		Event.bind(this.DOM.select, 'change', this.checkValueDebounce);
		Event.bind(this.DOM.select, 'click', this.checkValueDebounce);
	}

	checkValue()
	{
		if (this.DOM.select.value === 'add')
		{
			this.showAdd();
			this.setValue('');
		}
	}

	getValue()
	{
		return this.DOM.select.value;
	}

	setValue(value)
	{
		if (this.mailboxList.length
			&& this.mailboxList.find((mailbox) => {return mailbox.email === value}))
		{
			this.DOM.select.value = value;
		}
		else
		{
			this.DOM.select.value = '';
		}
		this.emit('onSetValue', {
			value: this.DOM.select.value
		});
	}

	setSelectValues()
	{
		Dom.clean(this.DOM.select);
		this.DOM.select.options.add(new Option(Loc.getMessage('EC_NO_VALUE'), ''));
		if (this.mailboxList.length)
		{
			this.mailboxList.forEach((value) => {
				this.DOM.select.options.add(new Option(value.formatted, value.email));
			}, this);
		}

		if (this.allowAddNewEmail)
		{
			this.DOM.select.options.add(new Option(Loc.getMessage('EC_ADD_NEW'), 'add'));
		}
	}

	onClick(item)
	{
		this.input.value = item.sender;
		this.mailbox.textContent = item.sender;
	}

	showAdd()
	{
		if (window.BXMainMailConfirm)
		{
			window.BXMainMailConfirm.showForm(this.onAdd.bind(this));
		}
		const mainMailConfirmPopup = PopupManager.getPopupById(this.CONFIRM_POPUP_ID);
		if (mainMailConfirmPopup)
		{
			mainMailConfirmPopup.subscribe('onClose', ()=>{
				this.reloadMailboxList();
			});
		}
	}

	onAdd(data)
	{
		this.reloadMailboxList()
			.then(() => {
				setTimeout(()=>{
					this.setValue(data.email);
				},0);
			});
	}

	getMenuItem(item)
	{
		return {
			'id': item.id,
			'text': BX.util.htmlspecialchars(item.sender),
			'onclick': this.onClick.bind(this, item)
		};
	}

	loadMailboxData()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.calendarajax.getAllowedMailboxData')
				.then(
					(response) => {
						BX.html(this.DOM.componentWrap, response.data.html);
						this.mailboxList = response.data.additionalParams.mailboxList;
						this.checkBXMainMailConfirmLoaded(resolve);
					}
				);
		});
	}

	checkBXMainMailConfirmLoaded(resolve)
	{
		if (window.BXMainMailConfirm)
		{
			this.setSelectValues();
			resolve();
		}
		else
		{
			setTimeout(()=>{this.checkBXMainMailConfirmLoaded(resolve)}, 200);
		}
	}

	reloadMailboxList()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.calendarajax.getAllowedMailboxList')
				.then(
					(response) => {
						this.mailboxList = response.data.mailboxList;
						this.setSelectValues();
						resolve();
					}
				);
		});
	}
}