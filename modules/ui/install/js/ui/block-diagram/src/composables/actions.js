import { toValue, toRaw } from 'ui.vue3';
import {
	STICKING_DISTANCE,
	NODE_HEADER_HEIGHT,
	NODE_CONTENT_HEADER_HEIGHT,
	NODE_TYPES,
} from '../constants';
import { commandToArray } from '../utils';
import { Text } from 'main.core';
import { HandlerOptions } from './history';
import type {
	DiagramBlockId,
	DiagramBlock,
	DiagramConnectionId,
	DiagramConnection,
	DiagramPortId,
	DiagramPort,
	Point,
} from '../types';

export type UseActions = {
	setState: () => void,
	isExistConnection: (connection: DiagramConnection) => boolean,
	addConnection: (connection: DiagramConnection) => void,
	deleteConnectionById: (connectionId: DiagramConnectionId) => void,
	addBlock: (block: DiagramBlock) => void,
	updateBlockPositionByIndex: (index: number, x: number, y: number) => void,
	updateBlock: (newBlock: DiagramBlock) => void,
	deleteBlockById: (blockId: DiagramBlockId) => void,
	getPortAbsolutePosition: (block: DiagramBlock, port: DiagramPort) => Point,
	findNearestPort: (clientX: number, clientY: number) => DiagramPort | null,
	transformEventToPoint: (point: { clientX: number, clientY: number }) => Point,
	setMovingBlock: (block: DiagramBlock) => void,
	updateMovingBlockPosition: (x: number, y: number) => void,
	resetMovingBlock: () => void,
	setHistoryHandlers: (HandlerOptions) => void,
	setPortOffsetByBlockId: (blockId: string, offsets: { x: number, y: number }) => void;
};

