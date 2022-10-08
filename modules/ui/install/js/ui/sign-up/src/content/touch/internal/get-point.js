import {Type} from 'main.core';

export function getPoint(event: MouseEvent | TouchEvent): {x: number, y: number}
{
	if (!Type.isNil(window.TouchEvent) && event instanceof window.TouchEvent)
	{
		const rect = event.target.getBoundingClientRect();
		const {touches, changedTouches} = event;
		const [touch] = touches.length > 0 ? touches : changedTouches;

		return {x: touch.clientX - rect.left, y: touch.clientY - rect.top};
	}

	return {x: event.offsetX, y: event.offsetY};
}