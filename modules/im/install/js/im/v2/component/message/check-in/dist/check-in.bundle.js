/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,stafftrack_userStatisticsLink,im_v2_lib_analytics,im_v2_component_message_base,im_v2_component_message_elements,im_v2_component_message_default) {
	'use strict';

	const paramsKey = Object.freeze({
	  url: 'url',
	  status: 'status',
	  location: 'location'
	});

	// @vue/component
	const CheckInMessage = {
	  name: 'CheckInMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessage: im_v2_component_message_default.DefaultMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent
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
	    componentParams() {
	      return this.message.componentParams;
	    },
	    mapUrl() {
	      const origin = window.location.origin;
	      const url = this.componentParams[paramsKey.url];
	      return url.startsWith('/') ? origin + url : url;
	    },
	    status() {
	      var _this$componentParams;
	      return (_this$componentParams = this.componentParams[paramsKey.status]) != null ? _this$componentParams : '';
	    },
	    location() {
	      var _this$componentParams2;
	      return (_this$componentParams2 = this.componentParams[paramsKey.location]) != null ? _this$componentParams2 : '';
	    },
	    hasLocation() {
	      return Boolean(this.location);
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return main_core.Loc.getMessage(phraseCode, replacements);
	    },
	    onClick() {
	      this.showQrPopup();
	      im_v2_lib_analytics.Analytics.getInstance().onOpenCheckInPopup();
	    },
	    showQrPopup() {
	      if (!stafftrack_userStatisticsLink.UserStatisticsLink) {
	        return;
	      }
	      new stafftrack_userStatisticsLink.UserStatisticsLink({
	        intent: 'check-in'
	      }).show();
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
		>
			<div class="bx-im-message-check-in__container">
				<div class="bx-im-message-check-in__image-container">
					<img class="bx-im-message-check-in__image" :src="mapUrl" alt="map" />
					<div v-if="hasLocation" class="bx-im-message-check-in__marker" />
					<div v-else class="bx-im-message-check-in__status">
						{{ status }}
					</div>
				</div>
				<div v-if="hasLocation" :title="location" class="bx-im-message-check-in__location">
					{{ location }}
				</div>
				<div
					class="bx-im-message-check-in__action" 
					@click="onClick"
					:title="loc('IM_MESSAGE_CHECK_IN_ACTION_TEXT')"
				>
					<div class="bx-im-message-check-in__action-icon"></div>
					<span>{{ loc('IM_MESSAGE_CHECK_IN_ACTION_TEXT') }}</span>
				</div>
				<div class="bx-im-message-check-in__bottom-panel">
					<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" />
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.CheckInMessage = CheckInMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX.Stafftrack,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=check-in.bundle.js.map
