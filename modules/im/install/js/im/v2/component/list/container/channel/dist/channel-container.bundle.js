/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_list_items_channel,im_v2_component_elements,im_v2_const,im_v2_lib_analytics,im_v2_lib_logger,im_v2_lib_promo,im_v2_lib_createChat) {
	'use strict';

	// @vue/component
	const ChannelListContainer = {
	  name: 'ChannelListContainer',
	  components: {
	    ChannelList: im_v2_component_list_items_channel.ChannelList,
	    CreateChatPromo: im_v2_component_elements.CreateChatPromo
	  },
	  emits: ['selectEntity'],
	  data() {
	    return {
	      showPromo: false
	    };
	  },
	  computed: {
	    ChatType: () => im_v2_const.ChatType
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('List: Channel container created');
	  },
	  methods: {
	    onChatClick(dialogId) {
	      this.$emit('selectEntity', {
	        layoutName: im_v2_const.Layout.channel.name,
	        entityId: dialogId
	      });
	    },
	    onCreateClick() {
	      im_v2_lib_analytics.Analytics.getInstance().onStartCreateNewChat(im_v2_const.ChatType.channel);
	      const promoBannerIsNeeded = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.createChannel);
	      if (promoBannerIsNeeded) {
	        this.showPromo = true;
	        return;
	      }
	      this.startChannelCreation();
	    },
	    onPromoContinueClick() {
	      im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.createChannel);
	      this.showPromo = false;
	      this.startChannelCreation();
	    },
	    startChannelCreation() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().startChatCreation(im_v2_const.ChatType.channel);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-container-channel__container">
			<div class="bx-im-list-container-channel__header_container">
				<div class="bx-im-list-container-channel__header_title">{{ loc('IM_LIST_CONTAINER_CHANNEL_HEADER_TITLE') }}</div>
				<div @click="onCreateClick" class="bx-im-list-container-channel__header_create-channel"></div>
			</div>
			<div class="bx-im-list-container-channel__elements_container">
				<div class="bx-im-list-container-channel__elements">
					<ChannelList @chatClick="onChatClick" />
				</div>
			</div>
		</div>
		<CreateChatPromo
			v-if="showPromo"
			:chatType="ChatType.channel"
			@continue="onPromoContinueClick"
			@close="showPromo = false"
		/>
	`
	};

	exports.ChannelListContainer = ChannelListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=channel-container.bundle.js.map
