/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements,im_v2_component_message_supervisor_base,main_core) {
	'use strict';

	const EnableFeatures = Object.freeze({
	  copilot: 'copilot',
	  newsLine: 'newsLine',
	  chatCalls: 'chatCalls',
	  calendar: 'calendar',
	  documents: 'documents',
	  mail: 'mail',
	  groups: 'groups',
	  tasks: 'tasks',
	  crm: 'crm',
	  marketing: 'marketing',
	  automation: 'automation',
	  warehouseAccounting: 'warehouseAccounting',
	  sign: 'sign',
	  websitesStores: 'websitesStores'
	});
	const UpdateFeatures = Object.freeze({
	  tariff: 'tariff'
	});

	const onOpenToolsSettings = () => BX.SidePanel.Instance.open(`${window.location.origin}/settings/configs/?page=tools`);
	const onHelpClick = ARTICLE_CODE => BX.Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);
	const metaData = {
	  [EnableFeatures.copilot]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.newsLine]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_NEWS_LINE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_NEWS_LINE_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.chatCalls]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.calendar]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CALENDAR_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CALENDAR_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.documents]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.mail]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.groups]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.tasks]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.crm]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.marketing]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.automation]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.warehouseAccounting]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.sign]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
	  },
	  [EnableFeatures.websitesStores]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12925062')
	    }
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
	    toolData() {
	      return metaData[this.toolId];
	    },
	    modifierImageClass() {
	      return `--${this.toolId}`;
	    }
	  },
	  template: `
		<SupervisorBaseMessage
			:item="item"
			:dialogId="dialogId"
			:title="toolData.title"
			:description="toolData.description"
		>
			<template #image>
				<div :class="['bx-im-message-enable-feature__image', modifierImageClass]" />
			</template>
			<template #actions>
				<ButtonComponent
					:size="ButtonSize.L"
					:isRounded="true"
					:text="toolData.detailButton.text"
					@click="toolData.detailButton.callback"
				/>
				<ButtonComponent
					:size="ButtonSize.L"
					:customColorScheme="buttonColorScheme"
					:isRounded="true"
					:text="toolData.infoButton.text"
					@click="toolData.infoButton.callback"
				/>
			</template>
		</SupervisorBaseMessage>
	`
	};

	exports.SupervisorEnableFeatureMessage = SupervisorEnableFeatureMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message,BX));
//# sourceMappingURL=enable-feature.bundle.js.map
