// @flow

'use strict';

import { Loc, Event, Text, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Guide } from 'ui.tour';
import { ConnectionProvider } from '../connectionproviders/connectionprovider';
import ConnectionItem from '../connectionproviders/connectionitem';

export default class SyncStatusPopupV2 extends EventEmitter
{
	static SYNC_POPUP_KEY = 'sync_popup';
	static SYNC_POPUP_TTL = 3600 * 24 * 30 * 1000; // 30 days
	static IS_RUN_REFRESH = false;
	#guide;

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Interface.SyncStatusPopupV2');
		const node = options.node;
		const failedConnection: ConnectionProvider = options.failedConnection;

		this.node = node;
		this.#guide = new Guide({
			steps: [
				{
					target: this.node,
					title: Loc.getMessage('CALENDAR_SYNC_MANAGER_AHA_TITLE'),
					text: this.getText(failedConnection),
					article: null,
				},
			],
			onEvents: true,
			autoHide: true,
			overlay: false,
		});

		this.#guide.start();
		this.setAsShown(failedConnection);

		const linkNode = this.#guide.getPopup()?.getPopupContainer()?.querySelector('[data-id="calendar-sync-manager__aha-link"]');
		if (linkNode)
		{
			Event.bind(linkNode, 'click', () => {
				options.onSyncPanelOpen();
				this.#guide.close();
			});
		}

		this.#guide.getPopup().setWidth(390);
	}

	static createInstance(options): SyncStatusPopupV2 | null
	{
		const failedConnection = SyncStatusPopupV2.getNotShownFailedConnection(options);
		const isFailed = options.status === 'failed';
		const syncErrors = options.syncErrors > 0;

		return (isFailed && syncErrors && failedConnection)
			? new this({ failedConnection, ...options })
			: null
			;
	}

	static getNotShownFailedConnection(options): ConnectionItem | null
	{
		return SyncStatusPopupV2.getFailedConnections(options).find((failedConnection: ConnectionItem) => {
			return !SyncStatusPopupV2.alreadyShown(failedConnection);
		});
	}

	static getFailedConnections(options): ConnectionItem[] | []
	{
		const failedConnections = [];
		// eslint-disable-next-line no-restricted-syntax
		for (const providerName in options.connectionsProviders)
		{
			if (Object.prototype.hasOwnProperty.call(options.connectionsProviders, providerName)
				&& options.connectionsProviders[providerName].getStatus() === 'failed')
			{
				failedConnections.push(
					...SyncStatusPopupV2.getFailedConnectionsFromProvider(options.connectionsProviders[providerName]),
				);
				break;
			}
		}

		return failedConnections;
	}

	static getFailedConnectionsFromProvider(provider: ConnectionProvider): ConnectionItem[] | []
	{
		return provider.getConnections()?.filter((connection: ConnectionItem) => {
			return connection.getStatus() === 'failed';
		});
	}

	static alreadyShown(failedConnection: ConnectionItem): Boolean
	{
		if (Type.isUndefined(window.localStorage))
		{
			return true;
		}

		const key = `${SyncStatusPopupV2.SYNC_POPUP_KEY}_${failedConnection.getConnectionName()}`;
		const itemString = window.localStorage.getItem(key);
		if (!itemString)
		{
			return false;
		}

		const item = JSON.parse(itemString);
		const now = new Date();

		return now.getTime() < item.expire;
	}

	getText(failedConnection: ConnectionItem): Element
	{
		const providerName = Text.encode(failedConnection.getConnectionName());
		const accountName = Text.encode(failedConnection.getAccountName()).trim();
		const accountNameCapitalized = `<span class="calendar-sync-manager__aha-content-element-type">${accountName}</span>`;

		return `
			<div class="calendar-sync-manager__aha-content">
				<div class="calendar-sync-manager__aha-content-element">
					${Loc.getMessage('CALENDAR_SYNC_MANAGER_AHA_TEXT_1', { '#PROVIDER_NAME#': providerName })}
				</div>
				<div class="calendar-sync-manager__aha-content-element">
					${Loc.getMessage('CALENDAR_SYNC_MANAGER_AHA_TEXT_2', { '#PROVIDER#': accountNameCapitalized })}
				</div>
				<div class="calendar-sync-manager__aha-link" data-id="calendar-sync-manager__aha-link">
					${Loc.getMessage('CALENDAR_SYNC_MANAGER_AHA_LINK')}
				</div>
			</div>
		`;
	}

	setAsShown(failedConnection: ConnectionItem): void
	{
		if (Type.isUndefined(window.localStorage))
		{
			return;
		}

		const now = new Date();
		const key = `${SyncStatusPopupV2.SYNC_POPUP_KEY}_${failedConnection.getConnectionName()}`;
		const payload = {
			expire: now.getTime() + SyncStatusPopupV2.SYNC_POPUP_TTL,
		};
		window.localStorage.setItem(key, JSON.stringify(payload));
	}
}