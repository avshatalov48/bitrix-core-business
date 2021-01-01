// @flow
'use strict';
import {Tag, Loc, Dom} from 'main.core';
import {Popup} from 'main.popup';
import {EventEmitter} from "main.core.events";

export default class SyncStatusPopup extends EventEmitter
{
	static IS_RUN_REFRESH = false;

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Interface.SyncStatusPopup');
		this.connections = options.connections;
		this.withUpdateButton = options.withUpdateButton;
		this.node = options.node;
		this.id = options.id;

		this.init();
	}

	static createInstance(options)
	{
		return new this(options)
	}

	init()
	{
		this.setPopupContent();
	}

	createPopup()
	{
		this.popup = new Popup({
			className: this.id,
			bindElement: this.node,
			content: this.container,
			angle: true,
			width: 360,
			offsetLeft: 60,
			offsetTop: 5,
			padding: 7,
			darkMode: true,
			autoHide: true,
			zIndexAbsolute: 3010,
		});
	}

	show()
	{
		this.createPopup();
		this.popup.show();
	}

	setPopupContent()
	{
		this.container = Tag.render `
			<div class="calendar-sync-popup-list"></div>
		`;

		this.connections.forEach(connection => {
			if (connection.getConnectStatus() !== true)
			{
				return;
			}

			const options = {};

			$
			options.syncTime = this.getTime(connection.getSyncTimestamp());
			options.classStatus = connection.getSyncStatus()
				? 'calendar-sync-popup-item-status-success'
				: 'calendar-sync-popup-item-status-fail';
			options.classLable = 'calendar-sync-popup-item-text-' + connection.getClassLable();
			options.title = connection.getConnectionName();
			const block = this.getSyncElement(options);
			this.container.append(block);
		});


		if (this.withUpdateButton)
		{
			this.container.append(this.getContentRefreshBlock());

			if (SyncStatusPopup.IS_RUN_REFRESH)
			{
				this.showRefreshStatus();
			}
		}

		return this.container;
	}

	hide()
	{
		this.popup.destroy();
	}

	getContainer()
	{
		return this.container;
	}

	getPopup()
	{
		return this.popup;
	}

	getTime(timestamp)
	{
		var format = [
			["tommorow", "tommorow, H:i:s"],
			["s" , Loc.getMessage('CAL_JUST')],
			["i" , "iago"],
			["H", "Hago"],
			["d", "dago"],
			["m100", "mago"],
			["m", "mago"],
			// ["m5", Loc.getMessage('CAL_JUST')],
			["-", ""]
		];

		return BX.date.format(format, timestamp);
	}

	getSyncElement(options)
	{
		return Tag.render `
				<div class="calendar-sync-popup-item">
					<span class="calendar-sync-popup-item-text ${options.classLable}">${BX.util.htmlspecialchars(options.title)}</span>
					<div class="calendar-sync-popup-item-detail">
						<span class="calendar-sync-popup-item-time">${options.syncTime}</span>
						<span class="calendar-sync-popup-item-status ${options.classStatus}"></span>
					</div>
				</div>
			`;
	}

	refresh(connections)
	{
		this.connections = connections;
		this.popup.setContent(this.setPopupContent());
		this.setRefreshStatusBlock();
	}

	setRefreshStatusBlock()
	{
		setTimeout(() => {
			this.removeRefreshStatusBlock();
			this.enableRefreshButton();
			SyncStatusPopup.IS_RUN_REFRESH = false;
		}, 300000);
	}

	removeRefreshStatusBlock()
	{
		this.refreshStatusBlock.remove();
	}

	enableRefreshButton()
	{
		this.refreshButton.className = 'calendar-sync-popup-footer-btn';
	}

	disableRefreshButton()
	{
		this.refreshButton.className = 'calendar-sync-popup-footer-btn calendar-sync-popup-footer-btn-disabled';
	}

	getContentRefreshBlock()
	{
		this.footerWrapper = Tag.render`
			<div class="calendar-sync-popup-footer-wrap">
				${this.getContentRefreshButton()}
			</div>
		`;

		return this.footerWrapper;
	}

	getContentRefreshButton()
	{
		this.refreshButton = Tag.render`
			<button class="calendar-sync-popup-footer-btn">${Loc.getMessage('CAL_REFRESH')}</button>
		`;

		this.refreshButton.addEventListener('click', () => {
			Dom.addClass(this.refreshButton, 'calendar-sync-popup-footer-btn-load');
			SyncStatusPopup.IS_RUN_REFRESH = true;
			this.refreshButton.innerText = Loc.getMessage('CAL_REFRESHING');
			this.runRefresh();
		});

		return this.refreshButton;
	}

	showRefreshStatus()
	{
		this.disableRefreshButton();
		this.footerWrapper.prepend(this.getRefreshStatus());
	}

	getRefreshStatus()
	{
		this.refreshStatusBlock = Tag.render`
			<span class="calendar-sync-popup-footer-status">${Loc.getMessage('CAL_REFRESH_JUST')}</span>
		`;

		return this.refreshStatusBlock;
	}

	runRefresh()
	{
		this.emit('onRefresh', {});
	}

	getId()
	{
		return this.id;
	}
}