import { Loc, Event, Tag } from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';
import { Button } from 'ui.buttons';
import { EventEmitter } from 'main.core.events';
import { EventType } from './event-type';
import 'ui.layout-form';

class DialogCostPriceAccountingMethodSelection
{
	static METHOD_AVERAGE: string = 'average';
	static METHOD_FIFO: string = 'fifo';

	selectedMethod = DialogCostPriceAccountingMethodSelection.METHOD_AVERAGE;

	#getArticleCode(): number
	{
		return 17_858_278;
	}

	popup()
	{
		return new Promise((resolve) => {
			const messageBox = MessageBox.create({
				title: Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_TITLE'),
				message: this.getContent(),
				buttons: [
					new Button({
						text: Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_SELECT'),
						color: Button.Color.PRIMARY,
						onclick: () => {
							EventEmitter.emit(EventType.popup.selectCostPriceAccountingMethod, { method: this.selectedMethod });
							messageBox.close();

							resolve();
						},
					}),
				],
				maxWidth: 500,
			});

			messageBox.show();
		});
	}

	getContent(): HTMLElement
	{
		const selector = Tag.render`
			<select class="ui-ctl-element">
				<option value="${DialogCostPriceAccountingMethodSelection.METHOD_AVERAGE}" selected>
					${Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_AVERAGE')}
				</option>
				<option value="${DialogCostPriceAccountingMethodSelection.METHOD_FIFO}">
					${Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_FIFO')}
				</option>
			</select>
		`;

		Event.bind(selector, 'change', () => {
			this.selectedMethod = selector.value;
		});

		const link = Tag.render`
			<a href='#' class="catalog-warehouse-master-clear-popup-hint">
				${Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_DETAILS')}
			</a>
		`;

		Event.bind(link, 'click', (e) => {
			e.preventDefault();

			if (top.BX.Helper)
			{
				top.BX.Helper.show(`redirect=detail&code=${this.#getArticleCode()}`);
			}
		});

		return Tag.render`
			<div class='catalog-warehouse-master-clear-popup-content'>
				<div class="catalog-warehouse-master-clear-popup-text">
					<p>${Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_TEXT')} ${link}</p>
				</div>
				<div class="catalog-warehouse-master-clear-popup-text ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					${selector}
				</div>
			</div>
		`;
	}
}

export { DialogCostPriceAccountingMethodSelection };
