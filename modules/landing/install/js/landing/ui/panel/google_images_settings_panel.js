;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * Implements interface for works with google images settings
	 * @extends {BX.Landing.UI.Panel.Content}
	 * @constructor
	 */
	BX.Landing.UI.Panel.GoogleImagesSettings = function()
	{
		BX.Landing.UI.Panel.Content.apply(this, ["google_images_settings", {
			title: BX.message("LANDING_GOOGLE_IMAGES_KEY_PANEL_TITLE"),
			footer: [
				new BX.Landing.UI.Button.BaseButton("save_block_content", {
					text: BX.message("BLOCK_SAVE"),
					onClick: this.onSaveClick.bind(this),
					className: "landing-ui-button-content-save",
					attrs: {title: BX.message("LANDING_TITLE_OF_SLIDER_SAVE")}
				}),
				new BX.Landing.UI.Button.BaseButton("cancel_block_content", {
					text: BX.message("BLOCK_CANCEL"),
					onClick: this.onCancelClick.bind(this),
					className: "landing-ui-button-content-cancel",
					attrs: {title: BX.message("LANDING_TITLE_OF_SLIDER_CANCEL")}
				})
			]
		}]);

		this.layout.style.width = "496px";
		this.layout.style.zIndex = "600";
		this.overlay.style.zIndex = "599";

		this.settingsForm = new BX.Landing.UI.Form.BaseForm();
		this.keyField = new BX.Landing.UI.Field.Text({
			title: BX.message("LANDING_GOOGLE_IMAGES_KEY_FIELD_TITLE"),
			textOnly: true,
			description: BX.message("LANDING_GOOGLE_IMAGES_GET_KEY_GUIDE"),
			content: BX.Landing.Client.Google.key
		});
		this.settingsForm.addField(this.keyField);
		this.appendForm(this.settingsForm);

		document.body.appendChild(this.layout);
	};


	BX.Landing.UI.Panel.GoogleImagesSettings.getInstance = function()
	{
		return (
			BX.Landing.UI.Panel.GoogleImagesSettings.instance ||
			(BX.Landing.UI.Panel.GoogleImagesSettings.instance = new BX.Landing.UI.Panel.GoogleImagesSettings())
		);
	};


	BX.Landing.UI.Panel.GoogleImagesSettings.prototype = {
		constructor: BX.Landing.UI.Panel.GoogleImagesSettings,
		__proto__: BX.Landing.UI.Panel.Content.prototype,
		superclass: BX.Landing.UI.Panel.Content.prototype,

		show: function()
		{
			this.superclass.show.call(this);
			return new Promise(function(resolve) {
				this.resolver = resolve;
			}.bind(this));
		},

		onSaveClick: function()
		{
			var key = this.keyField.getValue();

			this.resolver(key);
			BX.Landing.Backend.getInstance()
				.action("Utils::saveSettings", {
					settings: {googleImages: key}
				});

			BX.Landing.Client.Google.key = key;

			this.hide();
		},

		onCancelClick: function()
		{
			this.hide();
		}
	};
})();