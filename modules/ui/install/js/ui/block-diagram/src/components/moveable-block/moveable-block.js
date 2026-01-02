import './moveable-block.css';
import { toRefs, useTemplateRef, watch, onUnmounted, computed, toValue } from 'ui.vue3';
import { useMoveableBlock, useBlockState, useHighlightedBlocks } from '../../composables';
// eslint-disable-next-line no-unused-vars
import type { DiagramBlock } from '../../types';

type MoveableBlockSetup = {
	isDragged: boolean;
	isHiglitedBlock: boolean;
	blockPositionStyle: { [string]: string };
	isDisabled: boolean;
	onMouseDownSelectBlock: () => void;
};

// @vue/component
export const MoveableBlock = {
	name: 'moveable-block',
	props: {
		/** @type DiagramBlock */
		block: {
			type: Object,
			required: true,
		},
		highlighted: {
			type: Boolean,
			default: false,
		},
	},
	setup(props): MoveableBlockSetup
	{
		const { block } = toRefs(props);
		const {
			blockZindex,
			isHiglitedBlock,
			isDisabled,
		} = useBlockState(block);
		const highlightedBlocks = useHighlightedBlocks();
		const { isDragged, blockPositionStyle } = useMoveableBlock(
			useTemplateRef('blockEl'),
			block,
		);

		watch(() => props.highlighted, (value) => {
			if (value)
			{
				highlightedBlocks.add(props.block.id);
			}
			else
			{
				highlightedBlocks.remove(props.block.id);
			}
		});

		const blockStyle = computed((): { [string]: string } => ({
			...toValue(blockPositionStyle),
			...toValue(blockZindex),
		}));

		onUnmounted(() => {
			highlightedBlocks.remove(props.block.id);
		});

		function onMouseDownSelectBlock(): void
		{
			highlightedBlocks.clear();
			highlightedBlocks.add(props.block.id);
		}

		return {
			isHiglitedBlock,
			isDisabled,
			isDragged,
			blockStyle,
			blockZindex,
			blockPositionStyle,
			onMouseDownSelectBlock,
		};
	},
	template: `
		<div
			class="ui-block-diagram-moveable-block"
			:style="blockStyle"
			ref="blockEl"
			:data-test-id="$blockDiagramTestId('block', block.id)"
			@mousedown="onMouseDownSelectBlock"
		>
			<slot
				:block="block"
				:isHighlighted="isHiglitedBlock"
				:isDragged="isDragged"
				:isDisabled="isDisabled"
			/>
		</div>
	`,
};
