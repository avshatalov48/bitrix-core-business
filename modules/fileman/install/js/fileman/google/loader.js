;(function()
{
	'use strict';

	BX.namespace('BX.Fileman.Google');

	BX.Fileman.Google.getGoogleLatLng = function(latLng)
	{
		return new google.maps.LatLng(latLng[0], latLng[1]);
	};

	BX.Fileman.Google.getLatLng = function(googleLatLng)
	{
		return [googleLatLng.lat(), googleLatLng.lng()];
	};

	/**
	 * Google API loader. Singleton.
	 */
	BX.Fileman.Google.Loader = function()
	{
		this.apiKey = null;

		this.initProgress = false;
		this.inited = false;
		this.initStack = [];
	};

	BX.Fileman.Google.Loader.prototype.init = function(cb)
	{
		this.apiKey = BX.message('GOOGLE_MAP_API_KEY');

		if(!this.apiKey)
		{
			this.throwError('No Google API key!');
			return;
		}

		if(!this.inited)
		{
			this.initStack.push(cb);
		}

		if(!!window.google && !!window.google.maps)
		{
			this.resolveInit();
		}
		else if(!this.initProgress)
		{
			this.initProgress = true;

			BX.loadScript(location.protocol + '//maps.google.com/maps/api/js?key=' + BX.util.urlencode(this.apiKey) + '&libraries=places&language=' + BX.message('LANGUAGE_ID'), BX.delegate(this.resolveInit, this));
		}

		return this;
	};

	BX.Fileman.Google.Loader.prototype.resolveInit = function()
	{
		this.inited = true;
		this.init = function(cb)
		{
			if(BX.type.isFunction(cb))
			{
				cb.apply(this, []);
			}
		};

		var cb;
		while(cb = this.initStack.shift())
		{
			this.init(cb);
		}
	};

	BX.Fileman.Google.Loader.prototype.throwError = function(message)
	{
		console.error('BX.Fileman.Google.Loader: ' + message ? message.message : 'Google error!');
	};

	/* Singleton initialization*/
	BX.Fileman.Google.Loader = new BX.Fileman.Google.Loader();
})();