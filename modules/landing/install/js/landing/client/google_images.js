;(function() {
	"use strict";

	BX.namespace("BX.Landing.Client");


	/**
	 * Implements Google API client interface
	 * Implements singleton design pattern
	 * @constructor
	 */
	BX.Landing.Client.Google = function()
	{
		this.host = "https://www.googleapis.com/customsearch/v1";
	};


	/**
	 * Gets client instance
	 * @static
	 * @returns {BX.Landing.Client.Google}
	 */
	BX.Landing.Client.Google.getInstance = function()
	{
		return (
			BX.Landing.Client.Google.instance ||
			(BX.Landing.Client.Google.instance = new BX.Landing.Client.Google())
		);
	};


	BX.Landing.Client.Google.prototype = {
		/**
		 * Makes url for request
		 * @param data
		 * @returns {*}
		 */
		makeUrl: function(data)
		{
			data = typeof data === "object" ? data : {};
			data.key = BX.Landing.Client.Google.key;
			data.searchType = "image";
			data.rights = "cc_attributte";
			data.imgSize = "xxlarge";
			data.cx = "001784514362171586730:5nd_tu8dtbw";
			data.safe = "medium";
			data.imgType = "photo";
			data.filter = "1";
			data.num = "9";

			return BX.util.add_url_param(this.host, data);
		},


		/**
		 * Makes request
		 * @param {object} [data]
		 * @returns {Promise}
		 */
		request: function(data)
		{
			return new Promise(function(resolve, reject) {
				BX.ajax({
					url: this.makeUrl(data),
					method: "GET",
					data: data,
					onsuccess: function(res) {
						var response, error;

						try {
							response = JSON.parse(res);
							response = "items" in response ? response.items : [];
						} catch (err) {
							response = [];
							console.error(err);
							reject(err);
							error = true;
						}

						if (!error)
						{
							resolve(response);
						}
					},
					onfailure: reject
				});
			}.bind(this));
		},


		/**
		 * Gets photos by search string
		 * @param {string} query - Search query
		 * @param {int} [page = 1] - Number of page
		 * @returns {Promise}
		 */
		search: function(query, page)
		{
			page = (BX.type.isNumber(page) ? page : 1) * 10;
			return this.request({start: page, q: encodeURI(query)});
		}
	};
})();

