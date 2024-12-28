;(function(window) {
	if (window.BX.localStorage)
	{
		return;
	}

	const BX = window.BX;
	let localStorageInstance = null;
	let _prefix = null;

	BX.localStorage = function() {
		BX.bind(window, 'storage', BX.proxy(this._onchange, this));
		setInterval(BX.delegate(this._clear, this), 5000);
	};

	/* localStorage public interface */

	BX.localStorage.checkBrowser = function() {
		return true;
	};

	BX.localStorage.set = function(key, value, ttl) {
		return BX.localStorage.instance().set(key, value, ttl);
	};

	BX.localStorage.get = function(key) {
		return BX.localStorage.instance().get(key);
	};

	BX.localStorage.remove = function(key) {
		return BX.localStorage.instance().remove(key);
	};

	BX.localStorage.instance = function() {
		if (!localStorageInstance)
		{
			localStorageInstance = new BX.localStorage();
		}

		return localStorageInstance;
	};

	/* localStorage prototype */
	BX.localStorage.prototype.prefix = function() {
		if (!_prefix)
		{
			_prefix = 'bx' + BX.message('USER_ID') + '-' + (BX.message.SITE_ID ? BX.message('SITE_ID') : 'admin') + '-';
		}

		return _prefix;
	};

	BX.localStorage.prototype._onchange = function(e) {
		e = e || window.event;

		if (!e.key)
		{
			return;
		}

		if (!!e.key && e.key.substring(0, this.prefix().length) == this.prefix())
		{
			var d = {
				key: e.key.substring(this.prefix().length, e.key.length),
				value: !!e.newValue ? this._decode(e.newValue.substring(11, e.newValue.length)) : null,
				oldValue: !!e.oldValue ? this._decode(e.oldValue.substring(11, e.oldValue.length)) : null,
			};

			switch (d.key)
			{
				case 'BXGCE': // BX Global Custom Event
					if (d.value)
					{
						BX.onCustomEvent(d.value.e, d.value.p);
					}
					break;
				default:
					// normal event handlers
					if (e.newValue)
					{
						BX.onCustomEvent(window, 'onLocalStorageSet', [d]);
					}
					if (e.oldValue && !e.newValue)
					{
						BX.onCustomEvent(window, 'onLocalStorageRemove', [d]);
					}

					BX.onCustomEvent(window, 'onLocalStorageChange', [d]);
					break;
			}
		}
	};

	BX.localStorage.prototype._clear = function() {
		var curDate = +new Date(),
			key,
			i;

		for (i = 0; i < localStorage.length; i++)
		{
			key = localStorage.key(i);
			if (key.substring(0, 2) == 'bx')
			{
				var ttl = localStorage.getItem(key).split(':', 1) * 1000;
				if (curDate >= ttl)
				{
					localStorage.removeItem(key);
				}
			}
		}
	};

	BX.localStorage.prototype._encode = function(value) {
		if (typeof (value) == 'object')
		{
			value = JSON.stringify(value);
		}
		else
		{
			value = value.toString();
		}

		return value;
	};

	BX.localStorage.prototype._decode = function(value) {
		var answer = null;
		if (!!value)
		{
			try
			{
				answer = JSON.parse(value);
			}
			catch (e)
			{
				answer = value;
			}
		}

		return answer;
	};

	BX.localStorage.prototype._trigger_error = function(e, key, value, ttl) {
		BX.onCustomEvent(this, 'onLocalStorageError', [e, { key, value, ttl }]);
	};

	BX.localStorage.prototype.set = function(key, value, ttl) {
		if (!ttl || ttl <= 0)
		{
			ttl = 60;
		}

		if (key == undefined || key == null || value == undefined)
		{
			return false;
		}

		try
		{
			localStorage.setItem(
				this.prefix() + key,
				(Math.round((+new Date()) / 1000) + ttl) + ':' + this._encode(value),
			);
		}
		catch (e)
		{
			this._trigger_error(e, key, value, ttl);
		}
	};

	BX.localStorage.prototype.get = function(key) {
		var storageAnswer = localStorage.getItem(this.prefix() + key);

		if (storageAnswer)
		{
			var ttl = storageAnswer.split(':', 1) * 1000;
			if ((+new Date()) <= ttl)
			{
				storageAnswer = storageAnswer.substring(11, storageAnswer.length);
				return this._decode(storageAnswer);
			}
		}

		return null;
	};

	BX.localStorage.prototype.remove = function(key) {
		localStorage.removeItem(this.prefix() + key);
	};

	/* additional functions */

	BX.onGlobalCustomEvent = function(eventName, arEventParams, bSkipSelf) {
		if (!!BX.localStorage.checkBrowser())
		{
			BX.localStorage.set('BXGCE', { e: eventName, p: arEventParams }, 1);
		}

		if (!bSkipSelf)
		{
			BX.onCustomEvent(eventName, arEventParams);
		}
	};

	BX.localStorage.instance();
})(window);
