import { Loc } from 'main.core';

import { EnableFeatures } from '../../../base/src/const/features';

const onOpenToolsSettings = () => BX.SidePanel.Instance.open(`${window.location.origin}/settings/configs/?page=tools`);
const onHelpClick = (ARTICLE_CODE: string) => BX.Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);

export const metaData = {
	[EnableFeatures.copilot]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.newsLine]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_NEWS_LINE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_NEWS_LINE_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.chatCalls]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.calendar]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CALENDAR_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CALENDAR_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.documents]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.mail]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.groups]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.tasks]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.crm]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.marketing]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.automation]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.warehouseAccounting]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.sign]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
	[EnableFeatures.websitesStores]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings,
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12925062'),
		},
	},
};
