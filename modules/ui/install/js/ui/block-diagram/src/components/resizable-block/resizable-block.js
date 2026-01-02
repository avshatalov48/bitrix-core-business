import './resizable-block.css';
import {
	toValue,
	toRefs,
	useTemplateRef,
	computed,
	watch,
	onMounted,
	onUnmounted,
} from 'ui.vue3';
import {
	useMoveableBlock,
	useResizableBlock,
	useBlockState,
	useHighlightedBlocks,
} from '../../composables';
import type { DiagramBlock } from '../../types';

// @vue/component
export const ResizableBlock = {
	name: 'resizable-block',
	props: {
		/** @type DiagramBlock */
		block: {
			type: Object,
			required: true,
		},
		minWidth: {
			type: Number,
			default: 100,
		},
		minHeight: {
			type: Number,
			default: 100,
		},
		highlighted: {
			type: Boolean,
			default: false,
		},
	},
	setup(props): {...}
	{
		const { block, minWidth, minHeight } = toRefs(props);
		const { isHiglitedBlock, isDisabled } = useBlockState(block);
		const highlightedBlocks = useHighlightedBlocks();
		const { isDragged, blockPositionStyle } = useMoveableBlock(
			useTemplateRef('blockEl'),
			block,
		);
		const {
			isResize,
			sizeBlockStyle,
			onMounted: onMountedResizableBlock,
			onUnmounted: onUnmountedResizableBlock,
		} = useResizableBlock({
			block,
			minWidth,
			minHeight,
			leftSideRef: useTemplateRef('leftSide'),
			topSideRef: useTemplateRef('topSide'),
			rightSideRef: useTemplateRef('rightSide'),
			bottomSideRef: useTemplateRef('bottomSide'),
			leftTopCornerRef: useTemplateRef('leftTopCorner'),
			rightTopCornerRef: useTemplateRef('rightTopCorner'),
			rightBottomCornerRef: useTemplateRef('rightBottomCorner'),
			leftBottomCornerRef: useTemplateRef('leftBottomCorner'),
		});

		const blockStyle = computed(() => {
			return {
				...toValue(blockPositionStyle),
				...toValue(sizeBlockStyle),
			};
		});

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

		onMounted(() => {
			onMountedResizableBlock();
		});

		onUnmounted(() => {
			highlightedBlocks.remove(props.block.id);
			onUnmountedResizableBlock();
		});

		function onMouseDownSelectBlock(): void
		{
			highlightedBlocks.clear();
			highlightedBlocks.add(props.block.id);
		}

		return {
			isHiglitedBlock,
			isDisabled,
			isResize,
			isDragged,
			blockStyle,
			onMouseDownSelectBlock,
		};
	},
	template: `
		<div
			:style="blockStyle"
			ref="blockEl"
			class="ui-block-diagram-resizable-block"
			@mousedown="onMouseDownSelectBlock"
		>
			<div class="ui-block-diagram-resizable-block__container">
				<div
					ref="leftSide"
					class="ui-block-diagram-resizable-block__left-side"
				/>
				<div
					ref="topSide"
					class="ui-block-diagram-resizable-block__top-side"
				/>
				<div
					ref="rightSide"
					class="ui-block-diagram-resizable-block__right-side"
				/>
				<div
					ref="bottomSide"
					class="ui-block-diagram-resizable-block__bottom-side"
				/>
				<div
					ref="leftTopCorner"
					class="ui-block-diagram-resizable-block__top-left-corner"
				/>
				<div
					ref="rightTopCorner"
					class="ui-block-diagram-resizable-block__top-right-corner"
				/>
				<div
					ref="rightBottomCorner"
					class="ui-block-diagram-resizable-block__bottom-right-corner"
				/>
				<div
					ref="leftBottomCorner"
					class="ui-block-diagram-resizable-block__bottom-left-corner"
				/>

				<slot
					:block="block"
					:isHighlighted="isHiglitedBlock"
					:isDragged="isDragged"
					:isResize="isResize"
					:isDisabled="isDisabled"
				/>
			</div>
		</div>
	`,
};
