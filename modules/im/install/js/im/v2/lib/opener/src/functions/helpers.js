import { Type } from 'main.core';
import { SidePanel } from 'main.sidepanel';

import { GetParameter, NavigationMenuItem, Path } from 'im.v2.const';
import { LayoutManager } from 'im.v2.lib.layout';
import { Utils } from 'im.v2.lib.utils';

import type { SliderManager } from 'main.sidepanel';

type NavigationItem = $Values<typeof NavigationMenuItem>;
type GetParameterType = $Values<typeof GetParameter>;
type OpenChatConfig = {
	navigationItem: NavigationItem,
	dialogId: string,
	messageId?: number,
}

export const checkHistoryDialogId = (dialogId: string): boolean => {
	return Utils.dialog.isLinesHistoryId(dialogId) || Utils.dialog.isLinesExternalId(dialogId);
};

export const prepareHistorySliderLink = (dialogId: string): string => {
	const getParams = new URLSearchParams({
		[GetParameter.openHistory]: dialogId,
		[GetParameter.backgroundType]: 'light',
		[GetParameter.legacyMode]: 'Y',
	});

	return `${Path.history}?${getParams.toString()}`;
};

export const normalizeEntityId = (entityId: any): string => {
	if (Type.isString(entityId))
	{
		return entityId;
	}

	if (Type.isNumber(entityId))
	{
		return entityId.toString();
	}

	return '';
};

export const isEmbeddedModeWithActiveSlider = (): boolean => {
	const sidePanelManager: SliderManager = SidePanel.Instance;

	return LayoutManager.getInstance().isEmbeddedMode() && sidePanelManager.getOpenSlidersCount() > 0;
};

export const openChatInNewTab = ({ navigationItem, dialogId, messageId }: OpenChatConfig): void => {
	const getParams = new URLSearchParams();

	const urlParameter = getUrlParameterForNavigation(navigationItem);
	if (Type.isStringFilled(dialogId))
	{
		getParams.append(urlParameter, dialogId);
	}

	if (messageId > 0)
	{
		getParams.append(GetParameter.openMessage, messageId);
	}

	Utils.browser.openLink(`${Path.online}?${getParams.toString()}`);
};

const getUrlParameterForNavigation = (navigationItem: NavigationItem): GetParameterType => {
	const navigationToGetParameterMap = {
		[NavigationMenuItem.chat]: GetParameter.openChat,
		[NavigationMenuItem.openlines]: GetParameter.openLines,
	};

	return navigationToGetParameterMap[navigationItem] ?? GetParameter.openChat;
};
