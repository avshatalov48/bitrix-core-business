/**
 * Bitrix Messenger
 * LocalStorage manager
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

class LocalStorage
{
	constructor()
	{
		this.enabled = null;
		this.expireList = null;
		this.expireInterval = null;
	}

	isEnabled()
	{
		if (this.enabled !== null)
		{
			return this.enabled;
		}

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

		if (this.enabled && !this.expireInterval)
		{
			try
			{
				let expireList = window.localStorage.getItem('bx-messenger-localstorage-expire');
				if (expireList)
				{
					this.expireList = JSON.parse(expireList);
				}
			}
			catch(e)
			{
			}

			clearInterval(this.expireInterval);
			this.expireInterval = setInterval(this._checkExpireInterval.bind(this), 60000);
		}

		return this.enabled;
	}

	set(siteId, userId, name, value, ttl = 0)
	{
		if (!this.isEnabled())
		{
			return false;
		}

		let expire = null;
		if (ttl)
		{
			expire = new Date(((new Date()).getTime() + ttl * 1000));
		}

		let storeValue = JSON.stringify({value, expire});
		if (window.localStorage.getItem(this._getKey(siteId, userId, name)) !== storeValue)
		{
			window.localStorage.setItem(this._getKey(siteId, userId, name), storeValue);
		}


		if (ttl)
		{
			if (!this.expireList)
			{
				this.expireList = {};
			}
			this.expireList[this._getKey(siteId, userId, name)] = expire;
			window.localStorage.setItem('bx-messenger-localstorage-expire', JSON.stringify(this.expireList));
		}

		return true;
	}

	get(siteId, userId, name, defaultValue)
	{
		if (!this.isEnabled())
		{
			return typeof defaultValue !== 'undefined'? defaultValue: null;
		}

		let result = window.localStorage.getItem(this._getKey(siteId, userId, name));
		if (result === null)
		{
			return typeof defaultValue !== 'undefined'? defaultValue: null;
		}

		try
		{
			result = JSON.parse(result);
			if (result && typeof result.value !== 'undefined')
			{
				if (
					!result.expire
					|| new Date(result.expire) > new Date()
				)
				{
					result = result.value;
				}
				else
				{
					window.localStorage.removeItem(this._getKey(siteId, userId, name));

					if (this.expireList)
					{
						delete this.expireList[this._getKey(siteId, userId, name)];
					}

					return typeof defaultValue !== 'undefined'? defaultValue: null;
				}
			}
			else
			{
				return typeof defaultValue !== 'undefined'? defaultValue: null;
			}
		}
		catch(e)
		{
			return typeof defaultValue !== 'undefined'? defaultValue: null;
		}

		return result;
	}

	remove(siteId, userId, name)
	{
		if (!this.isEnabled())
		{
			return false;
		}

		if (this.expireList)
		{
			delete this.expireList[this._getKey(siteId, userId, name)];
		}

		return window.localStorage.removeItem(this._getKey(siteId, userId, name));
	}

	_getKey(siteId, userId, name)
	{
		return 'bx-messenger-' + siteId + '-' + userId + '-' + name;
	}

	_checkExpireInterval()
	{
		if (!this.expireList)
			return true;

		let currentTime = new Date();

		let count = 0;
		for (let name in this.expireList)
		{
			if (!this.expireList.hasOwnProperty(name))
			{
				continue;
			}

			if (new Date(this.expireList[name]) <= currentTime)
			{
				window.localStorage.removeItem(name);
				delete this.expireList[name];
			}
			else
			{
				count++;
			}
		}

		if (count)
		{
			window.localStorage.setItem('bx-messenger-localstorage-expire', JSON.stringify(this.expireList));
		}
		else
		{
			this.expireList = null;
			window.localStorage.removeItem('bx-messenger-localstorage-expire');
		}

		return true;
	}
}

let localStorage = new LocalStorage();

export {localStorage as LocalStorage};