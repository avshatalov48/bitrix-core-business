import { computed, toValue } from 'ui.vue3';
import { BLOCK_GROUP_DEFAULT_NAME, CONNECTION_GROUP_DEFAULT_NAME } from '../constants';
import type {
	GroupedBlocks,
	BlockGroupNames,
	GroupedConnections,
	ConnectionGroupNames,
	DiagramConnectionId,
	Transform,
} from '../types';

export type UseGetters = {
	transform: Transform;
	canvasId: string | null;
	groupedBlocks: GroupedBlocks;
	blockGroupNames: BlockGroupNames;
	groupedConnections: GroupedConnections;
	connectionGroupNames: ConnectionGroupNames;
	isAnimate: boolean;
	isDisabledBlockDiagram: boolean;
};

export function useGetters(state): UseGetters
{
	const transform = computed((): Transform => ({
		x: state.transformX,
		y: state.transformY,
		zoom: state.zoom,
		viewportX: state.viewportX,
		viewportY: state.viewportY,
	}));

	const canvasId = computed((): string | null => {
		return state.canvasRef?.canvasId ?? null;
	});

	const groupedBlocks = computed((): GroupedBlocks => {
		return state.blocks
			.reduce((acc, block) => {
				const type = block?.type ?? BLOCK_GROUP_DEFAULT_NAME;

				if (type in acc)
				{
					acc[type] = [...acc[type], block];
				}
				else
				{
					acc[type] = [block];
				}

				return acc;
			}, { [BLOCK_GROUP_DEFAULT_NAME]: [] });
	});

	const blockGroupNames = computed((): BlockGroupNames => {
		return Object.keys(toValue(groupedBlocks));
	});

	const groupedConnections = computed((): GroupedConnections => {
		return state.connections
			.reduce((acc, connection) => {
				const type = connection?.type ?? CONNECTION_GROUP_DEFAULT_NAME;

				if (type in acc)
				{
					acc[type] = [...acc[type], connection];
				}
				else
				{
					acc[type] = [connection];
				}

				return acc;
			}, { [CONNECTION_GROUP_DEFAULT_NAME]: [] });
	});

	const connectionGroupNames = computed((): ConnectionGroupNames => {
		return Object.keys(toValue(groupedConnections));
	});

	const isAnimate = computed((): boolean => {
		return state.animationQueue !== null;
	});

	const isDisabledBlockDiagram = computed((): boolean => {
		return state.isDisabled || toValue(isAnimate);
	});

	return {
		transform,
		canvasId,
		groupedBlocks,
		blockGroupNames,
		groupedConnections,
		connectionGroupNames,
		isAnimate,
		isDisabledBlockDiagram,
	};
}
