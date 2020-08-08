/**
 * Bitrix Messenger
 * Base Rest Answer Handler
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

class BaseRestHandler
{
	static create(params = {})
	{
		return new this(params);
	}

	constructor(params = {})
	{
		if (typeof params.controller === 'object' && params.controller)
		{
			this.controller = params.controller;
		}
		if (typeof params.store === 'object' && params.store)
		{
			this.store = params.store;
		}
	}

	execute(command, result, extra = {})
	{
		command = 'handle'+command.split('.').map(element => {
			return element.charAt(0).toUpperCase() + element.slice(1);
		}).join('');

		if (result.error())
		{
			if (typeof this[command+'Error'] === 'function')
			{
				return this[command+'Error'](result.error(), extra);
			}
		}
		else
		{
			if (typeof this[command+'Success'] === 'function')
			{
				return this[command+'Success'](result.data(), extra);
			}
		}

		return typeof this[command] === 'function'? this[command](result, extra): null;
	}
}

export {BaseRestHandler};