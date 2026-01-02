import { ajax, Loc } from 'main.core';
import { Params } from './access-rights';
import { Form } from './form';

export class AddForm extends Form
{
	#params: Params;

	constructor(params: Params)
	{
		super(params);

		this.#params = params;
	}

	open()
	{
		const slider = BX.SidePanel.Instance.getSlider(this.sidePanelId);

		if (slider?.isOpen())
		{
			return;
		}

		BX.SidePanel.Instance.open(this.sidePanelId, {
			cacheable: false,
			title: Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS'),
			contentCallback: async (sidePanel) => {
				try
				{
					const { data } = await ajax.runAction(
						'socialnetwork.collab.AccessRights.getAddForm',
						{
							data: {},
						},
					);

					const formData = this.#params?.formData ?? {};

					return this.render({ ...this.prepareFormData(data), ...formData });
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
