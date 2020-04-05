/**
 * Bitrix Vuex wrapper
 * LocalStorage driver for Vuex Builder
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import {md5} from "main.md5";

export class VuexBuilderDatabaseLocalStorage
{
	constructor(config = {})
	{
		this.siteId = config.siteId || 'default';
		this.userId = config.userId || 0;
		this.storage = config.storage || 'default';
		this.name = config.name || '';

		this.enabled = false;

		if (typeof window.localStorage !== 'undefined')
		{
			try
			{
				window.localStorage.setItem('__bx_test_ls_feature__', 'ok');
				if (window.localStorage.getItem('__bx_test_ls_feature__') === 'ok')
				{
					window.localStorage.removeItem('__bx_test_ls_feature__');
					this.enabled = true;
				}
			}
			catch(e)
			{
			}
		}

		this.code = 'bx-vuex-'+(window.md5 || md5)(
			this.siteId+'/'+
			this.userId+'/'+
			this.storage+'/'+
			this.name
		);
	}

	get()
	{
		return new Promise((resolve, reject) =>
		{
			if (!this.enabled)
			{
				resolve(null);
				return true;
			}

			let result = window.localStorage.getItem(this.code);
			if (typeof result !== "string")
			{
				resolve(null);
				return true;
			}

			try
			{
				resolve(
					this.prepareValueAfterGet(
						JSON.parse(result)
					)
				);
			}
			catch(error)
			{
				reject(error);
			}
		});
	}

	set(value)
	{
		return new Promise((resolve, reject) =>
		{
			if (this.enabled)
			{
				window.localStorage.setItem(this.code, JSON.stringify(this.prepareValueBeforeSet(value)));
			}
			resolve(true);
		});
	}

	clear()
	{
		return new Promise((resolve, reject) =>
		{
			if (this.enabled)
			{
				window.localStorage.removeItem(this.code);
			}
			resolve(true);
		});
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