/* eslint-disable no-param-reassign */
// eslint-disable-next-line max-lines-per-function
export function useActions({ state, getters, hooks }): UseActions
{
	function setState(options): void
	{
		state.blocks = toValue(options.blocks);
		state.connections = toValue(options.connections);
		state.transformX = options.transform.x;
		state.transformY = options.transfrom.y;
		state.zoom = toValue(options.zoom);
	}

	function updateCanvasTransform(transform: Transform): void
	{
		const {
			x = 0,
			y = 0,
			zoom = 1,
			viewportX = 0,
			viewportY = 0,
		} = transform;
		state.transformX = x;
		state.transformY = y;
		state.viewportX = viewportX;
		state.viewportY = viewportY;
		state.zoom = zoom;
	}

	const isExistConnection = (connection: DiagramConnection): boolean => {
		const {
			sourceBlockId,
			sourcePortId,
			targetBlockId,
			targetPortId,
		} = connection;

		return state.connections.some(({
			sourceBlockId: exSourceBlockId,
			sourcePortId: exSourcePortId,
			targetBlockId: exTargetBlockId,
			targetPortId: exTargetPortId,
		}) => {
			const isSource = (
				exSourceBlockId === sourceBlockId
				&& exSourcePortId === sourcePortId
				&& exTargetBlockId === targetBlockId
				&& exTargetPortId === targetPortId
			);
			const isTarget = (
				exSourceBlockId === targetBlockId
				&& exSourcePortId === targetPortId
				&& exTargetBlockId === sourceBlockId
				&& exTargetPortId === sourcePortId
			);

			return isSource || isTarget;
		});
	};

	const addConnection = (newConnection: DiagramConnection): void => {
		if (!isExistConnection(newConnection))
		{
			hooks.changedConnections.trigger(
				commandToArray.commandPush({
					id: Text.getRandom(),
					...newConnection,
				}),
			);
			hooks.createConnection.trigger(newConnection);
		}
	};

	const deleteConnectionById = (connectionId: DiagramConnectionId): void => {
		const connections = state.connections
			.filter((connection) => connection.id !== connectionId);

		hooks.changedConnections.trigger(
			commandToArray.commandReplace(connections),
		);
		hooks.deleteConnection.trigger(connectionId);
	};

	const deleteConnectionByBlockIdAndPortId = (blockId: DiagramBlockId, portId: DiagramPortId): void => {
		const block = state.blocks.find((stateBlock) => stateBlock.id === blockId);

		if (!block)
		{
			return;
		}

		const portIdMap = new Set(
			[
				...(block.ports?.input ?? []),
				...(block.ports?.output ?? []),
			].map((port) => port.id),
		);
		const newConnections = state.connections.filter((connection) => {
			const {
				sourceBlockId,
				sourcePortId,
				targetBlockId,
				targetPortId,
			} = connection;
			const isSource = sourceBlockId === blockId && portIdMap.has(sourcePortId);
			const isTarget = targetBlockId === blockId && portIdMap.has(targetPortId);

			return !isSource && !isTarget;
		});

		hooks.changedConnections.trigger(
			commandToArray.commandReplace(newConnections),
		);
	};

	const deleteBlockById = (blockId: DiagramBlockId): void => {
		const blockIndex = state.blocks.findIndex((block) => block.id === blockId);

		if (blockIndex === -1)
		{
			return;
		}

		deleteConnectionByBlockIdAndPortId(blockId);

		hooks.changedBlocks.trigger(
			commandToArray.commandDeleteByIndex(blockIndex),
		);
		hooks.deleteBlock.trigger(blockId);
	};

	const addBlock = (block: DiagramBlock): void => {
		hooks.changedBlocks.trigger(
			commandToArray.commandPush(block),
		);
		hooks.addBlock.trigger(block);
	};

	const updateBlockPositionByIndex = (index: number, x: number, y: nubmer): void => {
		state.blocks[index].position.x = x;
		state.blocks[index].position.y = y;
	};

	const updateBlock = (newBlock: DiagramBlock): void => {
		const blockIndex = state.blocks.findIndex((block) => block.id === newBlock.id);

		if (blockIndex === -1)
		{
			return;
		}

		hooks.changedBlocks.trigger(
			commandToArray.commandUpdateByIndex(blockIndex, newBlock),
		);
		hooks.updateBlock.trigger(newBlock);
	};

	const getPortAbsolutePosition = (block: DiagramBlock, port: DiagramPort): Point => {
		const {
			position: { x: blockX, y: blockY },
			dimensions: { width },
			ports: { output },
			node: { type: nodeType },
		} = block;
		const { position, id: portId } = port;

		let portOffsetY: number = position * NODE_HEADER_HEIGHT / 2;
		if (nodeType === NODE_TYPES.COMPLEX)
		{
			portOffsetY += NODE_CONTENT_HEADER_HEIGHT + NODE_HEADER_HEIGHT;
		}
		let portX: number = blockX;

		const isOutputPort = output.some((outputPort): boolean => outputPort.id === portId);

		if (isOutputPort)
		{
			portX = Number(blockX) + Number(width);
		}

		return { x: portX, y: Number(blockY) + portOffsetY };
	};

	const findNearestPort = (clientX: number, clientY: number): { block: DiagramBlock, port: DiagramPort } | null => {
		let nearest = null;
		let nearestDistance: number = Infinity;

		state.blocks.forEach((block): void => {
			const allPorts: Array = [...block.ports.input, ...block.ports.output];

			allPorts.forEach((port): void => {
				const { x, y } = getPortAbsolutePosition(block, port);
				const dx: number = clientX - x;
				const dy: number = clientY - y;
				const distance: number = Math.hypot(dx, dy);

				if (distance < STICKING_DISTANCE && distance < nearestDistance)
				{
					nearest = { block, port };
					nearestDistance = distance;
				}
			});
		});

		return nearest;
	};

	const transformEventToPoint = (point: { clientX: number, clientY: number }): Point => {
		let transformedX: number = Math.round(point.clientX / toValue(state.zoom));
		let transformedY: number = Math.round(point.clientY / toValue(state.zoom));

		const { top, left } = toValue(state.blockDiagramRef)?.getBoundingClientRect() ?? { top: 0, left: 0 };

		transformedX -= Math.round(left / toValue(state.zoom));
		transformedY -= Math.round(top / toValue(state.zoom));

		return { x: transformedX, y: transformedY };
	};

	const setMovingBlock = (block: DiagramBlock): void => {
		state.movingBlock = toRaw({ ...block });
		const movingConnections = [];

		const allPorts = [...block.ports.input, ...block.ports.output];

		allPorts.forEach((port) => {
			const connections = state.connections.filter((connection) => {
				const {
					targetBlockId,
					targetPortId,
					sourceBlockId,
					sourcePortId,
				} = connection;
				const isTarget = targetBlockId === block.id && targetPortId === port.id;
				const isSource = sourceBlockId === block.id && sourcePortId === port.id;

				return isTarget || isSource;
			});

			movingConnections.push(...connections);
		});

		state.movingConnections = movingConnections;
	};

	const updateMovingBlockPosition = (x: number, y: number): void => {
		state.movingBlock.position.x = x;
		state.movingBlock.position.y = y;
	};

	const resetMovingBlock = (): void => {
		state.movingBlock = null;
		state.movingConnections = [];
	};

	const setHistoryHandlers = ({
		snapshotHandler: newSnapshotHandler = null,
		revertHandler: newRevertHandler = null,
	}: HandlerOptions): void => {
		state.snapshotHandler = newSnapshotHandler || state.snapshotHandler;
		state.revertHandler = newRevertHandler || state.revertHandler;
	};

	const setPortOffsetByBlockId = (blockId: string, offsets: { x: number, y: number }): void => {
		const ports = toValue(state.portsRectMap)?.[blockId] ?? {};

		Object.entries(ports)
			.forEach(([id, portRect]) => {
				ports[id].x = portRect.x - offsets.x;
				ports[id].y = portRect.y - offsets.y;
			});
	};

	const updatePortPosition = (blockId: DigramBlockId, portId: DiagramPortId): void => {
		const {
			portsElMap,
			blockDiagramLeft,
			blockDiagramTop,
			zoom,
			transformX,
			transformY,
		} = state;
		const hasBlock = toValue(portsElMap).has(blockId);
		const hasPort = hasBlock && toValue(portsElMap).get(blockId).has(portId);

		if (!hasBlock || !hasPort)
		{
			return;
		}

		const {
			x = 0,
			y = 0,
			width = 0,
			height = 0,
		} = portsElMap.get(blockId)?.get(portId)?.getBoundingClientRect() ?? {};

		state.portsRectMap[blockId][portId].x = (x / zoom) + toValue(transformX) - (toValue(blockDiagramLeft) / zoom);
		state.portsRectMap[blockId][portId].y = (y / zoom) + toValue(transformY) - (toValue(blockDiagramTop) / zoom);
		state.portsRectMap[blockId][portId].width = width;
		state.portsRectMap[blockId][portId].height = height;
	};

	return {
		setState,
		updateCanvasTransform,
		isExistConnection,
		addConnection,
		deleteConnectionById,
		addBlock,
		updateBlockPositionByIndex,
		updateBlock,
		deleteBlockById,
		getPortAbsolutePosition,
		findNearestPort,
		transformEventToPoint,
		setMovingBlock,
		updateMovingBlockPosition,
		resetMovingBlock,
		setHistoryHandlers,
		setPortOffsetByBlockId,
		updatePortPosition,
	};
}
