import { Type } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { Logger } from 'im.v2.lib.logger';
import { LocalStorageKey, EventType, Layout, TextareaPanelType } from 'im.v2.const';

import type { JsonObject } from 'main.core';
import type { OnLayoutChangeEvent } from 'im.v2.const';
type TextareaPanelTypeItem = $Values<typeof TextareaPanelType>;
type Draft = {
	text?: string,
	panelType?: TextareaPanelTypeItem,
	panelMessageId?: number,
	mentions?: JsonObject
};

const WRITE_TO_STORAGE_TIMEOUT = 1000;
const SHOW_DRAFT_IN_RECENT_TIMEOUT = 1500;

export class DraftManager
{
	static instance: DraftManager = null;

	inited: boolean = false;
	initPromise: Promise;
	initPromiseResolver: () => void;
	drafts: { [dialogId: string]: Draft } = {};

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
		this.initPromise = new Promise((resolve) => {
			this.initPromiseResolver = resolve;
		});
		EventEmitter.subscribe(EventType.layout.onLayoutChange, this.onLayoutChange.bind(this));
	}

	initDraftHistory()
	{
		if (this.inited)
		{
			return;
		}

		this.inited = true;
		const draftHistory = LocalStorageManager.getInstance().get(this.getLocalStorageKey(), {});
		this.fillDraftsFromStorage(draftHistory);

		Logger.warn('DraftManager: initDrafts:', this.drafts);
		this.initPromiseResolver();
		this.setRecentListDraftText();
	}

	ready(): Promise
	{
		return this.initPromise;
	}

	fillDraftsFromStorage(draftHistory: { [dialogId: string]: Draft }): void
	{
		if (!Type.isPlainObject(draftHistory))
		{
			return;
		}

		Object.entries(draftHistory).forEach(([dialogId, draft]) => {
			if (!Type.isPlainObject(draft))
			{
				return;
			}

			this.drafts[dialogId] = draft;
		});
	}

	setDraftText(dialogId: number, text: string): void
	{
		if (!this.drafts[dialogId])
		{
			this.drafts[dialogId] = {};
		}
		this.drafts[dialogId].text = text.trim();

		this.refreshSaveTimeout();
	}

	setDraftPanel(dialogId: number, panelType: TextareaPanelTypeItem, messageId: number): void
	{
		if (!this.drafts[dialogId])
		{
			this.drafts[dialogId] = {};
		}
		this.drafts[dialogId].panelType = panelType;
		this.drafts[dialogId].panelMessageId = messageId;

		this.refreshSaveTimeout();
	}

	setDraftMentions(dialogId: number, mentions: JsonObject): void
	{
		if (!this.drafts[dialogId])
		{
			this.drafts[dialogId] = {};
		}
		this.drafts[dialogId].mentions = mentions;

		this.refreshSaveTimeout();
	}

	async getDraft(dialogId: number): Promise<Draft>
	{
		await this.initPromise;
		const draft = this.drafts[dialogId] ?? {};

		return Promise.resolve(draft);
	}

	clearDraft(dialogId: number)
	{
		delete this.drafts[dialogId];
		this.setRecentItemDraftText(dialogId, '');
	}

	setRecentListDraftText()
	{
		Object.entries(this.drafts).forEach(([dialogId, draft]) => {
			this.setRecentItemDraftText(dialogId, draft.text ?? '');
		});
	}

	setRecentItemDraftText(dialogId: number, text: string)
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
		setTimeout(async () => {
			const { text = '' } = await this.getDraft(dialogId);
			this.setRecentItemDraftText(dialogId, text);
		}, SHOW_DRAFT_IN_RECENT_TIMEOUT);
	}

	refreshSaveTimeout()
	{
		clearTimeout(this.writeToStorageTimeout);
		this.writeToStorageTimeout = setTimeout(() => {
			this.saveToLocalStorage();
		}, WRITE_TO_STORAGE_TIMEOUT);
	}

	saveToLocalStorage()
	{
		LocalStorageManager.getInstance().set(this.getLocalStorageKey(), this.prepareDrafts());
	}

	prepareDrafts(): { [dialogId: string]: Draft }
	{
		const result = {};
		Object.entries(this.drafts).forEach(([dialogId, draft]) => {
			if (!draft.text && !draft.panelType)
			{
				return;
			}

			if (draft.panelType === TextareaPanelType.edit)
			{
				return;
			}

			result[dialogId] = {
				text: draft.text,
				mentions: draft.mentions,
			};
		});

		return result;
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
