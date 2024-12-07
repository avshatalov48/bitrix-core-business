import { BaseField } from './base-field';
import { Switcher } from 'ui.switcher';
import { Type, Event } from 'main.core';

export class SingleChecker extends BaseField
{
	switcher: Switcher;

	constructor(params)
	{
		super(params);
		this.switcher = params.switcher;

		Event.bind(
			this.switcher.getNode(),
			'click',
			() => {
				if (!this.isEnable() && !this.switcher.isChecked())
				{
					this.switcher.check(true, false);
					if (!Type.isNil(this.getHelpMessage()))
					{
						this.getHelpMessage().show();
					}

					return;
				}

				this.switcher.inputNode.form.dispatchEvent(new window.Event('change'));
				this.emit('change', this.switcher.isChecked());
			},
		);
	}
}
