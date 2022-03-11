import {Browser, Event, Type} from 'main.core';

import {Tooltip} from './tooltip.js';
import {TooltipBalloon} from './balloon.js';

Event.ready(() => {
	if (
		Browser.isAndroid()
		|| Browser.isIOS()
	)
	{
		return;
	}

	document.addEventListener('mouseover', (e) => {

		const node = e.target;

		const userId = node.getAttribute('bx-tooltip-user-id');
		const loader = node.getAttribute('bx-tooltip-loader');

		let tooltipId = userId; // don't use integer value!

		if (Type.isStringFilled(loader))
		{
			let loaderHash = 0;

			[...loader].forEach((c, i) => {
				loaderHash = (31 * loaderHash + loader.charCodeAt(i)) << 0;
			});

			tooltipId = loaderHash + userId;
		}

		if (Type.isStringFilled(userId))
		{
			if (null == Tooltip.tooltipsList[tooltipId])
			{
				Tooltip.tooltipsList[tooltipId] = new TooltipBalloon({
					userId: userId,
					node: node,
					loader: loader
				});
			}
			else
			{
				Tooltip.tooltipsList[tooltipId].node = node;
				Tooltip.tooltipsList[tooltipId].create();
			}

			e.preventDefault();
		}
	});

});

export {
	Tooltip,
	TooltipBalloon,
}