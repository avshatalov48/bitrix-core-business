import {Dom} from 'main.core'
import {Popup} from 'main.popup'

export class HardwareDialog
{
	constructor(params)
	{
		this.bindNode = params.bindNode;
		this.offsetTop = params.offsetTop;
		this.offsetLeft = params.offsetLeft;

		this.popup = null;

		this.callbacks = {
			onDestroy: BX.type.isFunction(params.onDestroy) ? params.onDestroy : BX.DoNothing
		}
	};

	createPopup()
	{
		this.popup = new Popup({
			id: 'bx-messenger-call-access',
			bindNode: this.bindNode,
			targetContainer: document.body,
			lightShadow: true,
			zIndex: 200,
			offsetTop: this.offsetTop,
			offsetLeft: this.offsetLeft,
			cacheable: false,
			events: {
				onPopupDestroy: () =>
				{
					this.popup = null;
					this.callbacks.onDestroy();
				}
			},
			content: this.createLayout()
		});
	};

	createLayout()
	{
		return Dom.create("div", {
			props: {className: 'bx-messenger-call-dialog-allow'}, children: [
				Dom.create("div", {
					props: {className: 'bx-messenger-call-dialog-allow-image-block'}, children: [
						Dom.create("div", {
							props: {className: 'bx-messenger-call-dialog-allow-center'}, children: [
								Dom.create("div", {props: {className: 'bx-messenger-call-dialog-allow-arrow'}})
							]
						}),
						Dom.create("div", {
							props: {className: 'bx-messenger-call-dialog-allow-center'}, children: [
								Dom.create("div", {
									props: {className: 'bx-messenger-call-dialog-allow-button'},
									html: BX.message('IM_M_CALL_ALLOW_BTN')
								})
							]
						})
					]
				}),
				Dom.create("div", {
					props: {className: 'bx-messenger-call-dialog-allow-text'},
					html: BX.message('IM_M_CALL_ALLOW_TEXT')
				})
			]
		});
	};

	show()
	{
		if (!this.popup)
		{
			this.createPopup();
		}

		this.popup.show();
	};

	close()
	{
		if (this.popup)
		{
			this.popup.close();
		}
	}
}