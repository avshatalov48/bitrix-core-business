import {Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';

import {WorkgroupCardUtil} from './util';

class WorkgroupCardFavorites
{
	constructor(params)
	{
		this.value = !!params.value;
		this.containerNode = params.containerNode;
		this.styles = params.styles;
		this.groupId = parseInt(params.groupId);

		if (this.containerNode)
		{
			if (
				Type.isPlainObject(this.styles)
				&& Type.isStringFilled(this.styles.switch)
			)
			{
				this.containerNode.querySelectorAll(`.${this.styles.switch}`).forEach((node) => {
					node.addEventListener('click', (e) => {
						this.set(e);
					}, true);
				});
			}

			EventEmitter.subscribe('BX.Socialnetwork.WorkgroupMenu:onSetFavorites', (event: BaseEvent) => {
				const [ params ] = event.getCompatData();

				this.setValue(params.value);

				if (parseInt(params.groupId) === this.groupId)
				{

					const targetNode = this.containerNode.querySelector(`.${this.styles.switch}`);
					if (targetNode)
					{
						this.switch(targetNode, params.value)
					}
				}
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
		const sonetGroupMenu = BX.SocialnetworkUICommon.SonetGroupMenu.getInstance();

		this.setValue(newValue);

		sonetGroupMenu.favoritesValue = newValue;
		sonetGroupMenu.setItemTitle(newValue);

		let targetNode = (
			event.target.classList.contains(this.styles.switch)
				? event.target
				: null
		);

		if (!targetNode)
		{
			targetNode = this.containerNode.querySelector(`.${this.styles.switch}`);
		}

		if (targetNode)
		{
			this.switch(targetNode, newValue);
		}

		BX.SocialnetworkUICommon.setFavoritesAjax({
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

					this.switch(targetNode, currentValue);
				}
			}
		});

		event.preventDefault();
	}

	switch(node, active)
	{
		if (
			!Type.isDomNode(node)
			|| !Type.isPlainObject(this.styles)
			|| !Type.isStringFilled(this.styles.activeSwitch)
		)
		{
			return;
		}

		if (active)
		{
			node.classList.add(this.styles.activeSwitch);
		}
		else
		{
			node.classList.remove(this.styles.activeSwitch);
		}
	}
}

export {
	WorkgroupCardFavorites,
}