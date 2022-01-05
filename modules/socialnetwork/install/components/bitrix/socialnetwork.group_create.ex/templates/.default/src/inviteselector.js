import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';

export class InviteSelector
{
	constructor(
		params: {
			selectorId: string,
		}
	)
	{
		this.selectorId = params.selectorId;

		EventEmitter.subscribe('BX.Main.User.SelectorController:select', this.selectHandler.bind(this));
		EventEmitter.subscribe('BX.Main.User.SelectorController:unSelect', this.selectHandler.bind(this));
	}

	selectHandler(event)
	{
		const [ eventParams ] = event.getCompatData();

		if (eventParams.selectorId === this.selectorId)
		{
			this.showDepartmentHint({
				selectorId: eventParams.selectorId,
			});
		}
	}

	showDepartmentHint(params)
	{
		if (!Type.isStringFilled(params.selectorId))
		{
			return;
		}

		const hintNode = document.getElementById('GROUP_ADD_DEPT_HINT_block');
		if (!hintNode)
		{
			return;
		}

		const selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];
		if (Type.isUndefined(selectorInstance))
		{
			return;
		}

		if (!Type.isPlainObject(selectorInstance.itemsSelected))
		{
			hintNode.classList.remove('visible');
			return false;
		}

		let departmentFound = false;
		Object.entries(selectorInstance.itemsSelected).forEach(([ itemId]) => {
			if (departmentFound)
			{
				return;
			}

			if (itemId.match(/DR\d+/))
			{
				departmentFound = true;
			}
		});

		if (departmentFound)
		{
			hintNode.classList.add('visible');
		}
		else
		{
			hintNode.classList.remove('visible');
		}

		return departmentFound;
	}

}
