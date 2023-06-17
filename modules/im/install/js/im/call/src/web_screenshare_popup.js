import {Dom, Type} from 'main.core'
import {Popup} from 'main.popup'

export class WebScreenSharePopup
{
	constructor(options)
	{
		this.popup = null;
		this.options = options || {};

		this.callbacks = {
			onClose: Type.isFunction(this.options.onClose) ? this.options.onClose : BX.DoNothing,
			onStopSharingClick: Type.isFunction(this.options.onStopSharingClick) ? this.options.onStopSharingClick : BX.DoNothing,
		}
	};

	show()
	{
		if (this.popup)
		{
			this.popup.show();
			return;
		}

		var popupWidth = 400;

		this.popup = new Popup({
			bindElement: this.options.bindElement,
			targetContainer: this.options.targetContainer,
			content: this.render(),
			padding: 0,
			contentPadding: 0,
			height: 38,
			width: popupWidth,
			offsetTop: -15,
			offsetLeft: (this.options.bindElement.offsetWidth / 2) - (popupWidth / 2) + (this.options.bindElement.offsetWidth / 2),
			className: 'bx-call-view-popup-web-screenshare',
			contentBackground: 'unset',
			angle: {position: 'bottom', offset: (popupWidth / 2) - 10},
			cacheable: false,
			events: {
				onDestroy: () => this.popup = null
			}
		});

		this.popup.show();
	}

	render()
	{
		return Dom.create("div", {
			props: {className: "bx-call-view-popup-web-screenshare-body"},
			children: [
				Dom.create("div", {
					props: {className: "bx-call-view-popup-web-screenshare-left"},
					children: [
						Dom.create("div", {
							props: {className: "bx-call-view-popup-web-screenshare-icon-screen"},
						}),
						Dom.create("div", {
							props: {className: "bx-call-view-popup-web-screenshare-text"},
							text: BX.message("IM_CALL_WEB_SCREENSHARE_STATUS")
						}),
					]
				}),
				Dom.create("div", {
					props: {className: "bx-call-view-popup-web-screenshare-right"},
					children: [
						Dom.create("div", {
							props: {className: "bx-call-view-popup-web-screenshare-stop ui-btn ui-btn-primary ui-btn-xs ui-btn-round ui-btn-no-caps ui-btn-icon-stop"},
							text: BX.message("IM_CALL_WEB_SCREENSHARE_STOP"),
							events: {
								click: () => this.callbacks.onStopSharingClick()
							}
						}),
						Dom.create("div", {
							props: {className: "bx-call-view-popup-web-screenshare-close"},
							events: {
								click: () =>
								{
									this.popup.close();
									this.callbacks.onClose();
								}
							},
						})
					]
				}),
			]
		})
	}

	close()
	{
		if (this.popup)
		{
			this.popup.close();
		}
	}

	destroy()
	{
		if (this.popup)
		{
			this.popup.destroy();
		}
	}
}