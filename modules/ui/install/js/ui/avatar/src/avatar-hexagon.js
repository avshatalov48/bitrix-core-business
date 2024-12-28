import { Tag } from 'main.core';
import AvatarBase from './avatar-base';

export default class AvatarHexagon extends AvatarBase
{
	getUserPicNode(): HTMLElement
	{
		if (!this.node.svgUserpic)
		{
			this.node.svgUserpic = this.getSvgElement('image', { height: 102, width: 102, mask: `url(#${this.getUnicId()}-${this.constructor.name})`, preserveAspectRatio: 'xMidYMid slice' });
		}

		return this.node.svgUserpic;
	}

	getMaskNode(): SVGElement
	{
		if (!this.node.svgMask)
		{
			this.node.svgMask = this.getSvgElement('path', { class: 'ui-avatar-mask', d: 'M40.4429 2.77436C47.0211 -0.823713 54.979 -0.823711 61.5572 2.77436L88.9207 17.7412C95.9759 21.6001 100.363 29.001 100.363 37.0426V64.9573C100.363 72.9989 95.9759 80.3998 88.9207 84.2588L61.5572 99.2256C54.979 102.824 47.0211 102.824 40.4429 99.2256L13.0794 84.2588C6.02419 80.3998 1.6366 72.9989 1.6366 64.9573V37.0426C1.6366 29.001 6.0242 21.6001 13.0794 17.7412L40.4429 2.77436Z' });
		}

		return this.node.svgMask;
	}

	getContainer(): HTMLElement
	{
		if (!this.node.avatar)
		{
			this.node.avatar = Tag.render`
				<div class="ui-avatar --hexagon --base">
					<svg viewBox="0 0 102 102">
						<path class="ui-avatar-base" d="M40.4429 2.77436C47.0211 -0.823713 54.979 -0.823711 61.5572 2.77436L88.9207 17.7412C95.9759 21.6001 100.363 29.001 100.363 37.0426V64.9573C100.363 72.9989 95.9759 80.3998 88.9207 84.2588L61.5572 99.2256C54.979 102.824 47.0211 102.824 40.4429 99.2256L13.0794 84.2588C6.02419 80.3998 1.6366 72.9989 1.6366 64.9573V37.0426C1.6366 29.001 6.0242 21.6001 13.0794 17.7412L40.4429 2.77436Z"/>
					</svg>
				</div>
			`;
		}

		return this.node.avatar;
	}
}
