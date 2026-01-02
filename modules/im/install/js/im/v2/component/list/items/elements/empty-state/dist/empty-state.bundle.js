/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports) {
	'use strict';

	// @vue/component
	const RecentEmptyState = {
	  name: 'RecentEmptyState',
	  props: {
	    title: {
	      type: String,
	      required: true
	    },
	    subtitle: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  template: `
		<div class="bx-im-list-recent-empty-state__container">
			<div class="bx-im-list-recent-empty-state__image"></div>
			<div class="bx-im-list-recent-empty-state__title">{{ title }}</div>
			<div v-if="subtitle" class="bx-im-list-recent-empty-state__subtitle">{{ subtitle }}</div>
			<slot></slot>
		</div>
	`
	};

	exports.RecentEmptyState = RecentEmptyState;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {})));
//# sourceMappingURL=empty-state.bundle.js.map
