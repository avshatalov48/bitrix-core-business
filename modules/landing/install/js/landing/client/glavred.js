;(function() {
	"use strict";

	BX.namespace("BX.Landing.Client");

	var createSelectionRange = BX.Landing.Utils.createSelectionRange;
	var wrapSelection = BX.Landing.Utils.wrapSelection;

	var STATUS_OK = "ok";
	var STATUS_FAIL = "fail";
	var STATUS_PENDING = "pending";


	/**
	 * Implements Glavred API client interface
	 * Implements singleton design pattern
	 * @constructor
	 */
	BX.Landing.Client.Glavred = function()
	{
		this.markupMatcher = new RegExp("(<span[^>]*class=\"glvrd-underline\"[^>]*>)(.+?)(<\\/span>)", "g");
		this.serviceStatus = STATUS_PENDING;
		this.proofStatus = STATUS_OK;
	};


	/**
	 * Gets client instance
	 * @static
	 * @returns {BX.Landing.Client.Glavred}
	 */
	BX.Landing.Client.Glavred.getInstance = function()
	{
		return (
			BX.Landing.Client.Glavred.instance ||
			(BX.Landing.Client.Glavred.instance = new BX.Landing.Client.Glavred())
		);
	};


	BX.Landing.Client.Glavred.prototype = {
		/**
		 * Checks service API status
		 * @return {Promise}
		 */
		getStatus: function()
		{
			return new Promise(function(resolve, reject) {
				if (this.serviceStatus !== STATUS_OK)
				{
					top.glvrd.getStatus(function(response) {
						if(response.status === STATUS_OK) {
							resolve();
							this.serviceStatus = STATUS_OK;
						} else {
							reject(response.message);
							this.serviceStatus = STATUS_FAIL;
						}
					}.bind(this));
				}
				else
				{
					resolve();
				}
			}.bind(this));
		},


		/**
		 * Proofreads node text
		 * @param {HTMLElement} node
		 * @return {Promise<Number>} - Text score
		 */
		proofread: function(node)
		{
			return this.getStatus()
				.then(function() {
					return new Promise(function(resolve) {
						top.glvrd.proofread(node.textContent, resolve);
					})
				})
				.then(function(response) {
					this.removeMarkup(node);
					this.addMarkup(node, response);
					return this.getScore(node.textContent, response.fragments);
				}.bind(this));
		},


		/**
		 * Gets text score
		 * @param {string} text
		 * @param {object[]} fragments - Fragments from response
		 * @return {Promise<Number>}
		 */
		getScore: function(text, fragments)
		{
			return this.getStatus().then(function() {
				return top.glvrd.getScore({text: text, fragments: fragments});
			});
		},


		/**
		 * Adds markup
		 * @param {HTMLElement} node
		 * @param {object} response - glvrd.proofread response
		 */
		addMarkup: function(node, response)
		{
			response.fragments.forEach(function(fragment) {
				var range = createSelectionRange(node, fragment.start, fragment.end);
				var element = BX.create("span", {props: {className: "glvrd-underline"}});
				wrapSelection(element, range);
				element.addEventListener("mouseenter", function() {
					BX.Landing.UI.Tool.Suggest.getInstance().show(this, fragment.hint);
				});
			}.bind(this));
		},


		/**
		 * Removes all Glavred markup
		 */
		removeMarkup: function(node)
		{
			node.innerHTML = node.innerHTML.replace(this.markupMatcher, "$2");
			node.innerHTML = node.innerHTML.replace(this.markupMatcher, "$2");
		}
	};
})();