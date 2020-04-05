;(function(){
	'use strict';

	BX.namespace('BX.Fileman.Google');

	/**
	 * Google geocoder class. MUST implement method .search(searchString, callback)
	 *
	 * @constructor
	 */
	BX.Fileman.Google.GeoCoder = function()
	{
		this.geocoder = null;
	};

	BX.Fileman.Google.GeoCoder.prototype.search = function(searchString, cb)
	{
		if(searchString !== '')
		{
			BX.Fileman.Google.Loader.init(BX.delegate(function()
			{
				this.runSearch(searchString, cb);
			}, this));
		}
	};

	BX.Fileman.Google.GeoCoder.prototype.runSearch = function(searchString, cb)
	{
		if(this.geocoder === null)
		{
			this.geocoder = new google.maps.Geocoder();
		}

		var geocodeParam = {
			language: BX.message('LANGUAGE_ID')
		};

		if(BX.type.isArray(searchString))
		{
			geocodeParam.latLng = BX.Fileman.Google.getGoogleLatLng(searchString);
		}
		else
		{
			geocodeParam.placeId = searchString;
		}

		this.geocoder.geocode(geocodeParam, BX.delegate(this.processResult(cb), this));
	};

	BX.Fileman.Google.GeoCoder.prototype.processResult = function(cb)
	{
		return function(googleSearchResult, status)
		{
			var result = [];

			if(status !== google.maps.GeocoderStatus.OK && status !== google.maps.GeocoderStatus.ZERO_RESULTS)
			{
				this.throwError(status);
			}
			else
			{
				for(var i = 0; i < googleSearchResult.length; i++)
				{
					result.push({
						text: googleSearchResult[i].formatted_address,
						components: googleSearchResult[i].address_components,
						coords: [googleSearchResult[i].geometry.location.lat(), googleSearchResult[i].geometry.location.lng()],
						viewport: [
							[googleSearchResult[i].geometry.viewport.getNorthEast().lat(), googleSearchResult[i].geometry.viewport.getNorthEast().lng()],
							[googleSearchResult[i].geometry.viewport.getSouthWest().lat(), googleSearchResult[i].geometry.viewport.getSouthWest().lng()]
						]
					});
				}
			}

			cb(result);
		}
	};

	BX.Fileman.Google.GeoCoder.prototype.throwError = function(message)
	{
		console.error('BX.Fileman.Google.GeoCoder: ' + message ? message.message : 'Google error!');
	};
})();