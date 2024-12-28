import { Tag } from 'main.core';
import AvatarBase from './avatar-base';

export default class AvatarRoundGuest extends AvatarBase
{
	getMaskNode(): SVGElement
	{
		if (!this.node.svgMask)
		{
			this.node.svgMask = this.getSvgElement('circle', { cx: 51, cy: 51, r: 42.5, fill: 'white' });
		}

		return this.node.svgMask;
	}

	getDefaultUserPic(): SVGElement
	{
		if (!this.node.svgDefaultUserPic)
		{
			this.node.svgDefaultUserPic = this.getSvgElement(
				'svg',
				{ width: 56, height: 64, viewBox: '0 0 28 32', x: 23, y: 20 },
			);
			this.node.svgDefaultUserPic.innerHTML = `
				<path class="ui-avatar-default-path" d="M25.197 29.5091C26.5623 29.0513 27.3107 27.5994 27.0337 26.1625L26.6445 24.143C26.4489 22.8806 25.0093 21.4633 21.7893 20.6307C20.6983 20.3264 19.6613 19.8546 18.7152 19.232C18.5082 19.1138 18.5397 18.0214 18.5397 18.0214L17.5026 17.8636C17.5026 17.7749 17.4139 16.4649 17.4139 16.4649C18.6548 16.048 18.5271 13.5884 18.5271 13.5884C19.3151 14.0255 19.8283 12.0791 19.8283 12.0791C20.7604 9.37488 19.3642 9.53839 19.3642 9.53839C19.6085 7.88753 19.6085 6.20972 19.3642 4.55887C18.7435 -0.917471 9.39785 0.569216 10.506 2.35777C7.77463 1.85466 8.39788 8.06931 8.39788 8.06931L8.99031 9.67863C8.16916 10.2112 8.33041 10.8225 8.51054 11.5053C8.58564 11.7899 8.66401 12.087 8.67586 12.396C8.73309 13.9469 9.68211 13.6255 9.68211 13.6255C9.7406 16.1851 11.0028 16.5184 11.0028 16.5184C11.2399 18.1258 11.0921 17.8523 11.0921 17.8523L9.9689 17.9881C9.9841 18.3536 9.95432 18.7197 9.88022 19.078C9.2276 19.3688 8.82806 19.6003 8.43247 19.8294C8.0275 20.064 7.62666 20.2962 6.9627 20.5873C4.42693 21.6985 1.8838 22.3205 1.39387 24.2663C1.28119 24.7138 1.1185 25.4832 0.962095 26.2968C0.697567 27.673 1.44264 29.0328 2.74873 29.4755C5.93305 30.5548 9.46983 31.1912 13.2024 31.2728H14.843C18.5367 31.192 22.0386 30.5681 25.197 29.5091Z"/>
			`;
		}

		return this.node.svgDefaultUserPic;
	}

	getUserPicNode(): SVGElement
	{
		if (!this.node.svgUserpic)
		{
			this.node.svgUserpic = this.getSvgElement('image', { height: 86, width: 86, x: 8, y: 8, mask: `url(#${this.getUnicId()}-${this.constructor.name})`, preserveAspectRatio: 'xMidYMid slice' });
		}

		return this.node.svgUserpic;
	}

	getContainer(): HTMLElement
	{
		if (!this.node.avatar)
		{
			this.node.avatar = Tag.render`
				<div class="ui-avatar --round --guest">
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
