import { hint } from 'ui.vue3.directives.hint';

/**
 * A special case of Hint. We don't need interactivity here, but we do need to wrap slot with a hint.
 * Combine these properties in a single vue hint wrapper is impossible.
 */
export const SelectedHint = {
	name: 'SelectedHint',
	props: {
		html: {
			type: String,
			required: true,
		},
	},
	data(): Object {
		return {
			isRendered: true,
		};
	},
	watch: {
		html(): void {
			// force hint directive to re-render
			this.isRendered = false;
			void this.$nextTick(() => {
				this.isRendered = true;
			});
		},
	},
	directives: {
		hint,
	},
	// offsetTop is needed to fix infinite mouseenter/mouseleave loop in chromium. issue 204272
	template: `
		<div v-if="isRendered" v-hint="{
			html,
			popupOptions: {
				offsetTop: 3,
			},
		}" data-hint-init="vue">
			<slot/>
		</div>
	`,
};
