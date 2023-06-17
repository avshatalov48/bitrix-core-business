this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_dialog_chat,im_v2_component_textarea,im_v2_lib_logger) {
	'use strict';

	// @vue/component
	const OpenlineHeader = {
	  props: {
	    dialogId: {
	      type: Number,
	      default: 0
	    }
	  },
	  methods: {
	    toggleRightPanel() {
	      this.$emit('toggleRightPanel');
	    }
	  },
	  template: `
		<div class="bx-im-content-openline__header">
			<div class="bx-im-content-openline__header_left">Header for openline - {{ dialogId }}</div>
			<div class="bx-im-content-openline__header_right">
				<div class="bx-im-content-openline__header_item">Reassign</div>
				<div class="bx-im-content-openline__header_item">Close dialog</div>
				<div @click="toggleRightPanel" class="bx-im-content-openline__header_item">Panel</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const OpenlineActionPanel = {
	  template: `
		<div style="padding: 15px 20px;">Some specific openline interface</div>
	`
	};

	// @vue/component
	const OpenlineSidebar = {
	  name: 'OpenlineSidebar',
	  created() {
	    im_v2_lib_logger.Logger.warn('Sidebar: Openline Sidebar created');
	  },
	  template: `
		<div class="bx-im-content-openline__sidebar_container">
			<div class="bx-im-content-openline__sidebar_content">
				<div class="bx-im-content-openline__sidebar_item">Openline Right Panel</div>
				<div class="bx-im-content-openline__sidebar_item">Some specific openline info</div>
				<div class="bx-im-content-openline__sidebar_item">Some additional openline info</div>
				<div class="bx-im-content-openline__sidebar_item">And more</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const OpenlineContent = {
	  name: 'OpenlineContent',
	  components: {
	    OpenlineHeader,
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    ChatTextarea: im_v2_component_textarea.ChatTextarea,
	    OpenlineActionPanel,
	    OpenlineSidebar
	  },
	  props: {
	    entityId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      panelOpened: false
	    };
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Content: Openline created');
	  },
	  methods: {
	    toggleRightPanel() {
	      this.panelOpened = !this.panelOpened;
	    }
	  },
	  template: `
		<div class="bx-im-content-openline__container">
			<div class="bx-im-content-openline__content">
				<template v-if="entityId !== 0">
					<OpenlineHeader :dialogId="entityId" @toggleRightPanel="toggleRightPanel" />
					<div class="bx-im-content-openline__dialog_container">
						<div class="bx-im-content-openline__dialog_content">
							<ChatDialog />
						</div>
					</div>
					<OpenlineActionPanel />
					<ChatTextarea />
				</template>
				<template v-else>
					<div class="bx-im-content-openline__not-selected">
						<div class="bx-im-content-openline__not-selected_text">
							Choose openline from list
						</div>
					</div>
				</template>
			</div>
			<!-- Right Panel -->
			<transition name="right-panel-transition">
				<OpenlineSidebar v-if="panelOpened" />
			</transition>
		</div>
	`
	};

	exports.OpenlineContent = OpenlineContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Component,BX.Messenger.v2.Lib));
//# sourceMappingURL=openline-content.bundle.js.map
