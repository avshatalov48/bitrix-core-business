/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,ui_vue3_components_richLoc,im_v2_lib_analytics,im_v2_lib_desktopApi,im_v2_lib_utils,im_v2_lib_helpdesk) {
	'use strict';

	// @vue/component
	const DesktopUpdateBanner = {
	  name: 'DesktopUpdateBanner',
	  components: {
	    RichLoc: ui_vue3_components_richLoc.RichLoc
	  },
	  computed: {
	    desktopDownloadUrl() {
	      const settings = main_core.Extension.getSettings('im.v2.component.desktop.update-banner');
	      return settings.get('desktopDownloadUrl');
	    },
	    headerText() {
	      const messageCode = this.isSupportedOs ? 'IM_DESKTOP_UPDATE_BANNER_HEADING' : 'IM_DESKTOP_UPDATE_BANNER_HEADING_NOT_SUPPORTED_OS';
	      return this.loc(messageCode);
	    },
	    descriptionText() {
	      const messageCode = this.isSupportedOs ? 'IM_DESKTOP_UPDATE_BANNER_DESCRIPTION' : 'IM_DESKTOP_UPDATE_BANNER_DESCRIPTION_NOT_SUPPORTED_OS';
	      return main_core.Loc.getMessage(messageCode, {
	        '[br]': '\n'
	      });
	    },
	    isSupportedOpenLinkInBrowser() {
	      const DESKTOP_VERSION_SUPPORTED_OPEN_LINK_IN_BROWSER = 16;
	      return im_v2_lib_desktopApi.DesktopApi.getMajorVersion() >= DESKTOP_VERSION_SUPPORTED_OPEN_LINK_IN_BROWSER;
	    },
	    isSupportedOs() {
	      if (!main_core.Browser.isWin()) {
	        return true;
	      }
	      if (im_v2_lib_desktopApi.DesktopApi.getWindowsOSBuild() > 0) {
	        const MIN_SUPPORTED_WINDOWS_BUILD = 10000;
	        return im_v2_lib_desktopApi.DesktopApi.getWindowsOSBuild() > MIN_SUPPORTED_WINDOWS_BUILD;
	      }
	      return this.isSupportedWindowsVersion;
	    },
	    /**
	     * Old Windows versions (NT 6x) are not supported.
	     * Windows NT 6.2 - Windows 8
	     * Windows NT 6.3 - Windows 8.1
	     * Windows NT 6.1 - Windows 7
	     * Detection relies on checking for the substring "Windows NT 6" in the user agent.
	     * This method is not fully reliable and may cause issues with future Windows versions (e.g., Windows NT 6x).
	     * @returns {boolean}
	     */
	    isSupportedWindowsVersion() {
	      const userAgent = navigator.userAgent.toLowerCase();
	      return !userAgent.includes('windows nt 6');
	    }
	  },
	  methods: {
	    showHelpArticle() {
	      const ARTICLE_CODE = '25374968';
	      im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	      im_v2_lib_analytics.Analytics.getInstance().desktopUpdateBanner.onClickMoreInformation();
	    },
	    openRootDomain() {
	      im_v2_lib_analytics.Analytics.getInstance().desktopUpdateBanner.onOpenWebVersion();
	      im_v2_lib_desktopApi.DesktopApi.openInBrowser(location.origin);
	    },
	    openDesktopDownloadPage() {
	      im_v2_lib_analytics.Analytics.getInstance().desktopUpdateBanner.onClickUpdate();
	      im_v2_lib_utils.Utils.browser.openLink(this.desktopDownloadUrl);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-messenger__scope bx-im-desktop-update-banner__container">
			<div class="bx-im-desktop-update-banner__logo">
				<h1 class="bx-im-desktop-update-banner__logo-heading">{{ loc('IM_DESKTOP_UPDATE_BANNER_LOGO') }}</h1>
			</div>
			<div class="bx-im-desktop-update-banner__content-container">
				<div class="bx-im-desktop-update-banner__image"></div>
				<div class="bx-im-desktop-update-banner__content">
					<h2 class="bx-im-desktop-update-banner__heading">{{ loc('IM_DESKTOP_UPDATE_BANNER_HEADING') }}</h2>
					<RichLoc class="bx-im-desktop-update-banner__description" :text="descriptionText" placeholder="[helpdesk]">
						<template #helpdesk="{ text }">
							<span class="bx-im-desktop-update-banner__description-more" @click="showHelpArticle">
								{{ text }}
							</span>
						</template>
					</RichLoc>
				</div>
				<div v-if="isSupportedOs" class="bx-im-desktop-update-banner__buttons">
					<button @click="openDesktopDownloadPage" class="bx-im-desktop-update-banner__update">
						{{ loc('IM_DESKTOP_UPDATE_BANNER_UPDATE') }}
					</button>
					<button v-if="isSupportedOpenLinkInBrowser" @click="openRootDomain" class="bx-im-desktop-update-banner__version">
						{{ loc('IM_DESKTOP_UPDATE_BANNER_VERSION') }}
					</button>
				</div>
			</div>
		</div>
	`
	};

	exports.DesktopUpdateBanner = DesktopUpdateBanner;

}((this.BX.Messenger.v2.Component.Desktop = this.BX.Messenger.v2.Component.Desktop || {}),BX,BX.UI.Vue3.Components,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=update-banner.bundle.js.map
