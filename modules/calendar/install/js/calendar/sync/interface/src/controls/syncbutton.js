// @flow
'use strict';

import {Loc} from "main.core";
import SyncPanel from "../syncpanel";
import SyncStatusPopup from "./syncstatuspopup";
import SyncInterfaceManager from "../syncinterfacemanager";

export default class SyncButton
{
	SLIDER_WIDTH = 684;
	LOADER_NAME = "calendar:loader";
	BUTTON_SIZE = BX.UI.Button.Size.EXTRA_SMALL;
	BUTTON_ROUND = true;

	constructor(options)
	{
		this.connectionsProviders = options.connectionsProviders;
		this.wrapper = options.wrapper;
		this.userId = options.userId;
		this.status = options.status;

		this.buttonEnterTimeout = null;
		this.buttonLeaveTimeout = null;
	}

	static createInstance(options)
	{
		return new this(options);
	}

	show()
	{
		const buttonData = this.getButtonData();
		this.button = new BX.UI.Button({
			text: buttonData.text,
			round: this.BUTTON_ROUND,
			size: this.BUTTON_SIZE,
			color: buttonData.color,
			className: 'ui-btn-themes ' + (buttonData.iconClass || ''),

			onclick: () => {
				this.handleClick();
			},

			events: {
				mouseenter: this.handlerMouseEnter.bind(this),
				mouseleave: this.handlerMouseLeave.bind(this),
			},
		});

		this.button.renderTo(this.wrapper);
	}

	showPopup(button)
	{
		if(this.status !== 'not_connected')
		{
			const connections = [];
			const providersCollection = Object.values(this.connectionsProviders);

			providersCollection.forEach(provider => {
				const providerConnections = provider.getConnections();
				if(providerConnections.length > 0)
				{
					providerConnections.forEach(connection =>
						{
							if (connection.getConnectStatus() === true)
							{
								connections.push(connection);
							}
						}
					)
				}
			});

			this.popup = SyncStatusPopup.createInstance({
				connections: connections,
				withUpdateButton: true,
				node: button.getContainer(),
				id: 'calendar-syncPanel-status',
			});
			this.popup.show();

			this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', e => {
				clearTimeout(this.buttonEnterTimeout);
				clearTimeout(this.buttonLeaveTimeout);
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

	refresh(status, connectionProviders)
	{
		this.status = status;
		this.connectionsProviders = connectionProviders;
		const buttonData = this.getButtonData();
		this.button.setColor(buttonData.color);
		this.button.setText(buttonData.text);
		this.button.removeClass('ui-btn-icon-fail ui-btn-icon-success');
		this.button.addClass(buttonData.iconClass);
	}

	handleClick()
	{
		clearTimeout(this.buttonEnterTimeout);
		this.syncPanel = new SyncPanel({
			connectionsProviders: this.connectionsProviders,
			userId: this.userId,
			status: this.status,
		});

		const syncPanel = this.syncPanel;

		BX.SidePanel.Instance.open(SyncInterfaceManager.MAIN_SYNC_SLIDER_NAME, {
			contentCallback(slider)
			{
				return new Promise((resolve, reject) => {
					resolve(syncPanel.showContent());
				});
			},
			allowChangeHistory:false,
			events: {
				onLoad: (slider) => {
					this.syncPanel.setGridContent();
				},
				// onMessage: (event) => {
				// 	if (event.getEventId() === 'refreshSliderGrid')
				// 	{
				// 		this.refreshData();
				// 	}
				// },
				// onClose: (event) => {
				// 	BX.SidePanel.Instance.postMessageTop(window.top.BX.SidePanel.Instance.getTopSlider(), "refreshCalendarGrid", {});
				// },
			},
			cacheable: false,
			width: this.SLIDER_WIDTH,
			loader: this.LOADER_NAME,
		});

		// this.refreshData();
	}

	handlerMouseEnter(button)
	{
		clearTimeout(this.buttonEnterTimeout);
		this.buttonEnterTimeout = setTimeout(() =>
			{
				this.buttonEnterTimeout = null;
				this.showPopup(button);
			}, 500
		);
	}

	handlerMouseLeave()
	{
		if (this.buttonEnterTimeout !== null)
		{
			clearTimeout(this.buttonEnterTimeout);
			this.buttonEnterTimeout = null;
			return;
		}

		this.buttonLeaveTimeout = setTimeout(() =>
			{
				this.hidePopup();
			}, 500
		);
	}

	getButtonData()
	{
		if (this.status === 'success')
		{
			return {
				text: Loc.getMessage('STATUS_BUTTON_SYNCHRONIZATION'),
				color: BX.UI.Button.Color.LIGHT_BORDER,
				iconClass: 'ui-btn-icon-success',
			};
		}
		else if (this.status === 'failed')
		{
			return {
				text: Loc.getMessage('STATUS_BUTTON_FAILED'),
				color: BX.UI.Button.Color.LIGHT_BORDER,
				iconClass: 'ui-btn-icon-fail',
			}
		}

		return {
			text: Loc.getMessage('STATUS_BUTTON_SYNC_CALENDAR'),
			color: BX.UI.Button.Color.PRIMARY,
		}
	}

	getSyncPanel()
	{
		return this.syncPanel;
	}
}