/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core) {
	'use strict';

	const ARROW_CONTROL_SIZE = 50;
	const TabsColorScheme = Object.freeze({
	  white: 'white',
	  gray: 'gray'
	});

	// @vue/component
	const MessengerTabs = {
	  name: 'MessengerTabs',
	  props: {
	    colorScheme: {
	      type: String,
	      required: true,
	      default: TabsColorScheme.white,
	      validator: value => Object.values(TabsColorScheme).includes(value.toLowerCase())
	    },
	    tabs: {
	      type: Array,
	      default: () => []
	    }
	  },
	  data() {
	    return {
	      hasLeftControl: false,
	      hasRightControl: false,
	      currentElementIndex: 0,
	      highlightOffsetLeft: 0,
	      highlightWidth: 0,
	      isFirstCall: true
	    };
	  },
	  computed: {
	    highlightStyle() {
	      return {
	        left: `${this.highlightOffsetLeft}px`,
	        width: `${this.highlightWidth}px`
	      };
	    },
	    colorSchemeClass() {
	      return this.colorScheme === TabsColorScheme.white ? '--white' : '--gray';
	    }
	  },
	  watch: {
	    currentElementIndex(newIndex) {
	      this.updateHighlightPosition(newIndex);
	      this.$emit('tabSelect', this.tabs[newIndex]);
	      this.scrollToElement(newIndex);
	    }
	  },
	  mounted() {
	    const savedTabIndex = localStorage.getItem('lastOpenedTabIndex');
	    if (this.$refs.tabs.scrollWidth > this.$refs.tabs.offsetWidth) {
	      this.hasRightControl = true;
	    }
	    if (savedTabIndex && this.tabs[savedTabIndex]) {
	      this.currentElementIndex = parseInt(savedTabIndex, 10);
	    }
	    this.updateHighlightPosition(this.currentElementIndex);
	    setTimeout(() => {
	      this.isFirstCall = false;
	    }, 100);
	  },
	  beforeUnmount() {
	    localStorage.setItem('lastOpenedTabIndex', this.currentElementIndex.toString());
	  },
	  methods: {
	    getElementNodeByIndex(index) {
	      return [...this.$refs.tabs.children].filter(node => !main_core.Dom.hasClass(node, 'bx-im-elements-tabs__highlight'))[index];
	    },
	    updateHighlightPosition(index) {
	      const element = this.getElementNodeByIndex(index);
	      this.highlightOffsetLeft = element.offsetLeft;
	      this.highlightWidth = element.offsetWidth;
	    },
	    scrollToElement(elementIndex) {
	      const element = this.getElementNodeByIndex(elementIndex);
	      this.$refs.tabs.scroll({
	        left: element.offsetLeft - ARROW_CONTROL_SIZE,
	        behavior: 'smooth'
	      });
	    },
	    onTabClick(event) {
	      this.currentElementIndex = event.index;
	    },
	    isSelectedTab(index) {
	      return index === this.currentElementIndex;
	    },
	    onLeftClick() {
	      if (this.currentElementIndex <= 0) {
	        return;
	      }
	      this.currentElementIndex--;
	    },
	    onRightClick() {
	      if (this.currentElementIndex >= this.tabs.length - 1) {
	        return;
	      }
	      this.currentElementIndex++;
	    },
	    updateControlsVisibility() {
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
				<div class="bx-im-elements-tabs__highlight" :class="isFirstCall ? '' : '--transition'" :style="highlightStyle"></div>
				<div
					v-for="(tab, index) in tabs"
					:key="tab.id"
					class="bx-im-elements-tabs__item"
					:class="[isSelectedTab(index) ? '--selected' : '']"
					@click="onTabClick({index: index})"
					:title="tab.title"
				>
					<div class="bx-im-elements-tabs__item-title" :class="isFirstCall ? '' : '--transition'">{{ tab.title }}</div>
				</div>
			</div>
		</div>
	`
	};

	exports.TabsColorScheme = TabsColorScheme;
	exports.MessengerTabs = MessengerTabs;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX));
//# sourceMappingURL=tabs.bundle.js.map
