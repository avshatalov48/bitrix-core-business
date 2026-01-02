/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core) {
	'use strict';

	const AI_ASSISTANT_FORM_ID = 'im.ai-assistant.feedback';
	const COPILOT_FORM_ID = 'im.copilot.feedback';
	const GENERAL_FORM_ID = 'im-v2-feedback';
	const FormContext = {
	  aiAssistantBot: 'chat_ai-assistant_one_by_one',
	  copilotBot: 'chat_copilot_tab_one_by_one',
	  copilotGroup: 'chat_copilot_tab_multi',
	  general: 'profile'
	};
	const FormConfigAiAssistant = {
	  id: AI_ASSISTANT_FORM_ID,
	  forms: [{
	    zones: ['es'],
	    id: 838,
	    lang: 'es',
	    sec: 'm82wkx'
	  }, {
	    zones: ['en'],
	    id: 834,
	    lang: 'en',
	    sec: 'qnauno'
	  }, {
	    zones: ['de'],
	    id: 836,
	    lang: 'de',
	    sec: 'frcsm3'
	  }, {
	    zones: ['com.br'],
	    id: 840,
	    lang: 'com.br',
	    sec: 'ufjnte'
	  }, {
	    zones: ['ru', 'kz', 'by', 'uz'],
	    id: 2982,
	    lang: 'ru',
	    sec: 'vqmcxn'
	  }]
	};
	const FormConfigCopilot = {
	  id: COPILOT_FORM_ID,
	  forms: [{
	    zones: ['es'],
	    id: 684,
	    lang: 'es',
	    sec: 'svvq1x'
	  }, {
	    zones: ['en'],
	    id: 686,
	    lang: 'en',
	    sec: 'tjwodz'
	  }, {
	    zones: ['de'],
	    id: 688,
	    lang: 'de',
	    sec: 'nrwksg'
	  }, {
	    zones: ['com.br'],
	    id: 690,
	    lang: 'com.br',
	    sec: 'kpte6m'
	  }, {
	    zones: ['ru', 'by', 'kz'],
	    id: 692,
	    lang: 'ru',
	    sec: 'jbujn0'
	  }]
	};
	const FormConfigGeneral = {
	  id: GENERAL_FORM_ID,
	  forms: [{
	    zones: ['ru'],
	    id: 550,
	    sec: '50my2x',
	    lang: 'ru'
	  }, {
	    zones: ['en'],
	    id: 560,
	    sec: '621lbr',
	    lang: 'en'
	  }]
	};

	const FEEDBACK_EXTENSION = 'ui.feedback.form';
	var _openForm = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openForm");
	var _generateFormIdSuffix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("generateFormIdSuffix");
	class FeedbackManager {
	  constructor() {
	    Object.defineProperty(this, _generateFormIdSuffix, {
	      value: _generateFormIdSuffix2
	    });
	    Object.defineProperty(this, _openForm, {
	      value: _openForm2
	    });
	  }
	  async openAiAssistantForm() {
	    const {
	      id,
	      forms
	    } = FormConfigAiAssistant;
	    const formattedId = `${id}-${babelHelpers.classPrivateFieldLooseBase(this, _generateFormIdSuffix)[_generateFormIdSuffix]()}`;
	    const config = {
	      id: formattedId,
	      forms,
	      presets: {
	        sender_page: FormContext.aiAssistantBot
	      }
	    };
	    void babelHelpers.classPrivateFieldLooseBase(this, _openForm)[_openForm](config);
	  }
	  async openCopilotForm(params) {
	    const {
	      id,
	      forms
	    } = FormConfigCopilot;
	    const formattedId = `${id}-${babelHelpers.classPrivateFieldLooseBase(this, _generateFormIdSuffix)[_generateFormIdSuffix]()}`;
	    const context = params.userCounter <= 2 ? FormContext.copilotBot : FormContext.copilotGroup;
	    const config = {
	      id: formattedId,
	      forms,
	      presets: {
	        sender_page: context,
	        language: im_v2_application_core.Core.getLanguageId(),
	        cp_answer: params.text
	      }
	    };
	    void babelHelpers.classPrivateFieldLooseBase(this, _openForm)[_openForm](config);
	  }
	  async openGeneralForm() {
	    const {
	      id,
	      forms
	    } = FormConfigGeneral;
	    const config = {
	      id,
	      forms,
	      presets: {
	        sender_page: FormContext.general
	      }
	    };
	    void babelHelpers.classPrivateFieldLooseBase(this, _openForm)[_openForm](config);
	  }
	}
	async function _openForm2(config) {
	  await main_core.Runtime.loadExtension(FEEDBACK_EXTENSION);
	  BX.UI.Feedback.Form.open(config);
	}
	function _generateFormIdSuffix2() {
	  return Math.round(Math.random() * 1000);
	}

	exports.FeedbackManager = FeedbackManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application));
//# sourceMappingURL=feedback.bundle.js.map
