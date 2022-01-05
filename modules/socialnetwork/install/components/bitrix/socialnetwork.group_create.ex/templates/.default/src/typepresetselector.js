import {Type} from 'main.core';
import {Util} from './util';

import {WorkgroupForm} from './index';

export class TypePresetSelector
{
	constructor()
	{
		this.cssClass = {
			container: 'socialnetwork-group-create-ex__type-preset-wrapper',
			selector: 'socialnetwork-group-create-ex__type-preset-selector',
		};

		this.container = document.querySelector(`.${this.cssClass.container}`);
		if (!this.container)
		{
			return;
		}

		let firstItemSelected = false;

		const selectors = this.container.querySelectorAll(`.${this.cssClass.selector}`);
		selectors.forEach((selector) => {
			selector.addEventListener('click', (e) => {

				const selector = e.currentTarget;
				if (selector.classList.contains(Util.cssClass.selectorDisabled))
				{
					return;
				}

				Util.unselectAllSelectorItems(this.container, this.cssClass.selector);
				Util.selectSelectorItem(selector);

				const projectType = selector.getAttribute('data-bx-project-type');

				WorkgroupForm.getInstance().recalcForm({
					selectedProjectType: projectType,
				});

				WorkgroupForm.getInstance().wizardManager.setProjectType(projectType);
			});

			const projectType = selector.getAttribute('data-bx-project-type');

			if (Type.isStringFilled(WorkgroupForm.getInstance().selectedProjectType))
			{
				if (WorkgroupForm.getInstance().selectedProjectType === projectType)
				{
					this.selectItem(selector);
				}
			}
			else if (!firstItemSelected)
			{
				this.selectItem(selector);
				firstItemSelected = true;
			}
		});
	}

	selectItem(selector: Element)
	{
		const projectType = selector.getAttribute('data-bx-project-type');
		Util.selectSelectorItem(selector);
		WorkgroupForm.getInstance().recalcForm({
			selectedProjectType: projectType,
		});
		WorkgroupForm.getInstance().wizardManager.setProjectType(projectType);
	}


}
