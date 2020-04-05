;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isString = BX.Landing.Utils.isString;
	var addQueryParams = BX.Landing.Utils.addQueryParams;

	/**
	 * Implements interface for works with backend.
	 * Implements singleton design pattern not use as Function constructor,
	 * use getInstance method for gets instance of this.
	 * @example BX.Landing.Backend.getInstance()
	 * @constructor
	 */
	BX.Landing.Backend = function()
	{

		this.ajaxController = addQueryParams("/bitrix/tools/landing/ajax.php", {
			site: BX.message["SITE_ID"] ? BX.message("SITE_ID") : undefined
		});
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
			})
			.then(function(response) {
				if (
					requestBody.action === "Block::updateNodes"
					|| requestBody.action === "Block::removeCard"
					|| requestBody.action === "Block::cloneCard"
					|| requestBody.action === "Block::addCard"
					|| requestBody.action === "Block::updateStyles"
				)
				{
					BX.Landing.UI.Panel.StatusPanel.getInstance().update();
				}
				return response;
			})
			.catch(function(err) {
				if (requestBody.action !== "Block::getById")
				{
					err = isString(err) ? {type: "error"} : err;
					err.action = requestBody.action;
					BX.Landing.ErrorManager.getInstance().add(err);
				}

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
			})
			.then(function(response) {
				BX.Landing.UI.Panel.StatusPanel.getInstance().update();
				return response;
			})
			.catch(function(err) {
				if (requestBody.action !== "Block::getById")
				{
					err = isString(err) ? {type: "error"} : err;
					err.action = requestBody.action;
					BX.Landing.ErrorManager.getInstance().add(err);
				}

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

		upload: function(file, uploadParams)
		{
			var formData = new FormData();
			var params = uploadParams || {};
			var action = "Block::uploadFile";

			formData.append("sessid", BX.bitrix_sessid());
			formData.append("action", "Block::uploadFile");
			formData.append("picture", file, file.name);

			if ("block" in params)
			{
				formData.append("data[block]", params.block);
			}

			if ("lid" in params)
			{
				action = "Landing::uploadFile";
				formData.append("data[lid]", params.lid);
				formData.set("action", action);
			}

			if ("id" in params)
			{
				action = "Site::uploadFile";
				formData.append("data[id]", params.id);
				formData.set("action", action);
			}

			var url = BX.util.add_url_param(this.ajaxController, {
				action: action,
				site_id: this.getSiteId()
			});

			if (params.context) {
				url = BX.util.add_url_param(url, {
					context: params.context
				});
			}

			return new Promise(function(resolve, reject) {
				var xhr = BX.ajax({
					url: url,
					method: "POST",
					dataType: "json",
					data: formData,
					start: false,
					preparePost: false,
					onsuccess: function(response) {
						if (!!response && response.type === "error")
						{
							reject(response);
						}
						else
						{
							resolve(response.result);
						}
					},
					onfailure: function(error) {
						reject(error);
					}
				});

				xhr.send(formData);
			})
			.catch(function(err) {
				err = isString(err) ? {type: "error"} : err;
				err.action = "Block::uploadFile";
				BX.Landing.ErrorManager.getInstance().add(err);
				return Promise.reject(err);
			});
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
			if (!form)
			{
				form = document.createElement('form');
			}

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
						if (!!response && response.type === "error")
						{
							reject(response);
						}
						else
						{
							resolve(response.result);
						}
					},
					onfailure: function(error) {
						reject(error);
					}
				});
			})
			.catch(function(err) {
				err = isString(err) ? {type: "error"} : err;
				err.action = requestBody.action;
				BX.Landing.ErrorManager.getInstance().add(err);
				return Promise.reject();
			});
		}
	};
})();