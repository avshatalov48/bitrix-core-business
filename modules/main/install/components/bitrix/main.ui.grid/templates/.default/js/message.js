;(function() {
	'use strict';

	BX.namespace('BX.Grid');


	/**
	 * Works with message
	 * @param {BX.Main.grid} parent
	 * @param {object} types - Types of message
	 * @constructor
	 */
	BX.Grid.Message = function(parent, types)
	{
		this.parent = null;
		this.types = null;
		this.messages = null;
		this.popup = null;
		this.init(parent, types);
	};

	BX.Grid.Message.prototype = {

		/**
		 * @private
		 * @param {BX.Main.grid} parent
		 * @param {object} types
		 */
		init: function(parent, types)
		{
			this.parent = parent;
			this.types = types;
			this.show();
			BX.addCustomEvent('BX.Main.grid:paramsUpdated', BX.proxy(this.onUpdated, this));
		},

		/**
		 * @private
		 */
		onUpdated: function()
		{
			this.show();
		},


		/**
		 * Gets data for messages
		 * @return {object[]}
		 */
		getData: function()
		{
			return this.parent.arParams.MESSAGES;
		},


		/**
		 * Checks is need show message
		 * @return {boolean}
		 */
		isNeedShow: function()
		{
			return this.getData().length > 0;
		},


		/**
		 * Show message
		 */
		show: function()
		{
			if (this.isNeedShow())
			{
				this.getPopup().setContent(this.getContent());
				this.getPopup().show();
			}
		},


		/**
		 * Gets content for message popup
		 * @return {?HTMLElement}
		 */
		getContent: function()
		{
			var data = this.getData();
			var content = null;

			if (BX.type.isArray(data) && data.length)
			{
				var messagesDecl = {
					block: 'main-grid-messages',
					content: []
				};

				data.forEach(function(message) {
					var messageDecl = {
						block: 'main-grid-message',
						mix: 'main-grid-message-' + message.TYPE.toLowerCase(),
						content: []
					};

					if (BX.type.isNotEmptyString(message.TITLE))
					{
						messageDecl.content.push({
							block: 'main-grid-message-title',
							content: BX.create("div", {html: message.TITLE}).innerText
						});
					}

					if (BX.type.isNotEmptyString(message.TEXT))
					{
						messageDecl.content.push({
							block: 'main-grid-message-text',
							content: BX.create("div", {html: message.TEXT}).innerText
						});
					}

					messagesDecl.content.push(messageDecl);
				});

				content = BX.decl(messagesDecl);
			}

			return content;
		},


		/**
		 * Gets popup of message
		 * @return {BX.PopupWindow}
		 */
		getPopup: function()
		{
			if (this.popup === null)
			{
				this.popup = new BX.PopupWindow(
					this.getPopupId(),
					null,
					{
						autoHide: true,
						overlay: 0.3,
						width: 400,
						contentNoPaddings: true,
						closeByEsc: true,
						buttons: [
							new BX.PopupWindowButton({
								text: this.parent.getParam('CLOSE'),
								className: 'webform-small-button-blue webform-small-button',
								events: {
									click: function()
									{
										this.popupWindow.close();
									}
								}
							})
						]
					}
				);
			}

			return this.popup;
		},


		/**
		 * Gets popup id
		 * @return {string}
		 */
		getPopupId: function()
		{
			return this.parent.getContainerId() + '-main-grid-message';
		}
	};
})();