/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Embedding = this.BX.Messenger.Embedding || {};
(function (exports,main_core_events,im_oldChatEmbedding_component_recentList,im_oldChatEmbedding_component_search,im_oldChatEmbedding_const) {
	'use strict';

	// @vue/component
	const LeftPanel = {
	  components: {
	    RecentListComponent: im_oldChatEmbedding_component_recentList.RecentList,
	    SearchComponent: im_oldChatEmbedding_component_search.Search
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
	      main_core_events.EventEmitter.subscribe(im_oldChatEmbedding_const.EventType.recent.openSearch, this.onOpenSearchHandler);
	      main_core_events.EventEmitter.subscribe(im_oldChatEmbedding_const.EventType.recent.updateSearch, this.onUpdateSearchHandler);
	      main_core_events.EventEmitter.subscribe(im_oldChatEmbedding_const.EventType.recent.closeSearch, this.onCloseSearchHandler);
	    },
	    unregisterSearchEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_oldChatEmbedding_const.EventType.recent.openSearch, this.onOpenSearchHandler);
	      main_core_events.EventEmitter.unsubscribe(im_oldChatEmbedding_const.EventType.recent.updateSearch, this.onUpdateSearchHandler);
	      main_core_events.EventEmitter.unsubscribe(im_oldChatEmbedding_const.EventType.recent.closeSearch, this.onCloseSearchHandler);
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

}((this.BX.Messenger.Embedding.ComponentLegacy = this.BX.Messenger.Embedding.ComponentLegacy || {}),BX.Event,BX.Messenger.Embedding.ComponentLegacy,BX.Messenger.Embedding.ComponentLegacy,BX.Messenger.Embedding.Const));
//# sourceMappingURL=left-panel.bundle.js.map
