import { Tag } from 'main.core';
import AvatarRoundGuest from './avatar-round-guest';

export default class AvatarRoundAccent extends AvatarRoundGuest
{
	getContainer(): HTMLElement
	{
		if (!this.node.avatar)
		{
			this.node.avatar = Tag.render`
				<div class="ui-avatar --round --accent">
					<svg viewBox="0 0 102 102">
						<circle fill="var(--ui-avatar-border-inner-color)" cx="51" cy="51" r="51"/>
						<circle fill="var(--ui-avatar-base-color)" cx="51" cy="51" r="42.5"/>
						<path class="ui-avatar-border" fill="url(#ui-avatar-gradient-accent-${this.getUnicId()})" d="M51 98.26C77.101 98.26 98.26 77.101 98.26 51C98.26 24.899 77.101 3.74 51 3.74C24.899 3.74 3.74 24.899 3.74 51C3.74 77.101 24.899 98.26 51 98.26ZM51 102C79.1665 102 102 79.1665 102 51C102 22.8335 79.1665 0 51 0C22.8335 0 0 22.8335 0 51C0 79.1665 22.8335 102 51 102Z"/>
						<linearGradient id="ui-avatar-gradient-accent-${this.getUnicId()}" x1="13.3983" y1="2.16102" x2="53.5932" y2="60.0763" gradientUnits="userSpaceOnUse">
							<stop stop-color="var(--ui-avatar-color-gradient-start)"/>
							<stop offset="1" stop-color="var(--ui-avatar-color-gradient-stop)"/>
						</linearGradient>
					</svg>
				</div>
			`;
		}

		return this.node.avatar;
	}
}