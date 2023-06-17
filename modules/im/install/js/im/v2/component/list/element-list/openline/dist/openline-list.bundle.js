this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports) {
	'use strict';

	// @vue/component
	const OpenlineList = {
	  methods: {
	    onChatClick(i) {
	      this.$emit('chatClick', i);
	    }
	  },
	  template: `
		<div class="bx-im-list-openline__content">
			<div>Openline List</div>
			<br>
			<div v-for="i in 100" @click="onChatClick(i)" class="bx-im-list-openline__item">
				Openline {{ i }}
			</div>
		</div>
	`
	};

	exports.OpenlineList = OpenlineList;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {})));
//# sourceMappingURL=openline-list.bundle.js.map
