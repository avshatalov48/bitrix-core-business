import './connection.css';
import { computed, toValue } from 'ui.vue3';
import {
	useConnectionState,
	useContextMenu,
	useBlockDiagram,
	useLoc,
} from '../../composables';
import type { UseConnectionState } from '../../composables';
import { CONNECTION_VIEW_TYPE } from '../../constants';
import type { DiagramConnection, DiagramConnectionViewType } from '../../types';

type ConnectionSetup = {
	connectionPathInfo: Pick<UseConnectionState, 'connectionPathInfo'>;
	targetConnectionClasses: { [string]: boolean };
	barStyle: { [string]: string };
	onOpenContextMenu: () => void;
}

const TARGET_CONNECTION_CLASSES = {
	base: 'ui-block-diagram-connection__target',
	active: '--active',
};

// @vue/component
export const Connection = {
	name: 'diagram-connection',
	props: {
		/** @type DiagramConnection */
		connection: {
			type: Object,
			required: true,
		},
		/** @type DiagramConnectionViewType */
		viewType: {
			type: String,
			default: CONNECTION_VIEW_TYPE.SMOOTHSTEP,
			validator(viewType): boolean
			{
				return Object.values(CONNECTION_VIEW_TYPE).includes(viewType);
			},
		},
		barWidth: {
			type: Number,
			default: 22,
		},
		barHeight: {
			type: Number,
			default: 22,
		},
		contextMenuItems: {
			type: Array,
			default: () => ([]),
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	setup(props): ConnectionSetup
	{
		const {
			connectionPathInfo,
			isDisabled,
		} = useConnectionState({
			connection: props.connection,
			viewType: props.viewType,
		});
		const { deleteConnectionById } = useBlockDiagram();
		const loc = useLoc();
		const { isOpen, showContextMenu } = useContextMenu([
			{
				id: 'deleteConnection',
				text: loc.getMessage('UI_BLOCK_DIAGRAM_DELETE_CONNECTION_CONTEXT_MENU_ITEM'),
				onclick: () => {
					deleteConnectionById(props.connection.id);
				},
			},
		]);

		const targetConnectionClasses = computed((): { [string]: boolean } => ({
			[TARGET_CONNECTION_CLASSES.base]: true,
			[TARGET_CONNECTION_CLASSES.active]: toValue(isOpen),
		}));

		const barPosition = computed((): { [string]: number } => {
			const { x: centerX = 0, y: centerY = 0 } = toValue(connectionPathInfo).center ?? {};

			return {
				x: centerX - (props.barWidth / 2),
				y: centerY - (props.barHeight / 2),
			};
		});

		function onOpenContextMenu(event: MouseEvent): void
		{
			if (toValue(isDisabled) || props.disabled)
			{
				return;
			}

			event.preventDefault();
			showContextMenu(event);
		}

		return {
			isDisabled,
			connectionPathInfo,
			targetConnectionClasses,
			barPosition,
			onOpenContextMenu,
		};
	},
	template: `
		<svg class="ui-block-diagram-connection">
			<g class="ui-block-diagram-connection__group">
				<path
					:d="connectionPathInfo.path"
					:class="targetConnectionClasses"
					:data-test-id="$blockDiagramTestId('connectionLine', connection.id)"
				/>
				<path
					:d="connectionPathInfo.path"
					:data-test-id="$blockDiagramTestId('connectionHoveredLine', connection.id)"
					class="ui-block-diagram-connection__hovered"
					stroke="transparent"
					fill="transparent"
					@contextmenu="onOpenContextMenu"
				/>
				<foreignObject
					:x="barPosition.x"
					:y="barPosition.y"
					:width="barWidth"
					:height="barHeight"
					class="ui-block-diagram-connection__bar"
				>
					<slot :isDisabled="isDisabled || disabled" />
				</foreignObject>
			</g>
		</svg>
	`,
};
