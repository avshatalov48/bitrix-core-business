'use strict';
import {PopupDialog} from './popupdialog';

export class ConfirmDeleteDialog extends PopupDialog
{
	constructor(params = {})
	{
		super(params);
		this.title = BX.message('EC_DEL_REC_EVENT');
		this.entry = params.entry;
	}

	getContent()
	{
		this.DOM.content = BX.create('DIV');

		this.DOM.content.appendChild(new BX.PopupWindowButton({
			text: BX.message('EC_REC_EV_ONLY_THIS_EVENT'),
			events: {
				click : function() {
					this.entry.deleteThis();
					this.close();
				}.bind(this)
			}
		}).buttonNode);

		this.DOM.content.appendChild(new BX.PopupWindowButton({
			text: BX.message('EC_REC_EV_NEXT'),
			events: {
				click : function() {
					this.entry.deleteNext();
					this.close();
				}.bind(this)
			}
		}).buttonNode);

		this.DOM.content.appendChild(new BX.PopupWindowButton(
			{
				text: BX.message('EC_REC_EV_ALL'),
				events: {
					click : function() {
						this.entry.deleteAll();
						this.close();
					}.bind(this)
				}
			}).buttonNode);

		return this.DOM.content;
	}

	getButtons()
	{
		return [
			new BX.PopupWindowButtonLink({
				text: BX.message('EC_SEC_SLIDER_CANCEL'),
				className: "popup-window-button-link-cancel",
				events: {click : this.close.bind(this)}
			})
		];
	}
}