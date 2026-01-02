import { Tag } from 'main.core';
import AvatarRoundGuest from './avatar-round-guest';

export default class AvatarRoundMarta extends AvatarRoundGuest
{
	getDefaultUserPic(): SVGElement
	{
		if (!this.node.svgDefaultUserPic)
		{
			this.node.svgDefaultUserPic = this.getSvgElement(
				'svg',
				{ width: 86, height: 86, viewBox: '0 0 86 86', x: 8, y: 8 },
			);

			this.node.svgDefaultUserPic.innerHTML = `
				<mask id="mask0_37739_286983" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="86" height="86">
					<circle cx="43.0045" cy="43" r="42.5" fill="#D9D9D9"/>
				</mask>
				<g mask="url(#mask0_37739_286983)">
					<g clip-path="url(#clip0_37739_286983)">
						<rect width="86" height="86" transform="translate(0.0045166)" fill="url(#paint0_linear_37739_286983)"/>
						<g opacity="0.2" filter="url(#filter0_f_37739_286983)">
							<path d="M52.0017 76.8239C64.0812 80.2656 70.5411 77.8538 76.9714 72.7017L90.876 17.5222L20.969 -2.3958C16.488 -3.67252 10.7737 -3.8797 15.6093 -0.434309C26.4315 9.6986 27.2791 15.5905 30.5171 24.3292C33.7551 33.068 26.688 69.6115 52.0017 76.8239Z" fill="url(#paint1_linear_37739_286983)"/>
						</g>
					</g>
				</g>
				<defs>
					<filter id="filter0_f_37739_286983" x="11.7443" y="-5.2728" width="81.1553" height="85.7282" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
						<feFlood flood-opacity="0" result="BackgroundImageFix"/>
						<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
						<feGaussianBlur stdDeviation="1.01176" result="effect1_foregroundBlur_37739_286983"/>
					</filter>
					<linearGradient id="paint0_linear_37739_286983" x1="43" y1="0" x2="43" y2="86" gradientUnits="userSpaceOnUse">
						<stop stop-color="#50C2FF"/>
						<stop offset="1" stop-color="#0277FE"/>
					</linearGradient>
					<linearGradient id="paint1_linear_37739_286983" x1="32.0175" y1="27.0845" x2="70.4547" y2="44.9651" gradientUnits="userSpaceOnUse">
						<stop stop-color="#7BFEC3"/>
						<stop offset="0.945" stop-color="#44A5FC"/>
					</linearGradient>
					<clipPath id="clip0_37739_286983">
						<rect width="86" height="86" fill="white" transform="translate(0.0045166)"/>
					</clipPath>
				</defs>
			`;
		}

		return this.node.svgDefaultUserPic;
	}

	getContainer(): HTMLElement
	{
		if (!this.node.avatar)
		{
			this.node.avatar = Tag.render`
				<div class="ui-avatar --round --marta">
					<svg viewBox="0 0 102 102">
						<circle class="ui-avatar-border-inner" cx="51" cy="51"/>
						${this.getDefaultUserPic()}
						<path class="ui-avatar-border" fill="url(#ui-avatar-gradient-accent-${this.getUnicId()})" d="M51 98.26C77.101 98.26 98.26 77.101 98.26 51C98.26 24.899 77.101 3.74 51 3.74C24.899 3.74 3.74 24.899 3.74 51C3.74 77.101 24.899 98.26 51 98.26ZM51 102C79.1665 102 102 79.1665 102 51C102 22.8335 79.1665 0 51 0C22.8335 0 0 22.8335 0 51C0 79.1665 22.8335 102 51 102Z" transform="rotate(140 51 51)"/>
						<linearGradient id="ui-avatar-gradient-accent-${this.getUnicId()}" gradientUnits="userSpaceOnUse">
							<stop stop-color="rgba(0, 117, 255, 1)"/>
						    <stop offset="25%" stop-color="rgba(48, 180, 247, 1)" />
							<stop offset="75%" stop-color="rgba(124, 255, 194, 1)" />
							<stop offset="100%" stop-color="rgba(59, 243, 156, 1)" />
						</linearGradient>
					</svg>
				</div>
			`;
		}

		return this.node.avatar;
	}
}
