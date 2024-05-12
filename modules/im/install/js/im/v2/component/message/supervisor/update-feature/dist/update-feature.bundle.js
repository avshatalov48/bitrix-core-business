/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements,im_v2_component_message_supervisor_base) {
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

	const onOpenTariffSettings = () => BX.SidePanel.Instance.open(`${window.location.origin}/settings/license_all.php`);
	const onHelpClick = ARTICLE_CODE => BX.Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);
	const metaData = {
	  [UpdateFeatures.tariff]: {
	    title: 'default title',
	    description: 'default description',
	    detailButton: {
	      text: 'button text',
	      callback: onOpenTariffSettings
	    },
	    infoButton: {
	      text: 'button text',
	      callback: () => onHelpClick('12925062')
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

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=update-feature.bundle.js.map
