// @flow
'use strict';

import {Loc, Tag} from "main.core";
import SyncStatusPopup from "./syncstatuspopup";

export default class StatusBlock
{
	constructor(options)
	{
		this.status = options.status;
		this.connections = options.connections;
		this.withStatus = options.withStatus;
		this.popupWithUpdateButton = options.popupWithUpdateButton;
		this.popupId = options.id;
	}

	static createInstance(options)
	{
		return new this(options);
	}

	getContentStatusBlock()
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

		statusInfoBlock.addEventListener('mouseenter', (event) => {
			this.handlerMouseEnter(statusInfoBlock);
		});

		statusInfoBlock.addEventListener('mouseleave', (event) => {
			this.handlerMouseLeave();
		});

		const statusTextLabel = Tag.render `
			<div class="calendar-sync-status-subtitle">
				<span data-hint=""></span>
				<span class="calendar-sync-status-text">${Loc.getMessage('LABEL_STATUS_INFO')}:</span>
			</div>
		`;

		return Tag.render `
			<div class="calendar-sync-status-block" id="calendar-sync-status-block">
				${this.withStatus ? statusTextLabel : ''}
				${statusInfoBlock}
			</div>
		`;
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
			this.popup = SyncStatusPopup.createInstance({
				connections: this.connections,
				withUpdateButton: this.popupWithUpdateButton,
				node: node,
				id: this.popupId,
			});
			this.popup.show();

			this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', e => {
				clearTimeout(this.statusBlockEnterTimeout);
				clearTimeout(this.statusBlockLeaveTimeout);
			});
			this.popup.getPopup().getPopupContainer().addEventListener('mouseleave', () => {
				this.hidePopup();
			});
		}
	}

	hidePopup()
	{
		if (this.popup)
		{
			this.popup.hide();
		}
	}
}