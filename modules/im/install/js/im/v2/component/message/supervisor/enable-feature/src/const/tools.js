import { Loc } from 'main.core';

import { Analytics } from 'im.v2.lib.analytics';
import { openHelpdeskArticle } from 'im.v2.lib.helpdesk';

import { UserStatisticsLink as CheckInQrAuthPopup } from 'stafftrack.user-statistics-link';

import { EnableFeatures } from '../../../base/src/const/features';

const onOpenToolsSettings = (toolId: string) => {
	return () => {
		Analytics.getInstance().supervisor.onOpenToolsSettings(toolId);
		BX.SidePanel.Instance.open(`${window.location.origin}/settings/configs/?page=tools`);
	};
};

const openCheckInQrCode = () => {
	if (!CheckInQrAuthPopup)
	{
		return;
	}

	new CheckInQrAuthPopup({ intent: CheckInQrAuthPopup.CHECK_IN_SETTINGS_INTENT }).show();
};

const onHelpClick = (ARTICLE_CODE: string) => openHelpdeskArticle(ARTICLE_CODE);

export const metaData = {
	[EnableFeatures.copilot]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_COPILOT_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.copilot),
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
			callback: onOpenToolsSettings(EnableFeatures.newsLine),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('18634548'),
		},
	},
	[EnableFeatures.chatCalls]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHAT_CALLS_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.chatCalls),
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
			callback: onOpenToolsSettings(EnableFeatures.calendar),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('17525000'),
		},
	},
	[EnableFeatures.documents]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_DOCUMENTS_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.documents),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('20338924'),
		},
	},
	[EnableFeatures.mail]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MAIL_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.mail),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('12487078'),
		},
	},
	[EnableFeatures.groups]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_GROUPS_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.groups),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('6481473'),
		},
	},
	[EnableFeatures.tasks]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_TASKS_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.tasks),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('17962166'),
		},
	},
	[EnableFeatures.crm]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CRM_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.crm),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('1470510'),
		},
	},
	[EnableFeatures.marketing]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_MARKETING_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.marketing),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('10437776'),
		},
	},
	[EnableFeatures.automation]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_AUTOMATION_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.automation),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('16547618'),
		},
	},
	[EnableFeatures.warehouseAccounting]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WAREHOUSE_ACCOUNTING_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.warehouseAccounting),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('17792018'),
		},
	},
	[EnableFeatures.sign]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SIGN_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.sign),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('16555500'),
		},
	},
	[EnableFeatures.websitesStores]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_WEBSITES_STORES_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.websitesStores),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('17540360'),
		},
	},
	[EnableFeatures.scrum]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SCRUM_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SCRUM_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.scrum),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('13660630'),
		},
	},
	[EnableFeatures.invoices]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_INVOICES_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_INVOICES_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.invoices),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('17614982'),
		},
	},
	[EnableFeatures.saleshub]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SALESHUB_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_SALESHUB_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: onOpenToolsSettings(EnableFeatures.saleshub),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('9289135'),
		},
	},
	[EnableFeatures.checkIn]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHECK_IN_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHECK_IN_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: () => openCheckInQrCode(),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('20922794'),
		},
	},
	[EnableFeatures.checkInGeo]: {
		title: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHECK_IN_GEO_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_CHECK_IN_GEO_DESCRIPTION'),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_OPEN_SETTINGS'),
			callback: () => openCheckInQrCode(),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_ENABLE_FEATURE_BUTTON_MORE_DETAILED'),
			callback: () => onHelpClick('20922794'),
		},
	},
};
