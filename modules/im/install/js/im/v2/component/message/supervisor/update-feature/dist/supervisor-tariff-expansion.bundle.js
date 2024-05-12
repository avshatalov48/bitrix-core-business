/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements,im_v2_component_message_supervisor_base) {
	'use strict';

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
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor
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
			:title="loc('IM_MESSAGE_SUPERVISOR_TARIFF_EXPANSION_TITLE')"
			:description="loc('IM_MESSAGE_SUPERVISOR_TARIFF_EXPANSION_DESCRIPTION')"
		>
			<template #image>
				<div class="bx-im-message-tariff-expansion__image-wrapper" />
			</template>
			<template #actions>
				<ButtonComponent
					:size="ButtonSize.L"
					:isRounded="true"
					:color="ButtonColor.Success"
					:text="loc('IM_MESSAGE_SUPERVISOR_TARIFF_EXPANSION_BUTTON_CHANGE_TARIFF')"
				/>
				<ButtonComponent
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="loc('IM_MESSAGE_SUPERVISOR_TARIFF_EXPANSION_BUTTON_MORE_DETAILED')"
				/>
			</template>
		</SupervisorBaseMessage>
	`
	};

	exports.SupervisorTariffExpansionMessage = SupervisorTariffExpansionMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=supervisor-tariff-expansion.bundle.js.map
