import {Dom, Loc, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';

export class AddButton extends EventEmitter
{
	showTasks = false;
	DOM = {};

	constructor(params = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.AddButton');
		this.zIndex = params.zIndex || 3200;
		this.popupId = params.id || 'add-button-' + Math.round(Math.random() * 10000);
		this.showTasks = params.showTasks;

		this.addEntryHandler = Type.isFunction(params.addEntry) ? params.addEntry : null;
		this.addTaskHandler = Type.isFunction(params.addTask) ? params.addTask : null;
		this.create();
	}

	create()
	{
		this.menuItems = [
			{
				text: Loc.getMessage('EC_EVENT_BUTTON'),
				onclick: this.addEntry.bind(this)
			}
		];

		if (this.addTaskHandler)
		{
			this.menuItems.push({
				text: Loc.getMessage('EC_TASK_BUTTON'),
				onclick: this.addTask.bind(this)
			});
		}

		if (this.menuItems.length > 1)
		{
			this.DOM.wrap = Dom.create("span", {
				props: {className: "ui-btn-split ui-btn-success"},
				children: [
					Dom.create("button", {
						props: {className: "ui-btn-main", type: "button"},
						html: Loc.getMessage('EC_CREATE'),
						events: {click: this.addEntry.bind(this)}
					})
				]
			});
			this.DOM.addButtonExtra = Dom.create("span", {
				props: {className: "ui-btn-extra"},
				events: {click: this.showPopup.bind(this)}
			});

			this.DOM.wrap.appendChild(this.DOM.addButtonExtra)
		}
		else
		{
			this.DOM.wrap = Dom.create("button", {
				props: {className: "ui-btn ui-btn-success", type: "button"},
				html: Loc.getMessage('EC_CREATE'),
				events: {click: this.addEntry.bind(this)}
			});
		}
		this.DOM.wrap.setAttribute('data-role', 'addButton');
	}

	getWrap()
	{
		return this.DOM.wrap;
	}

	showPopup()
	{
		if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
		{
			return this.menuPopup.close();
		}

		this.menuPopup = BX.PopupMenu.create(
			this.popupId,
			this.DOM.addButtonExtra,
			this.menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: 15,
				angle: true
			}
		);

		this.menuPopup.show();

		BX.addCustomEvent(this.menuPopup.popupWindow, 'onPopupClose', function()
		{
			BX.PopupMenu.destroy(this.popupId);
			this.menuPopup = null;
			this.addBtnMenu = null;
		}.bind(this));
	}

	addEntry()
	{
		if (this.addEntryHandler)
		{
			this.addEntryHandler();
		}

		if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
		{
			this.menuPopup.close();
		}
	}

	addTask()
	{
		if (this.addTaskHandler)
		{
			this.addTaskHandler();
		}

		if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
		{
			this.menuPopup.close();
		}
	}
}