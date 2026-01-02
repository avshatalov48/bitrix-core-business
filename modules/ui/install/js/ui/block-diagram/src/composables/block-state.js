import { computed, toValue } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import { BLOCK_INDEXES } from '../constants';
import type { DiagramPort } from '../types';

export type UseBlockState = {
	isHiglitedBlock: boolean;
	isDisabled: boolean;
	updatePortsPositions: () => void;
};

export function useBlockState(block): UseBlockState
{
	const {
		highlitedBlockIds,
		isDisabledBlockDiagram,
		movingBlock,
		updatePortPosition,
	} = useBlockDiagram();

	const isHiglitedBlock = computed(() => {
		return highlitedBlockIds.value.includes(toValue(block).id);
	});

	const isDisabled = computed(() => {
		return toValue(isDisabledBlockDiagram);
	});

	const blockZindex = computed(() => {
		if (toValue(movingBlock)?.id === toValue(block).id)
		{
			return { zIndex: BLOCK_INDEXES.MOVABLE };
		}

		if (toValue(isHiglitedBlock))
		{
			return { zIndex: BLOCK_INDEXES.HIGHLITED };
		}

		return { zIndex: BLOCK_INDEXES.STANDING };
	});

	function updatePortsPositions()
	{
		([...toValue(block).ports.input, ...toValue(block).ports.output])
			.forEach((port: DiagramPort) => {
				updatePortPosition(toValue(block).id, port.id);
			});
	}

	return {
		isHiglitedBlock,
		isDisabled,
		blockZindex,
		updatePortsPositions,
	};
}
