import { Lottie } from 'ui.lottie';
import blockAnimation from '../../lottie/animation.json';

export const renderLottieAnimation = (target) => {
	if (!target)
	{
		console.error('Target container is not specified for Lottie animation.');

		return;
	}

	const animation = Lottie.loadAnimation({
		container: target,
		renderer: 'svg',
		loop: false,
		autoplay: true,
		animationData: blockAnimation,
	});

	setInterval(() => {
		animation.stop();
		animation.play();
	}, 15000);
};
