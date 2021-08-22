;(function() {
	"use strict";

	BX.namespace("BX.Landing.Client");


	var isEmpty = BX.Landing.Utils.isEmpty;
	var clone = BX.Landing.Utils.clone;


	/**
	 * Implements interface for works with Google Fonts API
	 * @constructor
	 */
	BX.Landing.Client.GoogleFonts = function()
	{
		this.key = "AIzaSyCqOG-HakgzOQh9prxtkuWLA16lnkNZvsg";
		this.patch = "https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=";
		this.fontUrl = "https://fonts.googleapis.com/css2";
		this.response = {};
	};


	/**
	 * Gets instance on BX.Landing.Client.GoogleFonts
	 * @return {BX.Landing.Client.GoogleFonts}
	 */
	BX.Landing.Client.GoogleFonts.getInstance = function()
	{
		return (
			BX.Landing.Client.GoogleFonts.instance ||
			(BX.Landing.Client.GoogleFonts.instance = new BX.Landing.Client.GoogleFonts())
		);
	};


	BX.Landing.Client.GoogleFonts.prototype = {
		/**
		 * Gets list
		 * @return {Promise<T>}
		 */
		getList: function()
		{
			if (!isEmpty(this.response))
			{
				return Promise.resolve(clone(this.response));
			}

			return this.request().then(function(response) {
				this.response = response;
				return clone(this.response);
			}.bind(this));
		},


		/**
		 * Makes request
		 * @returns {Promise}
		 */
		request: function()
		{
			return new Promise(function(resolve) {
				BX.ajax({
					url: this.patch + this.key,
					method: "GET",
					onsuccess: function(res) {
						var response;
						try
						{
							response = JSON.parse(res);
							response = "items" in response ? response.items : response;
						}
						catch (err)
						{
							response = [];
							console.error(err);
						}

						resolve(response);
					}
				});
			}.bind(this));
		},


		makeUrl: function(options)
		{
			return BX.util.add_url_param(this.fontUrl, options);
		}
	};
})();