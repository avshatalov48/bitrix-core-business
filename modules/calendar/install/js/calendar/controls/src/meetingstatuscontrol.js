import {Dom, Event, Loc, Type} from 'main.core';
import {Util} from "calendar.util";

export class MeetingStatusControl extends Event.EventEmitter
{
	showTasks = false;
	DOM = {};

	constructor(params = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.MeetingStatusControl');
		this.BX = Util.getBX();

		if (params.wrap && Type.isDomNode(params.wrap))
		{
			this.DOM.wrap = params.wrap;
		}
		else
		{
			throw new Error("The argument \"params.wrap\" must be a DOM node.");
		}
		this.id = params.id || 'meeting-status-control-' + Math.round(Math.random() * 10000);
		this.zIndex = 3100;

		this.create();

		this.status = params.currentStatus || null;
		if (this.status)
		{
			this.updateStatus();
		}
	}

	create()
	{
		this.DOM.selectorButton = this.DOM.wrap.appendChild(Dom.create("SPAN", {
			props: {className: "webform-small-button webform-small-button-transparent webform-small-button-dropdown"},
			events: {click: this.showPopup.bind(this)}
		}));

		this.DOM.selectorButtonText = this.DOM.selectorButton.appendChild(Dom.create("SPAN", {
			props: {className: "webform-small-button-text"}
		}));
		this.DOM.selectorButtonIcon = this.DOM.selectorButton.appendChild(Dom.create("SPAN", {
			props: {className: "webform-small-button-icon"}
		}));

		this.DOM.buttonY = this.DOM.wrap.appendChild(Dom.create("SPAN", {
			props: {className: "webform-small-button webform-small-button-accept"},
			events: {click: this.accept.bind(this)},
			html: Loc.getMessage('EC_VIEW_DESIDE_BUT_Y')
		}));
		// this.buttonI = this.DOM.wrap.appendChild(Dom.create("SPAN", {
		// 	props: {className: "webform-small-button webform-small-button-transparent"},
		// 	style: {display: 'none'},
		// 	events: {click: BX.proxy(function(){this.setStatus('I');}, this)},
		// 	html: Loc.getMessage('EC_VIEW_DESIDE_BUT_I')
		// }));

		this.DOM.buttonN = this.DOM.wrap.appendChild(Dom.create("SPAN", {
			props: {className: "webform-small-button webform-small-button-transparent"},
			events: {click: this.decline.bind(this)},
			html: Loc.getMessage('EC_VIEW_DESIDE_BUT_N')
		}));
	}

	updateStatus()
	{
		if (this.status === 'Q')
		{
			this.DOM.selectorButton.style.display = 'none';
			this.DOM.buttonY.style.display = '';
			this.DOM.buttonN.style.display = '';
		}
		else
		{
			this.DOM.selectorButton.style.display = '';
			this.DOM.selectorButtonText.innerHTML = Loc.getMessage('EC_VIEW_STATUS_BUT_' + this.status);

			this.DOM.buttonY.style.display = 'none';
			//this.DOM.buttonI.style.display = 'none';
			this.DOM.buttonN.style.display = 'none';
		}
	}

	accept()
	{
		this.setStatus('Y');
	}

	decline()
	{
		this.setStatus('N');
	}

	setStatus(value)
	{
		this.status = value;
		if (this.menuPopup)
		{
			this.menuPopup.close();
		}

		let res = true;
		if (Type.isFunction(this.changeStatusCallback))
		{
			res = this.changeStatusCallback(this.status);
		}

		this.emit('onSetStatus', new Event.BaseEvent({data: {status: value}}));

		if (res)
		{
			this.updateStatus();
		}
	}

	showPopup()
	{
		if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
		{
			return this.menuPopup.close();
		}

		let menuItems;

		if (this.status === 'Y' || this.status === 'H')
		{
			menuItems = [
				{
					text: Loc.getMessage('EC_VIEW_DESIDE_BUT_N'),
					onclick: this.decline.bind(this)
				}
			];
		}
		else if(this.status === 'N')
		{
			menuItems = [
				{
					text: Loc.getMessage('EC_VIEW_DESIDE_BUT_Y'),
					onclick: this.accept.bind(this)
				}
			];
		}
		else if(this.status === 'I')
		{
			menuItems =[
				{
					text: Loc.getMessage('EC_VIEW_DESIDE_BUT_Y'),
					onclick: this.accept.bind(this)
				},
				{
					text: Loc.getMessage('EC_VIEW_DESIDE_BUT_N'),
					onclick: this.decline.bind(this)
				}
			];
		}

		this.menuPopup = this.BX.PopupMenu.create(
			this.id,
			this.DOM.selectorButtonIcon,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 15,
				offsetLeft: 5,
				angle: true,
				cacheable: false
			}
		);

		this.menuPopup.show();
	}
}