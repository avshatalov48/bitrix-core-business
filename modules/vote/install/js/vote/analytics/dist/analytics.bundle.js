/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_analytics) {
	'use strict';

	const AnalyticsEvents = Object.freeze({
	  publishPoll: 'publish_poll',
	  finishPoll: 'finish_poll',
	  vote: 'vote',
	  cancelVote: 'cancel_vote',
	  copyPollLink: 'copy_poll_link',
	  setOptions: 'set_options',
	  isMultipleChoice: 'is_multiple_choice',
	  setCancelVote: 'set_cancel_vote',
	  setTimeLimit: 'set_time_limit',
	  downloadResult: 'download_result'
	});
	const AnalyticsCategories = Object.freeze({
	  pollSettings: 'poll_settings',
	  polls: 'polls'
	});

	const getAnalyticsOptions = async (dialogId, messageId) => {
	  try {
	    const {
	      Analytics: ImAnalytics
	    } = await main_core.Runtime.loadExtension('im.v2.lib.analytics');
	    const analyticsInstance = ImAnalytics.getInstance();
	    const options = analyticsInstance.vote.getAnalyticsOptions(dialogId);
	    options.p3 = `pollId_${messageId}`;
	    return options;
	  } catch (ex) {
	    console.error(ex);
	    return null;
	  }
	};
	const publishVote = (anonymousVote, messageId) => {
	  const {
	    searchParams
	  } = new URL(document.location.href);
	  const options = {
	    tool: searchParams.get('st[tool]'),
	    event: AnalyticsEvents.publishPoll,
	    type: anonymousVote ? 'anonymous' : 'public',
	    category: searchParams.get('st[category]'),
	    p1: searchParams.get('st[p1]'),
	    p2: searchParams.get('st[p2]'),
	    p3: `pollId_${messageId}`,
	    p5: searchParams.get('st[p5]')
	  };
	  if (searchParams.has('st[p4]')) {
	    options.p4 = searchParams.get('st[p4]');
	  }
	  ui_analytics.sendData(options);
	};
	const sendDataByImOptions = async (dialogId, messageId, event, type) => {
	  const analyticsOptions = await getAnalyticsOptions(dialogId, messageId);
	  if (!analyticsOptions) {
	    return;
	  }
	  analyticsOptions.event = event;
	  if (event === AnalyticsEvents.finishPoll) {
	    ui_analytics.sendData({
	      ...analyticsOptions,
	      type
	    });
	    return;
	  }
	  const {
	    p4,
	    p5,
	    ...rest
	  } = analyticsOptions;
	  if (type) {
	    rest.type = type;
	  }
	  ui_analytics.sendData(rest);
	};
	const downloadResult = messageId => {
	  const options = {
	    tool: 'im',
	    event: AnalyticsEvents.downloadResult,
	    category: AnalyticsCategories.polls,
	    p3: `pollId_${messageId}`
	  };
	  ui_analytics.sendData(options);
	};
	const setupVote = (optionValue, event, messageId) => {
	  let type = '';
	  switch (event) {
	    case AnalyticsEvents.isMultipleChoice:
	      type = optionValue ? 'Y' : 'N';
	      break;
	    case AnalyticsEvents.setCancelVote:
	      type = optionValue ? 'Y' : 'N';
	      break;
	    case AnalyticsEvents.setTimeLimit:
	      type = optionValue ? 'limited' : '';
	      break;
	    case AnalyticsEvents.setOptions:
	      type = optionValue > 2 ? 'multiple' : 'two';
	      break;
	    default:
	      break;
	  }
	  const options = {
	    tool: 'im',
	    category: AnalyticsCategories.pollSettings,
	    event,
	    type,
	    p3: `pollId_${messageId}`
	  };
	  ui_analytics.sendData(options);
	};
	const VoteAnalytics = {
	  publishVote,
	  downloadResult,
	  setupVote,
	  vote: (dialogId, messageId) => {
	    sendDataByImOptions(dialogId, messageId, AnalyticsEvents.vote);
	  },
	  revokeVote: (dialogId, messageId) => {
	    sendDataByImOptions(dialogId, messageId, AnalyticsEvents.cancelVote);
	  },
	  completeVote: (dialogId, messageId) => {
	    sendDataByImOptions(dialogId, messageId, AnalyticsEvents.finishPoll, 'user');
	  },
	  copyLink: (dialogId, messageId, type) => {
	    sendDataByImOptions(dialogId, messageId, AnalyticsEvents.copyPollLink, type);
	  }
	};

	exports.VoteAnalytics = VoteAnalytics;
	exports.AnalyticsEvents = AnalyticsEvents;

}((this.BX.Vote = this.BX.Vote || {}),BX,BX.UI.Analytics));
//# sourceMappingURL=analytics.bundle.js.map
