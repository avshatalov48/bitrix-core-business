import { Browser, Extension, Loc } from 'main.core';
import { RichLoc } from 'ui.vue3.components.rich-loc';

import { Analytics } from 'im.v2.lib.analytics';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Utils } from 'im.v2.lib.utils';
import { openHelpdeskArticle } from 'im.v2.lib.helpdesk';

import './css/update-banner.css';

// @vue/component
export const DesktopUpdateBanner = {
	name: 'DesktopUpdateBanner',
	components: { RichLoc },
	computed:
	{
		desktopDownloadUrl(): string
		{
			const settings = Extension.getSettings('im.v2.component.desktop.update-banner');

			return settings.get('desktopDownloadUrl');
		},
		headerText(): string
		{
			const messageCode = this.isSupportedOs
				? 'IM_DESKTOP_UPDATE_BANNER_HEADING'
				: 'IM_DESKTOP_UPDATE_BANNER_HEADING_NOT_SUPPORTED_OS'
			;

			return this.loc(messageCode);
		},
		descriptionText(): string
		{
			const messageCode = this.isSupportedOs
				? 'IM_DESKTOP_UPDATE_BANNER_DESCRIPTION'
				: 'IM_DESKTOP_UPDATE_BANNER_DESCRIPTION_NOT_SUPPORTED_OS'
			;

			return Loc.getMessage(messageCode, {
				'[br]': '\n',
			});
		},
		isSupportedOpenLinkInBrowser(): boolean
		{
			const DESKTOP_VERSION_SUPPORTED_OPEN_LINK_IN_BROWSER = 16;

			return DesktopApi.getMajorVersion() >= DESKTOP_VERSION_SUPPORTED_OPEN_LINK_IN_BROWSER;
		},
		isSupportedOs(): boolean
		{
			if (!Browser.isWin())
			{
				return true;
			}

			if (DesktopApi.getWindowsOSBuild() > 0)
			{
				const MIN_SUPPORTED_WINDOWS_BUILD = 10000;

				return DesktopApi.getWindowsOSBuild() > MIN_SUPPORTED_WINDOWS_BUILD;
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
		isSupportedWindowsVersion(): boolean
		{
			const userAgent = navigator.userAgent.toLowerCase();

			return !userAgent.includes('windows nt 6');
		},
	},
	methods:
	{
		showHelpArticle(): string
		{
			const ARTICLE_CODE = '25374968';

			openHelpdeskArticle(ARTICLE_CODE);
			Analytics.getInstance().desktopUpdateBanner.onClickMoreInformation();
		},
		openRootDomain()
		{
			Analytics.getInstance().desktopUpdateBanner.onOpenWebVersion();
			DesktopApi.openInBrowser(location.origin);
		},
		openDesktopDownloadPage()
		{
			Analytics.getInstance().desktopUpdateBanner.onClickUpdate();
			Utils.browser.openLink(this.desktopDownloadUrl);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
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
	`,
};
