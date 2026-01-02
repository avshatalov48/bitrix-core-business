import { debounce } from 'main.core';
import { ref, toValue } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import type { DiagramBlock } from '../types';

type UseSearchBlocksOptions = {
	searchCallback: (item: {...}, searchText: string) => boolean;
	delay: number;
};

type UseSearchBlocks = {
	seachText: string;
	foundBlocks: Array<DiagramBlock>;
	onSearchBlocks: (searchText: string) => void;
	onClearSearch: () => void;
};

const DEFAULT_OPTIONS: UseSearchBlocksOptions = {
	searchCallback: () => false,
	delay: 300,
};

export function useSearchBlocks(optionParams: UseSearchBlocksOptions): UseSearchBlocks
{
	const {
		blocks,
	} = useBlockDiagram();
	const options: UseSearchBlocksOptions = { ...DEFAULT_OPTIONS, ...optionParams };
	const seachText: string = ref('');
	const foundBlocks: Array<DiagramBlock> = ref([]);

	function onSearchBlocks(searchText: string): void
	{
		seachText.value = searchText;
		foundBlocks.value = [];

		if (toValue(seachText).trim() === '')
		{
			return;
		}

		blocks.value.forEach((block) => {
			if (options.searchCallback(block, toValue(seachText)))
			{
				foundBlocks.value.push(block);
			}
		});
	}

	function onClearSearch(): void
	{
		seachText.value = '';
		foundBlocks.value = [];
	}

	return {
		seachText,
		foundBlocks,
		onSearchBlocks: debounce(onSearchBlocks, options.delay),
		onClearSearch,
	};
}
