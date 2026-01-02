import { BaseField } from './base-field';
import { Dom } from 'main.core';
import { AvatarRound } from 'ui.avatar';

export class AvatarField extends BaseField
{
	render(avatarPath: string): void
	{
		const avatarOptions = {
			size: 28,
		};

		if (avatarPath)
		{
			avatarOptions.userpicPath = encodeURI(avatarPath);
		}

		const avatar = new AvatarRound(avatarOptions);

		avatar?.renderTo(this.getFieldNode());

		Dom.addClass(this.getFieldNode(), 'mailbox-grid_owner-photo');
	}
}
