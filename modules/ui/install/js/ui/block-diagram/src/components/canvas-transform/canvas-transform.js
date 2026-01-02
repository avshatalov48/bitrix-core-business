import './canvas-transform.css';
import {
	computed,
	toValue,
	useTemplateRef,
	onMounted,
	onUnmounted,
} from 'ui.vue3';
import { useCanvasTransfrom } from '../../composables';
import type { UseCanvasTransform } from '../../composables';

type CanvasTransformSetup = {
	canvasTransformClassNames: { [string]: boolean },
	onMouseDown: Pick<UseCanvasTransform, 'onMouseDown'>,
	onMouseMove: Pick<UseCanvasTransform, 'onMouseMove'>,
	onMouseUp: Pick<UseCanvasTransform, 'onMouseUp'>,
	onWheel: Pick<UseCanvasTransform, 'onWheel'>,
};

const CANVAS_TRANSFORM_CLASS_NAMES = {
	base: 'ui-block-diagram-canvas-transform',
	dragging: '--dragging',
};

// @vue/component
export const CanvasTransform = {
	name: 'canvas-transform',
	props: {
		canvasStyle: {
			type: Object,
			required: true,
		},
		zoomSensitivity: {
			type: Number,
			default: 0.01,
		},
		zoomSensitivityMouse: {
			type: Number,
			default: 0.04,
		},
	},
	setup(props): CanvasTransformSetup
	{
		const {
			isDragging,
			onMounted: onMountedCanvasTransform,
			onUmounted: onUnmountedCanvasTransform,
			onMouseDown,
			onMouseMove,
			onMouseUp,
			onWheel,
		} = useCanvasTransfrom({
			canvasRef: useTemplateRef('canvasLayout'),
			transformLayoutRef: useTemplateRef('transformLayout'),
			canvasStyle: props.canvasStyle,
			zoomSensitivity: props.zoomSensitivity,
			zoomSensitivityMouse: props.zoomSensitivityMouse,
		});

		const canvasTransformClassNames = computed((): { [string]: boolean } => ({
			[CANVAS_TRANSFORM_CLASS_NAMES.base]: true,
			[CANVAS_TRANSFORM_CLASS_NAMES.dragging]: toValue(isDragging),
		}));

		onMounted(() => {
			onMountedCanvasTransform();
		});

		onUnmounted(() => {
			onUnmountedCanvasTransform();
		});

		return {
			canvasTransformClassNames,
			onMouseDown,
			onMouseMove,
			onMouseUp,
			onWheel,
		};
	},
	template: `
		<div
			:class="canvasTransformClassNames"
			@mousedown="onMouseDown"
			@mousemove="onMouseMove"
			@mouseup="onMouseUp"
			@wheel="onWheel"
			@contextmenu.prevent
		>
			<canvas
				ref="canvasLayout"
				class="ui-block-diagram-canvas-transform__canvas"
			/>
			<div
				ref="transformLayout"
				class="ui-block-diagram-canvas-transform__transform"
			>
				<slot/>
			</div>
		</div>
	`,
};
