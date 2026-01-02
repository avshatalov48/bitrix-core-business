import { Tag } from 'main.core';
import AvatarRoundGuest from './avatar-round-guest';
import 'ui.design-tokens.air';

export default class AvatarRoundCopilot extends AvatarRoundGuest
{
	setInitials()
	{}

	getDefaultUserPic(): SVGElement
	{
		if (!this.node.svgDefaultUserPic)
		{
			this.node.svgDefaultUserPic = this.getSvgElement(
				'svg',
				{ width: 86, height: 86, viewBox: '0 0 86 86', x: 8, y: 8 },
			);

			this.node.svgDefaultUserPic.innerHTML = `
				<mask id="mask0_37169_151806" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="86" height="86">
					<circle cx="43" cy="43" r="42.5" fill="#D9D9D9"/>
				</mask>
				<g mask="url(#mask0_37169_151806)">
					<g clip-path="url(#clip0_37169_151806)">
						<rect width="86" height="86" fill="url(#paint0_linear_37169_151806)"/>
						<g filter="url(#filter0_f_37169_151806)">
							<path d="M7.29559 61.0024C7.29559 74.5261 12.0511 80.6392 19.8616 86H86V7.73433C86 2.71761 84.411 -3.30594 82.0987 2.65204C74.2152 16.6238 67.8963 19.0601 59.1554 24.7426C50.4145 30.4251 7.29559 32.6619 7.29559 61.0024Z" fill="url(#paint1_linear_37169_151806)"/>
						</g>
						<path fill-rule="evenodd" clip-rule="evenodd" d="M43.003 57.102C50.7377 57.102 57.0079 50.7916 57.0079 43.0074C57.0079 35.2232 50.7377 28.9129 43.003 28.9129C35.2683 28.9129 28.9981 35.2232 28.9981 43.0074C28.9981 50.7916 35.2683 57.102 43.003 57.102ZM40.7646 32.4541C40.6384 32.1107 40.1558 32.1107 40.0296 32.4541L38.6355 36.2456C38.2386 37.3251 37.393 38.1761 36.3204 38.5755L32.553 39.9786C32.2118 40.1056 32.2118 40.5912 32.553 40.7182L36.3204 42.1213C37.393 42.5207 38.2386 43.3717 38.6355 44.4512L40.0296 48.2427C40.1558 48.5861 40.6384 48.5861 40.7646 48.2427L42.1587 44.4512C42.5556 43.3717 43.4012 42.5207 44.4738 42.1213L48.2412 40.7182C48.5824 40.5912 48.5824 40.1056 48.2412 39.9786L44.4738 38.5755C43.4012 38.1761 42.5556 37.3251 42.1587 36.2456L40.7646 32.4541ZM48.7681 43.4107C48.6952 43.2124 48.4164 43.2124 48.3435 43.4107L47.538 45.6015C47.3086 46.2252 46.82 46.717 46.2003 46.9477L44.0234 47.7584C43.8263 47.8318 43.8263 48.1124 44.0234 48.1858L46.2003 48.9965C46.82 49.2273 47.3086 49.719 47.538 50.3427L48.3435 52.5335C48.4164 52.7319 48.6952 52.7319 48.7681 52.5335L49.5736 50.3427C49.803 49.719 50.2916 49.2273 50.9113 48.9965L53.0882 48.1858C53.2853 48.1124 53.2853 47.8318 53.0882 47.7584L50.9113 46.9477C50.2916 46.717 49.803 46.2252 49.5736 45.6015L48.7681 43.4107Z" fill="white"/>
						<path d="M63.1545 43.863C64.5305 43.923 65.6125 45.0982 65.4047 46.4685C63.7447 57.4186 54.3471 65.8076 43.0027 65.8076C30.4866 65.8076 20.3403 55.5963 20.3403 43.0001C20.3403 30.4039 30.4866 20.1926 43.0027 20.1926C48.9652 20.1926 54.3899 22.51 58.4361 26.2987C59.4465 27.2449 59.3211 28.8449 58.2754 29.7515C57.2309 30.6569 55.665 30.5236 54.6272 29.6104C51.5202 26.8765 47.4535 25.2201 43.0022 25.2201C33.2437 25.2201 25.3329 33.1815 25.3329 43.0025C25.3329 52.8235 33.2437 60.7849 43.0022 60.7849C51.6574 60.7849 58.8591 54.522 60.377 46.2536C60.6277 44.8875 61.7754 43.8028 63.1545 43.863Z" fill="white"/>
					</g>
				</g>
				<defs>
					<filter id="filter0_f_37169_151806" x="6.28383" y="-0.837937" width="80.7279" height="87.8497" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
						<feFlood flood-opacity="0" result="BackgroundImageFix"/>
						<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
						<feGaussianBlur stdDeviation="0.505882" result="effect1_foregroundBlur_37169_151806"/>
					</filter>
					<linearGradient id="paint0_linear_37169_151806" x1="49.5996" y1="0.681138" x2="34.8518" y2="86.4028" gradientUnits="userSpaceOnUse">
						<stop stop-color="#E574F3"/>
						<stop offset="1" stop-color="#6D55EB"/>
					</linearGradient>
					<linearGradient id="paint1_linear_37169_151806" x1="83.7262" y1="13.236" x2="32.6935" y2="83.15" gradientUnits="userSpaceOnUse">
						<stop stop-color="#E778E6"/>
						<stop offset="0.945" stop-color="#9B72F9"/>
					</linearGradient>
					<clipPath id="clip0_37169_151806">
						<rect width="86" height="86" fill="white"/>
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
				<div class="ui-avatar --round --copilot">
					<svg viewBox="0 0 102 102">
						<circle class="ui-avatar-border-inner" cx="51" cy="51" r="51"/>
						${this.getDefaultUserPic()}
						<path class="ui-avatar-border" d="M51 98.26C77.101 98.26 98.26 77.101 98.26 51C98.26 24.899 77.101 3.74 51 3.74C24.899 3.74 3.74 24.899 3.74 51C3.74 77.101 24.899 98.26 51 98.26ZM51 102C79.1665 102 102 79.1665 102 51C102 22.8335 79.1665 0 51 0C22.8335 0 0 22.8335 0 51C0 79.1665 22.8335 102 51 102Z"/>
					</svg>
				</div>
			`;
		}

		return this.node.avatar;
	}
}
