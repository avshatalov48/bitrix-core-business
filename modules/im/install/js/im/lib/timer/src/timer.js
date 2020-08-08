/**
 * Bitrix Messenger
 * Timer manager
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

class Timer
{
	constructor()
	{
		this.list = {};

		this.updateInterval = 1000;

		clearInterval(this.updateIntervalId);
		this.updateIntervalId = setInterval(this.worker.bind(this), this.updateInterval);
	}

	start(name, id = 'default', time = 1, callback = null, callbackParams = {})
	{
		id = id == null? 'default': id;

		time = parseFloat(time);
		if (isNaN(time) || time <= 0)
		{
			return false;
		}

		time = time*1000;

		if (typeof this.list[name] === 'undefined')
		{
			this.list[name] = {};
		}

		this.list[name][id] = {
			'dateStop': new Date().getTime()+time,
			'callback': typeof callback === 'function'? callback: function() {},
			'callbackParams': callbackParams
		};

		return true;
	}

	has(name, id = 'default')
	{
		id = id == null? 'default': id;
		if (id.toString().length <= 0 || typeof this.list[name] === 'undefined')
		{
			return false;
		}

		return !!this.list[name][id];
	}

	stop(name, id = 'default', skipCallback)
	{
		id = id == null? 'default': id;

		if (id.toString().length <= 0 || typeof this.list[name] === 'undefined')
		{
			return false;
		}

		if (!this.list[name][id])
		{
			return true;
		}

		if (skipCallback !== true)
		{
			this.list[name][id]['callback'](id, this.list[name][id]['callbackParams']);
		}

		delete this.list[name][id];

		return true;
	}

	stopAll(skipCallback)
	{
		for (let name in this.list)
		{
			if (this.list.hasOwnProperty(name))
			{
				for (let id in this.list[name])
				{
					if(this.list[name].hasOwnProperty(id))
					{
						this.stop(name, id, skipCallback);
					}
				}
			}
		}
		return true;
	}

	worker()
	{
		for (let name in this.list)
		{
			if (!this.list.hasOwnProperty(name))
			{
				continue;
			}
			for (let id in this.list[name])
			{
				if(!this.list[name].hasOwnProperty(id) || this.list[name][id]['dateStop'] > new Date())
				{
					continue;
				}
				this.stop(name, id);
			}
		}
		return true;
	}

	clean()
	{
		clearInterval(this.updateIntervalId);
		this.stopAll(true);

		return true;
	}
}

export {Timer};

