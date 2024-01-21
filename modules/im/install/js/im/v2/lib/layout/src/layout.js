import { EventEmitter, BaseEvent } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { Logger } from 'im.v2.lib.logger';
import { EventType, Layout, LocalStorageKey } from 'im.v2.const';

import type { ImModelLayout } from 'im.v2.model';

type EntityId = string;

export class LayoutManager
{
	static #instance: LayoutManager;

	#lastOpenedElement: { [layoutName: string]: EntityId } = {};

	static getInstance(): LayoutManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init(): void
	{
		LayoutManager.getInstance();
	}

	constructor()
	{
		EventEmitter.subscribe(EventType.dialog.goToMessageContext, this.#onGoToMessageContext.bind(this));
		EventEmitter.subscribe(EventType.desktop.onReload, this.#onDesktopReload.bind(this));
	}

	async setLayout(config: ImModelLayout): Promise
	{
		if (config.entityId)
		{
			this.setLastOpenedElement(config.name, config.entityId);
		}

		return Core.getStore().dispatch('application/setLayout', config);
	}

	getLayout(): ImModelLayout
	{
		return Core.getStore().getters['application/getLayout'];
	}

	saveCurrentLayout(): void
	{
		const currentLayout = this.getLayout();

		LocalStorageManager.getInstance().set(LocalStorageKey.layoutConfig, {
			name: currentLayout.name,
			entityId: currentLayout.entityId,
		});
	}

	restoreLastLayout(): Promise
	{
		const layoutConfig = LocalStorageManager.getInstance().get(LocalStorageKey.layoutConfig);
		if (!layoutConfig)
		{
			return Promise.resolve();
		}

		Logger.warn('LayoutManager: last layout was restored', layoutConfig);

		LocalStorageManager.getInstance().remove(LocalStorageKey.layoutConfig);

		return this.setLayout(layoutConfig);
	}

	getLastOpenedElement(layoutName: string): null | string
	{
		return this.#lastOpenedElement[layoutName] ?? null;
	}

	setLastOpenedElement(layoutName: string, entityId: string): void
	{
		this.#lastOpenedElement[layoutName] = entityId;
	}

	destroy(): void
	{
		EventEmitter.unsubscribe(EventType.dialog.goToMessageContext, this.#onGoToMessageContext);
		EventEmitter.unsubscribe(EventType.desktop.onReload, this.#onDesktopReload.bind(this));
	}

	#onGoToMessageContext(event: BaseEvent<{dialogId: string, messageId: number}>): void
	{
		const { dialogId, messageId } = event.getData();
		if (this.getLayout().entityId === dialogId)
		{
			return;
		}

		this.setLayout({
			name: Layout.chat.name,
			entityId: dialogId,
			contextId: messageId,
		});
	}

	#onDesktopReload()
	{
		this.saveCurrentLayout();
	}
}
