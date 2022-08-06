/**
 * Bitrix Messenger
 * Logger class
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {Type} from "main.core";

class Logger
{
	#types = {};
	#config = {};
	#custom = {};
	#localStorageKey = 'bx-messenger-logger';

	constructor()
	{
		this.#types = {
			desktop: true,
			log: false,
			info: false,
			warn: false,
			error: true,
			trace: true,
		};

		this.#config = this.#types;

		this.#load();
	}

	setConfig(types: Object)
	{
		Object.entries(types).forEach(([type, value]) => {
			if (!Type.isUndefined(this.#types[type]))
			{
				this.#types[type] = !!value;
				this.#config[type] = !!value;
			}
		});

		this.#load();
	}

	enable(type: string)
	{
		if (Type.isUndefined(this.#types[type]))
		{
			return false;
		}

		this.#types[type] = true;
		this.#custom[type] = true;

		this.#save();

		return true;
	}

	disable(type: string)
	{
		if (Type.isUndefined(this.#types[type]))
		{
			return false;
		}

		this.#types[type] = false;
		this.#custom[type] = false;

		this.#save();

		return true;
	}

	isEnabled(type: string): boolean
	{
		return this.#types[type] === true;
	}

	desktop(...params)
	{
		if (!this.isEnabled('desktop'))
		{
			return false;
		}

		console.log(...Logger.#getStyles('desktop'), ...params);
	}

	log(...params)
	{
		if (!this.isEnabled('log'))
		{
			return false;
		}

		console.log(...Logger.#getStyles('log'), ...params);
	}

	info(...params)
	{
		if (!this.isEnabled('info'))
		{
			return false;
		}

		console.info(...Logger.#getStyles('info'), ...params);
	}

	warn(...params)
	{
		if (!this.isEnabled('warn'))
		{
			return false;
		}

		console.warn(...Logger.#getStyles('warn'), ...params);
	}

	error(...params)
	{
		if (!this.isEnabled('error'))
		{
			return false;
		}

		console.error(...Logger.#getStyles('error'), ...params);
	}

	trace(...params)
	{
		if (!this.isEnabled('trace'))
		{
			return false;
		}

		console.trace(...params);
	}

	#save()
	{
		if (Type.isUndefined(window.localStorage))
		{
			return false;
		}

		try
		{
			const custom = {};
			Object.entries(this.#custom).forEach(([type, value]) => {
				if (this.#config[type] !== this.#custom[type])
				{
					custom[type] = !!value;
				}
			});

			console.warn('Logger: saving custom types', JSON.stringify(custom));

			window.localStorage.setItem(this.#localStorageKey, JSON.stringify(custom));
		}
		catch (error) {
			console.error('Logger: save error', error);
		}
	}

	#load()
	{
		if (Type.isUndefined(window.localStorage))
		{
			return false;
		}

		try
		{
			const custom = window.localStorage.getItem(this.#localStorageKey);
			if (Type.isString(custom))
			{
				this.#custom = JSON.parse(custom);
				this.#types = {...this.#types, ...this.#custom};
			}
		}
		catch (error) {
			console.error('Logger: load error', error);
		}
	}

	static #getStyles(type: string = 'all'): Object | Array
	{
		const styles = {
			'desktop': ["%cDESKTOP", "color: white; font-style: italic; background-color: #29619b; padding: 0 6px"],
			'log': ["%cLOG", "color: #2a323b; font-style: italic; background-color: #ccc; padding: 0 6px"],
			'info': ["%cINFO", "color: #fff; font-style: italic; background-color: #6b7f96; padding: 0 6px"],
			'warn': ["%cWARNING", "color: white; font-style: italic; padding: 0 6px; border: 1px solid #f0a74f"],
			'error': ["%cERROR", "color: white; font-style: italic; padding: 0 6px; border: 1px solid #8a3232"],
		};

		if (type === 'all')
		{
			return styles;
		}

		if (styles[type])
		{
			return styles[type];
		}

		return [];
	}

	static #getRemoveString()
	{
		const styles = Logger.#getStyles();
		const result = [];

		Object.entries(styles).forEach(([, style]) => {
			result.push(style[1]);
		});

		return result;
	}
}

const logger = new Logger();

export {logger as Logger};