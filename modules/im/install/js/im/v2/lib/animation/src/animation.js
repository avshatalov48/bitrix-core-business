import {Type} from 'main.core';

type AnimationParams = {
	start: number,
	end: number,
	increment: number,
	duration: number,
	element: HTMLElement,
	elementProperty: string,
	callback: Function
};

const DEFAULT_ANIMATION_PARAMS = {
	increment: 20,
	callback: () => {},
	duration: 500
};

export const Animation = {
	start(params: AnimationParams): number
	{
		if (Type.isUndefined(params.start) || Type.isUndefined(params.end) || !params.element || !params.elementProperty)
		{
			return 0;
		}

		params = {...DEFAULT_ANIMATION_PARAMS, ...params};
		const diff = params.end - params.start;
		let currentValue = 0;

		let frameId;
		const animate = () =>
		{
			currentValue += params.increment;

			params.element[params.elementProperty] = easeFunction(currentValue, params.start, diff, params.duration);
			if (currentValue < params.duration)
			{
				frameId = requestAnimationFrame(animate);
			}
			else
			{
				params.callback();
			}

			return frameId;
		};

		return animate();
	},

	cancel()
	{
		cancelAnimationFrame();
	}
};

const easeFunction = function (currentValue: number, start: number, diff: number, duration: number)
{
	currentValue /= duration / 2;

	if (currentValue < 1)
	{
		return (diff / 2) * (currentValue * currentValue) + start;
	}

	currentValue--;

	return (-diff / 2) * (currentValue * (currentValue - 2) - 1) + start;
};