import { Dom, Type, Event, Tag } from 'main.core';
import { toValue } from 'ui.vue3';
import type { DragData } from '../types';

let copiedDragItem = null;

function onDragStart(event: MouseEvent, value: DragData | () => DragData): void
{
	const { dragData, dragImage } = Type.isFunction(value)
		? value()
		: value;

	copiedDragItem = toValue(dragImage).cloneNode(true).children[0];

	const wrapper = document.getElementById('blockDiagramDragWrapper');
	Dom.append(copiedDragItem, wrapper);

	Dom.style(copiedDragItem, {
		display: 'block',
		position: 'absolute',
		top: 0,
		left: 0,
	});

	const { width, height } = copiedDragItem.getBoundingClientRect();

	event.dataTransfer.setDragImage(
		copiedDragItem,
		width / 2,
		height / 2,
	);

	event.dataTransfer.setData('text/plain', JSON.stringify({
		...toValue(dragData),
		dimensions: {
			width,
			height,
		},
	}));
}

function onDragEnd(event: MouseEvent): void
{
	Dom.remove(copiedDragItem);
	copiedDragItem = null;
}

function initDragItemWrapper(): void
{
	const hasWrapper = document.getElementById('blockDiagramDragWrapper');

	if (hasWrapper)
	{
		return;
	}

	const wrapper = Tag.render`
		<div>
			<div
				id="blockDiagramDragWrapper"
				style="position: relative; width: 100%; height: 100%;"
			>
			</div>
		</div>
	`;
	Dom.append(wrapper, document.body);
	Dom.style(wrapper, {
		position: 'absolute',
		transform: 'translate(-100%, -100%)',
		top: 0,
		right: 0,
	});
}

export const DragBlock = {
	mounted(el, { arg, value }): void
	{
		initDragItemWrapper();
		Dom.attr(el, 'draggable', 'true');
		Event.bind(el, 'dragstart', (event: MouseEvent) => onDragStart(event, value));
		Event.bind(el, 'dragend', (event: MouseEvent) => onDragEnd());
	},
	unmounted(el, { arg, value })
	{
		Event.unbind(el, 'dragstart', (event: MouseEvent) => onDragStart(event, value));
		Event.unbind(el, 'dragend', (event: MouseEvent) => onDragEnd(event));
	},
};
