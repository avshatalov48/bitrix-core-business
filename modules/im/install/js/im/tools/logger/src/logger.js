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
	constructor()
	{
		this.enabled = null;
	}

	enable()
	{
		this.enabled = true;

		if (typeof window.localStorage !== 'undefined')
		{
			try
			{
				window.localStorage.setItem('bx-messenger-logger', 'enable');
			}
			catch(e) {}
		}

		return this.enabled;
	}

	disable()
	{
		this.enabled = false;

		if (typeof window.localStorage !== 'undefined')
		{
			try
			{
				window.localStorage.removeItem('bx-messenger-logger');
			}
			catch(e) {}
		}

		return this.enabled;
	}

	isEnabled()
	{
		if (this.enabled === null)
		{
			if (typeof window.localStorage !== 'undefined')
			{
				try
				{
					this.enabled = window.localStorage.getItem('bx-messenger-logger') === 'enable';
				}
				catch(e) {}
			}
		}

		return this.enabled === true;
	}

	log(...params)
	{
		if (this.isEnabled())
		{
			console.log(...params);
		}
	}

	info(...params)
	{
		if (this.isEnabled())
		{
			console.info(...params);
		}
	}

	warn(...params)
	{
		if (this.isEnabled())
		{
			console.warn(...params);
		}
	}

	error(...params)
	{
		if (this.isEnabled())
		{
			console.error(...params);
		}
	}
}

let logger = new Logger();

export {logger as Logger};