/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements,im_v2_component_message_base) {
	'use strict';

	const tools = {
	  copilot: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_COPILOT_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_COPILOT_DESCRIPTION'
	  },
	  rights: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_RIGHTS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_RIGHTS_DESCRIPTION'
	  },
	  newsLine: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_NEWS_LINE_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_NEWS_LINE_DESCRIPTION'
	  },
	  chatCalls: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_CHAT_CALLS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_CHAT_CALLS_DESCRIPTION'
	  },
	  calendar: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_CALENDAR_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_CALENDAR_DESCRIPTION'
	  },
	  documents: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_DOCUMENTS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_DOCUMENTS_DESCRIPTION'
	  },
	  mail: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_MAIL_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_MAIL_DESCRIPTION'
	  },
	  groups: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_GROUPS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_GROUPS_DESCRIPTION'
	  },
	  tasks: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_TASKS_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_TASKS_DESCRIPTION'
	  },
	  crm: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_CRM_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_CRM_DESCRIPTION'
	  },
	  marketing: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_MARKETING_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_MARKETING_DESCRIPTION'
	  },
	  automation: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_AUTOMATION_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_AUTOMATION_DESCRIPTION'
	  },
	  warehouseAccounting: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_WAREHOUSE_ACCOUNTING_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_WAREHOUSE_ACCOUNTING_DESCRIPTION'
	  },
	  sign: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_SIGN_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_SIGN_DESCRIPTION'
	  },
	  websitesStores: {
	    title: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_WEBSITES_STORES_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_WEBSITES_STORES_DESCRIPTION'
	  }
	};

	const TOOL_ID_PARAMS_KEY = 'toolId';
	const BUTTON_COLOR = '#52c1e7';

	// @vue/component
	const SupervisorEnableFeatureMessage = {
	  name: 'SupervisorEnableFunctionality',
	  components: {
	    ButtonComponent: im_v2_component_elements.Button,
	    BaseMessage: im_v2_component_message_base.BaseMessage
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
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
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
	      var _this$message$compone;
	      return (_this$message$compone = this.message.componentParams) == null ? void 0 : _this$message$compone[TOOL_ID_PARAMS_KEY];
	    },
	    tool() {
	      return tools[this.toolId];
	    },
	    imageModify() {
	      return `--${this.toolId}`;
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withBackground="false"
			class="bx-im-message-enable-functionality__scope"
		>
			<div class="bx-im-message-enable-functionality__container">
				<div :class="['bx-im-message-enable-functionality__image', imageModify]" />
				<div class="bx-im-message-enable-functionality__content">
					<div class="bx-im-message-enable-functionality__title">
						{{ loc(tool.title) }}
					</div>
					<div class="bx-im-message-enable-functionality__description">
						{{ loc(tool.description) }}
					</div>
					<div class="bx-im-message-enable-functionality__buttons_container">
						<ButtonComponent
							:size="ButtonSize.L" 
							:isRounded="true"
							:text="loc('IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_BUTTON_OPEN_SETTINGS')"
						/>
						<ButtonComponent
							:size="ButtonSize.L"
							:customColorScheme="buttonColorScheme"
							:isRounded="true"
							:text="loc('IM_MESSAGE_SUPERVISOR_ENABLE_FUNCTIONALITY_BUTTON_MORE_DETAILED')"
						/>
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.SupervisorEnableFeatureMessage = SupervisorEnableFeatureMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=supervisor-enable-functionality.bundle.js.map
