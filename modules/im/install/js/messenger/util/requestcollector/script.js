"use strict";

(function(window)
{
	if (!window.BX)
	{
		window.BX = {};
	}
	if (typeof window.BX.Messenger == 'undefined')
	{
		window.BX.Messenger = {};
	}
	else if (typeof window.BX.Messenger.requestCollector != 'undefined')
	{
		return;
	}

	const BX = window.BX;

	class RequestCollector
	{
		constructor()
		{
			this.list = {};
		}

		register(name, xhr)
		{
			this.list[name] = xhr;
			return true;
		}

		unregister(name, abort = false)
		{
			if (this.list[name])
			{
				if (abort)
				{
					this.list[name].abort();
				}
				delete this.list[name];
			}
		}

		get(name)
		{
			return this.list[name]? this.list[name]: null;
		}

		abort(name)
		{
			if (this.list[name])
			{
				this.list[name].abort();
			}
			return true;
		}

		cleaner()
		{
			for (let name in this.list)
			{
				if (this.list.hasOwnProperty(name))
				{
					this.unregister(name, true);
				}
			}
		}
	}

	BX.Messenger.requestCollector = RequestCollector;

})(window);