import './block-diagram.css';
import { onMounted, onUnmounted, useTemplateRef, computed, ref, toValue } from 'ui.vue3';
import { CanvasTransform } from '../canvas-transform/canvas-transform';
import { Connection } from '../connection/connection';
import { DeleteConnectionBtn } from '../delete-connection-btn/delete-connection-btn';
import { ContextMenuLayout } from '../context-menu-layout/context-menu-layout';
import { GroupedBlocks } from '../grouped-blocks/grouped-blocks';
import { GroupedConnections } from '../grouped-connections/grouped-connections';
import { MoveableBlock } from '../moveable-block/moveable-block';
import { BlockContentStub } from '../block-content-stub/block-content-stub';
import {
	useHistory,
	useBlockDiagram,
	useModelValue,
	useWatchProps,
	useRegisterHooks,
	useInitAppElements,
	useDragAndDrop,
} from '../../composables';
import { getGroupBlockSlotName, getGroupConnectionSlotName } from '../../utils';

import { HOOK_NAMES, CURSOR_TYPES } from '../../constants';
import type {
	BlockGroupNames,
	ConnectionGroupNames,
} from '../../types';

type BlockDiagramSetup = {
	blockGroupNames: BlockGroupNames;
	connectionGroupNames: ConnectionGroupNames;
	getGroupBlockSlotName: typeof getGroupBlockSlotName;
	getGroupConnectionSlotName: typeof getGroupConnectionSlotName;
};

const UI_CANVAS_GRID_COLOR = '#A1B8D9';
const UI_CANVAS_BACKGROUND_COLOR = '#ECF0F2';

const BLOCK_DIAGRAM_CLASS_NAMES = {
	base: 'ui-block-diagram',
	ewResize: '--cursor-ew-resize',
	nsResize: '--cursor-ns-resize',
	nwSeResize: '--cursor-nwse-resize',
	neSwResize: '--cursor-nesw-resize',
	grabbing: '--grabbing',
};

