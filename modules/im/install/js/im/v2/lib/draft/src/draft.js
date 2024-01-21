import { EventEmitter, BaseEvent } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { Logger } from 'im.v2.lib.logger';
import { LocalStorageKey, EventType, Layout } from 'im.v2.const';

import type { OnLayoutChangeEvent } from 'im.v2.const';

const WRITE_TO_STORAGE_TIMEOUT = 1000;
const SHOW_DRAFT_IN_RECENT_TIMEOUT = 1500;

export class DraftManager
{
	static instance: DraftManager = null;

	inited: boolean = false;
	drafts: {[string]: string} = {};

	static getInstance(): DraftManager
	{
		if (!DraftManager.instance)
		{
			DraftManager.instance = new DraftManager();
		}

		return DraftManager.instance;
	}

	constructor()
	{
		EventEmitter.subscribe(EventType.layout.onLayoutChange, this.onLayoutChange.bind(this));
	}

	initDraftHistory()
	{
		if (this.inited)
		{
			return;
		}

		this.drafts = LocalStorageManager.getInstance().get(this.getLocalStorageKey(), {});
		Logger.warn('DraftManager: initDrafts:', this.drafts);
		this.setDraftsInRecentList();
		this.inited = true;
	}

	setDraft(dialogId: number, text: string)
	{
		const preparedText = text.trim();
		if (preparedText === '')
		{
			delete this.drafts[dialogId];
		}
		else
		{
			this.drafts[dialogId] = preparedText;
		}

		clearTimeout(this.writeToStorageTimeout);
		this.writeToStorageTimeout = setTimeout(() => {
			LocalStorageManager.getInstance().set(this.getLocalStorageKey(), this.drafts);
		}, WRITE_TO_STORAGE_TIMEOUT);
	}

	getDraft(dialogId: number): string
	{
		return this.drafts[dialogId] ?? '';
	}

	clearDraftInRecentList(dialogId: number)
	{
		this.setDraftInRecentList(dialogId, '');
	}

	setDraftsInRecentList()
	{
		Object.entries(this.drafts).forEach(([dialogId, text]) => {
			this.setDraftInRecentList(dialogId, text);
		});
	}

	setDraftInRecentList(dialogId: number, text: string)
	{
		Core.getStore().dispatch(this.getDraftMethodName(), {
			id: dialogId,
			text,
		});
	}

	onLayoutChange(event: BaseEvent<OnLayoutChangeEvent>)
	{
		const { from } = event.getData();
		if (from.name !== this.getLayoutName() || from.entityId === '')
		{
			return;
		}

		const dialogId = from.entityId;
		setTimeout(() => {
			this.setDraftInRecentList(dialogId, this.getDraft(dialogId));
		}, SHOW_DRAFT_IN_RECENT_TIMEOUT);
	}

	getLayoutName(): string
	{
		return Layout.chat.name;
	}

	getLocalStorageKey(): string
	{
		return LocalStorageKey.recentDraft;
	}

	getDraftMethodName(): string
	{
		return 'recent/setRecentDraft';
	}
}
