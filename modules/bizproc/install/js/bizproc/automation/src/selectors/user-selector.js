import { Type } from 'main.core';
import { InlineSelector } from './inline-selector';

export class UserSelector extends InlineSelector
{
	renderTo(targetInput: Element)
	{
		this.targetInput = targetInput;
		this.menuButton = targetInput;

		this.fieldProperty = JSON.parse(targetInput.getAttribute('data-property'));
		if (!this.fieldProperty)
		{
			this.context.useSwitcherMenu = false;
		}

		const additionalUserFields = this.context.get('additionalUserFields');
		this.userSelector = BX.Bizproc.UserSelector.decorateNode(
			targetInput,
			{
				additionalFields: Type.isArray(additionalUserFields) ? additionalUserFields : [],
			},
		);
	}

	destroy()
	{
		super.destroy();

		if (this.userSelector)
		{
			this.userSelector.destroy();
			this.userSelector = null;
		}
	}
}