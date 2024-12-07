/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_designTokens,ui_fonts_opensans,ui_vue,ui_forms,main_popup,im_lib_logger) {
    'use strict';

    ui_vue.BitrixVue.component('bx-im-component-call-feedback', {
      props: {
        darkMode: {
          type: Boolean,
          required: false,
          "default": false
        },
        callDetails: {
          type: Object,
          required: false,
          "default": function _default() {
            return {
              id: 0,
              provider: '',
              userCount: 0,
              browser: '',
              isMobile: false,
              isConference: false
            };
          }
        }
      },
      data: function data() {
        return {
          selectedRating: 0,
          hoveredRating: 0,
          selectedProblem: '',
          problemDescription: '',
          isFilled: false
        };
      },
      created: function created() {
        this.initProblemsList();
        this.selectedProblem = this.problemsList.noProblem;
      },
      computed: {
        showTextarea: function showTextarea() {
          return this.selectedProblem === this.problemsList.other;
        },
        wrapClasses: function wrapClasses() {
          return ['bx-im-call-feedback-wrap', this.darkMode ? 'bx-im-call-feedback-wrap-dark' : ''];
        }
      },
      methods: {
        onRatingMouseover: function onRatingMouseover(index) {
          this.hoveredRating = index;
        },
        onRatingMouseOut: function onRatingMouseOut(index) {
          this.hoveredRating = 0;
        },
        onRatingClick: function onRatingClick(index) {
          this.selectedRating = index;
        },
        prepareFeedback: function prepareFeedback() {
          return {
            event: 'call_feedback',
            call_id: this.callDetails.id,
            kind: this.callDetails.provider,
            userCount: this.callDetails.userCount,
            browser: this.callDetails.browser,
            isMobile: this.callDetails.isMobile,
            isConference: this.callDetails.isConference,
            callRating: this.selectedRating,
            callProblem: this.getProblemCode(),
            problemDescription: this.problemDescription
          };
        },
        getProblemCode: function getProblemCode() {
          var problem = '';
          for (var _i = 0, _Object$entries = Object.entries(this.problemsList); _i < _Object$entries.length; _i++) {
            var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
              key = _Object$entries$_i[0],
              value = _Object$entries$_i[1];
            if (this.selectedProblem === value) {
              problem = key;
            }
          }
          return problem;
        },
        sendFeedback: function sendFeedback() {
          this.isFilled = true;
          var feedback = this.prepareFeedback();
          im_lib_logger.Logger.warn('Call feedback', feedback);
          this.$emit('feedbackSent');
          if (this.selectedRating === 0 && this.selectedProblem === this.problemsList.noProblem) {
            return;
          }
          BX.Call.Util.sendTelemetryEvent(feedback);
        },
        getRatingStarClasses: function getRatingStarClasses(index) {
          return ['bx-im-call-feedback-rating-star', this.hoveredRating >= index || this.selectedRating >= index ? 'bx-im-call-feedback-rating-star-filled' : 'bx-im-call-feedback-rating-star-empty'];
        },
        initProblemsList: function initProblemsList() {
          this.problemsList = {
            noProblem: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_NO_ISSUE'),
            videoQuality: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_VIDEO_QUALITY'),
            cantSeeEachOther: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_CANT_SEE_EACH_OTHER'),
            cantHearEachOther: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_CANT_HEAR_EACH_OTHER'),
            audioQuality: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_AUDIO_QUALITY'),
            screenSharingProblem: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_SCREEN_SHARING_PROBLEM'),
            recordingProblem: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_RECORDING_PROBLEM'),
            callInterfaceProblem: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_CALL_INTERFACE_PROBLEM'),
            gotDisconnected: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_GOT_DISCONNECTED'),
            other: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_OTHER')
          };
        },
        createProblemSelectPopup: function createProblemSelectPopup() {
          var _this = this;
          var problemSelect = this.$refs['problemSelect'];
          var className = 'bx-im-call-feedback-problem-select' + (this.darkMode ? ' bx-im-call-feedback-problem-select-dark' : '');
          var items = [];
          for (var _i2 = 0, _Object$values = Object.values(this.problemsList); _i2 < _Object$values.length; _i2++) {
            var problem = _Object$values[_i2];
            items.push({
              text: problem,
              onclick: function onclick(event, item) {
                _this.onProblemClick(item);
              },
              className: 'bx-im-call-feedback-problem-option'
            });
          }
          this.problemSelectPopup = new main_popup.Menu({
            bindElement: problemSelect,
            items: items,
            className: className,
            offsetTop: 0
          });
        },
        toggleProblemSelectPopup: function toggleProblemSelectPopup() {
          if (!this.problemSelectPopup) {
            this.createProblemSelectPopup();
          }
          this.problemSelectPopup.toggle();
        },
        onProblemClick: function onProblemClick(problem) {
          this.selectedProblem = problem.text;
          this.problemSelectPopup.toggle();
        }
      },
      // language=Vue
      template: "\n\t\t<div :class=\"wrapClasses\">\n\t\t\t<div class=\"bx-im-call-feedback-header\">\n\t\t\t\t<div class=\"bx-im-call-feedback-header-title\">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_VIDEOCALL_FINISHED') }}</div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-call-feedback-content\">\n\t\t\t  \t<template v-if=\"!isFilled\">\n\t\t\t\t\t<div class=\"bx-im-call-feedback-content-title\">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_RATE_QUALITY') }}</div>\n\t\t\t\t\t<div class=\"bx-im-call-feedback-rating-wrap\">\n\t\t\t\t\t  \t<template v-for=\"i in 5\">\n\t\t\t\t\t\t\t<div\n\t\t\t\t\t\t  \t\t@click=\"onRatingClick(i)\"\n\t\t\t\t\t\t\t\t@mouseover=\"onRatingMouseover(i)\"\n\t\t\t\t\t\t\t\t@mouseout=\"onRatingMouseOut(i)\"\n\t\t\t\t\t\t\t  \t:class=\"getRatingStarClasses(i)\"\n\t\t\t\t\t\t\t></div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"bx-im-call-feedback-problem\">\n\t\t\t\t\t\t<div @click=\"toggleProblemSelectPopup\" class=\"bx-im-call-feedback-problem-selected ui-ctl ui-ctl-after-icon ui-ctl-dropdown\" ref=\"problemSelect\">\n\t\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t\t<div class=\"ui-ctl-element\">{{ selectedProblem }}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t  \t<template v-if=\"showTextarea\">\n\t\t\t\t  \t\t<textarea\n\t\t\t\t\t\t  class=\"bx-im-call-feedback-problem-description\"\n\t\t\t\t\t\t  v-model=\"problemDescription\"\n\t\t\t\t\t\t  :placeholder=\"$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_ISSUE_DESCRIPTION')\"\n\t\t\t\t\t\t></textarea>\n\t\t\t\t\t</template>\n\t\t\t\t  \t<div class=\"bx-im-call-feedback-submit-wrap\">\n\t\t\t\t\t\t<button @click=\"sendFeedback\" class=\"ui-btn ui-btn-lg ui-btn-primary bx-im-call-feedback-submit\">\n\t\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_SEND') }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t  \t<template v-else>\n\t\t\t\t  \t<div class=\"bx-im-call-feedback-filled-wrap\">\n\t\t\t\t\t\t<div class=\"bx-im-call-feedback-filled-icon\"></div>\n\t\t\t\t\t\t<div class=\"bx-im-call-feedback-filled-text\">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_FEEDBACK_FILLED') }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t</div>\n\t"
    });

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX,BX,BX,BX.Main,BX.Messenger.Lib));
//# sourceMappingURL=call-feedback.bundle.js.map
