import {Dom} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {MenuForm} from 'landing.ui.form.menuform';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class MenuItemField extends BaseField
{
	constructor(options = {})
	{
		super(options);
		this.depth = options.depth;
		this.fields = options.fields;

		if (this.depth > 0)
		{
			const levelPadding = 20;
			Dom.style(this.layout, 'margin-left', `${levelPadding * this.depth}px`);
		}

		this.form = new MenuForm({
			fields: this.fields,
		});

		Dom.append(this.form.layout, this.layout);
	}
}