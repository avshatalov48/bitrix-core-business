;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var clone = BX.Landing.Utils.clone;

	var availableActions = {
		"Landing::addBlock": BX.message("LANDING_ACTION_ERROR__ADD_BLOCK"),
		"Landing::deleteBlock": BX.message("LANDING_ACTION_ERROR__DELETE_BLOCK"),
		"Landing::upBlock": BX.message("LANDING_ACTION_ERROR__SAVE_CHANGES"),
		"Landing::downBlock": BX.message("LANDING_ACTION_ERROR__SAVE_CHANGES"),
		"Landing::showBlock": BX.message("LANDING_ACTION_ERROR__SAVE_CHANGES"),
		"Landing::hideBlock": BX.message("LANDING_ACTION_ERROR__SAVE_CHANGES"),
		"Block::cloneCard": BX.message("LANDING_ACTION_ERROR__CLONE_CARD"),
		"Block::removeCard": BX.message("LANDING_ACTION_ERROR__DELETE_CARD"),
		"Block::updateStyles": BX.message("LANDING_ACTION_ERROR__SAVE_CHANGES"),
		"Block::updateNodes": BX.message("LANDING_ACTION_ERROR__SAVE_CHANGES"),
		"Site::getList": BX.message("LANDING_ACTION_ERROR__SITE_GET_LIST"),
		"Block::getList": BX.message("LANDING_ACTION_ERROR__BLOCK_GET_LIST"),
		"Utils::uploadFile": BX.message("LANDING_ACTION_ERROR__UPLOAD_FILE"),
		"UNKNOWN_ACTION": BX.message("LANDING_ACTION_ERROR__UNKNOWN_ACTION")
	};


	/**
	 * Implements interface wor works with error manager.
	 * Implements singleton design pattern. !! Don't use it as constructor
	 * use BX.Landing.ErrorManager.getInstance() for gets instance of this module
	 * @constructor
	 */
	BX.Landing.ErrorManager = function()
	{
		this.stack = [];
		this.showTimeout = null;
	};


	/**
	 * Gets manager instance
	 * @return {BX.Landing.ErrorManager}
	 */
	BX.Landing.ErrorManager.getInstance = function()
	{
		if (!top.BX.Landing.ErrorManager.instance)
		{
			top.BX.Landing.ErrorManager.instance = new BX.Landing.ErrorManager();
		}

		return top.BX.Landing.ErrorManager.instance;
	};


	BX.Landing.ErrorManager.prototype = {
		/**
		 * Adds error to collection
		 * @param error
		 * @return {*}
		 */
		add: function(error)
		{
			if (error.type === "error")
			{
				error.action = error.action in availableActions ? error.action : "UNKNOWN_ACTION";

				this.stack.push({
					action: error.action,
					description: availableActions[error.action]
				});

				return this.show();
			}
		},


		/**
		 * Shows all errors from collection
		 */
		show: function()
		{
			clearTimeout(this.showTimeout);

			this.showTimeout = setTimeout(function() {
				var stack = clone(this.stack);
				this.stack = [];
				var text = stack.map(this.createErrorMessage, this).join("");
				BX.Landing.UI.Panel.Alert.getInstance().show("error", text);
			}.bind(this), 100);
		},


		/**
		 * Creates error item html
		 * @param {{descriptions: string}} error
		 * @return {string}
		 */
		createErrorMessage: function(error)
		{
			return "<div class=\"landing-ui-error-item\">"+error.description+"</div>"
		}
	};
})();