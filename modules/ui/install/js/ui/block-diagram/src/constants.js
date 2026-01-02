import type { AnimationItemTypes } from './types';

export const PORT_SIZE = 9;
export const NODE_HEADER_HEIGHT = 46;
export const NODE_CONTENT_HEADER_HEIGHT = 14;
export const DELETE_BUTTON_SIZE = 22;
export const GRID_SIZE = 100;
export const STICKING_DISTANCE = 5;

export const HOOK_NAMES = {
	CHANGED_BLOCKS: 'changedBlocks',
	CHANGED_CONNECTIONS: 'changedConnections',
	START_DRAG_BLOCK: 'startDragBlock',
	MOVE_DRAG_BLOCK: 'moveDragBlock',
	END_DRAG_BLOCK: 'endDragBlock',
	ADD_BLOCK: 'addBlock',
	UPDATE_BLOCK: 'updateBlock',
	DELETE_BLOCK: 'deleteBlock',
	CREATE_CONNECTION: 'createConnection',
	DELETE_CONNECTION: 'deleteConnection',
	BLOCK_TRANSITION_START: 'blockTransitionStart',
	BLOCK_TRANSITION_END: 'blockTransitionEnd',
	CONNECTION_TRANSITION_START: 'connectionTransitionStart',
	CONNECTION_TRANSITION_END: 'connectionTransitionEnd',
	DROP_NEW_BLOCK: 'dropNewBlock',
};

export const PORT_TYPES: { ... } = {
	INPUT: 'input',
	OUTPUT: 'output',
};

export const NODE_TYPES: { ... } = {
	SIMPLE: 'simple',
	TRIGGER: 'trigger',
	COMPLEX: 'complex',
};

export const BLOCK_GROUP_DEFAULT_NAME = 'default';
export const CONNECTION_GROUP_DEFAULT_NAME = 'default';

export const PORT_POSITION = {
	TOP: 'top',
	BOTTOM: 'bottom',
	RIGHT: 'right',
	LEFT: 'left',
};

export const CONNECTION_VIEW_TYPE = {
	BEZIER: 'bezier',
	SMOOTHSTEP: 'smoothstep',
};

export const NEW_CONNECTION_VIEW_TYPE = {
	BEZIER: 'bezier',
	LINE: 'line',
};

export const ANIMATED_TYPES: { [string]: AnimationItemTypes } = {
	BLOCK: 'block',
	CONNECTION: 'connection',
	REMOVE_BLOCK: 'remove_block',
	REMOVE_CONNECTION: 'remove_connection',
};

export const CURSOR_TYPES = {
	EW_RESIZE: 'ew-resize',
	NS_RESIZE: 'ns-resize',
	NWSE_RESIZE: 'nwse-resize',
	NESW_RESIZE: 'nesw-resize',
};

export const BLOCK_INDEXES = {
	HIGHLITED: 4,
	MOVABLE: 3,
	STANDING: 2,
	RESIZABLE: 1,
};
