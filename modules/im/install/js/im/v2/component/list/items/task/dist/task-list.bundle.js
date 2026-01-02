/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_draft,im_v2_lib_utils,im_v2_component_elements_listLoadingState,im_v2_component_list_items_recent,im_v2_component_list_items_elements_emptyState,im_v2_const,im_v2_provider_service_recent,im_v2_lib_menu) {
	'use strict';

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  components: {
	    RecentEmptyState: im_v2_component_list_items_elements_emptyState.RecentEmptyState
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<RecentEmptyState :title="loc('IM_LIST_TASK_EMPTY_STATE_TITLE')" />
	`
	};

	class TaskService extends im_v2_provider_service_recent.BaseRecentService {
	  getRestMethodName() {
	    return im_v2_const.RestMethod.imV2RecentExternalChatTail;
	  }
	  getQueryParams(firstPage = false) {
	    return {
	      ...super.getQueryParams(firstPage),
	      type: im_v2_const.ChatType.taskComments
	    };
	  }
	  getRecentSaveActionName() {
	    return 'recent/setTask';
	  }
	}

	class TaskRecentMenu extends im_v2_lib_menu.RecentMenu {
	  getMenuItems() {
	    return [this.getUnreadMessageItem(), this.getPinMessageItem(), this.getHideItem()];
	  }
	}

	// @vue/component
	const TaskList = {
	  name: 'TaskList',
	  components: {
	    EmptyState,
	    LoadingState: im_v2_component_elements_listLoadingState.ListLoadingState,
	    RecentItem: im_v2_component_list_items_recent.RecentItem
	  },
	  emits: ['chatClick'],
	  data() {
	    return {
	      isLoading: false,
	      isLoadingNextPage: false,
	      firstPageLoaded: false
	    };
	  },
	  computed: {
	    collection() {
	      return this.$store.getters['recent/getTaskCollection'];
	    },
	    preparedItems() {
	      return [...this.collection].sort((a, b) => {
	        const firstDate = this.$store.getters['recent/getSortDate'](a.dialogId);
	        const secondDate = this.$store.getters['recent/getSortDate'](b.dialogId);
	        return secondDate - firstDate;
	      });
	    },
	    pinnedItems() {
	      return this.preparedItems.filter(item => {
	        return item.pinned === true;
	      });
	    },
	    generalItems() {
	      return this.preparedItems.filter(item => {
	        return item.pinned === false;
	      });
	    },
	    isEmptyCollection() {
	      return this.collection.length === 0;
	    }
	  },
	  created() {
	    this.contextMenuManager = new TaskRecentMenu();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  async activated() {
	    this.isLoading = true;
	    await this.getRecentService().loadFirstPage();
	    if (!this.firstPageLoaded) {
	      void im_v2_lib_draft.DraftManager.getInstance().initDraftHistory();
	    }
	    this.firstPageLoaded = true;
	    this.isLoading = false;
	  },
	  methods: {
	    async onScroll(event) {
	      this.contextMenuManager.close();
	      if (!im_v2_lib_utils.Utils.dom.isOneScreenRemaining(event.target) || !this.getRecentService().hasMoreItemsToLoad()) {
	        return;
	      }
	      this.isLoadingNextPage = true;
	      await this.getRecentService().loadNextPage();
	      this.isLoadingNextPage = false;
	    },
	    onClick(item) {
	      this.$emit('chatClick', item.dialogId);
	    },
	    onRightClick(item, event) {
	      event.preventDefault();
	      const context = {
	        dialogId: item.dialogId,
	        recentItem: item
	      };
	      this.contextMenuManager.openMenu(context, event.currentTarget);
	    },
	    getRecentService() {
	      if (!this.service) {
	        this.service = new TaskService();
	      }
	      return this.service;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-task__container">
			<LoadingState v-if="isLoading && !firstPageLoaded" />
			<div v-else @scroll="onScroll" class="bx-im-list-task__scroll-container">
				<EmptyState v-if="isEmptyCollection" />
				<div v-if="pinnedItems.length > 0" class="bx-im-list-task__pinned_container">
					<RecentItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-task__general_container">
					<RecentItem
						v-for="item in generalItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<LoadingState v-if="isLoadingNextPage" />
			</div>
		</div>
	`
	};

	exports.TaskList = TaskList;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.List,BX.Messenger.v2.Const,BX.Messenger.v2.Service,BX.Messenger.v2.Lib));
//# sourceMappingURL=task-list.bundle.js.map
