import './new-connection.css';
import { useNewConnectionState } from '../../composables';
import type { UseNewConnectionState } from '../../composables';
import { NEW_CONNECTION_VIEW_TYPE } from '../../constants';
import type { DiagramNewConnectionViewType } from '../../types';

type NewConnectionSetup = {
	hasNewConnection: boolean;
	newConnectionPathInfo: Pick<UseNewConnectionState, 'newConnectionPathInfo'>;
};

// @vue/component
export const NewConnection = {
	name: 'new-connection',
	props: {
		/** @type DiagramNewConnectionViewType */
		viewType: {
			type: String,
			default: NEW_CONNECTION_VIEW_TYPE.BEZIER,
			validator(viewType): boolean {
				return Object.values(NEW_CONNECTION_VIEW_TYPE).includes(viewType);
			},
		},
	},
	setup(props): NewConnectionSetup
	{
		const {
			hasNewConnection,
			newConnectionPathInfo,
		} = useNewConnectionState({
			viewType: props.viewType,
		});

		return {
			hasNewConnection,
			newConnectionPathInfo,
		};
	},
	template: `
		<svg
			v-if="hasNewConnection"
			class="ui-block-diagram-new-connection"
		>
			<path
				:d="newConnectionPathInfo.path"
				class="ui-block-diagram-new-connection__path"
			/>
		</svg>
	`,
};
