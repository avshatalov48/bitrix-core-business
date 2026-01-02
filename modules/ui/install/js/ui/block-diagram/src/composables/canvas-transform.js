import { ref, toValue, markRaw } from 'ui.vue3';
import { Dom } from 'main.core';
import { useBlockDiagram } from './block-diagram';
import { Canvas, Grid } from '../utils';

export type UseCanvasTransform = {
	isDragging: boolean,
	onMounted: () => void,
	onUnmounted: () => void,
	onMouseDown: (event: MouseEvent) => void,
};

export type UseCanvasTransformOptions = {
	canvasRef: HTMLElement,
	transformLayoutRef: HTMLElement,
	canvasStyle: newCanvasStyle,
};

const CANVAS_STYLE_DEFAULT_OPTIONS: {...} = {
	grid: {
		options: {
			size: 64,
			gridColor: '#A1B8D9',
			backgroundColor: '#ECF0F2',
			zoomStep: 4,
			zoomSteps: [
				{
					zoom: 1.1,
					size: 64,
					zoomStep: 4,
				},
				{
					zoom: 1,
					size: 64,
					zoomStep: 4,
				},
				{
					zoom: 0.99,
					size: 64,
					zoomStep: 4,
				},
				{
					zoom: 0.5,
					size: 64 * 5,
					zoomStep: 0.5,
				},
				{
					zoom: 0.25,
					size: 64 * 25,
					zoomStep: 0.25,
				},
				{
					zoom: 0.125,
					size: 64 * 125,
					zoomStep: 0.125,
				},
			],
		},
		instance: Grid,
	},
};

// eslint-disable-next-line max-lines-per-function
export function useCanvasTransfrom(options: UseCanvasTransformOptions): UseCanvasTransform
{
	const {
		canvasRef: newCanvasRef,
		transformLayoutRef: newTransformLayoutRef,
		canvasStyle: newCanvasStyle,
		zoomSensitivity,
		zoomSensitivityMouse,
	} = options;

	const {
		isDisabledBlockDiagram,
		transformX,
		transformY,
		zoom,
		minZoom,
		maxZoom,
		canvasRef,
		transformLayoutRef,
		canvasWidth,
		canvasHeight,
		canvasInstance,
	} = useBlockDiagram();

	const dragOn = ref(false);
	const isDragging = ref(false);
	const zooming = ref(false);

	let requestAnimationId = null;

	function getCanvasStyleOptions(canvasStyle: CanvasStyle): CanvasStyle | null
	{
		if (canvasStyle && canvasStyle.style in CANVAS_STYLE_DEFAULT_OPTIONS)
		{
			return {
				instance: CANVAS_STYLE_DEFAULT_OPTIONS[canvasStyle.style].instance,
				options: {
					...CANVAS_STYLE_DEFAULT_OPTIONS[canvasStyle.style].options,
					...canvasStyle,
				},
			};
		}

		return null;
	}

	function onMounted(): void
	{
		canvasRef.value = newCanvasRef;
		transformLayoutRef.value = newTransformLayoutRef;

		canvasInstance.value = markRaw(new Canvas({
			canvas: toValue(newCanvasRef),
			canvasStyle: getCanvasStyleOptions(newCanvasStyle),
			minZoom: toValue(minZoom),
			maxZoom: toValue(maxZoom),
		}));

		toValue(canvasInstance).camera.setChangeTransformCallback((payload) => {
			transformX.value = payload.x;
			transformY.value = payload.y;
			zoom.value = payload.zoom;
			canvasWidth.value = payload.width;
			canvasHeight.value = payload.height;
		});

		render();
	}

	function onUnmounted(): void
	{
		toValue(canvasInstance)?.destroy();
		cancelAnimationFrame(requestAnimationId);
	}

	function onMouseDown(event: MouseEvent): void
	{
		if (toValue(isDisabledBlockDiagram))
		{
			return;
		}

		dragOn.value = true;
		toValue(canvasInstance)?.setCameraPositionByMouseDown(event);
	}

	function onMouseMove(event: MouseEvent): void
	{
		if (!toValue(dragOn) || toValue(isDisabledBlockDiagram))
		{
			return;
		}

		if (event.buttons !== 1)
		{
			dragOn.value = false;
			isDragging.value = false;

			return;
		}

		isDragging.value = true;
		toValue(canvasInstance)?.setCameraPositionByMouseMove(event);
	}

	function onMouseUp(): void
	{
		dragOn.value = false;
		isDragging.value = false;
	}

	function onWheel(event: WheelEvent): void
	{
		event.preventDefault();

		if (toValue(isDisabledBlockDiagram))
		{
			return;
		}

		const isTrackpad = event.wheelDeltaY
			? event.wheelDeltaY === -3 * event.deltaY
			: event.deltaMode === 0;

		if (event.ctrlKey)
		{
			const zoomChange = isTrackpad
				? -event.deltaY * toValue(zoomSensitivity)
				: -Math.sign(event.deltaY) * toValue(zoomSensitivityMouse);

			zooming.value = true;
			toValue(canvasInstance)?.setCameraZoomByWheel(event, zoomChange);

			setTimeout(() => {
				zooming.value = false;
			}, 200);
		}
		else
		{
			toValue(canvasInstance)?.setCameraPositionByWheel(event);
		}
	}

	function render(): void
	{
		if (canvasInstance)
		{
			toValue(canvasInstance)?.render();
			const viewMatrix = toValue(canvasInstance).viewMatrix;

			Dom.style(
				toValue(transformLayoutRef),
				'transform',
				`
					matrix(
						${viewMatrix[0]}, 0, 0, ${viewMatrix[4]}, ${viewMatrix[6]}, ${viewMatrix[7]}
					)
				`,
			);
		}

		requestAnimationId = requestAnimationFrame(render);
	}

	return {
		isDragging,
		onMounted,
		onUnmounted,
		onMouseDown,
		onMouseMove,
		onMouseUp,
		onWheel,
	};
}
