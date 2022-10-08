import {Loc, Tag, ajax} from 'main.core';
import {Popup} from "main.popup";
import {Button} from "ui.buttons";
import {EventEmitter} from "main.core.events";

import './event-type'
import {EventType} from "./event-type";
import {DialogClearing} from "catalog.store-use";

export class DialogEnable
{
	static QUANTITY_INCONSISTENCY_EXISTS = 'QUANTITY_INCONSISTENCY_EXISTS';
	static CONDUCTED_DOCUMENTS_EXIST = 'CONDUCTED_DOCUMENTS_EXIST';

	popup()
	{
		ajax.runAction(
			'catalog.config.checkEnablingConditions',
			{}
		).then(response => {
			const result = response.data;

			if (
				result.includes(DialogEnable.QUANTITY_INCONSISTENCY_EXISTS)
				&& result.includes(DialogEnable.CONDUCTED_DOCUMENTS_EXIST)
			)
			{
				this.quantityInconsistencyPopup();
			}
			else if (result.includes(DialogEnable.QUANTITY_INCONSISTENCY_EXISTS))
			{
				(new DialogClearing()).popup();
			}
			else if (result.includes(DialogEnable.CONDUCTED_DOCUMENTS_EXIST))
			{
				this.conductedDocumentsPopup();
			}
			else
			{
				EventEmitter.emit(EventType.popup.enable, {});
			}
		});
	}

	quantityInconsistencyPopup()
	{
		const popup = new Popup({
			events: {
				onPopupClose: () => {
					popup.destroy();
				}
			},
			content: this.#getPopupContent(Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_CLEAR_CONFIRM')),
			maxWidth: 500,
			overlay: true,
			closeIcon: true,
			closeByEsc: true,
			buttons: [
				new Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_WITH_RESET'),
					color: Button.Color.PRIMARY,
					onclick: () => {
						popup.close();
						EventEmitter.emit(EventType.popup.enableWithResetDocuments, {});
					}
				}),
				new BX.UI.Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_EASY'),
					color: BX.UI.Button.Color.LINK,
					onclick: () => {
						popup.close();
						EventEmitter.emit(EventType.popup.enableWithoutReset, {});
					}
				}),
			]
		});
		popup.show();
	}

	conductedDocumentsPopup()
	{
		const popup = new Popup({
			events: {
				onPopupClose: () => {
					popup.destroy();
				}
			},
			content: this.#getPopupContent(Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_CONFIRM')),
			maxWidth: 500,
			overlay: true,
			closeIcon: true,
			closeByEsc: true,
			buttons: [
				new Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_EASY'),
					color: Button.Color.PRIMARY,
					onclick: () => {
						popup.close();
						EventEmitter.emit(EventType.popup.enableWithoutReset, {});
					}
				}),
				new BX.UI.Button({
					text : Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_WITH_RESET'),
					color: BX.UI.Button.Color.LINK,
					onclick: () => {
						popup.close();
						EventEmitter.emit(EventType.popup.enableWithResetDocuments, {});
					}
				}),
			]
		});
		popup.show();
	}

	#getArticleCode()
	{
		return 15992592;
	}

	#getPopupContent(text: String)
	{
		const content = Tag.render`
			<div class='catalog-warehouse-master-clear-popup-content'>
				<h3>${Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_TITLE')}</h3>
				<div class="catalog-warehouse-master-clear-popup-text">
					<span>${text}</span> <a href='#' class="catalog-warehouse-master-clear-popup-hint">${Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_LINK_TITLE')}</a>
				<div>
			</div>
		`;

		content.querySelector('.catalog-warehouse-master-clear-popup-hint').addEventListener('click', (e) => {
			e.preventDefault();

			if (top.BX.Helper)
			{
				top.BX.Helper.show(`redirect=detail&code=${this.#getArticleCode()}`);
			}
		});

		return content;
	}
}
