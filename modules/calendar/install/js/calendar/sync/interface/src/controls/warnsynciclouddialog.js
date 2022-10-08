// @flow
'use strict';
import { Dom, Loc, Tag } from 'main.core';
import { Util } from 'calendar.util';
import { Popup } from 'main.popup';

export default class WarnSyncIcloudDialog
{
	zIndex = 3100;
	DOM = {};
	
	constructor(options = {})
	{
		this.authDialog = options.authDialog;
	}
	
	show()
	{
		this.popup = new Popup({
			className: 'calendar-sync__auth-popup calendar-sync__scope',
			titleBar: Loc.getMessage('CAL_ICLOUD_ALERT_OTHER_APPLE_SYNC_TITLE'),
			width: 500,
			draggable: true,
			content: this.getContainer(),
			cacheable: false,
			closeByEsc: true,
			closeIcon: true,
			contentBackground: "#fff",
			overlay: { opacity: 15 },
			buttons: [
				new BX.UI.Button({
					text: Loc.getMessage('CAL_ICLOUD_ALERT_OTHER_APPLE_SYNC_LEARN_MORE'),
					className: 'ui-btn ui-btn-md ui-btn-primary',
					events: { click: this.openHelpDesk.bind(this) }
				}),
				new BX.UI.Button({
					text: Loc.getMessage('CAL_BUTTON_CONTINUE'),
					className: 'ui-btn ui-btn-md ui-btn-light',
					events: { click: this.openAuthDialog.bind(this) }
				}),
			],
			events: {
				onPopupClose: this.close.bind(this)
			},
		});
		
		this.popup.show();
	}
	
	getContainer()
	{
		this.DOM.container = Tag.render`
			<div>
				${this.getAlertInformation()}
			</div>
		`;
		
		return this.DOM.container;
	}
	
	getAlertInformation()
	{
		this.DOM.alertBlock = new BX.UI.Alert({
			text: Loc.getMessage('CAL_ICLOUD_ALERT_OTHER_APPLE_SYNC_INFO'),
			color: BX.UI.Alert.Color.WARNING,
			icon: BX.UI.Alert.Icon.INFO
		});
		
		const container = this.DOM.alertBlock.getContainer();
		const text = container.querySelector('.ui-alert-message');
		Dom.addClass(text, 'calendar-sync__alert-popup--text')
		
		return container;
	}
	
	openHelpDesk()
	{
		const helpDeskCode = '16020988';
		top.BX.Helper.show('redirect=detail&code=' + helpDeskCode);
	}
	
	disableConnection()
	{
		BX.ajax.runAction('calendar.api.syncajax.disableIphoneOrMacConnection').then(() => {
			this.authDialog.show();
			this.close();
			Util.setIphoneConnectionStatus(false);
			Util.setMacConnectionStatus(false);
		})
	}
	
	openAuthDialog()
	{
		this.authDialog.show();
		this.close();
	}
	
	close()
	{
		if (this.popup)
		{
			this.popup.destroy();
		}
	}
}