this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_component_navigation,im_v2_component_list_container_recent,im_v2_component_list_container_openline,im_v2_component_content_chat,im_v2_component_content_createChat,im_v2_component_content_openline,im_v2_component_content_notification,im_v2_component_content_market,im_v2_lib_logger,im_v2_lib_init,im_v2_const,im_v2_lib_call,im_v2_lib_theme) {
	'use strict';

	// @vue/component
	const Messenger = {
	  name: 'MessengerRoot',
	  components: {
	    MessengerNavigation: im_v2_component_navigation.MessengerNavigation,
	    RecentListContainer: im_v2_component_list_container_recent.RecentListContainer,
	    OpenlineListContainer: im_v2_component_list_container_openline.OpenlineListContainer,
	    ChatContent: im_v2_component_content_chat.ChatContent,
	    CreateChatContent: im_v2_component_content_createChat.CreateChatContent,
	    OpenlineContent: im_v2_component_content_openline.OpenlineContent,
	    NotificationContent: im_v2_component_content_notification.NotificationContent,
	    MarketContent: im_v2_component_content_market.MarketContent
	  },
	  data() {
	    return {
	      contextMessageId: 0
	    };
	  },
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    currentLayout() {
	      return im_v2_const.Layout[this.layout.name];
	    },
	    entityId() {
	      return this.layout.entityId;
	    },
	    currentDialog() {
	      return this.$store.getters['dialogues/get'](this.entityId, true);
	    },
	    isChat() {
	      return this.layout.name === im_v2_const.Layout.chat.name;
	    },
	    isNotification() {
	      return this.layout.name === im_v2_const.Layout.notification.name;
	    },
	    containerClasses() {
	      return {
	        '--dark-theme': im_v2_lib_theme.ThemeManager.isDarkTheme(),
	        '--light-theme': im_v2_lib_theme.ThemeManager.isLightTheme()
	      };
	    },
	    callContainerClass() {
	      return [im_v2_lib_call.CallManager.viewContainerClass];
	    }
	  },
	  created() {
	    im_v2_lib_init.InitManager.start();
	    im_v2_lib_logger.Logger.warn('MessengerRoot created');
	  },
	  mounted() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	  },
	  methods: {
	    onNavigationClick({
	      layoutName,
	      layoutEntityId
	    }) {
	      let entityId = '';
	      const isChatNext = layoutName === im_v2_const.Layout.chat.name;
	      const isMarketNext = layoutName === im_v2_const.Layout.market.name;
	      if (isChatNext) {
	        entityId = this.previouslySelectedChat;
	      } else if (isMarketNext) {
	        entityId = layoutEntityId;
	      }
	      this.$store.dispatch('application/setLayout', {
	        layoutName,
	        entityId
	      });
	    },
	    onEntitySelect({
	      layoutName,
	      entityId
	    }) {
	      this.saveLastOpenedChat(entityId);
	      this.$store.dispatch('application/setLayout', {
	        layoutName,
	        entityId
	      });
	    },
	    onGoToMessageContext(event) {
	      const {
	        dialogId,
	        messageId
	      } = event.getData();
	      if (this.currentDialog.dialogId === dialogId) {
	        return;
	      }
	      this.$store.dispatch('application/setLayout', {
	        layoutName: im_v2_const.Layout.chat.name,
	        entityId: dialogId,
	        contextId: messageId
	      });
	    },
	    saveLastOpenedChat(dialogId) {
	      this.previouslySelectedChat = dialogId || '';
	    }
	  },
	  template: `
		<div class="bx-im-messenger__scope bx-im-messenger__container" :class="containerClasses">
			<div class="bx-im-messenger__navigation_container">
				<MessengerNavigation :currentLayoutName="currentLayout.name" @navigationClick="onNavigationClick" />
			</div>
			<div class="bx-im-messenger__layout_container">
				<div class="bx-im-messenger__layout_content">
					<div v-if="currentLayout.list" class="bx-im-messenger__list_container">
						<KeepAlive>
							<component :is="currentLayout.list" @selectEntity="onEntitySelect" />
						</KeepAlive>
					</div>
					<div class="bx-im-messenger__content_container">
						<component :is="currentLayout.content" :entityId="entityId" :contextMessageId="contextMessageId" />
					</div>
				</div>
			</div>
		</div>
		<div :class="callContainerClass"></div>
	`
	};

	exports.Messenger = Messenger;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Event,BX.Messenger.v2.Component,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=messenger.bundle.js.map
