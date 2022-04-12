/**
 * Bitrix Vuex wrapper
 * IndexedDB driver for Vuex Builder
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2022 Bitrix
 */

import {md5} from "main.md5";
import {Dexie} from "ui.dexie";

export class BuilderDatabaseIndexedDB
{
	constructor(config = {}): void
	{
		this.siteId = config.siteId || 'default';
		this.userId = config.userId || 0;
		this.storage = config.storage || 'default';
		this.name = config.name || '';

		this.code = (window.md5 || md5)(
			this.siteId+'/'+
			this.userId+'/'+
			this.storage+'/'+
			this.name
		);

		this.db = new Dexie('bx-vuex-model');

		this.db.version(1).stores({
			data: "code, value",
		});
	}

	get(): Promise<any>
	{
		return new Promise((resolve, reject) =>
		{
			this.db.data.where('code').equals(this.code).first().then(data => {
				resolve(data? data.value: null);
			}, error => {
				reject(error);
			});
		});
	}

	set(value): Promise<boolean>
	{
		return new Promise((resolve, reject) =>
		{
			this.db.data.put({code: this.code, value}).then(() => {
				resolve(true);
			}, error => {
				reject(error);
			});
		});
	}

	clear(): Promise<boolean>
	{
		return new Promise((resolve, reject) =>
		{
			this.db.data.delete(this.code).then(() => {
				resolve(true);
			}, error => {
				reject(error);
			});
		});
	}
}