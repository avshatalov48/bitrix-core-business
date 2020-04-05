;(function()
{
	'use strict';

	BX.namespace('BX.Fileman.Google');

	/**
	 * Google autocomplete class. MUST implement method .search(searchString, callback)
	 *
	 * @constructor
	 */
	BX.Fileman.Google.AutoComplete = function()
	{
		this.autocomplete = null;
		this.position = null;
	};

	BX.Fileman.Google.AutoComplete.prototype.search = function(searchString, cb)
	{
		if(searchString !== '')
		{
			BX.Fileman.Google.Loader.init(BX.delegate(function()
			{
				this.runSearch(searchString, cb);
			}, this));
		}
	};

	BX.Fileman.Google.AutoComplete.prototype.runSearch = function(searchString, cb)
	{
		if(this.autocomplete === null)
		{
			this.autocomplete = new google.maps.places.AutocompleteService();
		}

		var geocodeParam = {
			input: searchString,
			types: ['geocode']
		};

		this.autocomplete.getPlacePredictions(geocodeParam, BX.delegate(this.processResult(cb), this));
	};


	BX.Fileman.Google.AutoComplete.prototype.processResult = function(cb)
	{
		return function(googleSearchResult, status)
		{
			var result = [];

			if(status !== google.maps.places.PlacesServiceStatus.ZERO_RESULTS)
			{
				if(status !== google.maps.places.PlacesServiceStatus.OK)
				{
					this.throwError(status);
				}
				else
				{
					for(var i = 0; i < googleSearchResult.length; i++)
					{
						result.push({
							text: googleSearchResult[i].description,
							place_id: googleSearchResult[i].place_id
						});
					}
				}
			}

			cb(result);
		}
	};

	BX.Fileman.Google.AutoComplete.prototype.throwError = function(message)
	{
		console.error('BX.Fileman.Google.AutoComplete: ' + message ? message.message : 'Google error!');
	};
})();