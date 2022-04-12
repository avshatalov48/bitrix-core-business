/**
 * Bitrix Vuex wrapper
 * BitrixMobile ApplicationStorage driver for Vuex Builder
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2022 Bitrix
 */

import {md5} from "main.md5";
import {Type} from "main.core";

export class BuilderDatabaseJnSharedStorage
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

		if (!this.isJnContext() && Type.isUndefined(ApplicationStorage))
		{
			console.error('ApplicationStorage is not defined, load "webcomponent/storage" extension.')
		}
	}

	get(): Promise<object>
	{
		return new Promise((resolve) =>
		{
			if (this.isJnContext())
			{
				const result = Application.sharedStorage.get(this.code);
				resolve(result? result: null);
			}
			else if (!Type.isUndefined(ApplicationStorage))
			{
				ApplicationStorage.get(this.code, null)
					.then(data => resolve(this.prepareValueAfterGet(JSON.parse(data))))
				;
			}
			else
			{
				resolve(null);
			}
		});
	}

	set(value): Promise<void>
	{
		return new Promise((resolve) =>
		{
			if (this.isJnContext())
			{
				Application.sharedStorage().set(
					this.code,
					JSON.stringify(this.prepareValueBeforeSet(value))
				);
				resolve();
			}
			else if (!Type.isUndefined(ApplicationStorage))
			{
				ApplicationStorage.set(
					this.code,
					JSON.stringify(this.prepareValueBeforeSet(value))
				).then(() => resolve());
			}
			else
			{
				resolve();
			}
		});
	}

	clear(): Promise<void>
	{
		return this.set(null);
	}

	/**
	 * @private
	 */
	isJnContext(): boolean
	{
		return !Type.isUndefined(env);
	}

	/**
	 * @private
	 */
	prepareValueAfterGet(value): any
	{
		if (value instanceof Array)
		{
			value = value.map(element => this.prepareValueAfterGet(element));
		}
		else if (value instanceof Date)
		{
		}
		else if (Type.isObjectLike(value))
		{
			for (const index in value)
			{
				if (value.hasOwnProperty(index))
				{
					value[index] = this.prepareValueAfterGet(value[index]);
				}
			}
		}
		else if (Type.isString(value))
		{
			if (value.startsWith('#DT#'))
			{
				value = new Date(value.substring(4));
			}
		}

		return value;
	}

	/**
	 * @private
	 */
	prepareValueBeforeSet(value): any
	{
		if (value instanceof Array)
		{
			value = value.map(element => this.prepareValueBeforeSet(element));
		}
		else if (value instanceof Date)
		{
			value = '#DT#'+value.toISOString();
		}
		else if (Type.isObjectLike(value))
		{
			for (const index in value)
			{
				if (value.hasOwnProperty(index))
				{
					value[index] = this.prepareValueBeforeSet(value[index]);
				}
			}
		}

		return value;
	}
}