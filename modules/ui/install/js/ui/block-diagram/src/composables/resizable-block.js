import { Event } from 'main.core';
import { toValue, ref, computed } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import { CURSOR_TYPES } from '../constants';

// eslint-disable-next-line max-lines-per-function
export function useResizableBlock(options): {...}
{
	const {
		cursorType,
		resizingBlock,
		blockDiagramTop,
		blockDiagramLeft,
		transformX,
		transformY,
		zoom,
		updateBlock,
	} = useBlockDiagram();
	const {
		block,
		minWidth,
		minHeight,
		leftSideRef,
		topSideRef,
		rightSideRef,
		bottomSideRef,
		leftTopCornerRef,
		rightTopCornerRef,
		rightBottomCornerRef,
		leftBottomCornerRef,
	} = options;

	const isResize = ref(false);
	let prevBlockX = 0;
	let prevBlockY = 0;
	let prevBlockWidth = 0;
	let prevBlockHeight = 0;

	const sizeBlockStyle = computed(() => {
		if (toValue(isResize))
		{
			return {
				width: `${toValue(resizingBlock).dimensions.width}px`,
				height: `${toValue(resizingBlock).dimensions.height}px`,
				cursor: toValue(cursorType),
			};
		}

		return {
			width: `${toValue(block).dimensions.width}px`,
			height: `${toValue(block).dimensions.height}px`,
			cursor: toValue(cursorType),
		};
	});

	function updateResizableBlock(): void
	{
		updateBlock({
			...toValue(block),
			position: {
				x: toValue(resizingBlock).position.x,
				y: toValue(resizingBlock).position.y,
			},
			dimensions: {
				width: toValue(resizingBlock).dimensions.width,
				height: toValue(resizingBlock).dimensions.height,
			},
		});
	}

	function onMounted(): void
	{
		Event.bind(toValue(rightSideRef), 'mousedown', onMouseDownRightSide);
		Event.bind(toValue(bottomSideRef), 'mousedown', onMouseDownBottomSide);
		Event.bind(toValue(leftSideRef), 'mousedown', onMouseDownLeftSide);
		Event.bind(toValue(topSideRef), 'mousedown', onMouseDownTopSide);

		Event.bind(toValue(rightTopCornerRef), 'mousedown', onMouseDownRightTopCorner);
		Event.bind(toValue(rightBottomCornerRef), 'mousedown', onMouseDownRightBottomCorner);
		Event.bind(toValue(leftTopCornerRef), 'mousedown', onMouseDownLeftTopCorner);
		Event.bind(toValue(leftBottomCornerRef), 'mousedown', onMouseDownLeftBottomCorner);
	}

	function onUnmounted(): void
	{
		Event.unbind(toValue(rightSideRef), 'mousedown', onMouseDownRightSide);
		Event.unbind(toValue(bottomSideRef), 'mousedown', onMouseDownBottomSide);
		Event.unbind(toValue(leftSideRef), 'mousedown', onMouseDownLeftSide);
		Event.unbind(toValue(topSideRef), 'mousedown', onMouseDownTopSide);

		Event.unbind(toValue(rightTopCornerRef), 'mousedown', onMouseDownRightTopCorner);
		Event.unbind(toValue(rightBottomCornerRef), 'mousedown', onMouseDownRightBottomCorner);
		Event.unbind(toValue(leftTopCornerRef), 'mousedown', onMouseDownLeftTopCorner);
		Event.unbind(toValue(leftBottomCornerRef), 'mousedown', onMouseDownLeftBottomCorner);
	}

	function startResize(event: MouseEvent, curType: string): void
	{
		event.stopPropagation();
		cursorType.value = curType;
		resizingBlock.value = { ...toValue(block) };
		prevBlockX = toValue(block).position.x;
		prevBlockY = toValue(block).position.y;
		prevBlockWidth = toValue(block).dimensions.width;
		prevBlockHeight = toValue(block).dimensions.height;
		isResize.value = true;
	}

	function endResize(event: MouseEvent): void
	{
		event.stopPropagation();
		cursorType.value = 'default';
		updateResizableBlock();
		isResize.value = false;
		resizingBlock.value = null;
	}

	function resizeTopSide(event: MouseEvent): void
	{
		let newY = event.clientY / toValue(zoom);
		newY += toValue(transformY);
		newY -= toValue(blockDiagramTop) / toValue(zoom);

		let newHeight = event.clientY / toValue(zoom);
		newHeight += toValue(transformY);
		newHeight -= toValue(blockDiagramTop) / toValue(zoom);
		newHeight -= prevBlockY + prevBlockHeight;
		newHeight = Math.abs(newHeight);

		const fixedPositionY = prevBlockY + prevBlockHeight - toValue(minHeight);

		resizingBlock.value.position.y = newHeight < toValue(minHeight) || newY >= fixedPositionY
			? fixedPositionY
			: newY;

		resizingBlock.value.dimensions.height = newHeight < toValue(minHeight) || newY >= fixedPositionY
			? toValue(minHeight)
			: newHeight;
	}

	function resizeRightSide(event: MouseEvent): void
	{
		let cursorX = event.clientX / toValue(zoom);
		cursorX += toValue(transformX);
		cursorX -= toValue(blockDiagramLeft) / toValue(zoom);

		let newWidth = prevBlockX;
		newWidth -= event.clientX / toValue(zoom);
		newWidth -= toValue(transformX);
		newWidth -= toValue(blockDiagramLeft) / toValue(zoom);
		newWidth = Math.abs(newWidth);

		resizingBlock.value.dimensions.width = newWidth < toValue(minWidth) || cursorX <= prevBlockX
			? toValue(minWidth)
			: newWidth;
	}

	function resizeBottomSide(event: MouseEvent): void
	{
		let cursorX = event.clientY / toValue(zoom);
		cursorX += toValue(transformY);
		cursorX -= toValue(blockDiagramTop) / toValue(zoom);

		let newHeight = event.clientY / toValue(zoom);
		newHeight -= prevBlockY;
		newHeight += toValue(transformY);
		newHeight -= toValue(blockDiagramTop) / toValue(zoom);
		newHeight = Math.abs(newHeight);

		resizingBlock.value.dimensions.height = newHeight < toValue(minHeight) || cursorX <= prevBlockY
			? toValue(minHeight)
			: newHeight;
	}

	function resizeLeftSide(event: MouseEvent): void
	{
		let newX = event.clientX / toValue(zoom);
		newX += toValue(transformX);
		newX -= toValue(blockDiagramLeft) / toValue(zoom);

		let newWidth = event.clientX / toValue(zoom);
		newWidth += toValue(transformX);
		newWidth -= toValue(blockDiagramLeft) / toValue(zoom);
		newWidth -= (prevBlockX + prevBlockWidth);
		newWidth = Math.abs(newWidth);

		const fixedPositionX = prevBlockX + prevBlockWidth - toValue(minWidth);

		resizingBlock.value.position.x = newWidth < toValue(minWidth) || newX >= fixedPositionX
			? fixedPositionX
			: newX;

		resizingBlock.value.dimensions.width = newWidth < toValue(minWidth) || newX >= fixedPositionX
			? toValue(minWidth)
			: newWidth;
	}

	function onMouseDownRightSide(event: MouseEvent): void
	{
		startResize(event, CURSOR_TYPES.EW_RESIZE);
		Event.bind(document, 'mousemove', onMouseMoveRightSide);
		Event.bind(document, 'mouseup', onMouseUpRightSide);
	}

	function onMouseMoveRightSide(event: MouseEvent): void
	{
		event.stopPropagation();

		if (!toValue(isResize))
		{
			return;
		}

		resizeRightSide(event);
	}

	function onMouseUpRightSide(event: MouseEvent): void
	{
		endResize(event);
		Event.unbind(document, 'mousemove', onMouseMoveRightSide);
		Event.unbind(document, 'mouseup', onMouseUpRightSide);
	}

	function onMouseDownBottomSide(event: MouseEvent): void
	{
		startResize(event, CURSOR_TYPES.NS_RESIZE);
		Event.bind(document, 'mousemove', onMouseMoveBottomSide);
		Event.bind(document, 'mouseup', onMouseUpBottomSide);
	}

	function onMouseMoveBottomSide(event: MouseEvent): void
	{
		event.stopPropagation();

		if (!toValue(isResize))
		{
			return;
		}

		resizeBottomSide(event);
	}

	function onMouseUpBottomSide(event: MouseEvent): void
	{
		endResize(event);
		Event.unbind(document, 'mousemove', onMouseMoveBottomSide);
		Event.unbind(document, 'mouseup', onMouseUpBottomSide);
	}

	function onMouseDownLeftSide(event: MouseEvent): void
	{
		startResize(event, CURSOR_TYPES.EW_RESIZE);
		Event.bind(document, 'mousemove', onMouseMoveLeftSide);
		Event.bind(document, 'mouseup', onMouseUpLeftSide);
	}

	function onMouseMoveLeftSide(event: MouseEvent): void
	{
		event.stopPropagation();

		if (!toValue(isResize))
		{
			return;
		}

		resizeLeftSide(event);
	}

	function onMouseUpLeftSide(event: MouseEvent): void
	{
		endResize(event);
		Event.unbind(document, 'mousemove', onMouseMoveLeftSide);
		Event.unbind(document, 'mouseup', onMouseUpLeftSide);
	}

	function onMouseDownTopSide(event: MouseEvent): void
	{
		startResize(event, CURSOR_TYPES.NS_RESIZE);
		Event.bind(document, 'mousemove', onMouseMoveTopSide);
		Event.bind(document, 'mouseup', onMouseUpTopSide);
	}

	function onMouseMoveTopSide(event: MouseEvent): void
	{
		event.stopPropagation();

		if (!toValue(isResize))
		{
			return;
		}

		resizeTopSide(event);
	}

	function onMouseUpTopSide(event: MouseEvent)
	{
		endResize(event);
		Event.unbind(document, 'mousemove', onMouseMoveTopSide);
		Event.unbind(document, 'mouseup', onMouseUpTopSide);
	}

	function onMouseDownRightBottomCorner(event: MouseEvent): void
	{
		startResize(event, CURSOR_TYPES.NWSE_RESIZE);
		Event.bind(document, 'mousemove', onMouseMoveRightBottomCorner);
		Event.bind(document, 'mouseup', onMouseUpRightBottomCorner);
	}

	function onMouseMoveRightBottomCorner(event: MouseEvent): void
	{
		event.stopPropagation();

		if (!toValue(isResize))
		{
			return;
		}

		resizeRightSide(event);
		resizeBottomSide(event);
	}

	function onMouseUpRightBottomCorner(event: MouseEvent): void
	{
		endResize(event);
		Event.unbind(document, 'mousemove', onMouseMoveRightBottomCorner);
		Event.unbind(document, 'mouseup', onMouseUpRightBottomCorner);
	}

	function onMouseDownRightTopCorner(event: MouseEvent): void
	{
		startResize(event, CURSOR_TYPES.NESW_RESIZE);
		Event.bind(document, 'mousemove', onMouseMoveRightTopCorner);
		Event.bind(document, 'mouseup', onMouseUpRightTopCorner);
	}

	function onMouseMoveRightTopCorner(event: MouseEvent): void
	{
		event.stopPropagation();

		if (!toValue(isResize))
		{
			return;
		}

		resizeTopSide(event);
		resizeRightSide(event);
	}

	function onMouseUpRightTopCorner(event: MouseEvent): void
	{
		endResize(event);
		Event.unbind(document, 'mousemove', onMouseMoveRightTopCorner);
		Event.unbind(document, 'mouseup', onMouseUpRightTopCorner);
	}

	function onMouseDownLeftBottomCorner(event: MouseEvent): void
	{
		startResize(event, CURSOR_TYPES.NESW_RESIZE);
		Event.bind(document, 'mousemove', onMouseMoveLeftBottomCorner);
		Event.bind(document, 'mouseup', onMouseUpLeftBottomCorner);
	}

	function onMouseMoveLeftBottomCorner(event: MouseEvent): void
	{
		event.stopPropagation();

		if (!toValue(isResize))
		{
			return;
		}

		resizeLeftSide(event);
		resizeBottomSide(event);
	}

	function onMouseUpLeftBottomCorner(event: MouseEvent): void
	{
		endResize(event);
		Event.unbind(document, 'mousemove', onMouseMoveLeftBottomCorner);
		Event.unbind(document, 'mouseup', onMouseUpLeftBottomCorner);
	}

	function onMouseDownLeftTopCorner(event: MouseEvent): void
	{
		startResize(event, CURSOR_TYPES.NWSE_RESIZE);
		Event.bind(document, 'mousemove', onMouseMoveLeftTopCorner);
		Event.bind(document, 'mouseup', onMouseUpLeftTopCorner);
	}

	function onMouseMoveLeftTopCorner(event: MouseEvent): void
	{
		event.stopPropagation();

		if (!toValue(isResize))
		{
			return;
		}

		resizeLeftSide(event);
		resizeTopSide(event);
	}

	function onMouseUpLeftTopCorner(event: MouseEvent): void
	{
		endResize(event);
		Event.unbind(document, 'mousemove', onMouseMoveLeftTopCorner);
		Event.unbind(document, 'mouseup', onMouseUpLeftTopCorner);
	}

	return {
		isResize,
		sizeBlockStyle,
		onMounted,
		onUnmounted,
	};
}
