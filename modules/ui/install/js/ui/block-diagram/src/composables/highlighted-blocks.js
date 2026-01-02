import { toValue } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import type { DiagramBlockId } from '../types';

export type UseHighlightedBlocks = {
	highlitedBlockIds: Array<DiagramBlockId>;
	set: (blockIds: Array<DiagramBlockId>) => void;
	add: (blockId: DiagramBlockId) => void;
	remove: (blockId: DiagramBlockId) => void;
	clear: () => void;
};

export function useHighlightedBlocks(): UseHighlightedBlocks
{
	const { highlitedBlockIds } = useBlockDiagram();

	function set(blockIds: Array<DiagramBlockId>): void
	{
		toValue(highlitedBlockIds).push(...blockIds);
	}

	function add(blockId: DiagramBlockId): void
	{
		toValue(highlitedBlockIds).push(blockId);
	}

	function remove(blockId: DiagramBlockId)
	{
		const highlitedIndx: number = highlitedBlockIds.value.indexOf(blockId);

		if (highlitedIndx > -1)
		{
			highlitedBlockIds.value.splice(highlitedIndx, 1);
		}
	}

	function clear(): void
	{
		highlitedBlockIds.value = [];
	}

	return {
		highlitedBlockIds,
		set,
		add,
		remove,
		clear,
	};
}
