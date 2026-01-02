import './history-bar.css';
import { toValue } from 'ui.vue3';
import { useHistory, useBlockDiagram } from '../../composables';
import { IconButton } from '../icon-button/icon-button';
import { Outline } from 'ui.icon-set.api.vue';

export type HistoryBarSetup = {
	hasNext: boolean,
	hasPrev: boolean,
	onNext: () => void,
	onPrev: () => void,
	Outline: {...},
};

// @vue/component
export const HistoryBar = {
	name: 'history-bar',
	components: {
		IconButton,
	},
	props: {
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	setup(props): HistoryBarSetup
	{
		const { isDisabledBlockDiagram } = useBlockDiagram();
		const {
			hasNext,
			hasPrev,
			next,
			prev,
		} = useHistory();

		function onNext(): void
		{
			if (props.disabled || toValue(isDisabledBlockDiagram))
			{
				return;
			}

			next();
		}

		function onPrev(): void
		{
			if (props.disabled || toValue(isDisabledBlockDiagram))
			{
				return;
			}

			prev();
		}

		return {
			hasNext,
			hasPrev,
			onNext,
			onPrev,
			Outline,
		};
	},
	template: `
		<div class="ui-block-diagram-histoy-bar">
			<slot>
				<IconButton
					class="ui-block-diagram-histoy-bar__prev-button"
					:icon-name="Outline.FORWARD"
					:size="22"
					:disabled="!hasPrev"
					:data-test-id="$blockDiagramTestId('historyPrevBtn')"
					@click="onPrev"
				/>
				<IconButton
					:icon-name="Outline.FORWARD"
					:size="22"
					:disabled="!hasNext"
					:data-test-id="$blockDiagramTestId('historyNextBtn')"
					@click="onNext"
				/>
			</slot>
		</div>
	`,
};
