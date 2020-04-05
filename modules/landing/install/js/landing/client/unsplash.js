;(function() {
	"use strict";

	BX.namespace("BX.Landing.Client");


	/**
	 * Implements Unsplash API client interface
	 * Implements singleton design pattern
	 * @constructor
	 */
	BX.Landing.Client.Unsplash = function()
	{
		if (BX.Landing.Client.Unsplash.instance)
		{
			return BX.Landing.Client.Unsplash.instance;
		}

		this.host = "https://api.unsplash.com";
		this.clientId = "2f2daddc0c0ea9983e1edd64ef83925faae6d6ab6bf35fe4eb64c2bcfc76fb75";
		this.perPage = 31;
	};


	/**
	 * Gets client instance
	 * @static
	 * @returns {BX.Landing.Client.Unsplash}
	 */
	BX.Landing.Client.Unsplash.getInstance = function()
	{
		return (
			BX.Landing.Client.Unsplash.instance ||
			(BX.Landing.Client.Unsplash.instance = new BX.Landing.Client.Unsplash())
		)
	};


	BX.Landing.Client.Unsplash.prototype = {
		/**
		 * Makes url for request
		 * @param {string} path - /photos, /search/photos ...
		 * @param data
		 * @returns {*}
		 */
		makeUrl: function(path, data)
		{
			data = typeof data === "object" ? data : {};
			data.client_id = this.clientId;

			return BX.util.add_url_param(this.host + path, data);
		},


		/**
		 * Get download link
		 * @param {Object} image
		 * @return {*}
		 */
		download: function(image)
		{
			var url = BX.util.add_url_param(image.links.download_location, {client_id: this.clientId});

			return new Promise(function(resolve) {
				BX.ajax({
					url: url,
					method: "GET",
					headers: [
						{name: 'Authorization' , value: 'Client-ID ' + this.clientId}
					],
					onsuccess: function(res) {
						try {
							var response = JSON.parse(res);
							resolve(response.url);
						} catch (err) {
							resolve("");
							console.error(err);
						}
					}
				});
			}.bind(this));
		},


		/**
		 * Makes request
		 * @param {string} path
		 * @param {object} [data]
		 * @returns {Promise}
		 */
		request: function(path, data)
		{
			return new Promise(function(resolve) {
				BX.ajax({
					url: this.makeUrl(path, data),
					method: "GET",
					data: data,
					headers: [
						{name: 'Authorization' , value: 'Client-ID ' + this.clientId}
					],
					onsuccess: function(res) {
						var response;

						try {
							response = JSON.parse(res);
							response = "results" in response ? response.results : response;
						} catch (err) {
							response = [];
							console.error(err);
						}

						resolve(response);
					}
				});
			}.bind(this));
		},


		/**
		 * Gets popular photos
		 * @param {int} [page = 1] - Number of page
		 * @returns {Promise}
		 */
		popular: function(page)
		{
			page = BX.type.isNumber(page) ? page : 1;
			return this.request("/photos", {order_by: "popular", per_page: this.perPage, page: page});
		},


		/**
		 * Gets photos by
		 * @param {string} query - Search query
		 * @param {int} [page = 1] - Number of page
		 * @returns {Promise}
		 */
		search: function(query, page)
		{
			page = BX.type.isNumber(page) ? page : 1;
			return this.request("/search/photos", {per_page: this.perPage, page: page, query: encodeURI(query)});
		}
	};
})();