import { toValue, ref } from 'ui.vue3';
import { Event } from 'main.core';
import { useBlockDiagram } from './block-diagram';
import type { DiagramBlock, DiagramPort } from '../types';

export type UseNewConnection = {
	isSourcePort: boolean;
	onMouseDownPort: (event: MouseEvent) => void;
	onMouseOverPort: () => void;
	onMouseLeavePort: () => void;
};

// eslint-disable-next-line max-lines-per-function
export function useNewConnection(block: DiagramBlock, port: DiagramPort): UseNewConnection
{
	const {
		isDisabledBlockDiagram,
		newConnection,
		portsRectMap,
		blockDiagramTop,
		blockDiagramLeft,
		zoom,
		transformX,
		transformY,
		addConnection,
	} = useBlockDiagram();
	const isSourcePort = ref(false);

	function onMouseDownPort(event: MouseEvent): void
	{
		event.stopPropagation();

		if (toValue(isDisabledBlockDiagram))
		{
			return;
		}

		isSourcePort.value = true;
		const portRect = toValue(portsRectMap)?.[toValue(block).id]?.[toValue(port).id];
		const startPosition = {
			x: portRect.x + (portRect.width / 2),
			y: portRect.y + (portRect.height / 2),
		};

		newConnection.value = {
			sourceBlockId: toValue(block).id,
			sourcePortId: toValue(port).id,
			targetBlockId: null,
			targetPortId: null,
			start: startPosition,
			end: startPosition,
		};

		Event.bind(document, 'mousemove', onMouseMove);
		Event.bind(document, 'mouseup', onMouseUp);
	}

	function onMouseMove(event: MouseEvent): void
	{
		if (!toValue(newConnection) || toValue(isDisabledBlockDiagram))
		{
			return;
		}

		const x: number = event.clientX / toValue(zoom);
		const y: number = event.clientY / toValue(zoom);

		newConnection.value.end = {
			x: x + toValue(transformX) - (toValue(blockDiagramLeft) / toValue(zoom)),
			y: y + toValue(transformY) - (toValue(blockDiagramTop) / toValue(zoom)),
		};
	}

	function onMouseUp(event: MouseEvent): void
	{
		if (toValue(newConnection) === null || toValue(isDisabledBlockDiagram))
		{
			return;
		}

		const {
			sourceBlockId = null,
			sourcePortId = null,
			targetBlockId = null,
			targetPortId = null,
		} = toValue(newConnection);

		const isSamePort = sourceBlockId === targetBlockId && sourcePortId === targetPortId;
		const hasSourceIds = sourceBlockId !== null && sourcePortId !== null;
		const hasTargetIds = targetBlockId !== null && targetPortId !== null;

		if (!isSamePort && hasSourceIds && hasTargetIds)
		{
			addConnection({
				sourceBlockId,
				sourcePortId,
				targetBlockId,
				targetPortId,
			});
		}

		newConnection.value = null;
		isSourcePort.value = false;
		Event.unbind(document, 'mousemove', onMouseMove);
		Event.unbind(document, 'mouseup', onMouseUp);
	}

	function onMouseOverPort(): void
	{
		if (toValue(isDisabledBlockDiagram))
		{
			return;
		}

		if (toValue(newConnection) !== null)
		{
			newConnection.value.targetBlockId = toValue(block).id;
			newConnection.value.targetPortId = toValue(port).id;
		}
	}

	function onMouseLeavePort(): void
	{
		if (toValue(isDisabledBlockDiagram))
		{
			return;
		}

		if (toValue(newConnection) !== null)
		{
			newConnection.value.targetBlockId = null;
			newConnection.value.targetPortId = null;
		}
	}

	return {
		isSourcePort,
		onMouseDownPort,
		onMouseOverPort,
		onMouseLeavePort,
	};
}
