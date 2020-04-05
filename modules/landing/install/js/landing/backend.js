;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var isPlainObject = BX.Landing.Utils.isPlainObject;

	/**
	 * Implements interface for works with backend.
	 * Implements singleton design pattern not use as Function constructor,
	 * use getInstance method for gets instance of this.
	 * @example BX.Landing.Backend.getInstance()
	 * @constructor
	 */
	BX.Landing.Backend = function()
	{
		this.ajaxController = "/bitrix/tools/landing/ajax.php";
	};


	/**
	 * Stores instance
	 * @type {BX.Landing.Backend}
	 */
	BX.Landing.Backend.instance = null;


	/**
	 * Gets instance of BX.Landing.Backend
	 * @return {BX.Landing.Backend}
	 */
	BX.Landing.Backend.getInstance = function()
	{
		if (!BX.Landing.Backend.instance)
		{
			BX.Landing.Backend.instance = new BX.Landing.Backend();
		}

		return BX.Landing.Backend.instance;
	};


	BX.Landing.Backend.prototype = {
		/**
		 * Executes action request
		 * @param {string} action - Backend action
		 * @param {object} [data]
		 * @param {object} [queryParams = {}]
		 * @param {object} [uploadParams = {}]
		 * @return {Promise.<Object, Object>}
		 */
		action: function(action, data, queryParams, uploadParams)
		{
			uploadParams = BX.type.isPlainObject(uploadParams) ? uploadParams : {};
			queryParams = BX.type.isPlainObject(queryParams) ? queryParams : {};
			BX.Landing.Utils.assign(queryParams, {site_id: this.getSiteId()});
			var requestBody = {};
			requestBody.sessid = BX.bitrix_sessid();
			requestBody.action = action.replace("Landing\\Block", "Block");
			requestBody.data = typeof data === "object" ? data : {};
			requestBody.data.lid = (requestBody.data.lid || BX.Landing.Main.getInstance().id);

			if ("action" in uploadParams)
			{
				requestBody.action = uploadParams.action;
			}

			if ("block" in uploadParams)
			{
				requestBody.data.block = uploadParams.block;
			}

			if ("lid" in uploadParams)
			{
				requestBody.data.lid = uploadParams.lid;
			}

			if ("id" in uploadParams)
			{
				requestBody.data.id = uploadParams.id;
			}

			var url = BX.util.add_url_param(this.ajaxController, BX.util.objectMerge({action: requestBody.action}, queryParams));

			return new Promise(function(resolve, reject) {
				BX.ajax({
					method: "POST",
					dataType: "json",
					url: url,
					data: requestBody,
					onsuccess: function(data) {
						if (!!data && data.type === "error")
						{
							reject(data);
						}
						else
						{
							resolve(data.result);
						}
					},
					onfailure: function(error) {
						reject(error);
					}
				});
			}).catch(function(err) {
				err.action = requestBody.action;
				BX.Landing.ErrorManager.getInstance().add(err);
				return Promise.reject();
			});
		},


		/**
		 * Calls multiple actions
		 * @param {String} action
		 * @param {Object} data
		 * @param {Object} [queryParams]
		 * @return {Promise<any>}
		 */
		batch: function(action, data, queryParams)
		{
			queryParams = BX.type.isPlainObject(queryParams) ? queryParams : {};
			BX.Landing.Utils.assign(queryParams, {site_id: data.siteId || this.getSiteId()});

			var requestBody = {};
			requestBody.sessid = BX.bitrix_sessid();
			requestBody.action = action.replace("Landing\\Block", "Block");
			requestBody.data = {};
			requestBody.batch = typeof data === "object" ? data : {};
			requestBody.data.lid = (requestBody.data.lid || BX.Landing.Main.getInstance().id);
			var url = BX.util.add_url_param(this.ajaxController, BX.util.objectMerge({action: requestBody.action}, queryParams));

			return new Promise(function(resolve, reject) {
				BX.ajax({
					method: "POST",
					dataType: "json",
					url: url,
					data: requestBody,
					onsuccess: function(data) {
						if (!!data && data.type === "error")
						{
							reject(data);
						}
						else
						{
							resolve(data);
						}
					},
					onfailure: function(error) {
						reject(error);
					}
				});
			}).catch(function(err) {
				err.action = requestBody.action;
				BX.Landing.ErrorManager.getInstance().add(err);
				return Promise.reject();
			});
		},


		/**
		 * Gets current site id
		 * @return {Integer}
		 */
		getSiteId: function()
		{
			var siteId;

			try {
				siteId = BX.Landing.Main.getInstance().options.site_id;
			} catch(err) {
				siteId = -1;
			}

			return siteId;
		},


		/**
		 * Uploads image
		 * @param {HTMLFormElement} form
		 * @param {File} file
		 * @param {object} [params]
		 * @param {object} [uploadParams]
		 * @return {Promise<Object, Object>}
		 */
		uploadImage: function(form, file, params, uploadParams)
		{
			uploadParams = isPlainObject(uploadParams) ? uploadParams : {};

			var requestBody = {};
			requestBody.sessid = BX.bitrix_sessid();
			requestBody.action = "action" in uploadParams ? uploadParams.action : "Utils::uploadFile";
			requestBody.picture = file;
			requestBody.data = {};
			requestBody.data.params = typeof params === "object" ? params : {};

			if ("block" in uploadParams)
			{
				requestBody.data.block = uploadParams.block;
			}

			if ("lid" in uploadParams)
			{
				requestBody.data.lid = uploadParams.lid;
			}

			if ("id" in uploadParams)
			{
				requestBody.data.id = uploadParams.id;
			}

			var url = BX.util.add_url_param(this.ajaxController, {
				action: requestBody.action,
				site_id: this.getSiteId()
			});

			return new Promise(function(resolve, reject) {
				BX.ajax.submitAjax(form, {
					url: url,
					method: "POST",
					dataType: "json",
					data: requestBody,
					onsuccess: function(response) {
						resolve(response.result);
					},
					onfailure: function(error) {
						reject(error);
					}
				});
			}.bind(this));
		}
	};
})();