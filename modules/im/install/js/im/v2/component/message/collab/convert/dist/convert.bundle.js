/* eslint-disable */
this.BX = this.BX || {};
this.BX.IM = this.BX.IM || {};
this.BX.IM.V2 = this.BX.IM.V2 || {};
this.BX.IM.V2.Component = this.BX.IM.V2.Component || {};
this.BX.IM.V2.Component.Message = this.BX.IM.V2.Component.Message || {};
(function (exports,main_core,ui_vue3_components_richLoc,im_v2_component_message_base) {
	'use strict';

	// @vue/component
	const ConvertToCollabMessage = {
	  name: 'ConvertToCollabMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    RichLoc: ui_vue3_components_richLoc.RichLoc
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
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return main_core.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
		>
			<div class="bx-im-message-collab-convert__container">
				<div class="bx-im-message-collab-convert__image"/>
				<div class="bx-im-message-collab-convert__content">
					<div class="bx-im-message-collab-convert__title">
						{{ loc('IM_MESSAGE_COLLAB_CONVERT_TITLE') }}
					</div>
					<div class="bx-im-message-collab-convert__description">
						<RichLoc :text="loc('IM_MESSAGE_COLLAB_CONVERT_DESCRIPTION')" placeholder="[br/]">
							<template #br>
								<br>
							</template>
						</RichLoc>
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.ConvertToCollabMessage = ConvertToCollabMessage;

}((this.BX.IM.V2.Component.Message.Collab = this.BX.IM.V2.Component.Message.Collab || {}),BX,BX.UI.Vue3.Components,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=convert.bundle.js.map
