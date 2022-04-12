/**
 * Bitrix integration with external Vue DevTools
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2022 Bitrix
 */

class DevTools
{
	constructor(params)
	{
		this.host = 'http://localhost';
		this.port = '8098';


		this.script = null;
		this.changeToast = false;

		if (
			!navigator.userAgent.toLowerCase().includes('chrome')
			&& !navigator.userAgent.toLowerCase().includes('firefox')
		)
		{
			this.changeToast = true;
			console.info(
				"Install the Vue Remote Devtools application for a better development experience: https://github.com/vuejs/vue-devtools/blob/master/shells/electron/\n"+
				"For connect to localhost use %cBX.VueDevTools.connect();%c for remote host %cBX.VueDevTools.connect('__devtools_ip_address__');",
				"font-weight: bold", "font-weight: initial", "font-weight: bold", "font-weight: initial",
			);
		}
	}

	connect(address)
	{
		if (this.script)
		{
			document.body.removeChild(this.script);
		}

		if (address)
		{
			this.setUrl(address);
		}

		window.__VUE_DEVTOOLS_HOST__ = this.host;
		window.__VUE_DEVTOOLS_PORT__ = this.port;

		this.script = document.createElement('script');
		if (this.changeToast)
		{
			this.script.addEventListener('load', this.load.bind(this));
		}
		this.script.src = __VUE_DEVTOOLS_HOST__+':'+__VUE_DEVTOOLS_PORT__;

		document.body.appendChild(this.script);

		return true;
	}

	reconnect()
	{
		this.connect();
	}

	setUrl(address = 'localhost')
	{
		if (!address.startsWith('http'))
		{
			address = 'http://'+address;
		}

		let parts = address.split(':');
		if (parts.length > 2)
		{
			this.host = parts.slice(0,2).join(':');
			this.port = parts[2];
		}
		else
		{
			this.host = address;
			this.port = '8098';
		}

		return this;
	}

	load()
	{
		window.__VUE_DEVTOOLS_TOAST__ = new Proxy(window.__VUE_DEVTOOLS_TOAST__,
		{
			apply: (target, thisArg, argumentsList) =>
			{
				if (argumentsList[0].toString().toLowerCase().includes('disconnect'))
				{
					console.info(
						'%cDevTools:%c try to reconnect, if vue-devtools is not running, run and call %cBX.VueDevTools.reconnect();',
						"font-weight: bold", "font-weight: initial", "font-weight: bold",
					);
					setTimeout(() => this.reconnect(), 5000);
				}
				return target.apply(thisArg, argumentsList);
		  	}
		});
	}
}

export {DevTools};