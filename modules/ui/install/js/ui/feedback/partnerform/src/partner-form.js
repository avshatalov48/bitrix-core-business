import { Extension, Type } from 'main.core';
import { Form } from 'ui.feedback.form';

export type partnerFormParams = {
	id: string | number;
	source: string;
	title?: string;
	button?: string;
};

export class PartnerForm
{
	static show(params: partnerFormParams)
	{
		const formParams = {
			id: params.id,
			forms: Extension.getSettings('ui.feedback.partnerform').get('partnerForms'),
			portalUri: Extension.getSettings('ui.feedback.partnerform').get('partnerUri'),
			presets: { source: params.source },
		};

		if (Type.isStringFilled(params.title))
		{
			formParams.title = params.title;
			formParams.showTitle = true;
		}

		if (Type.isStringFilled(params.button))
		{
			formParams.button = params.button;
		}

		Form.open(formParams);
	}
}
