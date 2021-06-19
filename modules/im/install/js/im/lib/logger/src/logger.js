/**
 * Bitrix Messenger
 * Logger class
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

class Logger
{
	#types = {};
	#config = {};
	#custom = {};

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

		this.__load();
	}

	setConfig(types)
	{
		for (let type in types)
		{
			if (
				types.hasOwnProperty(type)
				&& typeof this.#types[type] !== 'undefined'
			)
			{
				this.#types[type] = !!types[type];
				this.#config[type] = !!types[type];
			}
		}

		this.__load();
	}

	enable(type)
	{
		if (typeof this.#types[type] === 'undefined')
		{
			return false;
		}

		this.#types[type] = true;
		this.#custom[type] = true;

		this.__save();

		return true;
	}

	disable(type)
	{
		if (typeof this.#types[type] === 'undefined')
		{
			return false;
		}

		this.#types[type] = false;
		this.#custom[type] = false;

		this.__save();

		return true;
	}

	isEnabled(type)
	{
		return this.#types[type] === true;
	}

	desktop(...params)
	{
		if (this.isEnabled('desktop'))
		{
			console.log(...[...this.__getStyles('desktop'), ...params]);
		}
	}

	log(...params)
	{
		if (this.isEnabled('log'))
		{
			console.log(...[...this.__getStyles('log'), ...params]);
		}
	}

	info(...params)
	{
		if (this.isEnabled('info'))
		{
			console.info(...[...this.__getStyles('info'), ...params]);
		}
	}

	warn(...params)
	{
		if (this.isEnabled('warn'))
		{
			console.warn(...[...this.__getStyles('warn'), ...params]);
		}
	}

	error(...params)
	{
		if (this.isEnabled('error'))
		{
			console.error(...[...this.__getStyles('error'), ...params]);
		}
	}

	trace(...params)
	{
		if (this.isEnabled('trace'))
		{
			console.trace(...params);
		}
	}

	__save()
	{
		if (typeof window.localStorage !== 'undefined')
		{
			try
			{
				let custom = {};
				for (let type in this.#custom)
				{
					if (
						this.#custom.hasOwnProperty(type)
						&& this.#config[type] !== this.#custom[type]
					)
					{
						custom[type] = !!this.#custom[type];
					}
				}

				console.warn(JSON.stringify(custom));

				window.localStorage.setItem('bx-messenger-logger', JSON.stringify(custom));
			}
			catch(e) {}
		}
	}

	__load()
	{
		if (typeof window.localStorage !== 'undefined')
		{
			try
			{
				let custom = window.localStorage.getItem('bx-messenger-logger');
				if (typeof custom === 'string')
				{
					this.#custom = JSON.parse(custom);
					this.#types = {...this.#types, ...this.#custom};
				}
			}
			catch(e) {}
		}
	}

	__getStyles(type = 'all')
	{
		const styles = {
			'desktop': ["%cDESKTOP", "color: white; font-style: italic; background-color: #29619b; padding: 0 6\px"],
			'log': ["%cLOG", "color: #2a323b; font-style: italic; background-color: #ccc; padding: 0 6\px"],
			'info': ["%cINFO", "color: #fff; font-style: italic; background-color: #6b7f96; padding: 0 6\px"],
			'warn': ["%cWARNING", "color: white; font-style: italic; padding: 0 6\px; border: 1px solid #f0a74f"],
			'error': ["%cERROR", "color: white; font-style: italic; padding: 0 6\px; border: 1px solid #8a3232"],
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

	__getRemoveString()
	{
		const styles = this.__getStyles();
		const result = [];
		for (let type in styles)
		{
			if (styles.hasOwnProperty(type))
			{
				result.push(styles[type][1]);
			}
		}
		return result;
	}
}

let logger = new Logger();

export {logger as Logger};