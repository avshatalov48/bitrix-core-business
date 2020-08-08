/**
 * Bitrix Messenger
 * Animation manager
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

class Animation
{
	static start(params)
	{
		let {
			start = 0,
			end = 0,
			increment = 20,
			callback = () => {},
			duration = 500,

			element,
			elementProperty
		} = params;

		let diff = end - start;
		let currentPosition = 0;

		const easeInOutQuad = function (current, start, diff, duration)
		{
			current /= duration/2;

			if (current < 1)
			{
				return diff / 2 * current * current + start;
			}

			current--;

			return -diff/2 * (current*(current-2) - 1) + start;
		};

		const requestFrame = (
			window.requestAnimationFrame
			|| window.webkitRequestAnimationFrame
			|| window.mozRequestAnimationFrame
			|| function(callback){return window.setTimeout(callback, 1000 / 60);}
		);

		let frameId = null;
		let animateScroll = () =>
		{
			currentPosition += increment;

			element[elementProperty] = easeInOutQuad(currentPosition, start, diff, duration);
			if (currentPosition < duration)
			{
				frameId = requestFrame(animateScroll);
			}
			else
			{
				if (callback && typeof callback === 'function')
				{
					callback();
				}
			}

			return frameId;
		};

		return animateScroll();
	}

	static cancel(id)
	{
		const cancelFrame = (
			window.cancelAnimationFrame
			|| window.webkitCancelAnimationFrame
			|| window.mozCancelAnimationFrame
			|| function(id){clearTimeout(id)}
		);

		cancelFrame(id);
	}
}

Animation.frameIds = {};

export {Animation};

