import {Type} from 'main.core';
import {Util} from './util';

import {WorkgroupForm} from './index';

export class ConfidentialitySelector
{
	static cssClass = {
		container: 'socialnetwork-group-create-ex__type-confidentiality-wrapper',
		selector: 'socialnetwork-group-create-ex__group-selector',
	};

	constructor()
	{
		let firstItemSelected = false;

		ConfidentialitySelector.getItems().forEach((selector) => {
			selector.addEventListener('click', (e) => {

				const selector = e.currentTarget;
				if (selector.classList.contains(Util.cssClass.selectorDisabled))
				{
					return;
				}

				Util.unselectAllSelectorItems(ConfidentialitySelector.getContainer(), ConfidentialitySelector.cssClass.selector);
				Util.selectSelectorItem(selector);

				WorkgroupForm.getInstance().recalcForm({
					selectedConfidentialityType: selector.getAttribute('data-bx-confidentiality-type'),
				});
			});

			const confidentialityType = selector.getAttribute('data-bx-confidentiality-type');

			if (Type.isStringFilled(WorkgroupForm.getInstance().selectedConfidentialityType))
			{
				if (WorkgroupForm.getInstance().selectedConfidentialityType === confidentialityType)
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

		this.bindEvents();
	}

	bindEvents()
	{
		WorkgroupForm.getInstance().subscribe('onSwitchExtranet', ConfidentialitySelector.onSwitchExtranet);
	}

	static onSwitchExtranet(event)
	{
		const data = event.getData();
		if (!Type.isBoolean(data.isChecked))
		{
			return;
		}

		if (data.isChecked)
		{
			ConfidentialitySelector.unselectAll();
			ConfidentialitySelector.select('secret')
			ConfidentialitySelector.disableAll();
			ConfidentialitySelector.enable('secret')
		}
		else
		{
			ConfidentialitySelector.enableAll();
		}
	}

	selectItem(selector: Element)
	{
		Util.selectSelectorItem(selector);
		WorkgroupForm.getInstance().recalcForm({
			selectedConfidentialityType: selector.getAttribute('data-bx-confidentiality-type'),
		});
	}

	static getContainer()
	{
		return document.querySelector(`.${this.cssClass.container}`);
	}

	static getItems()
	{
		const container = this.getContainer();
		if (!container)
		{
			return [];
		}

		return container.querySelectorAll(`.${this.cssClass.selector}`);
	}

	static unselectAll()
	{
		Util.unselectAllSelectorItems(this.getContainer(), this.cssClass.selector);
	}

	static select(accessCode)
	{
		this.getItems().forEach((selector) => {
			if (selector.getAttribute('data-bx-confidentiality-type') !== accessCode)
			{
				return;
			}

			Util.selectSelectorItem(selector);
		});
	}

	static disableAll()
	{
		Util.disableAllSelectorItems(this.getContainer(), this.cssClass.selector);
	}

	static enableAll()
	{
		Util.enableAllSelectorItems(this.getContainer(), this.cssClass.selector);
	}

	static enable(accessCode)
	{
		this.getItems().forEach((selector) => {
			if (selector.getAttribute('data-bx-confidentiality-type') !== accessCode)
			{
				return;
			}

			Util.enableSelectorItem(selector);
		});
	}
}
