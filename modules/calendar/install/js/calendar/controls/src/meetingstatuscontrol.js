import {Dom, Event, Loc, Type} from 'main.core';
import {Util} from 'calendar.util';

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
		this.acceptBtn = new BX.UI.Button({
			text: Loc.getMessage('EC_VIEW_DESIDE_BUT_Y'),
			className: 'ui-btn ui-btn-primary',
			events: {click: this.accept.bind(this)}
		});
		this.acceptBtn.renderTo(this.DOM.wrap);

		this.declineBtn = new BX.UI.Button({
			text: Loc.getMessage('EC_VIEW_DESIDE_BUT_N'),
			className: 'ui-btn ui-btn-light-border',
			events: {click: this.decline.bind(this)}
		});
		this.declineBtn.renderTo(this.DOM.wrap);
	}

	updateStatus()
	{
		if (this.status === 'H')
		{
			this.acceptBtn.getContainer().style.display = 'none';
			this.declineBtn.getContainer().style.display = '';
			this.declineBtn.setText(Loc.getMessage('EC_VIEW_DESIDE_BUT_OWNER_N'));
		}
		else
		{
			if (this.status === 'Y')
			{
				this.acceptBtn.getContainer().style.display = 'none';
				this.declineBtn.getContainer().style.display = '';
			}
			else if (this.status === 'N')
			{
				this.acceptBtn.getContainer().style.display = '';
				this.declineBtn.getContainer().style.display = 'none';
			}
			else
			{
				this.acceptBtn.getContainer().style.display = '';
				this.declineBtn.getContainer().style.display = '';
			}
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

	setStatus(value, emitEvent = true)
	{
		this.status = value;

		if (this.menuPopup)
		{
			this.menuPopup.close();
		}

		if (emitEvent)
		{
			this.emit('onSetStatus', new Event.BaseEvent({data: {status: value}}));
		}
	}
}