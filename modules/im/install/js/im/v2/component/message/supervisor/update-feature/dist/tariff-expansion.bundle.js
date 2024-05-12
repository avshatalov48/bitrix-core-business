/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements,im_v2_component_message_supervisor_base) {
	'use strict';

	const UpdateFeatures = BX.Messenger.v2.Const.UpdateFeatures;
	const metaData = {
	  [UpdateFeatures.tariff]: {
	    title: 'IM_MESSAGE_SUPERVISOR_TARIFF_EXPANSION_TITLE',
	    description: 'IM_MESSAGE_SUPERVISOR_TARIFF_EXPANSION_DESCRIPTION',
	    detailBtn: {
	      text: 'IM_MESSAGE_SUPERVISOR_TARIFF_EXPANSION_BUTTON_CHANGE_TARIFF',
	      fn: () => {}
	    },
	    infoBtn: {
	      text: 'IM_MESSAGE_SUPERVISOR_TARIFF_EXPANSION_BUTTON_MORE_DETAILED',
	      fn: () => {}
	    }
	  }
	};

	const TOOL_ID_PARAMS_KEY = 'toolId';
	// @vue/component
	const SupervisorTariffExpansionMessage = {
	  name: 'SupervisorTariffExpansionMessage',
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
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<SupervisorBaseMessage
			:item="item"
			:dialogId="dialogId"
			:title="loc(toolData.title)"
			:description="loc(toolData.description)"
		>
			<template #image>
				<div :class="['bx-im-message-update-features__image-wrapper', modifierImageClass]" />
			</template>
			<template #actions>
				<ButtonComponent
					:size="ButtonSize.L"
					:isRounded="true"
					:color="ButtonColor.Success"
					:text="loc(toolData.detailBtn.text)"
					@click="toolData.detailBtn.fn"
				/>
				<ButtonComponent
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="loc(toolData.infoBtn.text)"
					@click="toolData.infoBtn.fn"
				/>
			</template>
		</SupervisorBaseMessage>
	`
	};

	exports.SupervisorTariffExpansionMessage = SupervisorTariffExpansionMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=tariff-expansion.bundle.js.map
