import { ajax } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { DialogCostPriceAccountingMethodSelection } from './dialog-cost-price-accounting-method-selection';
import { DialogClearing } from './dialog-clearing';
import { EventType } from './event-type';

export class DialogEnable
{
	static QUANTITY_INCONSISTENCY_EXISTS = 'QUANTITY_INCONSISTENCY_EXISTS';
	static CONDUCTED_DOCUMENTS_EXIST = 'CONDUCTED_DOCUMENTS_EXIST';

	popup()
	{
		ajax.runAction(
			'catalog.config.checkEnablingConditions',
			{},
		).then((response) => {
			const result = response.data;

			/**
			 * if there are some existing documents or some quantities exist, we warn the user in the batch method popup
			 *
			 * if no documents and no unaccounted quantities exist, we show the batch method popup without any warnings
			 */
			const batchMethodPopupParams = {
				clearDocuments: false,
			};
			if (
				result.includes(DialogEnable.CONDUCTED_DOCUMENTS_EXIST)
				|| result.includes(DialogEnable.QUANTITY_INCONSISTENCY_EXISTS)
			)
			{
				batchMethodPopupParams.clearDocuments = true;
			}

			this.selectBatchMethodPopup(batchMethodPopupParams);
		})
			.catch(() => {});
	}

	selectBatchMethodPopup(params)
	{
		(new DialogCostPriceAccountingMethodSelection())
			.popup()
			.then(() => {
				if (params.clearDocuments)
				{
					(new DialogClearing()).popup();
				}
				else
				{
					EventEmitter.emit(EventType.popup.enableWithoutReset);
				}
			})
			.catch(() => {})
		;
	}
}
