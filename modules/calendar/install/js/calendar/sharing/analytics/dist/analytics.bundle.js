this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_analytics) {
	'use strict';

	class Analytics {
	  static sendPopupOpened(context) {
	    this.sendAnalytics(Analytics.events.form_open, {
	      c_section: context
	    });
	  }
	  static sendRuleUpdated(context, changes) {
	    for (const type of changes) {
	      this.sendAnalytics(Analytics.events.setup, {
	        type,
	        c_section: context
	      });
	    }
	  }
	  static sendMembersAdded(context, peopleCount) {
	    this.sendAnalytics(Analytics.events.adding_people, {
	      c_section: context,
	      p1: `peopleCount_${peopleCount}`
	    });
	  }
	  static sendLinkCopied(context, type, params) {
	    let method = Analytics.linkCreateMethods.calendar_copy_main;
	    if (context === Analytics.contexts.crm) {
	      method = Analytics.linkCreateMethods.crm_copy;
	    }
	    this.sendLinkCreated(context, type, method, params);
	  }
	  static sendLinkCopiedList(context, params) {
	    const method = Analytics.linkCreateMethods.calendar_copy_list;
	    this.sendLinkCreated(context, Analytics.linkTypes.multiple, method, params);
	  }
	  static sendLinkCreated(context, type, method, params) {
	    const ruleChanges = {
	      customDays: params.ruleChanges.includes(Analytics.ruleChanges.custom_days) ? 'Y' : 'N',
	      customLength: params.ruleChanges.includes(Analytics.ruleChanges.custom_length) ? 'Y' : 'N'
	    };
	    this.sendAnalytics(Analytics.events.link_created, {
	      type,
	      c_section: context,
	      c_element: method,
	      p1: `peopleCount_${params.peopleCount}`,
	      p2: `customDays_${ruleChanges.customDays}`,
	      p3: `customLength_${ruleChanges.customLength}`
	    });
	  }

	  /**
	   * @private
	   */
	  static sendAnalytics(event, params) {
	    ui_analytics.sendData({
	      tool: Analytics.tool,
	      category: Analytics.category,
	      event,
	      ...params
	    });
	  }
	}
	Analytics.tool = 'calendar';
	Analytics.category = 'slots';
	Analytics.contexts = {
	  calendar: 'calendar',
	  crm: 'crm'
	};
	Analytics.linkTypes = {
	  solo: 'solo',
	  multiple: 'multiple'
	};
	Analytics.events = {
	  form_open: 'form_open',
	  setup: 'setup',
	  adding_people: 'adding_people',
	  link_created: 'link_created'
	};
	Analytics.linkCreateMethods = {
	  crm_send: 'crm_send',
	  crm_copy: 'crm_copy',
	  calendar_copy_main: 'calendar_copy_main',
	  calendar_copy_list: 'calendar_copy_list'
	};
	Analytics.ruleChanges = {
	  custom_days: 'custom_days',
	  custom_length: 'custom_length'
	};

	exports.Analytics = Analytics;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.UI.Analytics));
//# sourceMappingURL=analytics.bundle.js.map
