import {BaseField} from 'landing.ui.field.basefield';
import {Dom} from 'main.core';
import {TagSelector} from 'ui.entity-selector';

export class UserSelect extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.UserSelectField');

		this.userId = parseInt(options.userId ?? 0);

		Dom.addClass(this.layout, 'landing-ui-field-userselect');
		this.createDialog();
	}

	createDialog()
	{
		this.dialog = new TagSelector({
			multiple: false,
			dialogOptions: {
				preselectedItems: [
					['user', this.userId],
				],
				enableSearch: true,
				multiple: false,
				autoHide: true,
				hideByEsc: true,
				context: 'LANDING_USER_SELECT',
				entities: [{
					id: 'user',
				}],
				popupOptions: {
					targetContainer: parent.document.body,
				},
			},
		});
		this.dialog.renderTo(this.input);
	}

	reset()
	{
		this.setValue(0);
	}

	getValue()
	{
		this.dialog.getTags().forEach(tag => {
			this.userId = tag.getId();
		});

		return this.userId;
	}

	setValue(userId: number)
	{
		this.userId = Math.max(0, userId);
	}
}