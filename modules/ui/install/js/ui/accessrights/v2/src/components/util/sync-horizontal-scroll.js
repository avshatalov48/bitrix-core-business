import { Text } from 'main.core';
import { type BaseEvent } from 'main.core.events';
import { mapState } from 'ui.vue3.vuex';

const isMaxListenersSet: Map<string, boolean> = new Map();
const lastScrollLeft: Map<string, number> = new Map();

/**
 * A div without styling that synchronizes horizontal scroll of all elements wrapped in this component with other
 * wrapped elements in this Vue application.
 */
export const SyncHorizontalScroll = {
	name: 'SyncHorizontalScroll',
	data(): Object {
		return {
			componentGuid: Text.getRandom(16),
		};
	},
	computed: {
		...mapState({
			guid: (state) => state.application.guid,
		}),
	},
	throttledEmitScrollEvent: null,
	created()
	{
		this.throttledEmitScrollEvent = requestAnimationFrameThrottle(this.emitScrollEvent);
	},
	mounted(): void {
		if (!isMaxListenersSet.has(this.guid))
		{
			// + 1 for header
			const sectionsNumber = this.$store.state.accessRights.collection.size + 1;

			// correctly notify about memory leak
			this.$Bitrix.eventEmitter.incrementMaxListeners('ui:accessrights:v2:syncScroll', sectionsNumber);

			isMaxListenersSet.set(this.guid, true);
		}

		this.$Bitrix.eventEmitter.subscribe('ui:accessrights:v2:syncScroll', this.handleScrollEvent);

		void this.$nextTick(() => {
			if (lastScrollLeft.has(this.guid))
			{
				this.syncScroll(lastScrollLeft.get(this.guid));
			}
		});
	},
	beforeUnmount()
	{
		this.$Bitrix.eventEmitter.unsubscribe('ui:accessrights:v2:syncScroll', this.handleScrollEvent);
	},
	methods: {
		emitScrollEvent(event): void {
			// this component instance is being scrolled, we need to notify other instances
			const { scrollLeft } = event.target;

			lastScrollLeft.set(this.guid, scrollLeft);

			// emit global application event so other SyncHorizontalScroll instances receive it
			this.$Bitrix.eventEmitter.emit('ui:accessrights:v2:syncScroll', {
				scrollLeft,
				componentGuid: this.componentGuid,
			});
		},
		handleScrollEvent(event: BaseEvent): void {
			const { scrollLeft, componentGuid } = event.getData();
			if (this.componentGuid === componentGuid)
			{
				// this event was sent by this exact instance
				return;
			}

			this.syncScroll(scrollLeft);
		},
		syncScroll(scrollLeft: number): void {
			// magic hack - don't update the element if value not changed.
			// I'm not sure whether this works, but why not
			if (this.$el.scrollLeft !== scrollLeft)
			{
				this.$el.scrollLeft = scrollLeft;
			}
		},
	},
	template: `
		<div @scroll="throttledEmitScrollEvent">
			<slot/>
		</div>
	`,
};

/**
 * Same as `Runtime.throttle`, but uses `requestAnimationFrame` instead of setTimeout.
 * Why? To sync wait time with display refresh rate for smother animations.
 */
function requestAnimationFrameThrottle(func: Function): Function
{
	let callbackSet = false;
	let invoke = false;

	return function wrapper(...args)
	{
		invoke = true;

		if (!callbackSet)
		{
			const q = function q()
			{
				if (invoke)
				{
					func(...args);
					invoke = false;
					requestAnimationFrame(q);
					callbackSet = true;
				}
				else
				{
					callbackSet = false;
				}
			};
			q();
		}
	};
}
