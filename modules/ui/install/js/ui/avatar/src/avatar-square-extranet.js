import { Tag } from 'main.core';
import AvatarSquareGuest from './avatar-square-guest';

export default class AvatarSquareExtranet extends AvatarSquareGuest
{
	getContainer(): HTMLElement
	{
		if (!this.node.avatar)
		{
			this.node.avatar = Tag.render`
				<div class="ui-avatar --square --extranet">
					<svg viewBox="0 0 102 102">
						<path class="ui-avatar-border-inner" d="M12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z"/>
						<path class="ui-avatar-border" d="M90 3.74H12C7.43813 3.74 3.74 7.43813 3.74 12V90C3.74 94.5619 7.43813 98.26 12 98.26H90C94.5619 98.26 98.26 94.5619 98.26 90V12C98.26 7.43813 94.5619 3.74 90 3.74ZM12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z"/>
						<path class="ui-avatar-base" d="M8.47241 14.4724C8.47241 11.1587 11.1587 8.47241 14.4724 8.47241H87.4724C90.7861 8.47241 93.4724 11.1587 93.4724 14.4724V87.4724C93.4724 90.7861 90.7861 93.4724 87.4724 93.4724H14.4724C11.1587 93.4724 8.47241 90.7861 8.47241 87.4724V14.4724Z"/>
					</svg>
				</div>
			`;
		}

		return this.node.avatar;
	}
}
