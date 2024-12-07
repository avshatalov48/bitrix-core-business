/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base) {
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

	// @vue/component
	const SupervisorBaseMessage = {
	  name: 'SupervisorBaseMessage',
	  components: {
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
	    },
	    title: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    description: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withBackground="false"
			:withContextMenu="false"
			:withReactions="false"
			class="bx-im-message-supervisor-base__scope"
		>
			<div class="bx-im-message-supervisor-base__container">
				<slot name="image" />
				<div class="bx-im-message-supervisor-base__content">
					<div class="bx-im-message-supervisor-base__title">
						{{ title }}
					</div>
					<div class="bx-im-message-supervisor-base__description">
						{{ description }}
					</div>
					<div class="bx-im-message-supervisor-base__buttons_container">
						<slot name="actions" />
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.EnableFeatures = EnableFeatures;
	exports.UpdateFeatures = UpdateFeatures;
	exports.SupervisorBaseMessage = SupervisorBaseMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message));
//# sourceMappingURL=supervisor-base.bundle.js.map
