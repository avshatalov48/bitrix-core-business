import { toValue, computed } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import { getBeziePath, getSmoothStepPath } from '../utils';
import type { PathInfo } from '../utils';
import { CONNECTION_VIEW_TYPE } from '../constants';
// eslint-disable-next-line no-unused-vars
import type { DiagramConnection, DiagramConnectionId } from '../types';

type PortPosition = {
	x: number;
	y: number;
};

type ConnectionPortPosition = {
	sourcePort: PortPosition;
	targetPort: PortPosition;
};

type UseConnectionState = {
	connectionPortsPosition: ConnectionPortPosition | null;
	connectionPathInfo: PathInfo;
	isDisabled: boolean;
}

// eslint-disable-next-line max-lines-per-function
export function useConnectionState(options): UseConnectionState
{
	const {
		portsRectMap,
		isDisabledBlockDiagram,
	} = useBlockDiagram();
	const { connection, viewType } = options;

	const connectionPortsPosition = computed((): ConnectionPortPosition | null => {
		const {
			sourceBlockId,
			sourcePortId,
			targetBlockId,
			targetPortId,
		} = toValue(connection);

		const hasSourceBlockId = sourceBlockId in toValue(portsRectMap);
		const hasSourcePortId = hasSourceBlockId && (sourcePortId in toValue(portsRectMap)[sourceBlockId]);
		const hasTargetBlockId = targetBlockId in toValue(portsRectMap);
		const hasTargetPortId = hasTargetBlockId && (targetPortId in toValue(portsRectMap)[targetBlockId]);

		if (
			!hasSourceBlockId
			|| !hasSourcePortId
			|| !hasTargetBlockId
			|| !hasTargetPortId
		)
		{
			return null;
		}

		const {
			x: sourceX,
			y: sourceY,
			width: sourceWidth,
			height: sourceHeight,
			position: sourcePosition,
		} = toValue(portsRectMap)[sourceBlockId][sourcePortId];
		const {
			x: targetX,
			y: targetY,
			width: targetWidth,
			height: targetHeight,
			position: targetPosition,
		} = toValue(portsRectMap)[targetBlockId][targetPortId];

		return {
			sourcePort: {
				x: sourceX + (sourceWidth / 2),
				y: sourceY + (sourceHeight / 2),
				position: sourcePosition,
			},
			targetPort: {
				x: targetX + (targetWidth / 2),
				y: targetY + (targetHeight / 2),
				position: targetPosition,
			},
		};
	});

	const connectionPathInfo = computed((): PathInfo => {
		if (toValue(connectionPortsPosition) === null)
		{
			return {
				path: '',
				center: {
					x: 0,
					y: 0,
				},
			};
		}

		if (toValue(viewType) === CONNECTION_VIEW_TYPE.BEZIER)
		{
			return getBeziePath(
				toValue(connectionPortsPosition).sourcePort,
				toValue(connectionPortsPosition).targetPort,
			);
		}

		return getSmoothStepPath({
			sourceX: toValue(connectionPortsPosition).sourcePort.x,
			sourceY: toValue(connectionPortsPosition).sourcePort.y,
			sourcePosition: toValue(connectionPortsPosition).sourcePort.position,
			targetX: toValue(connectionPortsPosition).targetPort.x,
			targetY: toValue(connectionPortsPosition).targetPort.y,
			targetPosition: toValue(connectionPortsPosition).targetPort.position,
			borderRadius: 10,
			offset: 30,
		});
	});

	const isDisabled = computed((): boolean => {
		return toValue(isDisabledBlockDiagram);
	});

	return {
		connectionPortsPosition,
		connectionPathInfo,
		isDisabled,
	};
}
