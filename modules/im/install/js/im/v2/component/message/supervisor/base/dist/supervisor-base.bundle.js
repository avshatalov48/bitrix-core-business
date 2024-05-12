/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base) {
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
