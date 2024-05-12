/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_application_core,im_v2_lib_utils,im_v2_component_message_base,im_v2_component_message_elements,im_v2_component_message_default,main_core,im_v2_component_elements) {
	'use strict';

	const Await = Object.freeze({
	  inviteCompany: 'inviteCompany',
	  inviteEmployeeSes: 'inviteEmployeeSes',
	  inviteEmployeeTaxcom: 'inviteEmployeeTaxcom',
	  inviteEmployeeGosKey: 'inviteEmployeeGosKey',
	  inviteReviewer: 'inviteReviewer'
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
	  refusedCompany: 'refusedCompany',
	  employeeStoppedToCompany: 'employeeStoppedToCompany',
	  documentStopped: 'documentStopped'
	});

	const metaData = {
	  [Await.inviteCompany]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {},
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteEmployeeSes]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_SES_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {},
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteEmployeeTaxcom]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_TAXCOM_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_TAXCOM_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_TAXCOM_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {},
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Await.inviteEmployeeGosKey]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_EMPLOYEE_GOS_KEY_DESCRIPTION'),
	    button: null
	  },
	  [Await.inviteReviewer]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_INVITE_REVIEWER_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {},
	      color: im_v2_component_elements.ButtonColor.Primary
	    }
	  },
	  [Success.doneCompany]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_DESCRIPTION'),
	    button: {
	      text: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_COMPANY_BUTTON_TEXT'),
	      callback: ({
	        user,
	        document
	      }) => {},
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
	      }) => {},
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
	      }) => {},
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
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DONE_FROM_REVIEWER_DESCRIPTION'),
	    button: null
	  },
	  [Failure.refusedCompany]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_REFUSED_COMPANY_DESCRIPTION'),
	    button: null
	  },
	  [Failure.employeeStoppedToCompany]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_EMPLOYEE_STOPPED_TO_COMPANY_DESCRIPTION'),
	    button: null
	  },
	  [Failure.documentStopped]: {
	    title: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_TITLE'),
	    description: main_core.Loc.getMessage('IM_MESSAGE_SIGN_DOCUMENT_STOPPED_DESCRIPTION'),
	    button: null
	  }
	};

	const PARAMS_KEY = {
	  STAGE_ID: 'stageId',
	  USER: 'user',
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
	      var _this$user, _this$document, _this$user2;
	      let text = phrase != null ? phrase : '';
	      const userLink = im_v2_lib_utils.Utils.user.getProfileLink((_this$user = this.user) == null ? void 0 : _this$user.id);
	      const articleLink = `javascript: BX.Helper?.show('redirect=detail&code=${this.helpArticle}')`;
	      const LINK_CLASS = 'bx-im-message-sign__link';
	      const DOCUMENT_CLASS = 'bx-im-message-sign__document';
	      const phrases = {
	        '#DOCUMENT_NAME#': `<span class="${DOCUMENT_CLASS}">${main_core.Text.encode((_this$document = this.document) == null ? void 0 : _this$document.name)}</span>`,
	        '#USER_LINK#': `<a href="${userLink}" class="${LINK_CLASS}">${main_core.Text.encode((_this$user2 = this.user) == null ? void 0 : _this$user2.name)}</a>`,
	        '[helpdesklink]': `<a href="${articleLink}" class="${LINK_CLASS}">`,
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
