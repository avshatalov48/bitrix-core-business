/**
 * Bitrix Vuex wrapper
 * BitrixMobile ApplicationStorage driver for Vuex Builder
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import {md5} from "main.md5";

export class VuexBuilderDatabaseJnSharedStorage
{
	constructor(config = {})
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

		if (!this.isJnContext() && typeof ApplicationStorage === 'undefined')
		{
			console.error('ApplicationStorage is not defined, load "webcomponent/storage" extension.')
		}
	}

	get()
	{
		return new Promise((resolve, reject) =>
		{
			if (this.isJnContext())
			{
				let result = Application.sharedStorage.get(this.code);
				resolve(result? result: null);
			}
			else if (typeof ApplicationStorage !== 'undefined')
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

	set(value)
	{
		return new Promise((resolve, reject) =>
		{
			if (this.isJnContext())
			{
				Application.sharedStorage().set(
					this.code,
					JSON.stringify(this.prepareValueBeforeSet(value))
				);
				resolve();
			}
			else if (typeof ApplicationStorage !== 'undefined')
			{
				ApplicationStorage.set(
					this.code,
					JSON.stringify(this.prepareValueBeforeSet(value))
				).then(data => resolve());
			}
			else
			{
				resolve();
			}
		});
	}

	clear()
	{
		return this.set(null);
	}

	/**
	 * @private
	 */
	isJnContext()
	{
		return typeof env !== 'undefined';
	}

	/**
	 * @private
	 */
	prepareValueAfterGet(value)
	{
		if (value instanceof Array)
		{
			value = value.map(element => this.prepareValueAfterGet(element));
		}
		else if (value instanceof Date)
		{
		}
		else if (value && typeof value === 'object')
		{
			for (let index in value)
			{
				if (value.hasOwnProperty(index))
				{
					value[index] = this.prepareValueAfterGet(value[index]);
				}
			}
		}
		else if (typeof value === 'string')
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
	prepareValueBeforeSet(value)
	{
		if (value instanceof Array)
		{
			value = value.map(element => this.prepareValueBeforeSet(element));
		}
		else if (value instanceof Date)
		{
			value = '#DT#'+value.toISOString();
		}
		else if (value && typeof value === 'object')
		{
			for (let index in value)
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