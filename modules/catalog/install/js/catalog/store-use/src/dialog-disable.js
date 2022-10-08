import {Loc, Type, Text, Tag, ajax} from 'main.core';
import 'ui.design-tokens';
import {Popup} from "main.popup";
import {Button} from "ui.buttons";
import {EventEmitter} from "main.core.events";

import './event-type'
import {EventType} from "./event-type";

import './store-use.css';

export class DialogDisable
{
	popup()
	{
		this.disablePopup();
	}

	disablePopup()
	{
		const popup = new Popup(null, null, {
			events: {
				onPopupClose: () => {
					popup.destroy();
				}
			},
			content: this.getDisablePopupContent(),
			maxWidth: 500,
			overlay: true,
			buttons: [
				new Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_6'),
					color: Button.Color.PRIMARY,
					onclick: () => {
						popup.close();
						EventEmitter.emit(EventType.popup.disable, {});
					}
				}),
				new BX.UI.Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_1'),
					color: BX.UI.Button.Color.LINK,
					onclick: () => {
						popup.close();
						EventEmitter.emit(EventType.popup.disableCancel, {});
					}
				})
			]
		});
		popup.show();
	}

	getDisablePopupContent()
	{
		return Tag.render`
					<div class='catalog-warehouse-master-clear-popup-content'>
						<h3>${Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_TITLE')}</h3>
						<div class="catalog-warehouse-master-clear-popup-text">${Text.encode(Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_7'))}
						<br>${Text.encode(Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_8'))}<div>
					</div>
				`;
	}
}
