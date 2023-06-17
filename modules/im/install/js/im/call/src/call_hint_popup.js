import {Dom, Text} from 'main.core'
import {Popup} from 'main.popup'
import Util from './util'

export class CallHint
{
	constructor(options)
	{
		this.popup = null;

		this.title = BX.prop.getString(options, "title", Text.encode(BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING")));
		this.icon = BX.prop.getString(options, "icon", "mic");

		this.bindElement = BX.prop.getElementNode(options, "bindElement", null);
		this.targetContainer = BX.prop.getElementNode(options, "targetContainer", null);
		this.callFolded = BX.prop.getBoolean(options, "callFolded", false);
		this.autoCloseDelay = BX.prop.getInteger(options, "autoCloseDelay", 5000);

		this.buttonsLayout = BX.prop.getString(options, "buttonsLayout", "right");
		this.buttons = BX.prop.getArray(options, "buttons", []);

		this.callbacks = {
			onClose: BX.prop.getFunction(options, "onClose", BX.DoNothing),
		}
		this.autoCloseTimeout = 0;
	};

	show()
	{
		clearTimeout(this.autoCloseTimeout);
		if (this.autoCloseDelay > 0)
		{
			this.autoCloseTimeout = setTimeout(() => this.onAutoClose(), this.autoCloseDelay);
		}

		if (this.popup)
		{
			this.popup.show();
			return;
		}

		this.popup = new Popup({
			bindElement: this.bindElement,
			targetContainer: this.targetContainer,
			content: this.render(),
			padding: 0,
			contentPadding: 14,
			// height: this.getPopupHeight(),
			className: 'bx-call-view-popup-call-hint',
			contentBackground: 'unset',
			maxWidth: 600,

			angle: this.bindElement ? {position: 'bottom', offset: 20} : null,
			events: {
				onClose: () => this.popup.destroy(),
				onDestroy: () => this.popup = null,
			}
		});

		this.popup.show();
	}

	render()
	{
		return Dom.create("div", {
			props: {className: "bx-call-view-popup-call-hint-body layout-" + this.buttonsLayout},
			children: [
				Dom.create("div", {
					props: {className: "bx-call-view-popup-call-hint-icon " + this.icon},
				}),
				Dom.create("div", {
					props: {className: "bx-call-view-popup-call-hint-middle-block"},
					children: [
						Dom.create("div", {
							props: {className: "bx-call-view-popup-call-hint-text"},
							html: this.getPopupMessage()
						}),
						(this.buttonsLayout == "bottom" ? this.renderButtons() : null),
					]
				}),

				(this.buttonsLayout == "right" ? this.renderButtons() : null),
				Dom.create("div", {
					props: {className: "bx-call-view-popup-call-hint-close"},
					events: {
						click:  () =>
						{
							this.callbacks.onClose();
							if (this.popup)
							{
								this.popup.close();
							}
						}
					},
				})
			]
		})
	}

	renderButtons()
	{
		return Dom.create("div", {
			props: {className: "bx-call-view-popup-call-hint-buttons-container"},
			children: this.buttons.map(button => button.render())
		})
	}

	getPopupMessage()
	{
		if (!Util.isDesktop())
		{
			return this.title;
		}
		let hotKeyMessage = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_HOTKEY");
		if (this.callFolded)
		{
			const hotkey = (BX.browser.IsMac() ? 'Shift + &#8984; + A' : 'Ctrl + Shift + A');
			hotKeyMessage = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_FOLDED_CALL_HOTKEY").replace('#HOTKEY#', hotkey);
		}
		hotKeyMessage = '<span class="bx-call-view-popup-call-hint-text-hotkey">' + hotKeyMessage + '</span>';

		return this.title + '<br>' + hotKeyMessage;
	}

	/**
	 * Returns height in pixels for the popup.
	 * The height depends on the hotkey hint (hint appears only in the desktop app).
	 *
	 * @returns {number}
	 */
	getPopupHeight()
	{
		return Util.isDesktop() ? 60 : 54;
	}

	close()
	{
		if (this.popup)
		{
			this.popup.close();
			this.callbacks.onClose();
		}
	}

	onAutoClose()
	{
		this.close();
	}

	destroy()
	{
		if (this.popup)
		{
			this.popup.destroy();
		}

		clearTimeout(this.autoCloseTimeout)
	}
}