import './block-content-stub.css';
import { computed } from 'ui.vue3';
import { Port } from '../port/port';
import { useBlockDiagram, useContextMenu, useLoc } from '../../composables';
import type { DiagramBlock } from '../../type';

type BlockContentStubSetup = {
	blockContentClassNames: { [string]: boolean };
};

const BLOCK_CONTENT_STUB_CLASS_NAMES = {
	base: 'ui-block-diagram-block-content-stub',
	highlighted: '--highlighted',
};

// @vue/component
export const BlockContentStub = {
	name: 'block-content-stub',
	components: {
		Port,
	},
	props: {
		/* @type DiagramBlock */
		block: {
			type: Object,
			required: true,
		},
		highlighted: {
			type: Boolean,
			default: false,
		},
		dragged: {
			type: Boolean,
			default: false,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	setup(props): BlockContentStubSetup
	{
		const { deleteBlockById } = useBlockDiagram();
		const loc = useLoc();
		const { showContextMenu } = useContextMenu([
			{
				id: 'deleteConnection',
				text: loc.getMessage('UI_BLOCK_DIAGRAM_DELETE_BLOCK_CONTEXT_MENU_ITEM'),
				onclick: () => {
					deleteBlockById(props.block.id);
				},
			},
		]);

		const blockContentClassNames = computed((): { [string]: boolean } => ({
			[BLOCK_CONTENT_STUB_CLASS_NAMES.base]: true,
			[BLOCK_CONTENT_STUB_CLASS_NAMES.highlighted]: props.highlighted,
		}));

		function onShowContextMenu(event: MouseEvent): void
		{
			event.preventDefault();

			if (props.disabled)
			{
				return;
			}

			const { clientX, clientY } = event;

			showContextMenu({
				clientX,
				clientY,
			});
		}

		return {
			blockContentClassNames,
			onShowContextMenu,
		};
	},
	template: `
		<div
			:class="blockContentClassNames"
			@contextmenu="onShowContextMenu"
		>
			<div class="ui-block-diagram-block-content-stub__id">
				{{ block.id }}
			</div>

			<div class="ui-block-diagram-block-content-stub__left-column">
				<div
					v-for="port in block.ports.input"
					:key="port.id"
					class="ui-block-diagram-block-content-stub__port-line"
				>
					<div class="ui-block-diagram-block-content-stub__port --left">
						<Port
							:block="block"
							:port="port"
							:styled="false"
							:portsToShow="null"
							position="left"
						/>
					</div>
				</div>
			</div>

			<div class="ui-block-diagram-block-content-stub__right-column">
				<div
					v-for="port in block.ports.output"
					:key="port.id"
					class="ui-block-diagram-block-content-stub__port-line"
				>
					<div class="ui-block-diagram-block-content-stub__port --right">
						<Port
							:block="block"
							:port="port"
							:styled="false"
							:portsToShow="null"
							position="right"
						/>
					</div>
				</div>
			</div>
		</div>
	`,
};