// @vue/component
export const BlockDiagram = {
	name: 'block-diagram',
	components: {
		CanvasTransform,
		ContextMenuLayout,
		GroupedBlocks,
		GroupedConnections,
		Connection,
		DeleteConnectionBtn,
		MoveableBlock,
		BlockContentStub,
	},
	props: {
		/** @type Array<DiagramBlock> */
		blocks: {
			type: Array,
			required: true,
		},
		/** @type Array<DiagramConnection> */
		connections: {
			type: Array,
			required: true,
		},
		canvasStyle: {
			type: Object,
			default: () => ({
				style: 'grid',
				size: 64,
				gridColor: UI_CANVAS_GRID_COLOR,
				backgroundColor: UI_CANVAS_BACKGROUND_COLOR,
			}),
		},
		zoomSensitivity: {
			type: Number,
			default: 0.01,
		},
		zoomSensitivityMouse: {
			type: Number,
			default: 0.04,
		},
		zoom: {
			type: Number,
			default: 1,
		},
		minZoom: {
			type: Number,
			default: 0.2,
		},
		maxZoom: {
			type: Number,
			default: 4,
		},
		historyHooks: {
			type: Array,
			default: () => ([
				HOOK_NAMES.END_DRAG_BLOCK,
				HOOK_NAMES.ADD_BLOCK,
				HOOK_NAMES.DELETE_BLOCK,
				HOOK_NAMES.CREATE_CONNECTION,
				HOOK_NAMES.DELETE_CONNECTION,
			]),
		},
		snapshotHandler: {
			type: Function,
			default: null,
		},
		revertHandler: {
			type: Function,
			default: null,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	emits: [
		'update:blocks',
		'update:connections',
		HOOK_NAMES.CHANGED_BLOCKS,
		HOOK_NAMES.CHANGED_CONNECTIONS,
		HOOK_NAMES.START_DRAG_BLOCK,
		HOOK_NAMES.MOVE_DRAG_BLOCK,
		HOOK_NAMES.END_DRAG_BLOCK,
		HOOK_NAMES.ADD_BLOCK,
		HOOK_NAMES.UPDATE_BLOCK,
		HOOK_NAMES.DELETE_BLOCK,
		HOOK_NAMES.CREATE_CONNECTION,
		HOOK_NAMES.DELETE_CONNECTION,
		HOOK_NAMES.BLOCK_TRANSITION_START,
		HOOK_NAMES.BLOCK_TRANSITION_END,
		HOOK_NAMES.CONNECTION_TRANSITION_START,
		HOOK_NAMES.CONNECTION_TRANSITION_END,
		HOOK_NAMES.DROP_NEW_BLOCK,
	],
	setup(props, { emit }): BlockDiagramSetup
	{
		const {
			blockGroupNames,
			connectionGroupNames,
			cursorType,
		} = useBlockDiagram(props);
		const initAppElements = useInitAppElements({
			blockDiagramRef: useTemplateRef('blockDiagram'),
		});
		const { makeSnapshot } = useHistory();
		const { dispose: disposeModelValue } = useModelValue(emit);
		const { dispose: disposeWatchProps } = useWatchProps(props);
		const { dispose: disposeRegisterHooks } = useRegisterHooks(
			{
				...Object.entries(HOOK_NAMES)
					.reduce((acc, [name, hookName]) => {
						acc[hookName] = (...args) => {
							emit(hookName, ...args);
						};

						return acc;
					}, {}),
			},
			{
				...props.historyHooks.reduce((acc, hookName) => {
					acc[hookName] = () => makeSnapshot();

					return acc;
				}, {}),
			},
		);
		const { onDrop } = useDragAndDrop();

		const isGrabbing = ref(false);

		const blockDiagramClassNames = computed(() => ({
			[BLOCK_DIAGRAM_CLASS_NAMES.base]: true,
			[BLOCK_DIAGRAM_CLASS_NAMES.grabbing]: isGrabbing.value,
			[BLOCK_DIAGRAM_CLASS_NAMES.ewResize]: toValue(cursorType) === CURSOR_TYPES.EW_RESIZE,
			[BLOCK_DIAGRAM_CLASS_NAMES.nsResize]: toValue(cursorType) === CURSOR_TYPES.NS_RESIZE,
			[BLOCK_DIAGRAM_CLASS_NAMES.nwSeResize]: toValue(cursorType) === CURSOR_TYPES.NWSE_RESIZE,
			[BLOCK_DIAGRAM_CLASS_NAMES.neSwResize]: toValue(cursorType) === CURSOR_TYPES.NESW_RESIZE,
		}));

		onMounted(() => {
			initAppElements.onMountedAppElements();
		});

		onUnmounted(() => {
			disposeModelValue();
			disposeWatchProps();
			disposeRegisterHooks();
			initAppElements.onUnmountedAppElements();
		});

		function onDragEnter(event)
		{
			isGrabbing.value = true;
		}

		function onDragLeave(event)
		{
			isGrabbing.value = false;
		}

		function onDragDrop(event): void
		{
			isGrabbing.value = false;
			onDrop(event);
		}

		return {
			blockDiagramClassNames,
			blockGroupNames,
			connectionGroupNames,
			getGroupBlockSlotName,
			getGroupConnectionSlotName,
			onDragDrop,
			onDragEnter,
			onDragLeave,
		};
	},
	template: `
		<div
			:class="blockDiagramClassNames"
			ref="blockDiagram"
			@dragover.prevent
			@dragenter="onDragEnter"
			@dragleave="onDragLeave"
			@drop="onDragDrop"
		>
			<CanvasTransform
				:canvasStyle="canvasStyle"
				:zoomSensitivity="zoomSensitivity"
				:zoomSensitivityMouse="zoomSensitivityMouse"
			>
				<ContextMenuLayout>
					<GroupedConnections>
						<template
							v-for="groupName in connectionGroupNames"
							#[getGroupConnectionSlotName(groupName)]="{ connections }"
							:key="groupName"
						>
							<slot
								v-for="connection in connections"
								:name="getGroupConnectionSlotName(groupName)"
								:key="connection.id"
								:connection="connection"
							>
								<Connection
									:connection="connection"
									:key="connection.id"
								>
									<template #default="{ isDisabled }">
										<DeleteConnectionBtn 
											:connectionId="connection.id"
											:disabled="isDisabled"
										/>
									</template>
								</Connection>
							</slot>
						</template>

						<template #new-connection>
							<slot name="new-connection"/>
						</template>
					</GroupedConnections>
					<GroupedBlocks>
						<template
							v-for="groupName in blockGroupNames"
							#[getGroupBlockSlotName(groupName)]="{ blocks }"
							:key="groupName"
						>
							<slot
								v-for="block in blocks"
								:name="getGroupBlockSlotName(groupName)"
								:key="block.id"
								:block="block"
							>
								<MoveableBlock
									:block="block"
									:key="block.id"
								>
									<template #default="{ isHighlighted, isDragged, isDisabled }">
										<BlockContentStub
											:block="block"
											:highlighted="isHighlighted"
											:dragged="isDragged"
											:disabled="isDisabled"
										/>
									</template>
								</MoveableBlock>
							</slot>
						</template>
					</GroupedBlocks>
				</ContextMenuLayout>
			</CanvasTransform>
		</div>
	`,
};
