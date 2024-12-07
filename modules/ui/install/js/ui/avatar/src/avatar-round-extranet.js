import { Tag } from 'main.core';
import AvatarRoundGuest from './avatar-round-guest';

export default class AvatarRoundExtranet extends AvatarRoundGuest
{
	getContainer(): HTMLElement
	{
		if (!this.node.avatar)
		{
			this.node.avatar = Tag.render`
				<div class="ui-avatar --round --extranet">
					<svg viewBox="0 0 102 102">
						<circle class="ui-avatar-border-inner" cx="51" cy="51" r="51"/>
						<circle class="ui-avatar-base" cx="51" cy="51" r="42.5"/>
						<path class="ui-avatar-border" d="M51 98.26C77.101 98.26 98.26 77.101 98.26 51C98.26 24.899 77.101 3.74 51 3.74C24.899 3.74 3.74 24.899 3.74 51C3.74 77.101 24.899 98.26 51 98.26ZM51 102C79.1665 102 102 79.1665 102 51C102 22.8335 79.1665 0 51 0C22.8335 0 0 22.8335 0 51C0 79.1665 22.8335 102 51 102Z"/>
					</svg>
				</div>
			`;
		}

		return this.node.avatar;
	}
}
