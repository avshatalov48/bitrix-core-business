/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements,im_v2_component_message_supervisor_base,main_core,im_v2_lib_analytics,im_v2_lib_helpdesk) {
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

	const onOpenPriceTable = featureId => {
	  return () => {
	    im_v2_lib_analytics.Analytics.getInstance().supervisor.onOpenPriceTable(featureId);
	    BX.SidePanel.Instance.open(`${window.location.origin}/settings/license_all.php`);
	  };
	};
	const onHelpClick = ARTICLE_CODE => im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	const metaData = {
	  [UpdateFeatures.collaborativeDocumentEditing]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.collaborativeDocumentEditing}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.collaborativeDocumentEditing}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.collaborativeDocumentEditing)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('20338924')
	    }
	  },
	  [UpdateFeatures.crmAnalytics]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.crmAnalytics}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.crmAnalytics}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.crmAnalytics)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('9673603')
	    }
	  },
	  [UpdateFeatures.crmHistory]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.crmHistory}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.crmHistory}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.crmHistory)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('17301310')
	    }
	  },
	  [UpdateFeatures.leadsCRM]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.leadsCRM}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.leadsCRM}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.leadsCRM)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('1357950')
	    }
	  },
	  [UpdateFeatures.crmInvoices]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.crmInvoices}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.crmInvoices}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.crmInvoices)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('17614982')
	    }
	  },
	  [UpdateFeatures.enterpriseAdmin]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.enterpriseAdmin}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.enterpriseAdmin}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.enterpriseAdmin)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('17994262')
	    }
	  },
	  [UpdateFeatures.loginHistory]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.loginHistory}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.loginHistory}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.loginHistory)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('19124604')
	    }
	  },
	  [UpdateFeatures.mailBoxNumber]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.mailBoxNumber}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.mailBoxNumber}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.mailBoxNumber)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('19083990')
	    }
	  },
	  [UpdateFeatures.tasksRobots]: {
	    title: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.tasksRobots}`),
	    description: main_core.Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.tasksRobots}`),
	    detailButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
	      callback: onOpenPriceTable(UpdateFeatures.tasksRobots)
	    },
	    infoButton: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
	      callback: () => onHelpClick('17784680')
	    }
	  }
	};

	const TOOL_ID_PARAMS_KEY = 'toolId';
	// @vue/component
	const SupervisorUpdateFeatureMessage = {
	  name: 'SupervisorUpdateFeatureMessage',
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
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
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
				<div :class="['bx-im-message-update-features__image-wrapper', modifierImageClass]" />
			</template>
			<template #actions>
				<ButtonComponent
					:size="ButtonSize.L"
					:isRounded="true"
					:color="ButtonColor.Success"
					:text="toolData.detailButton.text"
					@click="toolData.detailButton.callback"
				/>
				<ButtonComponent
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="toolData.infoButton.text"
					@click="toolData.infoButton.callback"
				/>
			</template>
		</SupervisorBaseMessage>
	`
	};

	exports.SupervisorUpdateFeatureMessage = SupervisorUpdateFeatureMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=update-feature.bundle.js.map
