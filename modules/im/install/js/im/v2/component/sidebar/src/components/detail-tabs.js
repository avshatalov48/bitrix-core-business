import {Dom} from 'main.core';
import '../css/detail-tabs.css';

const ARROW_CONTROL_SIZE = 50;

// @vue/component
export const DetailTabs = {
	name: 'DetailTabs',
	props: {
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
		highlightStyle()
		{
			return {
				left: `${this.highlightOffsetLeft}px`,
				width: `${this.highlightWidth}px`
			};
		},

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
			return [...this.$refs.tabs.children].filter(node => !Dom.hasClass(node, 'bx-sidebar-tabs-highlight'))[index];
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
		onTabClick(event)
		{
			this.currentElementIndex = event.index;
		},
		getTabTitle(tab: string): string
		{
			const langPhraseCode = `IM_SIDEBAR_FILES_${tab.toUpperCase()}_TAB`;

			return this.$Bitrix.Loc.getMessage(langPhraseCode);
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
		<div class="bx-im-sidebar-detail-tabs__container bx-im-sidebar-detail-tabs__scope">
			<div v-if="hasLeftControl" @click.stop="onLeftClick" class="bx-im-sidebar-ears__control --left">
				<div class="bx-im-sidebar__forward-icon"></div>
			</div>
			<div v-if="hasRightControl" @click.stop="onRightClick" class="bx-im-sidebar-ears__control --right">
				<div class="bx-im-sidebar__forward-icon"></div>
			</div>
			<div class="bx-im-sidebar-ears__elements" ref="tabs" @scroll.passive="updateControlsVisibility">
				<div class="bx-sidebar-tabs-highlight" :style="highlightStyle"></div>
				<div
					v-for="(tab, index) in tabs"
					:key="tab"
					class="bx-im-sidebar-detail-tabs__item"
					:class="[isSelectedTab(index) ? '--selected' : '']"
					@click="onTabClick({index: index})"
				>
					<div class="bx-im-sidebar-detail-tabs__item-title">{{ getTabTitle(tab) }}</div>
				</div>
			</div>
		</div>
	`
};