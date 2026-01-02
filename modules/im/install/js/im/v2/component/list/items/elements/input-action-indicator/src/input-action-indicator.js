import { Lottie } from 'ui.lottie';

import InputAnimation from './animations/input.json';

import './css/input-action-indicator.css';

// @vue/component
export const InputActionIndicator = {
	name: 'InputActionIndicator',
	mounted()
	{
		this.playAnimation();
	},
	beforeUnmount()
	{
		this.animation.destroy();
	},
	methods:
	{
		playAnimation(): void
		{
			this.animation = Lottie.loadAnimation({
				animationData: InputAnimation,
				container: this.$refs.animationContainer,
				renderer: 'svg',
				loop: true,
				autoplay: true,
			});
		},
	},
	template: `
		<div class="bx-im-input-action-indicator__container" ref="animationContainer"></div>
	`,
};
