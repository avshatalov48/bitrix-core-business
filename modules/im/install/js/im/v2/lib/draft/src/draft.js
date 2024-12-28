import { Type } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

import { Logger } from 'im.v2.lib.logger';
import { Core } from 'im.v2.application.core';
import { ChatType, EventType, Layout, TextareaPanelType } from 'im.v2.const';

import { IndexedDbManager } from './indexed-db-manager';

import type { JsonObject } from 'main.core';
import type { OnLayoutChangeEvent } from 'im.v2.const';
import type { PanelContext } from 'im.v2.provider.service';

type TextareaPanelTypeItem = $Values<typeof TextareaPanelType>;
type Draft = {
	text?: string,
	panelType?: TextareaPanelTypeItem,
	panelContext?: PanelContext,
	mentions?: JsonObject
};

const WRITE_TO_STORAGE_TIMEOUT = 1000;
const SHOW_DRAFT_IN_RECENT_TIMEOUT = 1500;

const STORAGE_KEY = 'recentDraft';

const NOT_AVAILABLE_CHAT_TYPES = new Set([ChatType.comment]);

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

	async initDraftHistory()
	{
		if (this.inited)
		{
			return;
		}

		this.inited = true;
		let draftHistory = null;
		try
		{
			draftHistory = await IndexedDbManager.getInstance().get(this.getStorageKey(), {});
		}
		catch (error)
		{
			// eslint-disable-next-line no-console
			console.error('DraftManager: error initing draft history', error);
			this.initPromiseResolver();

			return;
		}
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

	setDraftPanel(dialogId: number, panelType: TextareaPanelTypeItem, panelContext: PanelContext): void
	{
		if (!this.drafts[dialogId])
		{
			this.drafts[dialogId] = {};
		}
		this.drafts[dialogId].panelType = panelType;
		this.drafts[dialogId].panelContext = panelContext;

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

	clearDraft(dialogId: string)
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
		if (!this.canSetRecentItemDraftText(dialogId))
		{
			return;
		}

		void Core.getStore().dispatch(this.getDraftMethodName(), {
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
			this.saveToIndexedDb();
		}, WRITE_TO_STORAGE_TIMEOUT);
	}

	saveToIndexedDb()
	{
		IndexedDbManager.getInstance().set(this.getStorageKey(), this.prepareDrafts());
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

	getStorageKey(): string
	{
		return STORAGE_KEY;
	}

	getDraftMethodName(): string
	{
		return 'recent/setRecentDraft';
	}

	canSetRecentItemDraftText(dialogId: string): boolean
	{
		const chat = Core.getStore().getters['chats/get'](dialogId);
		if (!chat)
		{
			return false;
		}

		return !NOT_AVAILABLE_CHAT_TYPES.has(chat.type);
	}
}
