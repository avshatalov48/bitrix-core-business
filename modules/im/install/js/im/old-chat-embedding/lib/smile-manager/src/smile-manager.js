import {Extension} from 'main.core';
import {Dexie} from 'ui.dexie';
import {RestClient} from 'rest.client';

import {Core} from 'im.old-chat-embedding.application.core';
import {RestMethod, LocalStorageKey} from 'im.old-chat-embedding.const';
import {LocalStorageManager} from 'im.old-chat-embedding.lib.local-storage';

export type Set = {
	id: string;
	parentId: string;
	name: string;
	type: string;
	image: string;
};
export type Smile = {
	id: string;
	setId: string;
	name: string;
	image: string;
	typing: string;
	alternative: boolean;
	width: number;
	height: number;
	definition: string;
};
type SmileList = {
	sets: Array<Set>;
	smiles: Array<Smile>;
};

const sets = [
	'id',
	'parentId',
	'name',
	'type',
	'image',
	'selected'
].join(',');

const smiles = [
	'id',
	'setId',
	'name',
	'image',
	'typing',
	'width',
	'height',
	'definition',
	'alternative'
].join(',');

const CACHE_VERSION = 4;

export class SmileManager
{
	static #instance: SmileManager;

	#smileList: Array<SmileList>;
	#db: Dexie;
	#restClient: RestClient;
	#localStorageManager: LocalStorageManager;
	#lastUpdateTime: number;
	#recentEmoji: Set<String>;

	static getInstance(): SmileManager
	{
		SmileManager.#instance = SmileManager.#instance ?? new SmileManager();

		return SmileManager.#instance;
	}

	static init()
	{
		SmileManager.getInstance().initSmileList();
	}

	constructor()
	{
		this.#db = new Dexie('bx-im-smiles');
		this.#db.version(2).stores({sets, smiles, recentEmoji: ',symbols'});
		this.#restClient = Core.getRestClient();
		this.#localStorageManager = LocalStorageManager.getInstance();
		const {lastUpdate} = Extension.getSettings('im.old-chat-embedding.lib.smile-manager');
		this.#lastUpdateTime = Date.parse(lastUpdate) + CACHE_VERSION;
		// for debug purpose only
		// this.#lastUpdateTime = Date.now();
		this.#recentEmoji = new Set();
	}

	async #fetchDataFromServer(): Promise<SmileList>
	{
		const result = await this.#restClient.callMethod(RestMethod.imSmilesGet, {FULL_TYPINGS: 'Y'});
		const data = result.data();

		const smileList = [];
		data.smiles.forEach(smile => {
			const list = smile.typing.split(' ');
			let alternative = true;
			list.forEach(code => {
				smileList.push({...smile, typing: code, id: smileList.length, alternative});
				alternative = false;
			});
		});

		const setList = data.sets.map(set => {
			const firstSmileInSet = smileList.find(smile => smile.setId === set.id);
			const {image} = firstSmileInSet;
			return {...set, image};
		});

		return {sets: setList, smiles: smileList};
	}

	async #fetchDataFromStorage(): Promise<SmileList>
	{
		const {sets: setsTbl, smiles: smilesTbl} = this.#db;
		const data = await this.#db.transaction('r', setsTbl, smilesTbl, async () => {
			const [sets, smiles] = await Promise.all([
				setsTbl.toArray(),
				smilesTbl.toArray()
			]);

			return {sets, smiles};
		});

		return data;
	}

	async #fillStorage(smileList)
	{
		const {sets, smiles} = smileList;
		const setsToSave = sets.map((set) => ({...set, selected: 0}));
		setsToSave[0].selected = 1;
		await Promise.all([
			this.#db.smiles.clear(),
			this.#db.sets.clear()
		]);
		await Promise.all([
			this.#db.sets.bulkAdd(setsToSave),
			this.#db.smiles.bulkAdd(smiles)
		]);
		this.#smileList = {
			...this.#smileList,
			sets: setsToSave
		};
	}

	#shouldRequestFromServer(): boolean
	{
		const lastUpdateTimeFromStorage = this.#localStorageManager.get(LocalStorageKey.smileLastUpdateTime);
		const shouldRequestFromServer = this.#lastUpdateTime !== lastUpdateTimeFromStorage;

		return shouldRequestFromServer;
	}

	async #loadRecentEmoji()
	{
		const storageData = await this.#db.recentEmoji.get(0);
		this.#recentEmoji = storageData?.symbols ?? this.#recentEmoji;
	}

	async initSmileList()
	{
		try {
			const shouldRequestFromServer = this.#shouldRequestFromServer();
			if (shouldRequestFromServer)
			{
				this.#smileList = await this.#fetchDataFromServer();
				await this.#fillStorage(this.#smileList);
				this.#localStorageManager.set(LocalStorageKey.smileLastUpdateTime, this.#lastUpdateTime);
			}
			else
			{
				this.#smileList = await this.#fetchDataFromStorage();
			}

			await this.#loadRecentEmoji();
		}
		catch (err)
		{
			console.error('Smile Manager data fetch error:', err);
			this.#localStorageManager.remove(LocalStorageKey.smileLastUpdateTime);
		}
	}

	async updateSelectedSet(selectedSetId: string)
	{
		const setsDB = this.#db.sets;
		await setsDB.toCollection().modify((set) => {
			set.selected = set.id === selectedSetId ? 1 : 0;
		});
		const sets = this.#smileList.sets;
		this.#smileList.sets = sets.map((set) => {
			if (set.id === selectedSetId) {
				return {...set, selected: 1};
			}

			return {...set, selected: 0};
		});
	}

	async updateRecentEmoji(symbols: Set<String>)
	{
		await this.#db.recentEmoji.put({symbols}, 0);
		this.#recentEmoji = symbols;
	}

	get smileList(): SmileList
	{
		return this.#smileList;
	}

	get recentEmoji(): Set<String>
	{
		return this.#recentEmoji;
	}
}