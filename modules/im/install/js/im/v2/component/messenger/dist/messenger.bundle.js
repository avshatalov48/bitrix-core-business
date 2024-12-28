/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_designTokens,ui_fonts_opensans,im_v2_css_tokens,im_v2_css_icons,im_v2_css_classes,im_v2_component_navigation,im_v2_component_list_container_recent,im_v2_component_list_container_openline,im_v2_component_list_container_channel,im_v2_component_list_container_collab,im_v2_component_content_chat,im_v2_component_content_chatForms_forms,im_v2_component_content_openlines,im_v2_component_content_openlinesV2,im_v2_component_content_notification,im_v2_component_content_market,im_v2_component_content_settings,im_v2_component_list_container_copilot,im_v2_component_content_copilot,im_v2_lib_analytics,im_v2_lib_logger,im_v2_lib_init,im_v2_const,im_v2_lib_call,im_v2_lib_theme,im_v2_lib_desktop,im_v2_lib_layout) {
	'use strict';

	// @vue/component
	const Messenger = {
	  name: 'MessengerRoot',
	  components: {
	    MessengerNavigation: im_v2_component_navigation.MessengerNavigation,
	    RecentListContainer: im_v2_component_list_container_recent.RecentListContainer,
	    ChannelListContainer: im_v2_component_list_container_channel.ChannelListContainer,
	    CollabListContainer: im_v2_component_list_container_collab.CollabListContainer,
	    OpenlineListContainer: im_v2_component_list_container_openline.OpenlineListContainer,
	    ChatContent: im_v2_component_content_chat.ChatContent,
	    CreateChatContent: im_v2_component_content_chatForms_forms.CreateChatContent,
	    UpdateChatContent: im_v2_component_content_chatForms_forms.UpdateChatContent,
	    OpenlinesContent: im_v2_component_content_openlines.OpenlinesContent,
	    NotificationContent: im_v2_component_content_notification.NotificationContent,
	    OpenlinesV2Content: im_v2_component_content_openlinesV2.OpenlinesV2Content,
	    MarketContent: im_v2_component_content_market.MarketContent,
	    SettingsContent: im_v2_component_content_settings.SettingsContent,
	    CopilotListContainer: im_v2_component_list_container_copilot.CopilotListContainer,
	    CopilotContent: im_v2_component_content_copilot.CopilotContent
	  },
	  data() {
	    return {
	      openlinesContentOpened: false
	    };
	  },
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    layoutName() {
	      var _this$layout;
	      return (_this$layout = this.layout) == null ? void 0 : _this$layout.name;
	    },
	    currentLayout() {
	      return im_v2_const.Layout[this.layout.name];
	    },
	    entityId() {
	      return this.layout.entityId;
	    },
	    isOpenline() {
	      return this.layout.name === im_v2_const.Layout.openlines.name;
	    },
	    hasList() {
	      return Boolean(this.currentLayout.list);
	    },
	    containerClasses() {
	      return {
	        '--dark-theme': im_v2_lib_theme.ThemeManager.isDarkTheme(),
	        '--light-theme': im_v2_lib_theme.ThemeManager.isLightTheme(),
	        '--desktop': im_v2_lib_desktop.DesktopManager.isDesktop()
	      };
	    },
	    callContainerClass() {
	      return [im_v2_lib_call.CallManager.viewContainerClass];
	    }
	  },
	  watch: {
	    layoutName: {
	      handler(newLayoutName) {
	        if (newLayoutName !== im_v2_const.Layout.openlines.name) {
	          return;
	        }
	        this.openlinesContentOpened = true;
	      },
	      immediate: true
	    }
	  },
	  created() {
	    im_v2_lib_init.InitManager.start();
	    im_v2_lib_layout.LayoutManager.init();
	    im_v2_lib_logger.Logger.warn('MessengerRoot created');
	    this.getLayoutManager().restoreLastLayout();
	    this.sendAnalytics();
	  },
	  beforeUnmount() {
	    this.getLayoutManager().destroy();
	  },
	  methods: {
	    onNavigationClick({
	      layoutName,
	      layoutEntityId
	    }) {
	      let entityId = layoutEntityId;
	      const lastOpenedElement = this.getLayoutManager().getLastOpenedElement(layoutName);
	      if (!entityId && lastOpenedElement) {
	        entityId = lastOpenedElement;
	      }
	      this.getLayoutManager().setLayout({
	        name: layoutName,
	        entityId
	      });
	    },
	    onEntitySelect({
	      layoutName,
	      entityId
	    }) {
	      this.getLayoutManager().setLayout({
	        name: layoutName,
	        entityId
	      });
	    },
	    getLayoutManager() {
	      return im_v2_lib_layout.LayoutManager.getInstance();
	    },
	    sendAnalytics() {
	      im_v2_lib_analytics.Analytics.getInstance().onOpenMessenger();
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
					<div class="bx-im-messenger__content_container" :class="{'--with-list': hasList}">
						<div v-if="openlinesContentOpened" class="bx-im-messenger__openlines_container" :class="{'--hidden': !isOpenline}">
							<OpenlinesContent v-show="isOpenline" :entityId="entityId" />
						</div>
						<component v-if="!isOpenline" :is="currentLayout.content" :entityId="entityId" />
					</div>
				</div>
			</div>
		</div>
		<div :class="callContainerClass"></div>
	`
	};

	exports.Messenger = Messenger;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX,BX,BX.Messenger.v2.Css,BX.Messenger.v2.Css,BX.Messenger.v2.Css,BX.Messenger.v2.Component,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=messenger.bundle.js.map
