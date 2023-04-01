this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_component_oldChatEmbedding_recentList,im_v2_component_oldChatEmbedding_search,im_v2_const) {
	'use strict';

	// @vue/component
	const LeftPanel = {
	  components: {
	    RecentListComponent: im_v2_component_oldChatEmbedding_recentList.RecentList,
	    SearchComponent: im_v2_component_oldChatEmbedding_search.Search
	  },
	  data: function () {
	    return {
	      searchMode: false,
	      searchQuery: ''
	    };
	  },
	  created() {
	    this.registerSearchEvents();
	  },
	  beforeUnmount() {
	    this.unregisterSearchEvents();
	  },
	  methods: {
	    registerSearchEvents() {
	      this.onOpenSearchHandler = this.onOpenSearch.bind(this);
	      this.onUpdateSearchHandler = this.onUpdateSearch.bind(this);
	      this.onCloseSearchHandler = this.onCloseSearch.bind(this);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.openSearch, this.onOpenSearchHandler);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.updateSearch, this.onUpdateSearchHandler);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.closeSearch, this.onCloseSearchHandler);
	    },
	    unregisterSearchEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.openSearch, this.onOpenSearchHandler);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.updateSearch, this.onUpdateSearchHandler);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.closeSearch, this.onCloseSearchHandler);
	    },
	    onOpenSearch(event) {
	      if (this.searchMode) {
	        return;
	      }
	      this.searchMode = true;
	      this.searchQuery = event.data.query;
	    },
	    onUpdateSearch(event) {
	      this.searchMode = true;
	      this.searchQuery = event.data.query;
	    },
	    onCloseSearch() {
	      this.searchQuery = '';
	      this.searchMode = false;
	    }
	  },
	  template: `
		<div class="bx-im-left-panel-wrap">
			<SearchComponent v-show="searchMode" :searchMode="searchMode" :searchQuery="searchQuery" />
			<RecentListComponent v-show="!searchMode" />
		</div>
	`
	};

	exports.LeftPanel = LeftPanel;

}((this.BX.Messenger.v2.ComponentLegacy = this.BX.Messenger.v2.ComponentLegacy || {}),BX.Event,BX.Messenger.v2.ComponentLegacy,BX.Messenger.v2.ComponentLegacy,BX.Messenger.v2.Const));
//# sourceMappingURL=left-panel.bundle.js.map
