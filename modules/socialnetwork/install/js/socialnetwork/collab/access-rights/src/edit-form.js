import { ajax, Event, Loc, Tag, Text, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Params } from './access-rights';
import { Form } from './form';

export class EditForm extends Form
{
	#params: Params;

	constructor(params: Params)
	{
		super(params);

		this.#params = params;

		if (!Type.isNumber(this.#params.collabId))
		{
			throw new TypeError('Collab id is required');
		}
	}

	open()
	{
		const sidePanelId = `sn-collab-access-rights-${this.#params.collabId}`;

		const slider = BX.SidePanel.Instance.getSlider(sidePanelId);

		if (slider?.isOpen())
		{
			return;
		}

		BX.SidePanel.Instance.open(sidePanelId, {
			cacheable: false,
			title: Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS'),
			contentCallback: async (sidePanel) => {
				try
				{
					const { data } = await ajax.runAction(
						'socialnetwork.collab.AccessRights.getEditForm',
						{
							data: {
								collabId: this.#params.collabId,
							},
						},
					);

					return this.render(this.prepareFormData(data));
				}
				catch (e)
				{
					console.error(e);

					return Promise.reject();
				}
			},
			width: 661,
			events: {
				onLoad: this.onLoad.bind(this),
				onClose: this.onClose.bind(this),
			},
		});
	}
}
