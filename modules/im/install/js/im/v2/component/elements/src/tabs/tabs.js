import {Dom} from 'main.core';
import './tabs.css';

const ARROW_CONTROL_SIZE = 50;

export const TabsColorScheme = Object.freeze({
	white: 'white',
	gray: 'gray',
});

// @vue/component
export const MessengerTabs = {
	name: 'MessengerTabs',
	props: {
		colorScheme: {
			type: String,
			required: true,
			default: TabsColorScheme.white,
			validator: (value) => Object.values(TabsColorScheme).includes(value.toLowerCase())
		},
		tabs: {
			type: Array,
			default: () => []
		},
	},
	data() {
		return {
			hasLeftControl: false,
			hasRightControl: false,
			currentElementIndex: 0,
			highlightOffsetLeft: 0,
			highlightWidth: 0,
		};
	},
	computed:
	{
		highlightStyle(): {left: string, width: string}
		{
			return {
				left: `${this.highlightOffsetLeft}px`,
				width: `${this.highlightWidth}px`
			};
		},
		colorSchemeClass(): string
		{
			return this.colorScheme === TabsColorScheme.white ? '--white' : '--gray';
		}
	},
	watch:
	{
		currentElementIndex(newIndex: number)
		{
			this.updateHighlightPosition(newIndex);
			this.$emit('tabSelect', this.tabs[newIndex]);
			this.scrollToElement(newIndex);
		}
	},
	mounted()
	{
		if (this.$refs.tabs.scrollWidth > this.$refs.tabs.offsetWidth)
		{
			this.hasRightControl = true;
		}

		this.updateHighlightPosition(this.currentElementIndex);
	},
	methods:
	{
		getElementNodeByIndex(index: number): HTMLElement
		{
			return [...this.$refs.tabs.children].filter(
				(node) => !Dom.hasClass(node, 'bx-im-elements-tabs__highlight')
			)[index];
		},
		updateHighlightPosition(index: number)
		{
			const element = this.getElementNodeByIndex(index);
			this.highlightOffsetLeft = element.offsetLeft;
			this.highlightWidth = element.offsetWidth;
		},
		scrollToElement(elementIndex: number)
		{
			const element = this.getElementNodeByIndex(elementIndex);
			this.$refs.tabs.scroll({left: element.offsetLeft - ARROW_CONTROL_SIZE, behavior: 'smooth'});
		},
		onTabClick(event): {index: number}
		{
			this.currentElementIndex = event.index;
		},
		isSelectedTab(index: number): boolean
		{
			return index === this.currentElementIndex;
		},
		onLeftClick()
		{
			if (this.currentElementIndex <= 0)
			{
				return;
			}

			this.currentElementIndex--;
		},
		onRightClick()
		{
			if (this.currentElementIndex >= this.tabs.length - 1)
			{
				return;
			}

			this.currentElementIndex++;
		},
		updateControlsVisibility()
		{
			this.hasRightControl = this.$refs.tabs.scrollWidth > this.$refs.tabs.scrollLeft + this.$refs.tabs.clientWidth;
			this.hasLeftControl = this.$refs.tabs.scrollLeft > 0;
		}
	},
	template: `
		<div class="bx-im-elements-tabs__container bx-im-elements-tabs__scope" :class="colorSchemeClass">
			<div v-if="hasLeftControl" @click.stop="onLeftClick" class="bx-im-elements-tabs__control --left">
				<div class="bx-im-elements-tabs__forward-icon"></div>
			</div>
			<div v-if="hasRightControl" @click.stop="onRightClick" class="bx-im-elements-tabs__control --right">
				<div class="bx-im-elements-tabs__forward-icon"></div>
			</div>
			<div class="bx-im-elements-tabs__elements" ref="tabs" @scroll.passive="updateControlsVisibility">
				<div class="bx-im-elements-tabs__highlight" :style="highlightStyle"></div>
				<div
					v-for="(tab, index) in tabs"
					:key="tab.id"
					class="bx-im-elements-tabs__item"
					:class="[isSelectedTab(index) ? '--selected' : '']"
					@click="onTabClick({index: index})"
					:title="tab.title"
				>
					<div class="bx-im-elements-tabs__item-title">{{ tab.title }}</div>
				</div>
			</div>
		</div>
	`
};