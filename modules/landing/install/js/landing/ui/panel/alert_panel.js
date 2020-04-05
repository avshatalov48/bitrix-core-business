;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var append = BX.Landing.Utils.append;


	/**
	 * Implements interface for works with alert panel
	 * use this panel for show error and info messages
	 *
	 * Implements singleton design pattern. Don't use it as constructor
	 * use BX.Landing.UI.Panel.Alert.getInstance() for get instance of module
	 *
	 * @extends {BX.Landing.UI.Panel.BasePanel}
	 * @inheritDoc
	 * @param data
	 * @constructor
	 */
	BX.Landing.UI.Panel.Alert = function(data)
	{
		BX.Landing.UI.Panel.BasePanel.apply(this, arguments);
		addClass(this.layout, "landing-ui-panel-alert");

		this.onCloseClick = this.onCloseClick.bind(this);

		this.text = BX.create("div", {
			props: {className: "landing-ui-panel-alert-text"}
		});
		this.closeButton = BX.create("button", {
			props: {className: "ui-btn ui-btn-link"},
			html: BX.message("LANDING_ALERT_ACTION_CLOSE"),
			events: {click: this.onCloseClick}
		});
		this.action = BX.create("div", {
			props: {className: "landing-ui-panel-alert-action"},
			children: [this.closeButton]
		});

		append(this.text, this.layout);
		append(this.action, this.layout);
		append(this.layout, top.document.body);
	};


	/**
	 * Gets panel instance
	 * @return {BX.Landing.UI.Panel.Alert}
	 */
	BX.Landing.UI.Panel.Alert.getInstance = function()
	{
		if (!top.BX.Landing.UI.Panel.Alert.instance)
		{
			top.BX.Landing.UI.Panel.Alert.instance = new BX.Landing.UI.Panel.Alert();
		}

		return top.BX.Landing.UI.Panel.Alert.instance;
	};


	BX.Landing.UI.Panel.Alert.prototype = {
		constructor: BX.Landing.UI.Panel.Alert,
		__proto__: BX.Landing.UI.Panel.BasePanel.prototype,

		/**
		 * Sows message
		 * @param {string} [type = "alert"] - alert or error
		 * @param {string} text - text of message
		 */
		show: function(type, text)
		{
			var promise = Promise.resolve();

			if (this.isShown())
			{
				promise = this.hide();
			}

			promise.then(function() {
				BX.Landing.UI.Panel.BasePanel.prototype.show.call(this);

				removeClass(this.layout, "landing-ui-alert");
				removeClass(this.layout, "landing-ui-error");
				addClass(this.layout, type === "error" ? "landing-ui-error" : "landing-ui-alert");
				this.text.innerHTML = (text ? text : type) + " ";
				append(this.getSupportLink(), this.text);
			}.bind(this));
		},


		/**
		 * Creates link on support form
		 * @return {Element}
		 */
		getSupportLink: function()
		{
			if (!this.supportLink)
			{
				var url = "https://helpdesk.bitrix24.com/ticket.php";
				var currentLanguage = BX.message("LANGUAGE_ID");

				switch (currentLanguage)
				{
					case "ru":
					case "by":
					case "kz":
						url = "https://helpdesk.bitrix24.ru/ticket.php";
						break;
					case "de":
						url = "https://helpdesk.bitrix24.de/ticket.php";
						break;
					case "br":
						url = "https://helpdesk.bitrix24.com.br/ticket.php";
						break;
					case "es":
						url = "https://helpdesk.bitrix24.es/ticket.php";
						break;

				}

				this.supportLink = BX.create("a", {
					props: {className: "landing-ui-panel-alert-support-link"},
					html: BX.message("LANDING_ALERT_ACTION_SUPPORT_LINK"),
					attrs: {href: url, target: '_blank'}
				});
			}

			return this.supportLink;
		},


		/**
		 * Handles click event on close button
		 */
		onCloseClick: function()
		{
			this.hide();
		}
	};
})();