import { Dom, Tag } from 'main.core';
import 'ui.hint';
import { mapState } from 'ui.vue3.vuex';
import { ServiceLocator } from '../../service/service-locator';

/**
 * A special case of Hint that provides interactivity and reactivity.
 */
export const Hint = {
	name: 'Hint',
	props: {
		html: {
			type: String,
			required: true,
		},
	},
	computed: {
		...mapState({
			guid: (state) => state.application.guid,
		}),
	},
	mounted()
	{
		this.renderHint();
	},
	watch: {
		html(): void {
			// make ui.hint reactive :(
			Dom.clean(this.$refs.container);
			this.renderHint();
		},
	},
	methods: {
		renderHint(): void {
			const hintIconWrapper = Tag.render`<span data-hint-html="true" data-hint-interactivity="true"></span>`;
			// Tag.render cant set prop value with HTML properly :(
			hintIconWrapper.setAttribute('data-hint', this.html);

			Dom.append(
				hintIconWrapper,
				this.$refs.container,
			);

			this.getHintManager().initNode(hintIconWrapper);
		},
		getHintManager(): BX.UI.Hint {
			return ServiceLocator.getHint(this.guid);
		},
	},
	template: '<span ref="container"></span>',
};
