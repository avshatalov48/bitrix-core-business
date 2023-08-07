import { TileItem } from './tile-item';
import { TileMoreItem } from './tile-more-item';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { TileWidgetItem } from '../tile-widget-item';

/**
 * @memberof BX.UI.Uploader
 */
export const TileList: BitrixVueComponentProps = {
	components: {
		TileItem,
		TileMoreItem,
	},
	emits: ['onUnmount'],
	props: {
		autoCollapse: {
			type: Boolean,
			default: false,
		},
		items: {
			type: Array,
			default: []
		}
	},
	data: (): Object => ({
		pageSize: 5,
		firstHiddenItem: null,
		lastHiddenItem: null,
	}),
	created(): void
	{
		this.moreItemBlocked = false;

		if (!this.autoCollapse)
		{
			return;
		}

		if (this.items.length > this.pageSize)
		{
			this.firstHiddenItem = this.items[this.pageSize];
			this.lastHiddenItem = this.items[this.items.length - 1];
		}
	},
	unmounted(): void
	{
		this.$emit('onUnmount');
	},
	computed: {
		visibleItems(): TileWidgetItem[]
		{
			if (this.firstHiddenItem === null)
			{
				return this.items;
			}

			const index = this.items.indexOf(this.firstHiddenItem);
			if (index === -1)
			{
				this.resetMoreItem();

				return this.items;
			}

			return this.items.slice(0, index);
		},

		realtimeItems(): TileWidgetItem[]
		{
			if (this.lastHiddenItem === null)
			{
				return [];
			}

			const index = this.items.indexOf(this.lastHiddenItem);
			if (index === -1)
			{
				this.resetMoreItem();

				return [];
			}

			return this.items.slice(index + 1);
		},

		hiddenFilesCount(): number
		{
			if (this.lastHiddenItem === null)
			{
				return 0;
			}

			const firstIndex = this.items.indexOf(this.firstHiddenItem);
			const lastIndex = this.items.indexOf(this.lastHiddenItem);

			if (firstIndex === -1 || lastIndex === -1)
			{
				this.resetMoreItem();

				return 0;
			}

			return lastIndex - firstIndex + 1;
		},
	},
	methods: {
		getMore(): void
		{
			if (this.moreItemBlocked)
			{
				return;
			}

			this.pageSize = Math.min(this.pageSize + 5, 30);

			const currentFirstIndex = this.items.indexOf(this.firstHiddenItem);
			const lastIndex = this.items.indexOf(this.lastHiddenItem);

			const newFirstIndex: number = currentFirstIndex + this.pageSize;
			const nextFirstIndex: number = newFirstIndex > lastIndex ? lastIndex + 1 : newFirstIndex;
			let itemsToShow: number = nextFirstIndex - currentFirstIndex;

			for (let i = currentFirstIndex, delay = 0; i < nextFirstIndex; i++, delay++)
			{
				this.moreItemBlocked = true;
				setTimeout((): void => {
					if (i === lastIndex)
					{
						this.resetMoreItem();
					}
					else
					{
						this.firstHiddenItem = this.items[i + 1];
					}

					itemsToShow--;
					if (itemsToShow === 0)
					{
						this.moreItemBlocked = false;
					}

				}, 100 * delay);
			}
		},
		resetMoreItem(): void
		{
			this.firstHiddenItem = null;
			this.lastHiddenItem = null;
		},
	},
	// language=Vue
	template: `
		<div class="ui-tile-uploader-items">
			<transition-group name="ui-tile-uploader-item" type="animation">
				<TileItem v-for="item in visibleItems" :key="item.id" :item="item" />
			</transition-group>
			<transition name="ui-tile-uploader-item" type="animation">
				<TileMoreItem v-if="hiddenFilesCount > 0" :hidden-files-count="hiddenFilesCount" @onClick="getMore"/>
			</transition>
			<transition-group name="ui-tile-uploader-item" type="animation">
				<TileItem v-for="item in realtimeItems" :key="item.id" :item="item" />
			</transition-group>
		</div>
	`
};
