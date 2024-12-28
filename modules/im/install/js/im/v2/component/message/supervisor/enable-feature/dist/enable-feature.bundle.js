/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements,im_v2_component_message_supervisor_base,main_core,im_v2_lib_analytics,im_v2_lib_helpdesk,stafftrack_userStatisticsLink) {
	'use strict';

	const EnableFeatures = Object.freeze({
	  copilot: 'copilot',
	  newsLine: 'news',
	  chatCalls: 'instant_messenger',
	  calendar: 'calendar',
	  documents: 'docs',
	  mail: 'mail',
	  groups: 'workgroups',
	  tasks: 'tasks',
	  crm: 'crm',
	  marketing: 'marketing',
	  automation: 'automation',
	  warehouseAccounting: 'inventory_management',
	  sign: 'sign',
	  scrum: 'scrum',
	  invoices: 'invoices',
	  saleshub: 'saleshub',
	  websitesStores: 'sites',
	  checkIn: 'checkIn',
	  checkInGeo: 'checkInGeo'
	});
	const UpdateFeatures = Object.freeze({
	  collaborativeDocumentEditing: 'limit_office_no_document',
	  leadsCRM: 'limit_crm_lead_unlimited',
	  mailBoxNumber: 'limit_contact_center_mail_box_number',
	  enterpriseAdmin: 'info_enterprise_admin',
	  loginHistory: 'limit_office_login_history',
	  crmHistory: 'limit_crm_history_view',
	  tasksRobots: 'limit_tasks_robots',
	  crmAnalytics: 'limit_crm_analytics_max_number',
	  crmInvoices: 'limit_crm_free_invoices'
	});

	const onOpenToolsSettings = toolId => {
	  return () => {
	    im_v2_lib_analytics.Analytics.getInstance().supervisor.onOpenToolsSettings(toolId);
	    BX.SidePanel.Instance.open(`${window.location.origin}/settings/configs/?page=tools`);
	  };
	};
	const openCheckInQrCode = () => {
	  if (!stafftrack_userStatisticsLink.UserStatisticsLink) {
	    return;
	  }
	  new stafftrack_userStatisticsLink.UserStatisticsLink({
	    intent: stafftrack_userStatisticsLink.UserStatisticsLink.CHECK_IN_SETTINGS_INTENT
	  }).show();
	};
	const onHelpClick = ARTICLE_CODE => im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	const metaData = {
	  [EnableFeatures.copilot]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.copilot)
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
	      callback: onOpenToolsSettings(EnableFeatures.newsLine)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('18634548')
	    }
	  },
	  [EnableFeatures.chatCalls]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.chatCalls)
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
	      callback: onOpenToolsSettings(EnableFeatures.calendar)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('17525000')
	    }
	  },
	  [EnableFeatures.documents]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.documents)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('20338924')
	    }
	  },
	  [EnableFeatures.mail]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.mail)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('12487078')
	    }
	  },
	  [EnableFeatures.groups]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.groups)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('6481473')
	    }
	  },
	  [EnableFeatures.tasks]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.tasks)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('17962166')
	    }
	  },
	  [EnableFeatures.crm]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.crm)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('1470510')
	    }
	  },
	  [EnableFeatures.marketing]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.marketing)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('10437776')
	    }
	  },
	  [EnableFeatures.automation]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.automation)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('16547618')
	    }
	  },
	  [EnableFeatures.warehouseAccounting]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.warehouseAccounting)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('17792018')
	    }
	  },
	  [EnableFeatures.sign]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.sign)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('16555500')
	    }
	  },
	  [EnableFeatures.websitesStores]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.websitesStores)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('17540360')
	    }
	  },
	  [EnableFeatures.scrum]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SCRUM_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SCRUM_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.scrum)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('13660630')
	    }
	  },
	  [EnableFeatures.invoices]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_INVOICES_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_INVOICES_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.invoices)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('17614982')
	    }
	  },
	  [EnableFeatures.saleshub]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SALESHUB_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SALESHUB_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: onOpenToolsSettings(EnableFeatures.saleshub)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('9289135')
	    }
	  },
	  [EnableFeatures.checkIn]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHECK_IN_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHECK_IN_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: () => openCheckInQrCode()
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('20922794')
	    }
	  },
	  [EnableFeatures.checkInGeo]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHECK_IN_GEO_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHECK_IN_GEO_DESCRIPTION'),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
	      callback: () => openCheckInQrCode()
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
	      callback: () => onHelpClick('20922794')
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

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Stafftrack));
//# sourceMappingURL=enable-feature.bundle.js.map
