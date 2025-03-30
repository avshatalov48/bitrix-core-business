import { Dom, Event, Runtime } from 'main.core';
import { Ears } from 'ui.ears';
import { SyncHorizontalScroll } from '../util/sync-horizontal-scroll';
import { Column } from './column';
import { ColumnLayout } from '../layout/column-layout';

export const ColumnList = {
	name: 'ColumnList',
	components: { Column, SyncHorizontalScroll, ColumnLayout },
	props: {
		userGroups: {
			type: Map,
			required: true,
		},
		rights: {
			type: Map,
			required: true,
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
	created()
	{
		this.throttledScrollHandler = Runtime.throttle(() => {
			this.adjustShadowsVisibility();
		}, 200);
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
	template: `
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
				<ColumnLayout/>
			</SyncHorizontalScroll>
		</div>
	`,
};
