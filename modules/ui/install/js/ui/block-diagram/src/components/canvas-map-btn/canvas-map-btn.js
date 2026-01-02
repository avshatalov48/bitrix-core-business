import './canvas-map-btn.css';
import { Type } from 'main.core';
import { toValue, computed } from 'ui.vue3';
import { useBlockDiagram } from '../../composables';
import type { DiagramBlock } from '../../types';

type CanvasMapBtnSetup = {
	blocks: Array<DiagramBlock>,
	btnStyle: { [string]: string};
	startDiagramX: number;
	startDiagramY: number;
	scaleButton: number;
};

// @vue/component
export const CanvasMapBtn = {
	name: 'canvas-map-btn',
	props: {
		width: {
			type: Number,
			default: 75,
		},
		height: {
			type: Number,
			default: 32,
		},
	},
	setup(props): CanvasMapBtnSetup
	{
		const { blocks } = useBlockDiagram();

		const btnStyle = computed(() => ({
			width: `${props.width}px`,
			height: `${props.height}px`,
		}));

		const startDiagramX = computed((): number => {
			return toValue(blocks)
				.reduce((min, block) => Math.min(min, block.position.x), Infinity);
		});

		const startDiagramY = computed((): number => {
			return toValue(blocks)
				.reduce((min, block) => Math.min(min, block.position.y), Infinity);
		});

		const diagramWidth = computed((): number => {
			if (!Type.isArrayFilled(toValue(blocks)))
			{
				return props.width;
			}

			const maxX: number = toValue(blocks).reduce(
				(max, block): number => Math.max(max, block.position.x + block.dimensions.width),
				-Infinity,
			);

			return maxX - toValue(startDiagramX);
		});

		const diagramHeight = computed((): number => {
			if (!Type.isArrayFilled(toValue(blocks)))
			{
				return props.height;
			}

			const maxY: number = toValue(blocks).reduce(
				(max, block) => Math.max(max, block.position.y + block.dimensions.height),
				-Infinity,
			);

			return maxY - toValue(startDiagramY);
		});

		const scaleButton = computed((): number => {
			return Math.min(props.width / toValue(diagramWidth), props.height / toValue(diagramHeight));
		});

		return {
			blocks,
			btnStyle,
			startDiagramX,
			startDiagramY,
			scaleButton,
		};
	},
	template: `
		<button
			:style="btnStyle"
			class="ui-block-diagram-canvas-map-btn"
		>
			<svg
				:width="width"
				:height="height"
				class="ui-block-diagram-canvas-map-btn__icon"
			>
				<rect
					v-for="block in blocks"
					:key="block.id"
					:x="(block.position.x - startDiagramX) * scaleButton"
					:y="(block.position.y - startDiagramY) * scaleButton"
					:width="block.dimensions.width * scaleButton"
					:height="block.dimensions.height * scaleButton"
					:rx="1"
					class="ui-block-diagram-canvas-map-btn__rect"
				/>
			</svg>
		</button>
	`,
};
