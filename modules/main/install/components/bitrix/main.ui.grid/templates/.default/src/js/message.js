(function() {
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
		init(parent, types)
		{
			this.parent = parent;
			this.types = types;
			this.show();
			BX.addCustomEvent('BX.Main.grid:paramsUpdated', BX.proxy(this.onUpdated, this));
		},

		/**
		 * @private
		 */
		onUpdated()
		{
			this.show();
		},

		/**
		 * Gets data for messages
		 * @return {object[]}
		 */
		getData()
		{
			return this.parent.arParams.MESSAGES;
		},

		/**
		 * Checks is need show message
		 * @return {boolean}
		 */
		isNeedShow()
		{
			return this.getData().length > 0;
		},

		/**
		 * Show message
		 */
		show()
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
		getContent()
		{
			const data = this.getData();
			let content = null;

			if (BX.type.isArray(data) && data.length > 0)
			{
				const messagesDecl = {
					block: 'main-grid-messages',
					content: [],
				};

				data.forEach((message) => {
					const messageDecl = {
						block: 'main-grid-message',
						mix: `main-grid-message-${message.TYPE.toLowerCase()}`,
						content: [],
					};

					if (BX.type.isNotEmptyString(message.TITLE))
					{
						messageDecl.content.push({
							block: 'main-grid-message-title',
							content: BX.create('div', { html: message.TITLE }).innerText,
						});
					}

					if (BX.type.isNotEmptyString(message.TEXT))
					{
						messageDecl.content.push({
							block: 'main-grid-message-text',
							content: BX.create('div', { html: message.TEXT }).innerText,
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
		getPopup()
		{
			if (this.popup === null)
			{
				this.popup = new BX.PopupWindow(
					this.getPopupId(),
					null,
					{
						autoHide: true,
						overlay: 0.3,
						minWidth: 400,
						maxWidth: 800,
						contentNoPaddings: true,
						closeByEsc: true,
						buttons: [
							new BX.PopupWindowButton({
								text: this.parent.getParam('CLOSE'),
								className: 'webform-small-button-blue webform-small-button',
								events: {
									click()
									{
										this.popupWindow.close();
									},
								},
							}),
						],
					},
				);
			}

			return this.popup;
		},

		/**
		 * Gets popup id
		 * @return {string}
		 */
		getPopupId()
		{
			return `${this.parent.getContainerId()}-main-grid-message`;
		},
	};
})();
