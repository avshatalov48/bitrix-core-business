import { FeaturePromoter } from 'ui.info-helper';

import { Layout, SliderCode, NavigationMenuItem } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { LayoutManager } from 'im.v2.lib.layout';
import { PhoneManager } from 'im.v2.lib.phone';
import { Utils } from 'im.v2.lib.utils';
import { MarketManager } from 'im.v2.lib.market';

const customClickHandler = {
	[NavigationMenuItem.copilot]: onCopilotClick,
	[NavigationMenuItem.call]: onCallClick,
	[NavigationMenuItem.timemanager]: onTimeManagerClick,
	[NavigationMenuItem.homepage]: onHomepageClick,
	[NavigationMenuItem.market]: onMarketClick,
};

export type NavigationMenuItemParams = {
	id: string,
	entityId?: string,
	target?: HTMLElement,
};

export const NavigationManager = {
	open(menuItem: NavigationMenuItemParams): void
	{
		const { id, entityId } = menuItem;
		if (!NavigationMenuItem[id])
		{
			return;
		}

		if (customClickHandler[id])
		{
			customClickHandler[id](menuItem);

			return;
		}

		changeLayout({
			layoutName: id,
			layoutEntityId: entityId,
		});
	},
	isLayout(id: string): boolean
	{
		return Boolean(Layout[id]);
	},
	isMarketApp(payload: NavigationMenuItemParams): boolean
	{
		const { id, entityId } = payload;
		const isMarketMenuItem = id === NavigationMenuItem.market;

		return isMarketMenuItem && Boolean(entityId);
	},
};

function onCopilotClick(payload: NavigationMenuItemParams)
{
	if (!FeatureManager.isFeatureAvailable(Feature.copilotActive))
	{
		const promoter = new FeaturePromoter({ code: SliderCode.copilotDisabled });
		promoter.show();
		Analytics.getInstance().copilot.onOpenTab({ isAvailable: false });

		return;
	}

	changeLayout({
		layoutName: Layout.copilot,
		layoutEntityId: payload.entityId,
	});
}

function onCallClick(payload: NavigationMenuItemParams)
{
	const KEYPAD_OFFSET_TOP = -30;
	const KEYPAD_OFFSET_LEFT = 64;

	PhoneManager.getInstance().openKeyPad({
		bindElement: payload?.target,
		offsetTop: KEYPAD_OFFSET_TOP,
		offsetLeft: KEYPAD_OFFSET_LEFT,
	});
}

function onTimeManagerClick()
{
	BX.Timeman?.Monitor?.openReport();
}

function onHomepageClick()
{
	Utils.browser.openLink('/');
}

function onMarketClick(payload: NavigationMenuItemParams)
{
	const { entityId } = payload;
	if (entityId)
	{
		changeLayout({
			layoutName: Layout.market,
			layoutEntityId: entityId,
		});

		return;
	}

	MarketManager.openMarketplace();
}

function handleMenuItem(payload: NavigationMenuItemParams): void
{
	const { id: layoutName, entityId: layoutEntityId, asLink } = payload;
	if (asLink)
	{
		openLink({ layoutName, layoutEntityId });

		return;
	}

	changeLayout({ layoutName, layoutEntityId });
}

function openLink({ layoutName, layoutEntityId }: LayoutParams): void
{
	const LayoutToUrlConfigMap: Record<string, { paramName: string, useParamByDefault: boolean, canUseId: boolean }> = {
		[NavigationMenuItem.chat]: {
			paramName: GetParameter.openChat,
			useParamByDefault: false,
			canUseId: true,
		},
		[NavigationMenuItem.copilot]: {
			paramName: GetParameter.openCopilotChat,
			useParamByDefault: true,
			canUseId: true,
		},
		[NavigationMenuItem.collab]: {
			paramName: GetParameter.openCollab,
			useParamByDefault: true,
			canUseId: true,
		},
		[NavigationMenuItem.channel]: {
			paramName: GetParameter.openChannel,
			useParamByDefault: true,
			canUseId: true,
		},
		[NavigationMenuItem.tasksTask]: {
			paramName: GetParameter.openTaskComments,
			useParamByDefault: true,
			canUseId: true,
		},
		[NavigationMenuItem.openlines]: {
			paramName: GetParameter.openLines,
			useParamByDefault: true,
			canUseId: true,
		},
		[NavigationMenuItem.notification]: {
			paramName: GetParameter.openNotifications,
			useParamByDefault: true,
			canUseId: false,
		},
		[NavigationMenuItem.settings]: {
			paramName: GetParameter.openSettings,
			useParamByDefault: true,
			canUseId: false,
		},
	};

	const basePath = Path.online;
	const urlConfig = LayoutToUrlConfigMap[layoutName];
	if (!urlConfig)
	{
		return;
	}
}

function changeLayout({ layoutName, layoutEntityId }: { layoutName: string, layoutEntityId?: string | number })
{
	const layoutManager = LayoutManager.getInstance();
	if (!layoutManager.isValidLayout(layoutName))
	{
		return;
	}

	let entityId = layoutEntityId;

	const lastOpenedElement = layoutManager.getLastOpenedElement(layoutName);
	if (!entityId && lastOpenedElement)
	{
		entityId = lastOpenedElement;
	}

	void layoutManager.setLayout({ name: layoutName, entityId });
}
