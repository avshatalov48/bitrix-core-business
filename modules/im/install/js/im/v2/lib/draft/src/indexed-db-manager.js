import { Dexie } from 'ui.dexie';

import { LocalStorageManager } from 'im.v2.lib.local-storage';

const DB_NAME = 'bx-im-drafts';

const recentDraftLocalStorageKey = 'recentDraft';
const copilotDraftLocalStorageKey = 'copilotDraft';

export class IndexedDbManager
{
	db: Dexie;
	static instance: IndexedDbManager;

	static getInstance(): IndexedDbManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	constructor()
	{
		this.db = new Dexie(DB_NAME);
		this.db.version(1).stores({
			drafts: '',
		});
	}

	async migrateFromLocalStorage()
	{
		const migrationStatus = await this.db.drafts.get('migration_status');
		if (migrationStatus)
		{
			return;
		}

		const recentDrafts = LocalStorageManager.getInstance().get(recentDraftLocalStorageKey, {});
		this.set(recentDraftLocalStorageKey, recentDrafts);
		LocalStorageManager.getInstance().remove(recentDraftLocalStorageKey);

		const copilotDrafts = LocalStorageManager.getInstance().get(copilotDraftLocalStorageKey, {});
		this.set(copilotDraftLocalStorageKey, copilotDrafts);
		LocalStorageManager.getInstance().remove(copilotDraftLocalStorageKey);

		this.setMigrationFinished();
	}

	set(key: string, value: any)
	{
		this.db.drafts.put(value, key);
	}

	setMigrationFinished()
	{
		const result = {
			[recentDraftLocalStorageKey]: true,
			[copilotDraftLocalStorageKey]: true,
		};

		this.db.drafts.put(result, 'migration_status');
	}

	async get(key: string, defaultValue = null): Promise<any>
	{
		await this.migrateFromLocalStorage();
		const value = await this.db.drafts.get(key);

		return value || defaultValue;
	}
}
