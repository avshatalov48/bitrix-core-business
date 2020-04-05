;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");


	function createPopup()
	{
		return new BX.PopupWindow("action_dialog_" + (+new Date()), null, {
			titleBar: BX.message("LANDING_ACTION_DIALOG_TITLE"),
			offsetTop: 100,
			overlay: 0.5
		});
	}

	function createConfirmButton(text, callback)
	{
		return new BX.PopupWindowButton({
			id: "action_dialog_confirm",
			className: "popup-window-button-accept",
			text: text || BX.message("LANDING_ACTION_DIALOG_CONFIRM_BUTTON"),
			events: {click: callback}
		});
	}

	function createCancelButton(text, callback)
	{
		return new BX.PopupWindowButtonLink({
			id: "action_dialog_cancel",
			text: text || BX.message("LANDING_ACTION_DIALOG_CANCEL_BUTTON"),
			events: {click: callback}
		});
	}


	/**
	 * Implements interface of confirm / alert dialog
	 * @constructor
	 */
	BX.Landing.UI.Tool.ActionDialog = function()
	{
		this.popup = createPopup();
		this.popup.popupContainer.classList.add("landing-ui-dialog-action");
	};


	/**
	 * Stores instance of BX.Landing.UI.Tool.ActionDialog
	 * @static
	 * @type {BX.Landing.UI.Tool.ActionDialog}
	 */
	BX.Landing.UI.Tool.ActionDialog.instance = null;


	/**
	 * Gets instance of BX.Landing.UI.Tool.ActionDialog
	 * @static
	 * @returns {BX.Landing.UI.Tool.ActionDialog}
	 */
	BX.Landing.UI.Tool.ActionDialog.getInstance = function()
	{
		if (!BX.Landing.UI.Tool.ActionDialog.instance)
		{
			BX.Landing.UI.Tool.ActionDialog.instance = new BX.Landing.UI.Tool.ActionDialog();
		}

		return BX.Landing.UI.Tool.ActionDialog.instance;
	};


	BX.Landing.UI.Tool.ActionDialog.prototype = {
		/**
		 * Shows dialog window
		 * @param {{
		 * 		[type]: ?string
		 * 		[title]: !string,
		 * 		[content]: !string,
		 * 		[confirm]: !string,
		 * 		[cancel]: !string
		 * }} [options]
		 * @returns {Promise}
		 */
		show: function(options)
		{
			options = typeof options === "object" ? options : {};
			options.title = options.title || BX.message("LANDING_ACTION_DIALOG_TITLE");
			options.content = options.content || BX.message("LANDING_ACTION_DIALOG_CONTENT");

			if (BX.type.isNotEmptyString(options.content))
			{
				options.content = BX.create("div", {
					props: {className: "landing-ui-dialog-action-content"},
					html: options.content
				});
			}
			else if (BX.type.isDomNode(options.content))
			{
				options.content.style.display = null;
				options.content = BX.create("div", {
					props: {className: "landing-ui-dialog-action-content"},
					children: [options.content]
				});
			}

			this.popup.setTitleBar(options.title);
			this.popup.setContent(options.content);

			return new Promise(function(resolve, reject) {
				this.popup.setButtons([
					createConfirmButton(options.confirm, function() {
						resolve();
						this.close();
					}.bind(this)),
					options.type !== "alert" ?
						createCancelButton(options.cancel, function() {
							reject();
							this.close();
						}.bind(this)) :
						""
				]);
				this.popup.show();
			}.bind(this));
		},


		/**
		 * Closes dialog window
		 */
		close: function()
		{
			this.popup.close();
		}
	};
})();