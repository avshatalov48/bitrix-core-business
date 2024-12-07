import { Loc, Browser } from 'main.core';

import { ButtonColor } from 'im.v2.component.elements';
import { Await, Failure, Success } from './sign';

export const metaData = {
	[Await.inviteCompany]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_DESCRIPTION_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Await.inviteCompanyWithInitiator]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_TITLE_INITIATOR'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_DESCRIPTION_INITIATOR'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_BUTTON_TEXT_INITIATOR'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Await.inviteEmployeeSes]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_DESCRIPTION_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Await.inviteEmployeeSesWithInitiator]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_DESCRIPTION_INITIATOR'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Await.inviteEmployeeGosKey]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_TITLE_MSGVER_1'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_DESCRIPTION_MSGVER_1'),
		button: null,
	},
	[Await.inviteEmployeeGosKeyV2]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_TITLE_MSGVER_1'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_V2_DESCRIPTION'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_V2_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.PrimaryBorder,
		},
	},
	[Await.inviteEmployeeGosKeyWithInitiator]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_TITLE_MSGVER_1'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_V2_INITIATOR_DESCRIPTION'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_V2_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.PrimaryBorder,
		},
	},
	[Await.inviteReviewer]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_DESCRIPTION_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Await.inviteReviewerWithInitiator]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_TITLE_INITIATOR'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_DESCRIPTION_INITIATOR'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_BUTTON_TEXT_INITIATOR'),
			callback: ({ initiator, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Await.inviteEditor]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_DESCRIPTION_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Await.inviteEditorWithInitiator]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_TITLE_INITIATOR'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_DESCRIPTION_INITIATOR'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_BUTTON_TEXT_INITIATOR'),
			callback: ({ initiator, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Success.doneCompany]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_TITLE_MSGVER_1'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_DESCRIPTION'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, false);
			},
			color: ButtonColor.PrimaryBorder,
		},
	},
	[Success.doneEmployee]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_DESCRIPTION'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.PrimaryBorder,
		},
	},
	[Success.doneEmployeeGosKey]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_GOS_KEY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_GOS_KEY_DESCRIPTION'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_GOS_KEY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.PrimaryBorder,
		},
	},
	[Success.doneFromAssignee]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_ASSIGNEE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_ASSIGNEE_DESCRIPTION'),
		button: null,
	},
	[Success.doneFromEditor]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_EDITOR_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_EDITOR_DESCRIPTION'),
		button: null,
	},
	[Success.doneFromReviewer]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_REVIEWER_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_REVIEWER_DESCRIPTION_MSGVER_1'),
		button: null,
	},
	[Failure.refusedCompanyV2]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTION_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.refusedCompanyV2M]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTIONM_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.refusedCompanyV2F]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTIONF_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.stoppedToEmployee]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE_DESCRIPTION'),
	},
	[Failure.stoppedToEmployeeM]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE_DESCRIPTIONM'),
	},
	[Failure.stoppedToEmployeeF]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE_DESCRIPTIONF'),
	},
	[Failure.employeeStoppedToCompanyV2]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTION_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.employeeStoppedToCompanyV2M]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTIONM_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.employeeStoppedToCompanyV2F]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTIONF_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.documentStoppedToAssignee]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_DESCRIPTION'),
	},
	[Failure.documentStoppedToAssigneeM]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_DESCRIPTIONM'),
	},
	[Failure.documentStoppedToAssigneeF]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_DESCRIPTIONF'),
	},
	[Failure.documentStoppedToReviewer]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_DESCRIPTION'),
	},
	[Failure.documentStoppedToReviewerM]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_DESCRIPTIONM'),
	},
	[Failure.documentStoppedToReviewerF]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_DESCRIPTIONF'),
	},
	[Failure.documentStoppedToEditor]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_DESCRIPTION'),
	},
	[Failure.documentStoppedToEditorM]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_DESCRIPTIONM'),
	},
	[Failure.documentStoppedToEditorF]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_DESCRIPTIONF'),
	},
	[Failure.documentStoppedToInitiator]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTION_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.documentStoppedToInitiatorM]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTIONM_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.documentStoppedToInitiatorF]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTIONF_MSGVER_1'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.refusedCompany]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTION'),
	},
	[Failure.employeeStoppedToCompany]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTION'),
	},
	[Failure.documentStopped]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTION'),
	},
	[Failure.documentCancelled]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_CANCELLED_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_CANCELLED_DESCRIPTION'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_CANCELLED_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.signingError]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_SIGNING_ERROR_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_SIGNING_ERROR_DESCRIPTION'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_SIGNING_ERROR_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
	[Failure.repeatSigning]: {
		title: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_REPEAT_TITLE'),
		description: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_REPEAT_DESCRIPTION'),
		button: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_REPEAT_BUTTON_TEXT'),
			callback: ({ user, document }) => {
				goToPrimaryLink(document, true);
			},
			color: ButtonColor.Primary,
		},
	},
};

function goToPrimaryLink(document: { link: string }, openInSlider: boolean = false)
{
	if (document.link !== undefined)
	{
		if (!Browser.isMobile() && openInSlider)
		{
			openLinkInSlider(document.link);
		}
		else
		{
			window.open(document.link);
		}
	}
}

function openLinkInSlider(link: string): any
{
	if (!isSigningLink(link))
	{
		return BX.Runtime.loadExtension('sign.v2.b2e.sign-link').then(() => {
			return BX.SidePanel.Instance.open(link, {
				extensions: ['sign.v2.b2e.sign-link'],
			});
		});
	}

	return BX.SidePanel.Instance.open('sign:stub:sign-link', {
		width: 900,
		cacheable: false,
		allowCrossOrigin: true,
		allowCrossDomain: true,
		allowChangeHistory: false,
		newWindowUrl: link,
		copyLinkLabel: true,
		newWindowLabel: true,
		loader: '/bitrix/js/intranet/sidepanel/bindings/images/sign_mask.svg',
		label: {
			text: Loc.getMessage('IM_MESSAGE_SIGN_SIDEPANEL_BTN_SIGN'),
			bgColor: '#C48300',
		},
		contentCallback(slider): Promise {
			return BX.Runtime.loadExtension('sign.v2.b2e.sign-link').then((exports) => {
				const memberIdFromLinkToSigning = /\/sign\/link\/member\/(\d+)\//i.exec(link);

				return (new exports.SignLink({ memberId: memberIdFromLinkToSigning[1], slider }))
					.render()
				;
			});
		},
	});
}

function isSigningLink(link: string): boolean
{
	return /^\/sign\/link\/member\/\d+\/$/.test(link);
}
