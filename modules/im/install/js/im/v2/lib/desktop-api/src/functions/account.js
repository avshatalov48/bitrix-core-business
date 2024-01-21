/* eslint-disable no-undef */
import { ajax as Ajax } from 'main.core';

import { RestMethod } from 'im.v2.const';

import { lifecycleFunctions } from './lifecycle';

import type { DesktopAccount } from '../types/account';

export const accountFunctions = {
	openAddAccountTab()
	{
		BXDesktopSystem?.AccountAddForm();
	},
	deleteAccount(host: string, login: string)
	{
		BXDesktopSystem?.AccountDelete(host, login);
	},
	connectAccount(host: string, login: string, protocol: string, userLang: string)
	{
		BXDesktopSystem?.AccountConnect(host, login, protocol, userLang);
	},
	disconnectAccount(host: string)
	{
		BXDesktopSystem?.AccountDisconnect(host);
	},
	getAccountList(): DesktopAccount[]
	{
		return BXDesktopSystem?.AccountList();
	},
	login(): Promise
	{
		return new Promise((resolve) => {
			BXDesktopSystem?.Login({
				// there is no fail callback. If it fails, desktop will show login form
				success: () => resolve(),
			});
		});
	},
	async logout(): Promise
	{
		try
		{
			await Ajax.runAction(RestMethod.imV2DesktopLogout);
			BXDesktopSystem?.Logout(2);
		}
		catch (error)
		{
			console.error('DesktopApi logout error', error);
			BXDesktopSystem?.Logout(3);
		}
	},
	async terminate(): Promise
	{
		try
		{
			await Ajax.runAction(RestMethod.imV2DesktopLogout);
		}
		finally
		{
			lifecycleFunctions.shutdown();
		}
	},
};
