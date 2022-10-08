import {Loc, Type, Text, Tag} from 'main.core';
import {Popup} from "main.popup";
import {Button} from "ui.buttons";
import {EventEmitter} from "main.core.events";

import './event-type'
import {EventType} from "./event-type";

export class DialogClearing
{
	popup()
	{
		const popup = new Popup(null, null, {
			events: {
				onPopupClose: () => {
					popup.destroy();
				}
			},
			content: this.getContent(),
			maxWidth: 500,
			overlay: true,
			buttons: [
				new Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_2'),
					color: Button.Color.PRIMARY,
					onclick: () => {
						popup.close();
						EventEmitter.emit(EventType.popup.enable, {})
					}
				}),
				new BX.UI.Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_1'),
					color: BX.UI.Button.Color.LINK,
					onclick: () => {
						popup.close();
						EventEmitter.emit(EventType.popup.confirmCancel, {});
					}
				})
			]
		});
		popup.show();
	}

	getContent()
	{
		return Tag.render`
					<div class='catalog-warehouse-master-clear-popup-content'>
						<h3>${Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_3')}</h3>
						<div class="catalog-warehouse-master-clear-popup-text">${Text.encode(Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_4'))}
						<br>${Text.encode(Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_5'))}<div>
					</div>
				`;
	}
}