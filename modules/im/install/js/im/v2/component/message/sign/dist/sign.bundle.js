/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_application_core,im_v2_lib_utils,im_v2_component_message_base,im_v2_component_message_elements,im_v2_component_message_default,main_core,im_v2_component_elements) {
	'use strict';

	const Await = Object.freeze({
	  inviteCompany: 'inviteCompany',
	  inviteCompanyWithInitiator: 'inviteCompanyWithInitiator',
	  inviteEmployeeSes: 'inviteEmployeeSes',
	  inviteEmployeeSesWithInitiator: 'inviteEmployeeSesWithInitiator',
	  inviteEmployeeGosKey: 'inviteEmployeeGosKey',
	  inviteEmployeeGosKeyV2: 'inviteEmployeeGosKeyV2',
	  inviteEmployeeGosKeyWithInitiator: 'inviteEmployeeGosKeyWithInitiator',
	  inviteReviewer: 'inviteReviewer',
	  inviteReviewerWithInitiator: 'inviteReviewerWithInitiator',
	  inviteEditor: 'inviteEditor',
	  inviteEditorWithInitiator: 'inviteEditorWithInitiator'
	});
	const Success = Object.freeze({
	  doneCompany: 'doneCompany',
	  doneEmployee: 'doneEmployee',
	  doneEmployeeGosKey: 'doneEmployeeGosKey',
	  doneFromAssignee: 'doneFromAssignee',
	  doneFromEditor: 'doneFromEditor',
	  doneFromReviewer: 'doneFromReviewer'
	});
	const Failure = Object.freeze({
	  refusedCompanyV2: 'refusedCompanyV2',
	  refusedCompanyV2M: 'refusedCompanyV2M',
	  refusedCompanyV2F: 'refusedCompanyV2F',
	  employeeStoppedToCompanyV2: 'employeeStoppedToCompanyV2',
	  employeeStoppedToCompanyV2M: 'employeeStoppedToCompanyV2M',
	  employeeStoppedToCompanyV2F: 'employeeStoppedToCompanyV2F',
	  documentStoppedToInitiator: 'documentStoppedToInitiator',
	  documentStoppedToInitiatorM: 'documentStoppedToInitiatorM',
	  documentStoppedToInitiatorF: 'documentStoppedToInitiatorF',
	  documentStoppedToAssignee: 'documentStoppedToAssignee',
	  documentStoppedToAssigneeM: 'documentStoppedToAssigneeM',
	  documentStoppedToAssigneeF: 'documentStoppedToAssigneeF',
	  documentStoppedToReviewer: 'documentStoppedToReviewer',
	  documentStoppedToReviewerM: 'documentStoppedToReviewerM',
	  documentStoppedToReviewerF: 'documentStoppedToReviewerF',
	  documentStoppedToEditor: 'documentStoppedToEditor',
	  documentStoppedToEditorM: 'documentStoppedToEditorM',
	  documentStoppedToEditorF: 'documentStoppedToEditorF',
	  refusedCompany: 'refusedCompany',
	  employeeStoppedToCompany: 'employeeStoppedToCompany',
	  documentStopped: 'documentStopped',
	  documentCancelled: 'documentCancelled',
	  stoppedToEmployee: 'stoppedToEmployee',
	  stoppedToEmployeeM: 'stoppedToEmployeeM',
	  stoppedToEmployeeF: 'stoppedToEmployeeF',
	  signingError: 'signingError',
	  repeatSigning: 'repeatSigning'
	});

	const metaData = {
	  [Await.inviteCompany]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_DESCRIPTION_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteCompanyWithInitiator]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_TITLE_INITIATOR'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_DESCRIPTION_INITIATOR'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_BUTTON_TEXT_INITIATOR'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteEmployeeSes]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_DESCRIPTION_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteEmployeeSesWithInitiator]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_DESCRIPTION_INITIATOR'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteEmployeeGosKey]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_TITLE_MSGVER_1'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_DESCRIPTION_MSGVER_1'),
	    button: null
	  },
	  [Await.inviteEmployeeGosKeyV2]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_TITLE_MSGVER_1'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_V2_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_V2_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.PrimaryBorder
	    }
	  },
	  [Await.inviteEmployeeGosKeyWithInitiator]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_TITLE_MSGVER_1'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_V2_INITIATOR_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_V2_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.PrimaryBorder
	    }
	  },
	  [Await.inviteReviewer]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_DESCRIPTION_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteReviewerWithInitiator]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_TITLE_INITIATOR'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_DESCRIPTION_INITIATOR'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_BUTTON_TEXT_INITIATOR'),
	      callback: ({
	        initiator,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteEditor]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_DESCRIPTION_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteEditorWithInitiator]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_TITLE_INITIATOR'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_DESCRIPTION_INITIATOR'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EDITOR_BUTTON_TEXT_INITIATOR'),
	      callback: ({
	        initiator,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Success.doneCompany]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_TITLE_MSGVER_1'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, false);
	      },
	      color: im_v2_component_elements.ButtonColor.PrimaryBorder
	    }
	  },
	  [Success.doneEmployee]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.PrimaryBorder
	    }
	  },
	  [Success.doneEmployeeGosKey]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_GOS_KEY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_GOS_KEY_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_EMPLOYEE_GOS_KEY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.PrimaryBorder
	    }
	  },
	  [Success.doneFromAssignee]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_ASSIGNEE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_ASSIGNEE_DESCRIPTION'),
	    button: null
	  },
	  [Success.doneFromEditor]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_EDITOR_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_EDITOR_DESCRIPTION'),
	    button: null
	  },
	  [Success.doneFromReviewer]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_REVIEWER_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_REVIEWER_DESCRIPTION_MSGVER_1'),
	    button: null
	  },
	  [Failure.refusedCompanyV2]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTION_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.refusedCompanyV2M]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTIONM_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.refusedCompanyV2F]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTIONF_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.stoppedToEmployee]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE_DESCRIPTION')
	  },
	  [Failure.stoppedToEmployeeM]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE_DESCRIPTIONM')
	  },
	  [Failure.stoppedToEmployeeF]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_STOPPED_TO_EMPLOYEE_TITLE_DESCRIPTIONF')
	  },
	  [Failure.employeeStoppedToCompanyV2]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTION_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.employeeStoppedToCompanyV2M]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTIONM_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.employeeStoppedToCompanyV2F]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTIONF_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.documentStoppedToAssignee]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_DESCRIPTION')
	  },
	  [Failure.documentStoppedToAssigneeM]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_DESCRIPTIONM')
	  },
	  [Failure.documentStoppedToAssigneeF]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_ASSIGNEE_DESCRIPTIONF')
	  },
	  [Failure.documentStoppedToReviewer]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_DESCRIPTION')
	  },
	  [Failure.documentStoppedToReviewerM]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_DESCRIPTIONM')
	  },
	  [Failure.documentStoppedToReviewerF]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_REVIEWER_DESCRIPTIONF')
	  },
	  [Failure.documentStoppedToEditor]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_DESCRIPTION')
	  },
	  [Failure.documentStoppedToEditorM]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_DESCRIPTIONM')
	  },
	  [Failure.documentStoppedToEditorF]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TO_EDITOR_DESCRIPTIONF')
	  },
	  [Failure.documentStoppedToInitiator]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTION_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.documentStoppedToInitiatorM]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTIONM_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.documentStoppedToInitiatorF]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTIONF_MSGVER_1'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.refusedCompany]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTION')
	  },
	  [Failure.employeeStoppedToCompany]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTION')
	  },
	  [Failure.documentStopped]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTION')
	  },
	  [Failure.documentCancelled]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_CANCELLED_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_CANCELLED_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_CANCELLED_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.signingError]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_SIGNING_ERROR_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_SIGNING_ERROR_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_SIGNING_ERROR_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Failure.repeatSigning]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_REPEAT_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_REPEAT_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_REPEAT_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {
	        goToPrimaryLink(document, true);
	      },
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  }
	};
	function goToPrimaryLink(document, openInSlider = false) {
	  if (document.link !== undefined) {
	    if (!main_core.Browser.isMobile() && openInSlider) {
	      openLinkInSlider(document.link);
	    } else {
	      window.open(document.link);
	    }
	  }
	}
	function openLinkInSlider(link) {
	  if (!isSigningLink(link)) {
	    return BX.Runtime.loadExtension('sign.v2.b2e.sign-link').then(() => {
	      return BX.SidePanel.Instance.open(link, {
	        extensions: ['sign.v2.b2e.sign-link']
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
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_SIDEPANEL_BTN_SIGN'),
	      bgColor: '#C48300'
	    },
	    contentCallback(slider) {
	      return BX.Runtime.loadExtension('sign.v2.b2e.sign-link').then(exports => {
	        const memberIdFromLinkToSigning = /\/sign\/link\/member\/(\d+)\//i.exec(link);
	        return new exports.SignLink({
	          memberId: memberIdFromLinkToSigning[1],
	          slider
	        }).render();
	      });
	    }
	  });
	}
	function isSigningLink(link) {
	  return /^\/sign\/link\/member\/\d+\/$/.test(link);
	}

	const PARAMS_KEY = {
	  STAGE_ID: 'stageId',
	  USER: 'user',
	  INITIATOR: 'initiator',
	  DOCUMENT: 'document',
	  HELP_ARTICLE: 'helpArticle'
	};

	// @vue/component
	const SignMessage = {
	  name: 'SignMessage',
	  components: {
	    ButtonComponent: im_v2_component_elements.Button,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessage: im_v2_component_message_default.DefaultMessage,
	    MessageStatus: im_v2_component_message_elements.MessageStatus
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    componentParams() {
	      return this.message.componentParams;
	    },
	    stageId() {
	      var _this$componentParams;
	      return (_this$componentParams = this.componentParams[PARAMS_KEY.STAGE_ID]) != null ? _this$componentParams : '';
	    },
	    user() {
	      return this.componentParams[PARAMS_KEY.USER];
	    },
	    initiator() {
	      return this.componentParams[PARAMS_KEY.INITIATOR];
	    },
	    document() {
	      return this.componentParams[PARAMS_KEY.DOCUMENT];
	    },
	    helpArticle() {
	      var _this$componentParams2;
	      return (_this$componentParams2 = this.componentParams[PARAMS_KEY.HELP_ARTICLE]) != null ? _this$componentParams2 : '';
	    },
	    signData() {
	      const data = metaData[this.stageId];
	      if (!data) {
	        console.error('SignMessage: signData is undefined.');
	      }
	      return data;
	    },
	    title() {
	      var _this$signData$title, _this$signData;
	      return (_this$signData$title = (_this$signData = this.signData) == null ? void 0 : _this$signData.title) != null ? _this$signData$title : '';
	    },
	    description() {
	      var _this$signData$descri, _this$signData2;
	      return (_this$signData$descri = (_this$signData2 = this.signData) == null ? void 0 : _this$signData2.description) != null ? _this$signData$descri : '';
	    },
	    button() {
	      var _this$signData3;
	      return (_this$signData3 = this.signData) == null ? void 0 : _this$signData3.button;
	    },
	    isAwaitSign() {
	      return Object.values(Await).includes(this.stageId);
	    },
	    isSuccessSign() {
	      return Object.values(Success).includes(this.stageId);
	    },
	    isFailureSign() {
	      return Object.values(Failure).includes(this.stageId);
	    },
	    isSelfMessage() {
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    containerClasses() {
	      return {
	        '--self': this.isSelfMessage,
	        '--await': this.isAwaitSign,
	        '--success': this.isSuccessSign,
	        '--failure': this.isFailureSign
	      };
	    }
	  },
	  methods: {
	    replacePhrase(phrase) {
	      var _this$user, _this$initiator, _this$document, _this$user2, _this$initiator2;
	      let text = phrase != null ? phrase : '';
	      const userLink = im_v2_lib_utils.Utils.user.getProfileLink((_this$user = this.user) == null ? void 0 : _this$user.id);
	      const initiatorLink = im_v2_lib_utils.Utils.user.getProfileLink((_this$initiator = this.initiator) == null ? void 0 : _this$initiator.id);
	      const articleLink = `BX.Helper?.show('redirect=detail&code=${this.helpArticle}')`;
	      const LINK_CLASS = 'bx-im-message-sign__link';
	      const DOCUMENT_CLASS = 'bx-im-message-sign__document';
	      const phrases = {
	        '#DOCUMENT_NAME#': `<span class="${DOCUMENT_CLASS}">${main_core.Text.encode((_this$document = this.document) == null ? void 0 : _this$document.name)}</span>`,
	        '#USER_LINK#': `<a href="${userLink}" class="${LINK_CLASS}">${main_core.Text.encode((_this$user2 = this.user) == null ? void 0 : _this$user2.name)}</a>`,
	        '#INITIATOR_LINK#': `<a href="${initiatorLink}" class="${LINK_CLASS}">${main_core.Text.encode((_this$initiator2 = this.initiator) == null ? void 0 : _this$initiator2.name)}</a>`,
	        '[helpdesklink]': `<a onclick="${articleLink}" class="${LINK_CLASS}">`,
	        '[/helpdesklink]': '</a>'
	      };
	      Object.keys(phrases).forEach(code => {
	        text = text.replaceAll(code, phrases[code]);
	      });
	      return text;
	    }
	  },
	  template: `
		<DefaultMessage v-if="!signData" :item="item" :dialogId="dialogId" />
		<BaseMessage
			v-else
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
			class="bx-im-message-sign__scope"
		>
			<div :class="['bx-im-message-sign__container', containerClasses]">
				<div class="bx-im-message-sign__image" />
				<div class="bx-im-message-sign__content">
					<div class="bx-im-message-sign__title">
						{{ title }}
					</div>
					<div class="bx-im-message-sign__description" v-html="replacePhrase(description)" />
					<div class="bx-im-message-sign__buttons_container">
						<ButtonComponent
							v-if="button"
							:size="ButtonSize.L"
							isRounded
							:text="button.text"
							:color="button.color"
							@click="button.callback({ user, document })"
						/>
					</div>
				</div>
				<div class="bx-im-message-sign__status_container">
					<MessageStatus :item="message" />
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.SignMessage = SignMessage;
	exports.Await = Await;
	exports.Success = Success;
	exports.Failure = Failure;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=sign.bundle.js.map
