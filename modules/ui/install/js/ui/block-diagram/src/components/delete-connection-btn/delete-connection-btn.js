import './delete-connection-btn.css';
import { BIcon, Outline } from 'ui.icon-set.api.vue';
import { useBlockDiagram } from '../../composables';

type DeleteConnectionBtnSetup = {
	iconSet: string,
	onDeleteConnection: () => void,
};

// @vue/component
export const DeleteConnectionBtn = {
	name: 'delete-connection-btn',
	components: {
		BIcon,
	},
	props: {
		connectionId: {
			type: String,
			required: true,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	setup(props): DeleteConnectionBtnSetup
	{
		const { deleteConnectionById } = useBlockDiagram();

		function onDeleteConnection(): void
		{
			if (props.disabled)
			{
				return;
			}

			deleteConnectionById(props.connectionId);
		}

		return {
			iconSet: Outline,
			onDeleteConnection,
		};
	},
	template: `
		<button
			class="ui-block-diagram-delete-connection-btn"
			:data-test-id="$blockDiagramTestId('connectionDeleteBtn', connectionId)"
			@click="onDeleteConnection"
		>
			<div class="ui-block-diagram-delete-connection-btn__icon-wrap">
				<BIcon
					:name="iconSet.TRASHCAN"
					:size="14"
					class="ui-block-diagram-delete-connection-btn__icon"
				/>
			</div>
		</button>
	`,
};
