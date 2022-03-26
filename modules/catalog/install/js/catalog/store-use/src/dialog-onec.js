import {Loc, Type, Text, Tag} from 'main.core';
import {Popup} from "main.popup";
import {Button} from "ui.buttons";
import {EventEmitter} from "main.core.events";

export class DialogOneC
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
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_CLOSE'),
					color: Button.Color.PRIMARY,
					onclick: () => {
						popup.close();
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
						<h3>${Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_10')}</h3>
						<div class="catalog-warehouse-master-clear-popup-text">${Text.encode(Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_9'))}
					</div>
				`;
	}
}