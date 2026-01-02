import { Event, Dom, Tag } from 'main.core';

const transform = (element: HTMLElement, value: number): void => {
	Dom.style(element, 'transform', `translate3d(0, ${value}px, 0)`);
};

const transformGhost = (
	draggedNode: HTMLElement,
	dragContainer: HTMLElement,
	ghost: HTMLElement,
	transformY: number,
): void => {
	const y = draggedNode.offsetTop + transformY;
	if (y < 0)
	{
		transform(ghost, 0);
	}
	else if (y + ghost.offsetHeight > dragContainer.offsetHeight)
	{
		transform(ghost, dragContainer.offsetHeight - ghost.offsetHeight);
	}
	else
	{
		transform(ghost, y);
	}
};

const createGhost = (draggedNode: HTMLElement): HTMLElement => {
	const ghost = draggedNode.cloneNode(true);
	Dom.addClass(ghost, 'vote-creation-form__answer_ghost');
	transform(ghost, draggedNode.offsetTop);
	Dom.prepend(ghost, draggedNode.parentElement);

	return ghost;
};

const createPositionPointer = (container: HTMLElement): HTMLElement => {
	const pointer = Tag.render`<div class="vote-creation-form__answer_position-pointer" hidden></div>`;
	Dom.prepend(pointer, container);

	return pointer;
};

const dragStartHandler = ({ target, currentTarget: dragContainer }: MouseEvent, order: Function): void => {
	if (!Dom.hasClass(target, 'vote-creation-form__answer_dnd-icon'))
	{
		return;
	}

	let transformY = 0;
	let targetNode = null;
	let prevScrollY = window.scrollY;
	const pointerOffset = 8;
	const borderWidth = 1;
	const draggedNode = target.closest('.vote-creation-form__answer');
	const ghost = createGhost(draggedNode);
	const positionPointer = createPositionPointer(dragContainer);
	const mouseMoveHandler = ({ movementY }: MouseEvent): void => {
		Dom.style(document.body, 'userSelect', 'none');
		Dom.style(document.body, 'cursor', 'grabbing');
		Dom.addClass(dragContainer, '--pointer-events-disabled');
		transformY += movementY;
		transformGhost(draggedNode, dragContainer, ghost, transformY);
		Dom.removeClass(dragContainer, '--pointer-events-disabled');
		const { x, y } = ghost.getBoundingClientRect();
		const belowY = transformY > 0 ? y + ghost.offsetHeight - borderWidth : y;
		const belowNode = document.elementFromPoint(x, belowY);
		targetNode = belowNode?.closest('.vote-creation-form__answer');
		Dom.addClass(dragContainer, '--pointer-events-disabled');
		if (!targetNode || targetNode === draggedNode)
		{
			positionPointer.hidden = true;

			return;
		}

		const { offsetTop, offsetHeight } = targetNode;
		const pointerPosition = transformY > 0 ? offsetTop + offsetHeight + pointerOffset : offsetTop - pointerOffset;
		positionPointer.hidden = false;
		transform(positionPointer, pointerPosition);
	};

	const mouseUpHandler = (): void => {
		Dom.style(document.body, 'userSelect', '');
		Dom.style(document.body, 'cursor', '');
		Dom.removeClass(dragContainer, '--pointer-events-disabled');
		Event.unbind(document, 'mousemove', mouseMoveHandler);
		Event.unbind(document, 'mouseup', mouseUpHandler);
		Event.unbind(document, 'scroll', scrollHandler);
		Dom.remove(ghost);
		Dom.remove(positionPointer);
		if (targetNode && targetNode !== draggedNode)
		{
			order(draggedNode.dataset.id, targetNode.dataset.id, transformY > 0);
		}
	};

	const scrollHandler = () => {
		const shift = window.scrollY - prevScrollY;
		prevScrollY = window.scrollY;
		transformY += shift;
		transformGhost(draggedNode, dragContainer, ghost, transformY);
	};

	Event.bind(document, 'mousemove', mouseMoveHandler);
	Event.bind(document, 'mouseup', mouseUpHandler);
	Event.bind(document, 'scroll', scrollHandler);
};

export const DragAndDrop = {
	mounted(el: HTMLElement, binding: Object): void
	{
		Event.bind(el, 'mousedown', (event: MouseEvent) => {
			dragStartHandler(event, binding.value);
		});
	},
};
