import {Dom, Type} from 'main.core';

class SettingsPopup
{
	constructor(config)
	{
		config = Type.isPlainObject(config) ? config : {};
		this.bindElement = config.bindElement;

		this.elements = {
			root: null,
			input: null,
		};

		this.url = config.url || '';

		this.popup = null;

		this.callbacks = {
			onDestroy: BX.type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
		}
	};

	show()
	{
		if (!this.elements.root)
		{
			this.render();
		}
		this.createPopup();
		this.popup.show();
	}

	close()
	{
		if (this.popup)
		{
			this.popup.close();
		}
	}

	createPopup()
	{
		var self = this;

		this.popup = new BX.PopupWindow('bx-call-popup-settings', this.bindElement, {
			targetContainer: document.body,
			lightShadow: true,
			autoHide: true,
			closeByEsc: true,
			closeIcon: true,
			content: this.elements.root,
			bindOptions: {
				position: "top"
			},
			titleBar: BX.message("IM_CALL_SETTINGS_LINK"),
			angle: {position: "bottom", offset: 49},

			contentNoPaddings: true,
			contentColor: "white",
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message("IM_CALL_SETTINGS_COPY"),
					events: {
						click: function (e)
						{
							this.elements.input.select();
							document.execCommand('copy');
							this.close();
						}.bind(this)
					}
				}),
			],

			events: {
				onPopupClose: function () { this.destroy() },
				onPopupDestroy: function ()
				{
					self.popup = null;
					self.callbacks.onDestroy();
				}
			}
		});
	}

	render()
	{
		this.elements.root = Dom.create("div", {
			props: {className: "bx-call-settings-container"},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-dest bx-messenger-popup-newchat-dest-even"},
					children: [
						this.elements.input = Dom.create("input", {
							props: {className: "bx-messenger-input"},
							attrs: {
								value: this.url,
								readonly: "readonly"
							}
						})
					]
				})
			]

		});
	}
}