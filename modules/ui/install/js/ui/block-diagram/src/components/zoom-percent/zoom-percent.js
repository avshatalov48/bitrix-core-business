import './zoom-percent.css';
import { computed, toValue } from 'ui.vue3';
import { useBlockDiagram } from '../../composables';

type ZoomPercentSetup = {
	percent: number,
}

// @vue/component
export const ZoomPercent = {
	name: 'zoom-percent',
	setup(props): ZoomPercentSetup
	{
		const { zoom } = useBlockDiagram();

		const percent = computed(() => {
			return ((toValue(zoom) ?? 0) * 100).toFixed(0);
		});

		return {
			percent,
		};
	},
	template: `
		<span class="ui-block-diagram-percent">{{ percent }}</span>
	`,
};
