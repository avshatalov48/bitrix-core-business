import { Tag } from 'main.core';
import AvatarBase from './avatar-base';

export default class AvatarSquare extends AvatarBase
{
	getMaskNode(): SVGElement
	{
		if (!this.node.svgMask)
		{
			this.node.svgMask = this.getSvgElement('path', { class: 'ui-avatar-mask', d: 'M12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z' });
		}

		return this.node.svgMask;
	}

	getContainer(): HTMLElement
	{
		if (!this.node.avatar)
		{
			this.node.avatar = Tag.render`
				<div class="ui-avatar --square --base">
					<svg viewBox="0 0 102 102">
						<path class="ui-avatar-base" d="M12 0C5.37258 0 0 5.37258 0 12V90C0 96.6274 5.37258 102 12 102H90C96.6274 102 102 96.6274 102 90V12C102 5.37258 96.6274 0 90 0H12Z"/>
					</svg>
				</div>
			`;
		}

		return this.node.avatar;
	}
}
