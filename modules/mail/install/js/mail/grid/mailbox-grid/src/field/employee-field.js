import { BaseField } from './base-field';
import { FullNameField } from './full-name-field';
import { AvatarField } from './avatar-field';
import { Tag, Text, Dom } from 'main.core';
import type { User } from './component/types';

export class EmployeeField extends BaseField
{
	render(params: User): void
	{
		const avatarFieldId = Text.getRandom(6);
		const fullNameFieldId = Text.getRandom(6);
		this.appendToFieldNode(Tag.render`<span id="${avatarFieldId}"></span>`);
		this.appendToFieldNode(Tag.render`<span class="mailbox-grid_full-name-wrapper" id="${fullNameFieldId}"></span>`);

		(new AvatarField({ fieldId: avatarFieldId })).render(params.avatar?.src);
		(new FullNameField({ fieldId: fullNameFieldId })).render(params);

		Dom.addClass(this.getFieldNode(), 'mailbox-grid_employee-card-container');
	}
}
