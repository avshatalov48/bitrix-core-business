/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,ui_feedback_form) {
	'use strict';

	class PartnerForm {
	  static show(params) {
	    const formParams = {
	      id: params.id,
	      forms: main_core.Extension.getSettings('ui.feedback.partnerform').get('partnerForms'),
	      portalUri: main_core.Extension.getSettings('ui.feedback.partnerform').get('partnerUri'),
	      presets: {
	        source: params.source
	      }
	    };
	    if (main_core.Type.isStringFilled(params.title)) {
	      formParams.title = params.title;
	      formParams.showTitle = true;
	    }
	    if (main_core.Type.isStringFilled(params.button)) {
	      formParams.button = params.button;
	    }
	    ui_feedback_form.Form.open(formParams);
	  }
	}

	exports.PartnerForm = PartnerForm;

}((this.BX.UI.Feedback = this.BX.UI.Feedback || {}),BX,BX.UI.Feedback));
//# sourceMappingURL=partner-form.bundle.js.map
