import { Loc } from 'main.core';

import { Analytics } from 'im.v2.lib.analytics';

import { UpdateFeatures } from '../../../base/src/const/features';

const onOpenPriceTable = (featureId: string) => {
	return () => {
		Analytics.getInstance().onOpenPriceTable(featureId);
		BX.SidePanel.Instance.open(`${window.location.origin}/settings/license_all.php`);
	};
};
const onHelpClick = (ARTICLE_CODE: string) => BX.Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);

export const metaData = {
	[UpdateFeatures.collaborativeDocumentEditing]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.collaborativeDocumentEditing}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.collaborativeDocumentEditing}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.collaborativeDocumentEditing),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('20338924'),
		},
	},
	[UpdateFeatures.crmAnalytics]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.crmAnalytics}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.crmAnalytics}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.crmAnalytics),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('9673603'),
		},
	},
	[UpdateFeatures.crmHistory]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.crmHistory}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.crmHistory}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.crmHistory),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('17301310'),
		},
	},
	[UpdateFeatures.leadsCRM]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.leadsCRM}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.leadsCRM}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.leadsCRM),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('1357950'),
		},
	},
	[UpdateFeatures.crmInvoices]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.crmInvoices}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.crmInvoices}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.crmInvoices),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('17614982'),
		},
	},
	[UpdateFeatures.enterpriseAdmin]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.enterpriseAdmin}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.enterpriseAdmin}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.enterpriseAdmin),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('17994262'),
		},
	},
	[UpdateFeatures.loginHistory]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.loginHistory}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.loginHistory}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.loginHistory),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('19124604'),
		},
	},
	[UpdateFeatures.mailBoxNumber]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.mailBoxNumber}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.mailBoxNumber}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.mailBoxNumber),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('19083990'),
		},
	},
	[UpdateFeatures.tasksRobots]: {
		title: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_TITLE_${UpdateFeatures.tasksRobots}`),
		description: Loc.getMessage(`IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DESCRIPTION_${UpdateFeatures.tasksRobots}`),
		detailButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_DETAIL_BUTTON_TITLE'),
			callback: onOpenPriceTable(UpdateFeatures.tasksRobots),
		},
		infoButton: {
			text: Loc.getMessage('IM_MESSAGE_SUPERVISOR_UPDATE_FEATURE_TARIFF_INFO_BUTTON_TITLE'),
			callback: () => onHelpClick('17784680'),
		},
	},
};
