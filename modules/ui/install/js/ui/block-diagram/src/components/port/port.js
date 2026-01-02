import './port.css';
import {
	useTemplateRef,
	computed,
	toValue,
	onMounted,
	onUnmounted,
} from 'ui.vue3';
import { usePortState, useNewConnection } from '../../composables';
import { PORT_POSITION } from '../../constants';
import type {
	// eslint-disable-next-line no-unused-vars
	DiagramPortPosition,
	// eslint-disable-next-line no-unused-vars
	DiagramBlock,
	// eslint-disable-next-line no-unused-vars
	DiagramPort,
} from '../../types';

type PortSetup = {
	portClassNames: { [string]: boolean },
	onMouseDownPort: (event: MouseEvent) => void;
	onMouseOverPort: () => void;
	onMouseLeavePort: () => void;
};

const PORT_CLASS_NAMES = {
	base: 'ui-block-diagram-port',
	disabled: '--disabled',
	active: '--active',
};

// @vue/component
export const Port = {
	name: 'diagram-port',
	props: {
		/** @type DiagramBlock */
		block: {
			type: Object,
			required: true,
		},
		/** @type DiagramPort */
		port: {
			type: Object,
			required: true,
		},
		/** @type DiagramPortPosition */
		position: {
			type: String,
			required: true,
			validator(position): boolean
			{
				return Object.values(PORT_POSITION).includes(position);
			},
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	setup(props): PortSetup
	{
		const {
			isDisabled,
			onMountedPort,
			onUnmountedPort,
		} = usePortState({
			portRef: useTemplateRef('port'),
			block: props.block,
			port: props.port,
			position: props.position,
		});
		const {
			isSourcePort,
			onMouseDownPort,
			onMouseOverPort,
			onMouseLeavePort,
		} = useNewConnection(props.block, props.port);

		const portClassNames = computed((): { [string]: boolean } => ({
			[PORT_CLASS_NAMES.base]: true,
			[PORT_CLASS_NAMES.active]: toValue(isSourcePort),
			[PORT_CLASS_NAMES.disabled]: toValue(isDisabled),
		}));

		onMounted(() => {
			onMountedPort();
		});

		onUnmounted(() => {
			onUnmountedPort();
		});

		return {
			portClassNames,
			onMouseDownPort,
			onMouseOverPort,
			onMouseLeavePort,
		};
	},
	template: `
		<div
			ref="port"
			:class="portClassNames"
			:data-test-id="$blockDiagramTestId('port', port.id)"
			@mousedown="onMouseDownPort"
			@mouseover="onMouseOverPort"
			@mouseleave="onMouseLeavePort"
		/>
	`,
};
