import {Dom} from 'main.core';
import calculateDurationTransition from './calculate-duration-transition';

export default function scrollTo(container, element)
{
	return new Promise((resolve) => {
		let elementTop = 0;
		let duration = 0;

		if (element)
		{
			const defaultMargin = 20;
			const elementMarginTop = Math.max(parseInt(Dom.style(element, 'margin-top')), defaultMargin);
			let containerScrollTop = container.scrollTop;
			if (!(container instanceof HTMLIFrameElement))
			{
				elementTop = element.offsetTop - (container.offsetTop || 0) - elementMarginTop;
			}
			else
			{
				containerScrollTop = container.contentWindow.scrollY;
				elementTop = BX.pos(element).top - elementMarginTop - 100;
			}

			duration = calculateDurationTransition(
				Math.abs(elementTop - containerScrollTop)
			);

			const start = Math.max(containerScrollTop, 0);
			const finish = Math.max(elementTop, 0);

			if (start !== finish)
			{
				(new BX.easing({
					duration,
					start: {scrollTop: start},
					finish: {scrollTop: finish},
					step(state) {
						if (!(container instanceof HTMLIFrameElement))
						{
							container.scrollTop = state.scrollTop;
						}
						else
						{
							container.contentWindow.scrollTo(0, Math.max(state.scrollTop, 0));
						}
					},
				})).animate();

				setTimeout(resolve, duration);
			}
			else
			{
				resolve();
			}
		}
		else
		{
			resolve();
		}
	});
}