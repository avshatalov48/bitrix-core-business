// @flow
'use strict';

import {Loc, Tag} from "main.core";
import {SyncStatusPopup} from "calendar.sync.manager";

export default class StatusBlock
{
	constructor(options)
	{
		this.status = options.status;
		this.connections = options.connections;
		this.withStatusLabel = options.withStatusLabel;
		this.popupWithUpdateButton = options.popupWithUpdateButton;
		this.popupId = options.popupId;
	}

	static createInstance(options)
	{
		return new this(options);
	}

	setStatus(status)
	{
		this.status = status;

		return this;
	}

	setConnections(connections)
	{
		this.connections = connections;

		return this;
	}

	getContent()
	{
		let statusInfoBlock;
		if (this.status === 'success')
		{
			statusInfoBlock = Tag.render `
				<div id="status-info-block" class="ui-alert ui-alert-success calendar-sync-status-info">
					<span class="ui-alert-message">${Loc.getMessage('SYNC_STATUS_SUCCESS')}</span>
				</div>
			`;
		}
		else if (this.status === 'failed')
		{
			statusInfoBlock = Tag.render `
				<div id="status-info-block" class="ui-alert ui-alert-danger calendar-sync-status-info">
					<span class="ui-alert-message">${Loc.getMessage('SYNC_STATUS_ALERT')}</span>
				</div>
			`;
		}
		else
		{
			statusInfoBlock = Tag.render `
				<div id="status-info-block" class="ui-alert ui-alert-primary calendar-sync-status-info">
					<span class="ui-alert-message">${Loc.getMessage('SYNC_STATUS_NOT_CONNECTED')}</span>
				</div>
			`;
		}

		statusInfoBlock.addEventListener('mouseenter', () => {
			this.handlerMouseEnter(statusInfoBlock);
		});

		statusInfoBlock.addEventListener('mouseleave', () => {
			this.handlerMouseLeave();
		});

		this.statusBlock = Tag.render `
			<div class="calendar-sync-status-block" id="calendar-sync-status-block">
				${this.getStatusTextLabel()}
				${statusInfoBlock}
			</div>
		`;

		return this.statusBlock;
	}

	getStatusTextLabel()
	{
		return this.withStatusLabel
			? Tag.render`
				<div class="calendar-sync-status-subtitle">
					<span data-hint=""></span>
					<span class="calendar-sync-status-text">${Loc.getMessage('LABEL_STATUS_INFO')}:</span>
				</div>`
			: ''
		;
	}

	handlerMouseEnter(statusBlock)
	{
		clearTimeout(this.statusBlockEnterTimeout);
		this.buttonEnterTimeout = setTimeout(() =>
			{
				this.statusBlockEnterTimeout = null;
				this.showPopup(statusBlock);
			}, 500
		);
	}

	handlerMouseLeave()
	{
		if (this.statusBlockEnterTimeout !== null)
		{
			clearTimeout(this.statusBlockEnterTimeout);
			this.statusBlockEnterTimeout = null;
			return;
		}

		this.statusBlockLeaveTimeout = setTimeout(() =>
			{
				this.hidePopup();
			}, 500
		);
	}

	showPopup(node)
	{
		if(this.status !== 'not_connected')
		{
			this.popup = this.getPopup(node);
			this.popup.show();

			this.addPopupHandlers();
		}
	}

	hidePopup()
	{
		if (this.popup)
		{
			this.popup.hide();
		}
	}

	addPopupHandlers()
	{
		this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', () =>
		{
			clearTimeout(this.statusBlockEnterTimeout);
			clearTimeout(this.statusBlockLeaveTimeout);
		});

		this.popup.getPopup().getPopupContainer().addEventListener('mouseleave', () =>
		{
			this.hidePopup();
		});
	}

	getPopup(node)
	{
		return SyncStatusPopup.createInstance({
			connections: this.connections,
			withUpdateButton: this.popupWithUpdateButton,
			node: node,
			id: this.popupId,
		});
	}

	refresh(status, connections)
	{
		this.status = status;
		this.connections = connections;

		return this;
	}
}