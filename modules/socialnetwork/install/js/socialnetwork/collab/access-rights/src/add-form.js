import { ajax, Event, Loc, Tag, Text, Type } from 'main.core';
import { Form } from './form';

export class AddForm extends Form
{
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
