// @flow
'use strict';

import SyncButton from "./controls/syncbutton";
import {EventEmitter} from "main.core.events";
import "./css/syncinterface.css"

export default class SyncInterface extends EventEmitter
{
	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Interface.SyncInterface');
		this.options = options;
	}

	createSyncButton()
	{
		this.syncButton = new SyncButton(this.options);
		this.syncButton.createButton();

		EventEmitter.subscribe('BX.Calendar.Sync.Interface.SyncStatusPopup:onRefresh', event => {
			this.refreshData();
		});
	}

	refreshData()
	{
		if (this.syncButton)
		{
			BX.ajax.runAction('calendar.api.calendarajax.updateConnection', {
				data: {
					userId: this.options.userId,
					type: 'user',
				}
			}).then(response => {
				this.syncButton.refreshData();
				this.options.calendar.reload();
			});
		}
	}
}