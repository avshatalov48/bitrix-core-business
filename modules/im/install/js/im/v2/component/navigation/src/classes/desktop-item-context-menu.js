import { Loc } from 'main.core';
import { PopupManager } from 'main.popup';

import { BaseMenu } from 'im.v2.lib.menu';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { PopupType } from 'im.v2.const';
import { showDesktopDeleteConfirm } from 'im.v2.lib.confirm';

import type { MenuItem } from 'im.v2.lib.menu';
import type { DesktopAccount } from 'im.v2.lib.desktop-api';

export class DesktopItemContextMenu extends BaseMenu
{
	context: DesktopAccount;

	constructor()
	{
		super();
		this.id = PopupType.desktopItemMenu;
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.#getConnectItem(),
			this.#getDeleteItem(),
		];
	}

	#getConnectItem(): MenuItem
	{
		const title = this.context.connected
			? Loc.getMessage('IM_USER_SETTINGS_DESKTOP_CONTEXT_MENU_DISCONNECT')
			: Loc.getMessage('IM_USER_SETTINGS_DESKTOP_CONTEXT_MENU_CONNECT')
		;

		return {
			text: title,
			onclick: function() {
				if (this.context.connected)
				{
					this.#disconnect();
				}
				else
				{
					this.#connect();
				}
				this.menuInstance.close();
				PopupManager.getPopupById(PopupType.userProfile)?.close();
			}.bind(this),
		};
	}

	#getDeleteItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_USER_SETTINGS_DESKTOP_CONTEXT_MENU_DELETE'),
			onclick: async function() {
				const userChoice = await showDesktopDeleteConfirm();
				if (userChoice === true)
				{
					DesktopApi.deleteAccount(this.context.host, this.context.login);
					PopupManager.getPopupById(PopupType.userProfile)?.close();
				}
			}.bind(this),
		};
	}

	#connect()
	{
		const { host, login, protocol } = this.context;

		const userLang = navigator.language;
		DesktopApi.connectAccount(host, login, protocol, userLang);
	}

	#disconnect()
	{
		const { host } = this.context;
		DesktopApi.disconnectAccount(host);
	}

	close()
	{
		PopupManager.getPopupById(PopupType.userProfile)?.setAutoHide(true);
		super.close();
	}
}
