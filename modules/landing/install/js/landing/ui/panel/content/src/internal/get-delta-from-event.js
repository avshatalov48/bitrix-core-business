import {Type} from 'main.core';

export default function getDeltaFromEvent(event)
{
	let {deltaX} = event;
	let deltaY = -1 * event.deltaY;

	if (Type.isUndefined(deltaX) || Type.isUndefined(deltaY))
	{
		deltaX = -1 * event.wheelDeltaX / 6;
		deltaY = event.wheelDeltaY / 6;
	}

	if (event.deltaMode === 1)
	{
		deltaX *= 10;
		deltaY *= 10;
	}

	/** NaN checks */
	if (Number.isNaN(deltaX) && Number.isNaN(deltaY))
	{
		deltaX = 0;
		deltaY = event.wheelDelta;
	}

	return {x: deltaX, y: deltaY};
}