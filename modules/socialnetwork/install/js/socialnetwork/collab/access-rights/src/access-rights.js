import { Type } from 'main.core';

import { Form } from './form';
import { AddForm } from './add-form';
import { EditForm } from './edit-form';

import 'ui.design-tokens';
import 'ui.sidepanel-content';

import './css/base.css';

export type Params = {
	collabId?: number,
	enableServerSave?: boolean,
};

export class AccessRights
{
	static async openForm(params: Params): Promise<Form>
	{
		await top.BX.Runtime.loadExtension(
			'socialnetwork.collab.access-rights',
		);

		const isEditMode = Number(params?.collabId) > 0;
		if (isEditMode)
		{
			const form = new EditForm(params);
			form.open();

			return form;
		}

		const form = new AddForm(params);
		form.open();

		return form;
	}
}
