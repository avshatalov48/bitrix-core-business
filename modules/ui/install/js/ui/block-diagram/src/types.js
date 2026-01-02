import { SnapshotHandler, RevertHandler } from './composables';

export type Point = {
	x: number,
	y: number,
}

export type DiagramNewConnectionViewType = 'bezier' | 'line';

export type DiagramNewConnection = {
	sourceBlockId: DiagramBlockId,
	sourcePortId: DiagramPortId,
	targetBlockId: DiagramBlockId | null;
	targetPortId: DiagramPortId | null;
	start: Point,
	end: Point,
};

export type DiagramBlockId = string;

export type DigramBlockPosition = {
	x: number;
	y: number;
}

export type DiagramBlockDimensions = {
	width: number;
	height: number;
}

export type DiagramPortPosition = 'top' | 'bottom' | 'left' | 'right';

export type DiagramPortId = string;

export type DiagramPort = {
	id: DiagramPortId;
	position: DiagramPortPosition;
}

export type DiagramPortRect = {
	x: number;
	y: number;
	width: number;
	height: number;
	position: DiagramPortPosition;
};

export type DiagramBlockPorts = {
	input: Array<DiagramPort>;
	output: Array<DiagramPort>;
}

export type DiagramBlock = {
	id: DiagramBlockId;
	position: DigramBlockPosition;
	dimensions: DiagramBlockDimensions;
	ports: DiagramBlockPorts;
};

export type GroupedBlocks = { [string]: Array<DiagramBlock> };
export type BlockGroupNames = Array<string>;

export type DiagramConnectionId = string;

export type DiagramConnectionViewType = 'smoothstep' | 'bezier' | 'line';

export type DiagramConnection = {
	id: DiagramConnectionId;
	type?: string;
	viewType?: DiagramConnectionViewType;
	sourceBlockId: DiagramBlockId;
	sourcePortId: DiagramPortId;
	targetBlockId: DiagramBlockId;
	targetPortId: DiagramPortId;
};

export type GroupedConnections = { [string]: Array<DiagramConnection> };
export type ConnectionGroupNames = Array<string>;

export type Transform = {
	x: number,
	y: number,
	zoom: number,
	viewportX: number,
	viewportY: number,
};

export type Snapshot = {
	snapshot: {...},
	revertHandler: (snapshot: Snapshot) => void,
	next: Snapshot | null,
	prev: Snapshot | null,
};

export type AnimationItemTypes = 'block' | 'connection' | 'remove_block' | 'remove_connection';

export type AnimationItem = {
	type: AnimatedItemTypes;
	item: DiagramBlock | DiagramConnection;
};

export type DragData = {
	dragData: DiagramBlock,
	dragImage: HTMLElement,
};

export type State = {
	blockDiagramRef: HTMLElement | null;
	blockDiagramTop: number;
	blockDiagramLeft: number;

	isDisabled: boolean;

	blocks: Array<DiagramBlock>;
	connections: Array<DiagramConnection>;

	portsElMap: Map<DiagramBlockId, Map<DiagramPortId, HTMLElement>>;
	portsRectMap: { [DiagramBlockId]: { [DiagramPortId]: DiagramPortRect } };

	newConnection: DiagramNewConnection | null;

	movingBlock: DiagramBlock | null;
	movingConnections: Array<Connection>;

	canvasRef: HTMLElement | null,
	transformLayoutRef: HTMLElement | null,
	canvasInstance: {...} | null,
	canvasWidth: number,
	canvasHeight: number,
	transformX: number;
	transformY: number;
	viewportX: number;
	viewportY: number;
	zoom: number;
	minZoom: number,
	maxZoom: number,

	contextMenuLayerRef: HTMLElement | null;
	targetContainerRef: HTMLElement | null;
	isOpenContextMenu: boolean;
	contextMenuInstance: null;
	positionContextMenu: {
		top: number;
		left: number;
	};

	headSnapshot: Snapshot | null;
	tailSnapshot: Snapshot | null;
	currentSnapshot: Snapshot | null;
	maxCountSnapshots: number;
	snapshotHandler: SnapshotHandler;
	revertHandler: RevertHandler;

	highlitedBlockIds: Array<DiagramBlockId>;

	animationQueue: Generator<AnimationItem | undefined> | null;
	currentAnimationItem: AnimationItem | null;
	isPauseAnimation: boolean;
	isStopAnimation: boolean;
};
