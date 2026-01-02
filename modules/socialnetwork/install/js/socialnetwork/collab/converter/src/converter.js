import { ErrorPopup } from './error/error-popup';
import { api } from './service/api';
import { Wizard } from './wizard/wizard';

export type ConverterParams = {
	redirectAfterSuccess: boolean,
};

export class Converter
{
	#params: ConverterParams;

	constructor(params: ConverterParams)
	{
		this.#params = params;
	}

	convertToCollab(groupId: number)
	{
		api.validateGroup(groupId).then((result) => {
			if (!result.isValid)
			{
				new ErrorPopup({ groupId, errors: result.errors }).show();

				return;
			}

			void new Wizard({ groupId, redirectAfterSuccess: this.#params.redirectAfterSuccess }).show();
		}).catch((result) => {
			new ErrorPopup({ groupId, errors: result.errors }).show();
		});
	}
}
