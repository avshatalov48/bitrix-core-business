/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements,im_v2_component_message_supervisor_base) {
	'use strict';

	const tools = {
	  copilot: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_DESCRIPTION'
	  },
	  rights: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_RIGHTS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_RIGHTS_DESCRIPTION'
	  },
	  newsLine: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_NEWS_LINE_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_NEWS_LINE_DESCRIPTION'
	  },
	  chatCalls: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_DESCRIPTION'
	  },
	  calendar: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CALENDAR_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CALENDAR_DESCRIPTION'
	  },
	  documents: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_DESCRIPTION'
	  },
	  mail: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_DESCRIPTION'
	  },
	  groups: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_DESCRIPTION'
	  },
	  tasks: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_DESCRIPTION'
	  },
	  crm: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_DESCRIPTION'
	  },
	  marketing: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_DESCRIPTION'
	  },
	  automation: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_DESCRIPTION'
	  },
	  warehouseAccounting: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_DESCRIPTION'
	  },
	  sign: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_DESCRIPTION'
	  },
	  websitesStores: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_DESCRIPTION'
	  }
	};

	const TOOL_ID_PARAMS_KEY = 'toolId';
	const BUTTON_COLOR = '#52c1e7';

	// @vue/component
	const SupervisorEnableFeatureMessage = {
	  name: 'SupervisorEnableFeatureMessage',
	  components: {
	    ButtonComponent: im_v2_component_elements.Button,
	    SupervisorBaseMessage: im_v2_component_message_supervisor_base.SupervisorBaseMessage
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    buttonColorScheme() {
	      return {
	        backgroundColor: 'transparent',
	        borderColor: BUTTON_COLOR,
	        iconColor: BUTTON_COLOR,
	        textColor: BUTTON_COLOR,
	        hoverColor: 'transparent'
	      };
	    },
	    toolId() {
	      return this.message.componentParams[TOOL_ID_PARAMS_KEY];
	    },
	    tool() {
	      return tools[this.toolId];
	    },
	    modifierImageClasses() {
	      return `--${this.toolId}`;
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<SupervisorBaseMessage
			:item="item"
			:dialogId="dialogId"
			:title="loc(tool.title)"
			:description="loc(tool.description)"
		>
			<template #image>
				<div :class="['bx-im-message-enable-feature__image', modifierImageClasses]" />
			</template>
			<template #actions>
				<ButtonComponent
					:size="ButtonSize.L"
					:isRounded="true"
					:text="loc('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS')"
				/>
				<ButtonComponent
					:size="ButtonSize.L"
					:customColorScheme="buttonColorScheme"
					:isRounded="true"
					:text="loc('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED')"
				/>
			</template>
		</SupervisorBaseMessage>
	`
	};

	exports.SupervisorEnableFeatureMessage = SupervisorEnableFeatureMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=supervisor-enable-feature.bundle.js.map
