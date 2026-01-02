/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_list_items_task,im_v2_const,im_v2_lib_logger) {
	'use strict';

	// @vue/component
	const TaskListContainer = {
	  name: 'TaskListContainer',
	  components: {
	    TaskList: im_v2_component_list_items_task.TaskList
	  },
	  emits: ['selectEntity'],
	  created() {
	    im_v2_lib_logger.Logger.warn('List: Task container created');
	  },
	  methods: {
	    onChatClick(dialogId) {
	      this.$emit('selectEntity', {
	        layoutName: im_v2_const.Layout.taskComments,
	        entityId: dialogId
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-container-task__container">
			<div class="bx-im-list-container-task__header_container">
				<div class="bx-im-list-container-task__header_title">
					{{ loc('IM_LIST_CONTAINER_TASK_HEADER_TITLE') }}
				</div>
			</div>
			<div class="bx-im-list-container-task__elements_container">
				<div class="bx-im-list-container-task__elements">
					<TaskList @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`
	};

	exports.TaskListContainer = TaskListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Component.List,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=task-container.bundle.js.map
