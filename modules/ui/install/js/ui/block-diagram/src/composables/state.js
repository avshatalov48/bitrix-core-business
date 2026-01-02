import { markRaw } from 'ui.vue3';
import type { State } from '../types';

export function useState(): State
{
	return {
		blockDiagramRef: null,
		blockDiagramTop: 0,
		blockDiagramLeft: 0,

		cursorType: 'default',

		isResizing: false,
		isDisabled: false,

		blocks: [],
		connections: [],

		portsElMap: markRaw(new Map()),
		portsRectMap: {},

		newConnection: null,

		movingBlock: null,
		movingConnections: [],

		resizingBlock: null,

		canvasRef: null,
		transformLayoutRef: null,
		canvasInstance: null,
		canvasWidth: 0,
		canvasHeight: 0,
		transformX: 0,
		transformY: 0,
		viewportX: 0,
		viewportY: 0,
		zoom: 1,
		minZoom: 0.2,
		maxZoom: 4,

		contextMenuLayerRef: null,
		targetContainerRef: null,
		isOpenContextMenu: false,
		contextMenuInstance: null,
		positionContextMenu: {
			top: 0,
			left: 0,
		},

		historyCurrentState: markRaw({
			blocks: [],
			connections: [],
		}),

		headSnapshot: null,
		tailSnapshot: null,
		currentSnapshot: null,
		maxCountSnapshots: 20,
		snapshotHandler: null,
		revertHandler: null,

		highlitedBlockIds: [],

		animationQueue: null,
		currentAnimationItem: null,
		isPauseAnimation: false,
		isStopAnimation: false,
	};
}
