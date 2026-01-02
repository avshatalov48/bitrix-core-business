import './context-menu-layout.css';
import { computed, toValue } from 'ui.vue3';
import { useBlockDiagram } from '../../composables';
import type { UseBlockDiagran } from '../../composables';

export type ContextMenuLayoutSetup = {
	instance: UseBlockDiagran,
	targetContainerStyle: { [string]: string },
};

// @vue/component
export const ContextMenuLayout = {
	name: 'ContextMenuLayout',
	setup(): ContextMenuLayoutSetup
	{
		const instance = useBlockDiagram();

		const targetContainerStyle = computed(() => ({
			top: `${toValue(instance.positionContextMenu).top}px`,
			left: `${toValue(instance.positionContextMenu).left}px`,
		}));

		return {
			instance,
			targetContainerStyle,
		};
	},
	template: `
		<div
			:ref="instance.contextMenuLayerRef"
			class="ui-block-diagram-context-menu__layout"
		>
			<slot/>
			<div
				:ref="instance.targetContainerRef"
				:style="targetContainerStyle"
				class="ui-block-diagram-context-menu__target-container"
			/>
		</div>
	`,
};
