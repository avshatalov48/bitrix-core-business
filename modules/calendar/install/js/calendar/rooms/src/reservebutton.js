import { Type, Dom, Loc } from 'main.core';
import { AddButton } from 'calendar.controls';

export class ReserveButton extends AddButton
{
	constructor(params = {})
	{
		super(params);
		this.setEventNamespace('BX.Calendar.Rooms.ReserveButton');
		this.zIndex = params.zIndex || 3200;
		this.popupId = params.id || 'add-button-' + Math.round(Math.random() * 10000);
		this.showTasks = params.showTasks;

		this.addEntryHandler = Type.isFunction(params.addEntry) ? params.addEntry : null;
		this.addTaskHandler = Type.isFunction(params.addTask) ? params.addTask : null;
		this.create();
	}

	create()
	{
		this.DOM.wrap = Dom.create('button', {
			props: { className: 'ui-btn ui-btn-success', type: 'button' },
			html: Loc.getMessage('EC_RESERVE'),
			events: { click: this.addEntry.bind(this) }
		});
	}
}