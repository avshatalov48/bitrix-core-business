import './grouped-blocks.css';
import { useBlockDiagram } from '../../composables';
import { getGroupBlockSlotName } from '../../utils';
import { BlocksQueueTransition } from '../blocks-queue-transition/blocks-queue-transition';
import type {
	GroupedBlocks as TGroupedBlocks,
	BlockGroupNames,
} from '../../types';

type GroupedBlocksSetup = {
	blockGroupNames: BlockGroupNames;
	groupedBlocks: TGroupedBlocks;
	getGroupBlockSlotName: typeof getGroupBlockSlotName;
};

// @vue/component
export const GroupedBlocks = {
	name: 'grouped-blocks',
	components: {
		BlocksQueueTransition,
	},
	setup(): GroupedBlocksSetup
	{
		const { groupedBlocks, blockGroupNames } = useBlockDiagram();

		return {
			blockGroupNames,
			groupedBlocks,
			getGroupBlockSlotName,
		};
	},
	template: `
		<BlocksQueueTransition>
			<slot
				v-for="group in blockGroupNames"
				:key="group"
				:name="getGroupBlockSlotName(group)"
				:blocks="groupedBlocks[group]"
			/>
		</BlocksQueueTransition>
	`,
};
