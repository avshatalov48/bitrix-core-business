import {Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Common, SonetGroupMenu} from 'socialnetwork.common';

import {WorkgroupCardUtil} from './util';

class WorkgroupCardFavorites
{
	constructor(params)
	{
		this.value = !!params.value;
		this.containerNode = params.containerNode;
		this.groupId = parseInt(params.groupId);

		if (this.containerNode)
		{
			EventEmitter.subscribe('BX.Socialnetwork.WorkgroupMenu:onSetFavorites', (event: BaseEvent) => {
				const [ params ] = event.getCompatData();

				this.setValue(params.value);
			});
		}
	}

	setValue(value)
	{
		this.value = value;
	}

	getValue()
	{
		return this.value;
	}

	set(event)
	{
		const currentValue = this.getValue();
		const newValue = !currentValue;
		const sonetGroupMenu = SonetGroupMenu.getInstance();

		this.setValue(newValue);

		sonetGroupMenu.favoritesValue = newValue;
		sonetGroupMenu.setItemTitle(newValue);

		Common.setFavoritesAjax({
			groupId: this.groupId,
			favoritesValue: currentValue,
			callback: {
				success: (data) => {

					const eventData = {
						code: 'afterSetFavorites',
						data: {
							groupId: data.ID,
							value: (data.RESULT == 'Y'),
						}
					};
					window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);

					if (
						Type.isStringFilled(data.NAME)
						&& Type.isStringFilled(data.URL)
					)
					{
						EventEmitter.emit('BX.Socialnetwork.WorkgroupFavorites:onSet', new BaseEvent({
							compatData: [{
								id: this.groupId,
								name: data.NAME,
								url: data.URL,
								extranet: (Type.isStringFilled(data.EXTRANET) ? data.EXTRANET : 'N'),
							}, newValue],
						}));
					}
				},
				failure: (data) => {

					this.setValue(currentValue);
					sonetGroupMenu.favoritesValue = currentValue;
					sonetGroupMenu.setItemTitle(currentValue);

					if (Type.isStringFilled(data.ERROR))
					{
						WorkgroupCardUtil.processAJAXError(data.ERROR);
					}
				}
			}
		});

		event.preventDefault();
	}
}

export {
	WorkgroupCardFavorites,
}