import { Dom, Event, Runtime } from 'main.core';
import { Ears } from 'ui.ears';
import { computed } from 'ui.vue3';
import { Column } from './section/column';
import { Header } from './section/header';
import { TitleColumn } from './section/title-column';
import { SyncHorizontalScroll } from './util/sync-horizontal-scroll';

export const Section = {
	name: 'Section',
	components: { Column, SyncHorizontalScroll, TitleColumn, Header },
	props: {
		userGroups: {
			type: Map,
			required: true,
		},
		rights: {
			type: Map,
			required: true,
		},
		code: {
			type: String,
			required: true,
		},
		isExpanded: {
			type: Boolean,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		subTitle: {
			type: String,
		},
		hint: {
			type: String,
		},
		icon: {
			/** @type AccessRightSectionIcon */
			type: Object,
		},
	},
	throttledScrollHandler: null,
	throttledResizeHandler: null,
	ears: null,
	isEarsInited: false,
	data(): Object {
		return {
			isLeftShadowShown: false,
			isRightShadowShown: false,
		};
	},
	provide(): Object {
		return {
			section: computed(() => {
				return {
					sectionCode: this.code,
					sectionTitle: this.title,
					sectionSubTitle: this.subTitle,
					sectionIcon: this.icon,
					sectionHint: this.hint,
					isExpanded: this.isExpanded,
					rights: this.rights,
				};
			}),
		};
	},
	created()
	{
		this.throttledScrollHandler = Runtime.throttle(this.adjustShadowsVisibility, 200);
		this.throttledResizeHandler = Runtime.throttle(() => {
			this.adjustShadowsVisibility();
			this.adjustEars();
		}, 200);
	},
	mounted()
	{
		Event.bind(window, 'resize', this.throttledResizeHandler);
		this.adjustShadowsVisibility();

		this.initEars();
	},
	beforeUnmount()
	{
		this.destroyEars();

		Event.unbind(window, 'resize', this.throttledResizeHandler);
	},
	watch: {
		userGroups(newValue: Map, oldValue: Map): void {
			if (newValue.size !== oldValue.size)
			{
				this.adjustShadowsVisibility();
				this.adjustEars();
			}
		},
		isExpanded(newValue): void {
			if (newValue === true)
			{
				void this.$nextTick(() => {
					this.initEars();
				});
			}
			else
			{
				this.destroyEars();
			}
		},
	},
	methods: {
		calculateShadowsVisibility(): { isLeftShadowShown: boolean, isRightShadowShown: boolean } {
			if (!this.$refs['column-container'])
			{
				// in case it's accidentally called before mount or after unmount
				return { isLeftShadowShown: false, isRightShadowShown: false };
			}

			const scrollLeft = this.$refs['column-container'].$el.scrollLeft;

			const isLeftShadowShown = scrollLeft > 0;

			const offsetWidth = this.$refs['column-container'].$el.offsetWidth;

			return {
				isLeftShadowShown,
				isRightShadowShown: this.$refs['column-container'].$el.scrollWidth > (Math.round(scrollLeft + offsetWidth)),
			};
		},
		adjustShadowsVisibility(): void {
			// avoid "forced synchronous layout"
			requestAnimationFrame(() => {
				const { isLeftShadowShown, isRightShadowShown } = this.calculateShadowsVisibility();
				this.isLeftShadowShown = isLeftShadowShown;
				this.isRightShadowShown = isRightShadowShown;
			});
		},
		adjustEars(): void {
			if (!this.isEarsInited)
			{
				return;
			}

			// avoid "forced synchronous layout"
			requestAnimationFrame(() => {
				// force ears to recalculate its visibility
				this.ears.toggleEars();
			});
		},
		initEars(): void {
			if (!this.$refs['column-container'])
			{
				return;
			}

			if (this.ears)
			{
				return;
			}

			this.ears = new Ears({
				container: this.$refs['column-container'].$el,
				immediateInit: true,
				smallSize: true,
			});

			// chrome is not happy when we query DOM values (scrollLeft, offsetWidth, ...) just after we've changed them
			// avoid "forced synchronous layout"
			requestAnimationFrame(() => {
				if (!this.ears || !this.$refs['column-container'])
				{
					this.ears = null;

					// sometimes the callback is fired after the component is unmounted
					return;
				}

				const scrollLeft = this.$refs['column-container'].$el.scrollLeft;
				this.ears.init();

				// Ears add wrapper around the container, and it breaks our markup a little. Fix it
				Dom.style(this.ears.getWrapper(), 'flex', 1);
				if (scrollLeft > 0)
				{
					// ears.init resets scrollLeft to 0
					this.$refs['column-container'].$el.scrollLeft = scrollLeft;
				}

				this.isEarsInited = true;
			});
		},
		destroyEars(): void {
			this.ears?.destroy();
			this.isEarsInited = false;
			this.ears = null;
		},
	},
	// data attributes are needed for e2e automated tests
	template: `
		<div class="ui-access-rights-v2-section" :data-accessrights-section-code="code">
			<Header/>
			<div v-if="isExpanded" class='ui-access-rights-v2-section-container'>
				<div class='ui-access-rights-v2-section-head'>
					<TitleColumn :rights="rights" />
				</div>
				<div
					class='ui-access-rights-v2-section-content'
					:class="{
						'ui-access-rights-v2-section-shadow-left-shown': isLeftShadowShown,
						'ui-access-rights-v2-section-shadow-right-shown': isRightShadowShown,
					}"
				>
					<SyncHorizontalScroll
						ref="column-container"
						class='ui-access-rights-v2-section-wrapper'
						@scroll="throttledScrollHandler"
					>
						<Column 
							v-for="[groupId, group] in userGroups"
							:key="groupId"
							:user-group="group"
							:rights="rights"
							:data-accessrights-user-group-id="groupId"
						/>
					</SyncHorizontalScroll>
				</div>
			</div>
		</div>
	`,
};
