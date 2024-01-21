// @flow
'use strict';

import { Dom, Loc, Tag } from 'main.core';
import { Popup } from 'main.popup';
import SyncStatusPopup from './syncstatuspopup';

export default class SyncButton
{
	BUTTON_SIZE = BX.UI.Button.Size.EXTRA_SMALL;
	BUTTON_ROUND = true;

	constructor(options)
	{
		this.connectionsProviders = options.connectionsProviders;
		this.wrapper = options.wrapper;
		this.userId = options.userId;
		this.status = options.status;
		this.isGoogleApplicationRefused = options.isGoogleApplicationRefused;

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

	showGoogleApplicationRefusedPopup()
	{
		const popup = new Popup({
			bindElement: this.button.getContainer(),
			borderRadius: '3px',
			className: 'calendar-popup-ui-tour-animate',
			content: Tag.render`
				<div class="calendar-sync-popup-status-refused">
					<div class="calendar-sync-popup-status-refused-title">
						${Loc.getMessage('CAL_SYNC_INFO_STATUS_REFUSED_POPUP_TITLE')}
					</div>
					<div class="calendar-sync-popup-status-refused-text">
						${Loc.getMessage('CAL_SYNC_INFO_STATUS_REFUSED_POPUP_TEXT')}
					</div>
				</div>
			`,
			width: 400,
			angle: {
				offset: this.button.getContainer().offsetWidth / 2,
				position: 'top',
			},
			closeIcon: true,
			autoHide: true,
		});

		setTimeout(() => {
			popup.show();
			BX.ajax.runAction('calendar.api.syncajax.disableShowGoogleApplicationRefused');
		}, 1000);
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
				id: 'calendar-sync-button-status',
				isGoogleApplicationRefused: this.isGoogleApplicationRefused,
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

	refresh(status)
	{
		this.status = status;

		const buttonData = this.getButtonData();
		this.button.setColor(buttonData.color);
		this.button.setText(buttonData.text);
		this.button.removeClass('ui-btn-icon-fail ui-btn-icon-success ui-btn-clock calendar-sync-btn-icon-refused');
		this.button.addClass(buttonData.iconClass);
	}

	handleClick()
	{
		clearTimeout(this.buttonEnterTimeout);
		(window.top.BX || window.BX).Runtime.loadExtension('calendar.sync.interface').then((exports) => {
			if (!Dom.hasClass(this.button.button, 'ui-btn-clock'))
			{
				this.syncPanel = new exports.SyncPanel({
					connectionsProviders: this.connectionsProviders,
					userId: this.userId,
					status: this.status,
				});
				this.syncPanel.openSlider();
			}
		});
	}

	handlerMouseEnter(button)
	{
		clearTimeout(this.buttonEnterTimeout);
		this.buttonEnterTimeout = setTimeout(() =>
			{
				this.buttonEnterTimeout = null;
				if (!Dom.hasClass(button.button, 'ui-btn-clock'))
				{
					this.showPopup(button);
				}
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
		if (this.status === 'refused')
		{
			return {
				text: Loc.getMessage('STATUS_BUTTON_SYNCHRONIZATION'),
				color: BX.UI.Button.Color.LIGHT_BORDER,
				iconClass: 'calendar-sync-btn-icon-refused',
			};
		}

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
		else if (this.status === 'synchronizing')
		{
			return {
				text: Loc.getMessage('STATUS_BUTTON_SYNCHRONIZATION'),
				color: BX.UI.Button.Color.LIGHT_BORDER,
				iconClass: 'ui-btn-clock',
			}
		}

		return {
			text: Loc.getMessage('STATUS_BUTTON_SYNC_CALENDAR_NEW'),
			color: BX.UI.Button.Color.PRIMARY,
		}
	}

	getSyncPanel()
	{
		return this.syncPanel;
	}
	
	setConnectionProviders(connectionsProviders)
	{
		this.connectionsProviders = connectionsProviders;
	}
}