import './canvas-map.css';
import { Type } from 'main.core';
import { toValue, ref, computed, toRefs, useTemplateRef } from 'ui.vue3';
import { useBlockDiagram, useCanvas } from '../../composables';
import type { DiagramBlock } from '../../types';

type CursorRectPosition = {
	x: number;
	y: number;
	width: number;
	height: number;
}

type CanvasMapSetup = {
	mapWidth: number;
	mapHeight: number;
	preparedBlock: Array<DiagramBlock>,
	canvasMapStyle: { [string]: string };
	startDiagramX: number;
	startDiagramY: number;
	scaleMap: number;
	cursorRectPosition: CursorRectPosition;
	onMapMouseDown: (event: MouseEvent) => void;
	onMapMouseMove: (event: MouseEvent) => void;
	onMapMouseUp: (event: MouseEvent) => void;
};

const CURSOR_RECT_STROKE_WIDTH: number = 2;

const FIRST_EMPTY_BLOCK_ID = 'firstCanvasBlock';
const LAST_EMPTY_BLOCK_ID = 'lastCanvasBlock';

// @vue/component
export const CanvasMap = {
	name: 'canvas-map',
	props: {
		mapWidth: {
			type: Number,
			default: 310,
		},
		mapHeight: {
			type: Number,
			default: 183,
		},
	},
	// eslint-disable-next-line max-lines-per-function
	setup(props, { emit }): CanvasMapSetup
	{
		const {
			blocks,
			canvasWidth,
			canvasHeight,
			transformX,
			transformY,
			zoom,
		} = useBlockDiagram();
		const { setCamera } = useCanvas();
		const { mapWidth, mapHeight } = toRefs(props);
		const mapEl = useTemplateRef('map');

		let mapElLeft: number = 0;
		let mapElTop: number = 0;

		const isDragged = ref(false);

		const canvasMapStyle = computed((): { [string]: string } => ({
			width: `${toValue(mapWidth)}px`,
			height: `${toValue(mapHeight)}px`,
		}));

		const preparedBlock = computed((): Array<DiagramBlock> => {
			if (toValue(blocks).length === 1)
			{
				return [
					{
						id: FIRST_EMPTY_BLOCK_ID,
						position: {
							x: 0,
							y: 0,
						},
						dimensions: {
							width: 0,
							height: 0,
						},
					},
					{ ...toValue(blocks)[0] },
					{
						id: LAST_EMPTY_BLOCK_ID,
						position: {
							x: props.mapWidth,
							y: props.mapHeight,
						},
						dimensions: {
							width: 0,
							height: 0,
						},
					},
				];
			}

			return toValue(blocks);
		});

		const startDiagramX = computed((): number => {
			return toValue(preparedBlock)
				.reduce((min, block) => Math.min(min, block.position.x), Infinity);
		});

		const startDiagramY = computed((): number => {
			return toValue(preparedBlock)
				.reduce((min, block) => Math.min(min, block.position.y), Infinity);
		});

		const diagramWidth = computed((): number => {
			if (!Type.isArrayFilled(toValue(blocks)))
			{
				return props.mapWidth;
			}

			const maxX: number = toValue(preparedBlock).reduce(
				(max, block): number => Math.max(max, block.position.x + block.dimensions.width),
				-Infinity,
			);

			return maxX - toValue(startDiagramX);
		});

		const diagramHeight = computed((): number => {
			if (!Type.isArrayFilled(toValue(blocks)))
			{
				return props.mapHeight;
			}

			const maxY: number = toValue(preparedBlock).reduce(
				(max, block) => Math.max(max, block.position.y + block.dimensions.height),
				-Infinity,
			);

			return maxY - toValue(startDiagramY);
		});

		const scaleMap = computed((): number => {
			return Math.min(
				toValue(mapWidth) / toValue(diagramWidth),
				toValue(mapHeight) / toValue(diagramHeight),
			);
		});

		const cursorRectWidth = computed((): number => {
			return toValue(canvasWidth) * toValue(scaleMap) / toValue(zoom);
		});

		const cursorRectHeight = computed((): number => {
			return toValue(canvasHeight) * toValue(scaleMap) / toValue(zoom);
		});

		const cursorRectPosition = computed((): CursorRectPosition => {
			let width = toValue(cursorRectWidth);
			let height = toValue(cursorRectHeight);

			let x = (toValue(transformX) - toValue(startDiagramX)) * toValue(scaleMap);
			let y = (toValue(transformY) - toValue(startDiagramY)) * toValue(scaleMap);

			[x, width] = width > toValue(mapWidth)
				? [1, toValue(mapWidth) - CURSOR_RECT_STROKE_WIDTH]
				: [x, width];

			[y, height] = height > toValue(mapHeight)
				? [1, toValue(mapHeight) - CURSOR_RECT_STROKE_WIDTH]
				: [y, height];

			x = x < 0 ? 1 : x;
			y = y < 0 ? 1 : y;

			x = (x + width) > toValue(mapWidth)
				? toValue(mapWidth) - width - CURSOR_RECT_STROKE_WIDTH
				: x;
			y = (y + height) > toValue(mapHeight)
				? toValue(mapHeight) - height - CURSOR_RECT_STROKE_WIDTH
				: y;

			return {
				x,
				y,
				width,
				height,
			};
		});

		function updateCameraPosition(event: MouseEvent): void
		{
			const x: number = event.clientX - mapElLeft;
			const y: number = event.clientY - mapElTop;

			const canvasX: number = x / toValue(scaleMap) + toValue(startDiagramX);
			const canvasY: number = y / toValue(scaleMap) + toValue(startDiagramY);

			setCamera({
				x: canvasX - (toValue(canvasWidth) / toValue(zoom) / 2),
				y: canvasY - (toValue(canvasHeight) / toValue(zoom) / 2),
				zoom: toValue(zoom),
				viewportX: 0,
				viewportY: 0,
			});
		}

		function onMapMouseDown(event: MouseEvent): void
		{
			isDragged.value = true;

			if (toValue(mapEl))
			{
				const { left, top } = toValue(mapEl).getBoundingClientRect();
				[mapElLeft, mapElTop] = [left, top];
			}

			updateCameraPosition(event);
		}

		function onMapMouseMove(event: MouseEvent): void
		{
			if (!toValue(isDragged))
			{
				return;
			}

			updateCameraPosition(event);
		}

		function onMapMouseUp(event: MouseEvent): void
		{
			isDragged.value = false;
			updateCameraPosition(event);
		}

		return {
			preparedBlock,
			canvasMapStyle,
			startDiagramX,
			startDiagramY,
			scaleMap,
			cursorRectPosition,
			onMapMouseDown,
			onMapMouseMove,
			onMapMouseUp,
		};
	},
	template: `
		<div :style="canvasMapStyle">
			<svg
				:width="mapWidth"
				:height="mapHeight"
				ref="map"
				class="ui-block-diagram-canvas-map"
				@mousedown="onMapMouseDown"
				@mousemove="onMapMouseMove"
				@mouseup="onMapMouseUp"
			>
				<rect
					v-for="block in preparedBlock"
					:key="block.id"
					:x="(block.position.x - startDiagramX) * scaleMap"
					:y="(block.position.y - startDiagramY) * scaleMap"
					:width="block.dimensions.width * scaleMap"
					:height="block.dimensions.height * scaleMap"
					:rx="2"
					class="ui-block-diagram-canvas-map__block"
				/>
				<rect
					:x="cursorRectPosition.x"
					:y="cursorRectPosition.y"
					:width="cursorRectPosition.width"
					:height="cursorRectPosition.height"
					:rx="3"
					class="ui-block-diagram-canvas-map__cursor"
				/>
			</svg>
		</div>
	`,
};
