import {EventEmitter, BaseEvent} from 'main.core.events';

import {Core} from 'im.v2.application.core';
import {LocalStorageManager} from 'im.v2.lib.local-storage';
import {Logger} from 'im.v2.lib.logger';
import {LocalStorageKey, EventType, Layout} from 'im.v2.const';

import type {OnLayoutChangeEvent} from 'im.v2.const';

const WRITE_TO_STORAGE_TIMEOUT = 1000;
const SHOW_DRAFT_IN_RECENT_TIMEOUT = 1500;

export class DraftManager
{
	static instance: DraftManager;
	static inited: boolean;

	#drafts: {[string]: string} = {};
	#store: Object;

	static getInstance(): DraftManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	constructor()
	{
		this.#store = Core.getStore();

		EventEmitter.subscribe(EventType.layout.onLayoutChange, this.#onLayoutChange.bind(this));
	}

	initDraftHistory()
	{
		if (DraftManager.inited)
		{
			return false;
		}
		this.#drafts = LocalStorageManager.getInstance().get(LocalStorageKey.draft, {});
		Logger.warn('DraftManager: initDrafts:', this.#drafts);
		this.#setDraftsInRecentList();
		DraftManager.inited = true;
	}

	setDraft(dialogId: number, text: string)
	{
		text = text.trim();
		if (text === '')
		{
			delete this.#drafts[dialogId];
		}
		else
		{
			this.#drafts[dialogId] = text;
		}

		clearTimeout(this.writeToStorageTimeout);
		this.writeToStorageTimeout = setTimeout(() => {
			LocalStorageManager.getInstance().set(LocalStorageKey.draft, this.#drafts);
		}, WRITE_TO_STORAGE_TIMEOUT);
	}

	clearDraft(dialogId: number)
	{
		this.setDraft(dialogId, '');
	}

	getDraft(dialogId: number): string
	{
		return this.#drafts[dialogId] ?? '';
	}

	clearDraftInRecentList(dialogId: number)
	{
		this.#setDraftInRecentList(dialogId, '');
	}

	#setDraftsInRecentList()
	{
		Object.entries(this.#drafts).forEach(([dialogId, text]) => {
			this.#setDraftInRecentList(dialogId, text);
		});
	}

	#setDraftInRecentList(dialogId: number, text: string)
	{
		this.#store.dispatch('recent/draft', {
			id: dialogId,
			text
		});
	}

	#onLayoutChange(event: BaseEvent<OnLayoutChangeEvent>)
	{
		const {from} = event.getData();
		if (from.name !== Layout.chat.name || from.entityId === '')
		{
			return;
		}

		const dialogId = from.entityId;
		setTimeout(() => {
			this.#setDraftInRecentList(dialogId, this.getDraft(dialogId));
		}, SHOW_DRAFT_IN_RECENT_TIMEOUT);
	}
}