/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core_events,main_core,im_v2_component_list_elementList_recent,im_v2_component_search_chatSearchInput,im_v2_component_search_searchResult,im_v2_component_search_searchExperimental,im_v2_lib_logger,im_v2_provider_service,im_v2_lib_promo,im_v2_lib_createChat,im_v2_const,ui_lottie,im_v2_component_elements) {
	'use strict';

	// @vue/component
	const HeaderMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements.MessengerMenu,
	    MenuItem: im_v2_component_elements.MenuItem
	  },
	  emits: ['showUnread'],
	  data() {
	    return {
	      showPopup: false
	    };
	  },
	  computed: {
	    menuConfig() {
	      return {
	        id: 'im-recent-header-menu',
	        width: 284,
	        bindElement: this.$refs['icon'] || {},
	        offsetTop: 4,
	        padding: 0
	      };
	    },
	    unreadCounter() {
	      return this.$store.getters['recent/getTotalChatCounter'];
	    }
	  },
	  methods: {
	    onIconClick() {
	      this.showPopup = true;
	    },
	    onReadAllClick() {
	      new im_v2_provider_service.ChatService().readAll();
	      this.showPopup = false;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div @click="onIconClick" class="bx-im-list-container-recent__header-menu_icon" :class="{'--active': showPopup}" ref="icon"></div>
		<MessengerMenu v-if="showPopup" :config="menuConfig" @close="showPopup = false">
			<MenuItem
				:title="loc('IM_RECENT_HEADER_MENU_READ_ALL')"
				@click="onReadAllClick"
			/>
			<MenuItem
				:title="loc('IM_RECENT_HEADER_MENU_SHOW_UNREAD_ONLY')"
				:counter="unreadCounter"
				:disabled="true"
			/>
			<MenuItem
				:title="loc('IM_RECENT_HEADER_MENU_CHAT_GROUPS_TITLE')"
				:subtitle="loc('IM_RECENT_HEADER_MENU_CHAT_GROUPS_SUBTITLE')"
				:disabled="true"
			/>
		</MessengerMenu>
	`
	};

	// @vue/component
	const CreateChatHelp = {
	  emits: ['articleOpen'],
	  data() {
	    return {};
	  },
	  methods: {
	    openHelpArticle() {
	      var _BX$Helper;
	      const ARTICLE_CODE = 17412872;
	      (_BX$Helper = BX.Helper) == null ? void 0 : _BX$Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);
	      this.$emit('articleOpen');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-create-chat-help__container">
			<div @click="openHelpArticle" class="bx-im-create-chat-help__content">
				<div class="bx-im-create-chat-help__icon"></div>
				<div class="bx-im-create-chat-help__text">{{ loc('IM_RECENT_CREATE_CHAT_WHAT_TO_CHOOSE') }}</div>	
			</div>
		</div>
	`
	};

	const POPUP_ID = 'im-create-chat-promo-popup';

	// @vue/component
	const PromoPopup = {
	  name: 'PromoPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      return {
	        width: 492,
	        padding: 0,
	        overlay: true,
	        autoHide: false,
	        closeByEsc: false
	      };
	    }
	  },
	  template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<slot></slot>
		</MessengerPopup>
	`
	};

	var nm = "Anim 23";
	var v = "5.9.6";
	var fr = 60;
	var ip = 0;
	var op = 257.0000014305115;
	var w = 428;
	var h = 172;
	var ddd = 0;
	var markers = [];
	var assets = [{
	  nm: "A 3",
	  fr: 60,
	  id: "410:308",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 5,
	    hd: false,
	    nm: "A 3 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [382, 194],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [382, 107]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 180
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 6,
	    hd: false,
	    nm: "Anim 23 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 172], [428, 172], [0, 172], [0, 172], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "cont3",
	  fr: 60,
	  id: "410:310",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 7,
	    hd: false,
	    nm: "A 3 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [382, 194],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [382, 107]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 180
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 8,
	    hd: false,
	    nm: "cont3 - Null",
	    parent: 7,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [29, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 9,
	    ty: 0,
	    nm: "A 3",
	    td: 1,
	    refId: "410:308",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 10,
	    hd: false,
	    nm: "Anim 23 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 172], [428, 172], [0, 172], [0, 172], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "A 2",
	  fr: 60,
	  id: "405:636",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 16,
	    hd: false,
	    nm: "A 2 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 114.00000071525574,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [60, 181],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 114.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 174.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 17,
	    hd: false,
	    nm: "Anim 23 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 114.00000071525574,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 172], [428, 172], [0, 172], [0, 172], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "cont2",
	  fr: 60,
	  id: "405:638",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 18,
	    hd: false,
	    nm: "A 2 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 114.00000071525574,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [60, 181],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 114.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 174.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 19,
	    hd: false,
	    nm: "cont2 - Null",
	    parent: 18,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [29, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 20,
	    ty: 0,
	    nm: "A 2",
	    td: 1,
	    refId: "405:636",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 21,
	    hd: false,
	    nm: "Anim 23 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 172], [428, 172], [0, 172], [0, 172], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "A 1",
	  fr: 60,
	  id: "410:297",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 27,
	    hd: false,
	    nm: "A 1 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [4],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 174.00000071525574,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [60, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, -80]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 28,
	    hd: false,
	    nm: "Anim 23 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [4],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 174.00000071525574,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 172], [428, 172], [0, 172], [0, 172], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "cont1",
	  fr: 60,
	  id: "410:300",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 29,
	    hd: false,
	    nm: "A 1 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [4],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 174.00000071525574,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [60, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, -80]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 30,
	    hd: false,
	    nm: "cont1 - Null",
	    parent: 29,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [46, 13.73]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 31,
	    ty: 0,
	    nm: "A 1",
	    td: 1,
	    refId: "410:297",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 32,
	    hd: false,
	    nm: "Anim 23 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 172], [428, 172], [0, 172], [0, 172], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "[GROUP] Rectangle 1590 - Null / Rectangle 1590 / Rectangle 1590 / Rectangle 1589 - Null / Rectangle 1589 / Rectangle 1589 / Rectangle 1592 - Null / Rectangle 1592 / Rectangle 1592 / Rectangle 1591 - Null / Rectangle 1591 / Rectangle 1591 / Rectangle 1588 - Null / Rectangle 1588 / Rectangle 1588",
	  fr: 60,
	  id: "ljwkeu9112alnhm752p",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 38,
	    hd: false,
	    nm: "A 3 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [382, 194],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [382, 107]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 180
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 39,
	    hd: false,
	    nm: "cont3 - Null",
	    parent: 38,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [29, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 40,
	    hd: false,
	    nm: "3 - Null",
	    parent: 39,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [17, 13.73]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 41,
	    hd: false,
	    nm: "Rectangle 1590 - Null",
	    parent: 40,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0, 29.27]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 42,
	    hd: false,
	    nm: "Rectangle 1590",
	    parent: 41,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [76, 0], [78, 2], [78, 2], [76, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 43,
	    hd: false,
	    nm: "Rectangle 1590",
	    parent: 41,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [76, 0], [78, 2], [78, 2], [76, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [39.5, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [158, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 44,
	    hd: false,
	    nm: "Rectangle 1589 - Null",
	    parent: 40,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0, 20.27]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 45,
	    hd: false,
	    nm: "Rectangle 1589",
	    parent: 44,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [172, 0], [174, 2], [174, 2], [172, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 46,
	    hd: false,
	    nm: "Rectangle 1589",
	    parent: 44,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [172, 0], [174, 2], [174, 2], [172, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [87.5, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [350, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 47,
	    hd: false,
	    nm: "Rectangle 1592 - Null",
	    parent: 40,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [40.651400000000024, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 48,
	    hd: false,
	    nm: "Rectangle 1592",
	    parent: 47,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [9.6124, 0], [11.6124, 2], [11.6124, 2], [9.6124, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000007, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 49,
	    hd: false,
	    nm: "Rectangle 1592",
	    parent: 47,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [9.6124, 0], [11.6124, 2], [11.6124, 2], [9.6124, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000007, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [6.3062, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [25.2248, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 50,
	    hd: false,
	    nm: "Rectangle 1591 - Null",
	    parent: 40,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0.007799999999974716, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 51,
	    hd: false,
	    nm: "Rectangle 1591",
	    parent: 50,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [32.8372, 0], [34.8372, 2], [34.8372, 2], [32.8372, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000025, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 52,
	    hd: false,
	    nm: "Rectangle 1591",
	    parent: 50,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [32.8372, 0], [34.8372, 2], [34.8372, 2], [32.8372, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000025, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [17.9186, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [71.6744, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 53,
	    hd: false,
	    nm: "Rectangle 1588 - Null",
	    parent: 40,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0, 12.27]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 54,
	    hd: false,
	    nm: "Rectangle 1588",
	    parent: 53,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [205, 0], [207, 2], [207, 2], [205, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 55,
	    hd: false,
	    nm: "Rectangle 1588",
	    parent: 53,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [205, 0], [207, 2], [207, 2], [205, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [104, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [416, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] 3 / Rectangle 3 - Null / Rectangle 3 / Rectangle 3",
	  fr: 60,
	  id: "ljwkeu91si44f2dxsr",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 56,
	    hd: false,
	    nm: "A 3 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [382, 194],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [382, 107]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 180
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 57,
	    hd: false,
	    nm: "cont3 - Null",
	    parent: 56,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [29, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 58,
	    ty: 0,
	    nm: "3",
	    refId: "ljwkeu9112alnhm752p",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 59,
	    hd: false,
	    nm: "Rectangle 3 - Null",
	    parent: 57,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 64]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 60,
	    hd: false,
	    nm: "Rectangle 3",
	    parent: 59,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [228, 0], [229.5608, 0.1536], [231.0616, 0.6088], [232.4448, 1.348], [233.6568, 2.3432], [234.652, 3.5552], [235.3912, 4.9384], [235.8464, 6.4392], [236, 8], [236, 56], [235.8464, 57.5608], [235.3912, 59.0616], [234.652, 60.4448], [233.6568, 61.6568], [232.4448, 62.652], [231.0616, 63.3912], [229.5608, 63.8464], [228, 64], [15.2615, 64], [13.7007, 63.8464], [12.1999, 63.3912], [10.8167, 62.652], [9.6047, 61.6568], [8.6095, 60.4448], [7.8703, 59.0616], [7.4151, 57.5608], [7.2615, 56], [7.2615, 6.5143], [7.2269, 5.8162], [7.1238, 5.1246], [6.9521, 4.447], [6.7147, 3.7892], [6.4137, 3.1583], [6.0512, 2.5598], [0, 0], [0, 0]],
	            i: [[0, 0], [-0.5304, 0], [-0.52, -0.1032], [-0.4904, -0.2032], [-0.4408, -0.2944], [-0.3752, -0.3752], [-0.2944, -0.4408], [-0.2032, -0.4904], [-0.1032, -0.52], [0, -0.5304], [0, -0.5304], [0.1032, -0.52], [0.2032, -0.4904], [0.2944, -0.4408], [0.3752, -0.3752], [0.4408, -0.2944], [0.4904, -0.2032], [0.52, -0.1032], [0.5304, 0], [0.5304, 0], [0.52, 0.1032], [0.4904, 0.2032], [0.4408, 0.2944], [0.3752, 0.3752], [0.2944, 0.4408], [0.2032, 0.4904], [0.1032, 0.52], [0, 0.5304], [0, 0.2388], [0.0233, 0.2374], [0.0466, 0.2339], [0.0699, 0.2282], [0.0918, 0.2204], [0.1138, 0.2098], [0.1335, 0.1978], [4.8411, 0.7681], [0, 0]],
	            o: [[2.4205, 0.0001], [0.530399999999986, 0], [0.5200000000000102, 0.10319999999999999], [0.49039999999999395, 0.20320000000000005], [0.44079999999999586, 0.2944], [0.37520000000000664, 0.3752], [0.294399999999996, 0.44079999999999986], [0.2032000000000096, 0.49040000000000017], [0.10319999999998686, 0.5199999999999996], [0, 0.5304000000000002], [0, 0.5304000000000002], [-0.10319999999998686, 0.5200000000000031], [-0.2032000000000096, 0.49040000000000106], [-0.294399999999996, 0.44080000000000297], [-0.37520000000000664, 0.37519999999999953], [-0.44079999999999586, 0.2944000000000031], [-0.49039999999999395, 0.2032000000000025], [-0.5200000000000102, 0.10320000000000107], [-0.530399999999986, 0], [-0.5304000000000002, 0], [-0.5199999999999996, -0.10320000000000107], [-0.4903999999999993, -0.2032000000000025], [-0.4407999999999994, -0.2944000000000031], [-0.37519999999999953, -0.37519999999999953], [-0.29439999999999955, -0.44080000000000297], [-0.20319999999999983, -0.49040000000000106], [-0.10320000000000018, -0.5200000000000031], [0, -0.5304000000000002], [0, -0.23880000000000035], [-0.023299999999999876, -0.23740000000000006], [-0.04659999999999975, -0.23390000000000022], [-0.06989999999999963, -0.22820000000000018], [-0.0918000000000001, -0.22040000000000015], [-0.11380000000000035, -0.2098], [-0.13349999999999973, -0.19779999999999998], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 61,
	    hd: false,
	    nm: "Rectangle 3",
	    parent: 59,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [228, 0], [229.5608, 0.1536], [231.0616, 0.6088], [232.4448, 1.348], [233.6568, 2.3432], [234.652, 3.5552], [235.3912, 4.9384], [235.8464, 6.4392], [236, 8], [236, 56], [235.8464, 57.5608], [235.3912, 59.0616], [234.652, 60.4448], [233.6568, 61.6568], [232.4448, 62.652], [231.0616, 63.3912], [229.5608, 63.8464], [228, 64], [15.2615, 64], [13.7007, 63.8464], [12.1999, 63.3912], [10.8167, 62.652], [9.6047, 61.6568], [8.6095, 60.4448], [7.8703, 59.0616], [7.4151, 57.5608], [7.2615, 56], [7.2615, 6.5143], [7.2269, 5.8162], [7.1238, 5.1246], [6.9521, 4.447], [6.7147, 3.7892], [6.4137, 3.1583], [6.0512, 2.5598], [0, 0], [0, 0]],
	            i: [[0, 0], [-0.5304, 0], [-0.52, -0.1032], [-0.4904, -0.2032], [-0.4408, -0.2944], [-0.3752, -0.3752], [-0.2944, -0.4408], [-0.2032, -0.4904], [-0.1032, -0.52], [0, -0.5304], [0, -0.5304], [0.1032, -0.52], [0.2032, -0.4904], [0.2944, -0.4408], [0.3752, -0.3752], [0.4408, -0.2944], [0.4904, -0.2032], [0.52, -0.1032], [0.5304, 0], [0.5304, 0], [0.52, 0.1032], [0.4904, 0.2032], [0.4408, 0.2944], [0.3752, 0.3752], [0.2944, 0.4408], [0.2032, 0.4904], [0.1032, 0.52], [0, 0.5304], [0, 0.2388], [0.0233, 0.2374], [0.0466, 0.2339], [0.0699, 0.2282], [0.0918, 0.2204], [0.1138, 0.2098], [0.1335, 0.1978], [4.8411, 0.7681], [0, 0]],
	            o: [[2.4205, 0.0001], [0.530399999999986, 0], [0.5200000000000102, 0.10319999999999999], [0.49039999999999395, 0.20320000000000005], [0.44079999999999586, 0.2944], [0.37520000000000664, 0.3752], [0.294399999999996, 0.44079999999999986], [0.2032000000000096, 0.49040000000000017], [0.10319999999998686, 0.5199999999999996], [0, 0.5304000000000002], [0, 0.5304000000000002], [-0.10319999999998686, 0.5200000000000031], [-0.2032000000000096, 0.49040000000000106], [-0.294399999999996, 0.44080000000000297], [-0.37520000000000664, 0.37519999999999953], [-0.44079999999999586, 0.2944000000000031], [-0.49039999999999395, 0.2032000000000025], [-0.5200000000000102, 0.10320000000000107], [-0.530399999999986, 0], [-0.5304000000000002, 0], [-0.5199999999999996, -0.10320000000000107], [-0.4903999999999993, -0.2032000000000025], [-0.4407999999999994, -0.2944000000000031], [-0.37519999999999953, -0.37519999999999953], [-0.29439999999999955, -0.44080000000000297], [-0.20319999999999983, -0.49040000000000106], [-0.10320000000000018, -0.5200000000000031], [0, -0.5304000000000002], [0, -0.23880000000000035], [-0.023299999999999876, -0.23740000000000006], [-0.04659999999999975, -0.23390000000000022], [-0.06989999999999963, -0.22820000000000018], [-0.0918000000000001, -0.22040000000000015], [-0.11380000000000035, -0.2098], [-0.13349999999999973, -0.19779999999999998], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [118.5, 32.5]
	        },
	        s: {
	          a: 0,
	          k: [474, 130]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] cont3 / Ellipse 3 - Null / Ellipse 3 / Ellipse 3",
	  fr: 60,
	  id: "ljwkeu91ewddnaz26q",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 62,
	    hd: false,
	    nm: "A 3 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [382, 194],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [382, 107]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 180
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 63,
	    ty: 0,
	    nm: "cont3",
	    refId: "ljwkeu91si44f2dxsr",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 64,
	    hd: false,
	    nm: "Ellipse 3 - Null",
	    parent: 62,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 39]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 65,
	    hd: false,
	    nm: "Ellipse 3",
	    parent: 64,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[25, 12.5], [12.5, 25], [0, 12.5], [12.5, 0], [25, 12.5], [25, 12.5]],
	            i: [[0, 0], [6.9036, 0], [0, 6.9036], [-6.9036, 0], [0, -6.9036], [0, 0]],
	            o: [[0, 6.903559999999999], [-6.90356, 0], [0, -6.90356], [6.903559999999999, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 66,
	    hd: false,
	    nm: "Ellipse 3",
	    parent: 64,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[25, 12.5], [12.5, 25], [0, 12.5], [12.5, 0], [25, 12.5], [25, 12.5]],
	            i: [[0, 0], [6.9036, 0], [0, 6.9036], [-6.9036, 0], [0, -6.9036], [0, 0]],
	            o: [[0, 6.903559999999999], [-6.90356, 0], [0, -6.90356], [6.903559999999999, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [13, 13]
	        },
	        s: {
	          a: 0,
	          k: [52, 52]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] Rectangle 1592 - Null / Rectangle 1592 / Rectangle 1592 / Rectangle 1591 - Null / Rectangle 1591 / Rectangle 1591 / Rectangle 1593 - Null / Rectangle 1593 / Rectangle 1593",
	  fr: 60,
	  id: "ljwkeu98518darxkuyo",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 67,
	    hd: false,
	    nm: "A 2 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 114.00000071525574,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [60, 181],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 114.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 174.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 68,
	    hd: false,
	    nm: "cont2 - Null",
	    parent: 67,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [29, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 69,
	    hd: false,
	    nm: "2 - Null",
	    parent: 68,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [17, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 70,
	    hd: false,
	    nm: "Rectangle 1592 - Null",
	    parent: 69,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 71,
	    hd: false,
	    nm: "Rectangle 1592",
	    parent: 70,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [16, 0], [18, 2], [18, 2], [16, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.104569999999999, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 72,
	    hd: false,
	    nm: "Rectangle 1592",
	    parent: 70,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [16, 0], [18, 2], [18, 2], [16, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.104569999999999, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [9.5, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [38, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 73,
	    hd: false,
	    nm: "Rectangle 1591 - Null",
	    parent: 69,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [23, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 74,
	    hd: false,
	    nm: "Rectangle 1591",
	    parent: 73,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [49, 0], [51, 2], [51, 2], [49, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000025, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 75,
	    hd: false,
	    nm: "Rectangle 1591",
	    parent: 73,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [49, 0], [51, 2], [51, 2], [49, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000025, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [26, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [104, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 76,
	    hd: false,
	    nm: "Rectangle 1593 - Null",
	    parent: 69,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      },
	      p: {
	        a: 0,
	        k: [0, 11]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 77,
	    hd: false,
	    nm: "Rectangle 1593",
	    parent: 76,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[6, 0], [225, 0], [231, 6], [231, 58], [225, 64], [6, 64], [0, 58], [0, 6], [6, 0], [6, 0]],
	            i: [[0, 0], [0, 0], [0, -3.3137], [0, 0], [3.3137, 0], [0, 0], [0, 3.3137], [0, 0], [-3.3137, 0], [0, 0]],
	            o: [[0, 0], [3.313709999999986, 0], [0, 0], [0, 3.3137100000000004], [0, 0], [-3.31371, 0], [0, 0], [0, -3.31371], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 78,
	    hd: false,
	    nm: "Rectangle 1593",
	    parent: 76,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[6, 0], [225, 0], [231, 6], [231, 58], [225, 64], [6, 64], [0, 58], [0, 6], [6, 0], [6, 0]],
	            i: [[0, 0], [0, 0], [0, -3.3137], [0, 0], [3.3137, 0], [0, 0], [0, 3.3137], [0, 0], [-3.3137, 0], [0, 0]],
	            o: [[0, 0], [3.313709999999986, 0], [0, 0], [0, 3.3137100000000004], [0, 0], [-3.31371, 0], [0, 0], [0, -3.31371], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [116, 32.5]
	        },
	        s: {
	          a: 0,
	          k: [464, 130]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] 2 / Rectangle 2 - Null / Rectangle 2 / Rectangle 2",
	  fr: 60,
	  id: "ljwkeu97ngsbn04wt0a",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 79,
	    hd: false,
	    nm: "A 2 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 114.00000071525574,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [60, 181],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 114.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 174.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 80,
	    hd: false,
	    nm: "cont2 - Null",
	    parent: 79,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [29, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 81,
	    ty: 0,
	    nm: "2",
	    refId: "ljwkeu98518darxkuyo",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 82,
	    hd: false,
	    nm: "Rectangle 2 - Null",
	    parent: 80,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 98]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 83,
	    hd: false,
	    nm: "Rectangle 2",
	    parent: 82,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [249, 0], [250.5608, 0.1536], [252.0616, 0.6088], [253.4448, 1.348], [254.6568, 2.3432], [255.652, 3.5552], [256.3912, 4.9384], [256.8464, 6.4392], [257, 8], [257, 90], [256.8464, 91.5608], [256.3912, 93.0616], [255.652, 94.4448], [254.6568, 95.6568], [253.4448, 96.652], [252.0616, 97.3912], [250.5608, 97.8464], [249, 98], [15.9077, 98], [14.3469, 97.8464], [12.8461, 97.3912], [11.4629, 96.652], [10.2509, 95.6568], [9.2557, 94.4448], [8.5165, 93.0616], [8.0613, 91.5608], [7.9077, 90], [7.9077, 8.4857], [7.8757, 7.7705], [7.7797, 7.0601], [7.6205, 6.3625], [7.3997, 5.6809], [7.1189, 5.0217], [0, 0], [0, 0]],
	            i: [[0, 0], [-0.5304, 0], [-0.52, -0.1032], [-0.4904, -0.2032], [-0.4408, -0.2944], [-0.3752, -0.3752], [-0.2944, -0.4408], [-0.2032, -0.4904], [-0.1032, -0.52], [0, -0.5304], [0, -0.5304], [0.1032, -0.52], [0.2032, -0.4904], [0.2944, -0.4408], [0.3752, -0.3752], [0.4408, -0.2944], [0.4904, -0.2032], [0.52, -0.1032], [0.5304, 0], [0.5304, 0], [0.52, 0.1032], [0.4904, 0.2032], [0.4408, 0.2944], [0.3752, 0.3752], [0.2944, 0.4408], [0.2032, 0.4904], [0.1032, 0.52], [0, 0.5304], [0, 0.2472], [0.0224, 0.2464], [0.044, 0.2432], [0.0656, 0.2384], [0.0864, 0.2312], [0.1072, 0.2232], [5.2718, 1.1762], [0, 0]],
	            o: [[2.6359, 0.0002], [0.530399999999986, 0], [0.5200000000000102, 0.10319999999999999], [0.49039999999999395, 0.20320000000000005], [0.44079999999999586, 0.2944], [0.37520000000000664, 0.3752], [0.294399999999996, 0.44079999999999986], [0.20319999999998117, 0.49040000000000017], [0.10320000000001528, 0.5199999999999996], [0, 0.5304000000000002], [0, 0.5304000000000002], [-0.10320000000001528, 0.519999999999996], [-0.20319999999998117, 0.49039999999999395], [-0.294399999999996, 0.44079999999999586], [-0.37520000000000664, 0.37520000000000664], [-0.44079999999999586, 0.294399999999996], [-0.49039999999999395, 0.20319999999999538], [-0.5200000000000102, 0.10320000000000107], [-0.530399999999986, 0], [-0.5304000000000002, 0], [-0.5199999999999996, -0.10320000000000107], [-0.4903999999999993, -0.20319999999999538], [-0.4407999999999994, -0.294399999999996], [-0.37519999999999953, -0.37520000000000664], [-0.29439999999999955, -0.44079999999999586], [-0.2032000000000007, -0.49039999999999395], [-0.10320000000000018, -0.519999999999996], [0, -0.5304000000000002], [0, -0.24719999999999942], [-0.022400000000000198, -0.2464000000000004], [-0.043999999999999595, -0.24319999999999986], [-0.06559999999999988, -0.2384000000000004], [-0.08640000000000025, -0.2312000000000003], [-0.10719999999999974, -0.2232000000000003], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 84,
	    hd: false,
	    nm: "Rectangle 2",
	    parent: 82,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [249, 0], [250.5608, 0.1536], [252.0616, 0.6088], [253.4448, 1.348], [254.6568, 2.3432], [255.652, 3.5552], [256.3912, 4.9384], [256.8464, 6.4392], [257, 8], [257, 90], [256.8464, 91.5608], [256.3912, 93.0616], [255.652, 94.4448], [254.6568, 95.6568], [253.4448, 96.652], [252.0616, 97.3912], [250.5608, 97.8464], [249, 98], [15.9077, 98], [14.3469, 97.8464], [12.8461, 97.3912], [11.4629, 96.652], [10.2509, 95.6568], [9.2557, 94.4448], [8.5165, 93.0616], [8.0613, 91.5608], [7.9077, 90], [7.9077, 8.4857], [7.8757, 7.7705], [7.7797, 7.0601], [7.6205, 6.3625], [7.3997, 5.6809], [7.1189, 5.0217], [0, 0], [0, 0]],
	            i: [[0, 0], [-0.5304, 0], [-0.52, -0.1032], [-0.4904, -0.2032], [-0.4408, -0.2944], [-0.3752, -0.3752], [-0.2944, -0.4408], [-0.2032, -0.4904], [-0.1032, -0.52], [0, -0.5304], [0, -0.5304], [0.1032, -0.52], [0.2032, -0.4904], [0.2944, -0.4408], [0.3752, -0.3752], [0.4408, -0.2944], [0.4904, -0.2032], [0.52, -0.1032], [0.5304, 0], [0.5304, 0], [0.52, 0.1032], [0.4904, 0.2032], [0.4408, 0.2944], [0.3752, 0.3752], [0.2944, 0.4408], [0.2032, 0.4904], [0.1032, 0.52], [0, 0.5304], [0, 0.2472], [0.0224, 0.2464], [0.044, 0.2432], [0.0656, 0.2384], [0.0864, 0.2312], [0.1072, 0.2232], [5.2718, 1.1762], [0, 0]],
	            o: [[2.6359, 0.0002], [0.530399999999986, 0], [0.5200000000000102, 0.10319999999999999], [0.49039999999999395, 0.20320000000000005], [0.44079999999999586, 0.2944], [0.37520000000000664, 0.3752], [0.294399999999996, 0.44079999999999986], [0.20319999999998117, 0.49040000000000017], [0.10320000000001528, 0.5199999999999996], [0, 0.5304000000000002], [0, 0.5304000000000002], [-0.10320000000001528, 0.519999999999996], [-0.20319999999998117, 0.49039999999999395], [-0.294399999999996, 0.44079999999999586], [-0.37520000000000664, 0.37520000000000664], [-0.44079999999999586, 0.294399999999996], [-0.49039999999999395, 0.20319999999999538], [-0.5200000000000102, 0.10320000000000107], [-0.530399999999986, 0], [-0.5304000000000002, 0], [-0.5199999999999996, -0.10320000000000107], [-0.4903999999999993, -0.20319999999999538], [-0.4407999999999994, -0.294399999999996], [-0.37519999999999953, -0.37520000000000664], [-0.29439999999999955, -0.44079999999999586], [-0.2032000000000007, -0.49039999999999395], [-0.10320000000000018, -0.519999999999996], [0, -0.5304000000000002], [0, -0.24719999999999942], [-0.022400000000000198, -0.2464000000000004], [-0.043999999999999595, -0.24319999999999986], [-0.06559999999999988, -0.2384000000000004], [-0.08640000000000025, -0.2312000000000003], [-0.10719999999999974, -0.2232000000000003], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [129, 49.5]
	        },
	        s: {
	          a: 0,
	          k: [516, 198]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: [{
	      nm: "DropShadow",
	      ty: 25,
	      en: 1,
	      ef: [{
	        ty: 2,
	        v: {
	          a: 1,
	          k: [{
	            t: 96,
	            s: [0, 0, 0, 1],
	            o: {
	              x: [0],
	              y: [0]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 114.00000071525574,
	            s: [0, 0, 0, 1]
	          }]
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 1,
	          k: [{
	            t: 96,
	            s: [10],
	            o: {
	              x: [0],
	              y: [0]
	            },
	            i: {
	              x: [0.15],
	              y: [1]
	            }
	          }, {
	            t: 114.00000071525574,
	            s: [0]
	          }]
	        }
	      }, {
	        ty: 1,
	        v: {
	          a: 0,
	          k: 1.5707963267948966
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 0,
	          k: -1
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 0,
	          k: 3
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "[GROUP] cont2 / Ellipse 2 - Null / Ellipse 2 / Ellipse 2",
	  fr: 60,
	  id: "ljwkeu97566xvua8q9p",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 85,
	    hd: false,
	    nm: "A 2 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 114.00000071525574,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [60, 181],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 114.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 174.00000071525574,
	          s: [60, 73],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 86,
	    ty: 0,
	    nm: "cont2",
	    refId: "ljwkeu97ngsbn04wt0a",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 87,
	    hd: false,
	    nm: "Ellipse 2 - Null",
	    parent: 85,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 73]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 88,
	    hd: false,
	    nm: "Ellipse 2",
	    parent: 87,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[25, 12.5], [12.5, 25], [0, 12.5], [12.5, 0], [25, 12.5], [25, 12.5]],
	            i: [[0, 0], [6.9036, 0], [0, 6.9036], [-6.9036, 0], [0, -6.9036], [0, 0]],
	            o: [[0, 6.903559999999999], [-6.90356, 0], [0, -6.90356], [6.903559999999999, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 89,
	    hd: false,
	    nm: "Ellipse 2",
	    parent: 87,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[25, 12.5], [12.5, 25], [0, 12.5], [12.5, 0], [25, 12.5], [25, 12.5]],
	            i: [[0, 0], [6.9036, 0], [0, 6.9036], [-6.9036, 0], [0, -6.9036], [0, 0]],
	            o: [[0, 6.903559999999999], [-6.90356, 0], [0, -6.90356], [6.903559999999999, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [13, 13]
	        },
	        s: {
	          a: 0,
	          k: [52, 52]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] Rectangle 1590 - Null / Rectangle 1590 / Rectangle 1590 / Rectangle 1589 - Null / Rectangle 1589 / Rectangle 1589 / Rectangle 1592 - Null / Rectangle 1592 / Rectangle 1592 / Rectangle 1591 - Null / Rectangle 1591 / Rectangle 1591 / Rectangle 1588 - Null / Rectangle 1588 / Rectangle 1588",
	  fr: 60,
	  id: "ljwkeu9det9tk030otk",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 90,
	    hd: false,
	    nm: "A 1 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [4],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 174.00000071525574,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [60, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, -80]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 91,
	    hd: false,
	    nm: "cont1 - Null",
	    parent: 90,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [46, 13.73]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 92,
	    hd: false,
	    nm: "1 - Null",
	    parent: 91,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 93,
	    hd: false,
	    nm: "Rectangle 1590 - Null",
	    parent: 92,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0, 29.2676]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 94,
	    hd: false,
	    nm: "Rectangle 1590",
	    parent: 93,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [76, 0], [78, 2], [78, 2], [76, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 95,
	    hd: false,
	    nm: "Rectangle 1590",
	    parent: 93,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [76, 0], [78, 2], [78, 2], [76, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [39.5, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [158, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 96,
	    hd: false,
	    nm: "Rectangle 1589 - Null",
	    parent: 92,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0, 20.2676]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 97,
	    hd: false,
	    nm: "Rectangle 1589",
	    parent: 96,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [172, 0], [174, 2], [174, 2], [172, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 98,
	    hd: false,
	    nm: "Rectangle 1589",
	    parent: 96,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [172, 0], [174, 2], [174, 2], [172, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [87.5, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [350, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 99,
	    hd: false,
	    nm: "Rectangle 1592 - Null",
	    parent: 92,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [40.651399999999995, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 100,
	    hd: false,
	    nm: "Rectangle 1592",
	    parent: 99,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [9.6124, 0], [11.6124, 2], [11.6124, 2], [9.6124, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000007, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 101,
	    hd: false,
	    nm: "Rectangle 1592",
	    parent: 99,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [9.6124, 0], [11.6124, 2], [11.6124, 2], [9.6124, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000007, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [6.3062, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [25.2248, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 102,
	    hd: false,
	    nm: "Rectangle 1591 - Null",
	    parent: 92,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0.007800000000003138, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 103,
	    hd: false,
	    nm: "Rectangle 1591",
	    parent: 102,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [32.8372, 0], [34.8372, 2], [34.8372, 2], [32.8372, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000025, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 104,
	    hd: false,
	    nm: "Rectangle 1591",
	    parent: 102,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [32.8372, 0], [34.8372, 2], [34.8372, 2], [32.8372, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045700000000025, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [17.9186, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [71.6744, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 105,
	    hd: false,
	    nm: "Rectangle 1588 - Null",
	    parent: 92,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 50
	      },
	      p: {
	        a: 0,
	        k: [0, 12.27]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 106,
	    hd: false,
	    nm: "Rectangle 1588",
	    parent: 105,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [205, 0], [207, 2], [207, 2], [205, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 107,
	    hd: false,
	    nm: "Rectangle 1588",
	    parent: 105,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 50
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[2, 0], [205, 0], [207, 2], [207, 2], [205, 4], [2, 4], [0, 2], [0, 2], [2, 0], [2, 0]],
	            i: [[0, 0], [0, 0], [0, -1.1046], [0, 0], [1.1046, 0], [0, 0], [0, 1.1046], [0, 0], [-1.1046, 0], [0, 0]],
	            o: [[0, 0], [1.1045699999999954, 0], [0, 0], [0, 1.1045699999999998], [0, 0], [-1.10457, 0], [0, 0], [0, -1.10457], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [104, 2.5]
	        },
	        s: {
	          a: 0,
	          k: [416, 10]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] 1",
	  fr: 60,
	  id: "ljwkeu9dmspnbua2tyg",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 108,
	    hd: false,
	    nm: "A 1 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [4],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 174.00000071525574,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [60, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, -80]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 109,
	    hd: false,
	    nm: "cont1 - Null",
	    parent: 108,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [46, 13.73]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 110,
	    ty: 0,
	    nm: "1",
	    refId: "ljwkeu9det9tk030otk",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }]
	}, {
	  nm: "[GROUP] cont1 / Rectangle 1 - Null / Rectangle 1 / Rectangle 1 / Ellipse 1 - Null / Ellipse 1 / Ellipse 1",
	  fr: 60,
	  id: "ljwkeu9dma2e2m1rlsa",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 111,
	    hd: false,
	    nm: "A 1 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [4],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 174.00000071525574,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [60, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 192.00000143051147,
	          s: [60, -80]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 112,
	    ty: 0,
	    nm: "cont1",
	    refId: "ljwkeu9dmspnbua2tyg",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 113,
	    hd: false,
	    nm: "Rectangle 1 - Null",
	    parent: 111,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [30],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [30, 61],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 66,
	          s: [25, 64]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 114,
	    hd: false,
	    nm: "Rectangle 1",
	    parent: 113,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [30],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100]
	        }]
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 48,
	            s: [{
	              c: true,
	              v: [[9.0197, 0], [221, 0], [222.5608, 0.1536], [224.0616, 0.6088], [225.4448, 1.348], [226.6568, 2.3432], [227.652, 3.5552], [228.3912, 4.9384], [228.8464, 6.4392], [229, 8], [229, 53], [228.8464, 54.5608], [228.3912, 56.0616], [227.652, 57.4448], [226.6568, 58.6568], [225.4448, 59.652], [224.0616, 60.3912], [222.5608, 60.8464], [221, 61], [12.5099, 61], [10.9491, 60.8464], [9.4483, 60.3912], [8.0651, 59.652], [6.8531, 58.6568], [5.8579, 57.4448], [5.1187, 56.0616], [4.6635, 54.5608], [4.5099, 53], [4.5099, 8.8104], [4.4659, 7.9744], [4.3355, 7.148], [4.1187, 6.34], [3.8195, 5.5584], [3.4395, 4.8128], [2.9843, 4.1112], [2.2548, 3.1067], [2.0422, 2.7336], [1.9161, 2.3231], [1.8826, 1.895], [1.9432, 1.47], [2.0951, 1.0683], [2.3309, 0.7094], [2.6393, 0.4106], [3.0053, 0.1863], [3.4117, 0.0473], [3.8384, 0.0002], [9.0197, 0], [9.0197, 0]],
	              i: [[0, 0], [-0.5304, 0], [-0.52, -0.1032], [-0.4904, -0.2032], [-0.4408, -0.2944], [-0.3752, -0.3752], [-0.2944, -0.4408], [-0.2032, -0.4904], [-0.1032, -0.52], [0, -0.5304], [0, -0.5304], [0.1032, -0.52], [0.2032, -0.4904], [0.2944, -0.4408], [0.3752, -0.3752], [0.4408, -0.2944], [0.4904, -0.2032], [0.52, -0.1032], [0.5304, 0], [0.5304, 0], [0.52, 0.1032], [0.4904, 0.2032], [0.4408, 0.2944], [0.3752, 0.3752], [0.2944, 0.4408], [0.2032, 0.4904], [0.1032, 0.52], [0, 0.5304], [0, 0.2856], [0.0296, 0.284], [0.0592, 0.2792], [0.088, 0.272], [0.116, 0.2608], [0.1424, 0.2472], [0.168, 0.2312], [0.0849, 0.117], [0.0574, 0.1327], [0.027, 0.1421], [-0.0046, 0.1445], [-0.0359, 0.14], [-0.0656, 0.1289], [-0.0922, 0.1114], [-0.1144, 0.0886], [-0.1309, 0.0616], [-0.1411, 0.0316], [-0.1446, 0], [0, 0], [0, 0]],
	              o: [[0, 0], [0.530399999999986, 0], [0.5200000000000102, 0.10319999999999999], [0.49039999999999395, 0.20320000000000005], [0.44079999999999586, 0.2944], [0.37520000000000664, 0.3752], [0.294399999999996, 0.44079999999999986], [0.2032000000000096, 0.49040000000000017], [0.10319999999998686, 0.5199999999999996], [0, 0.5304000000000002], [0, 0.5304000000000002], [-0.10319999999998686, 0.5200000000000031], [-0.2032000000000096, 0.49040000000000106], [-0.294399999999996, 0.44080000000000297], [-0.37520000000000664, 0.37519999999999953], [-0.44079999999999586, 0.2944000000000031], [-0.49039999999999395, 0.2032000000000025], [-0.5200000000000102, 0.10320000000000107], [-0.530399999999986, 0], [-0.5304000000000002, 0], [-0.5199999999999996, -0.10320000000000107], [-0.4903999999999993, -0.2032000000000025], [-0.4408000000000003, -0.2944000000000031], [-0.3752000000000004, -0.37519999999999953], [-0.29440000000000044, -0.44080000000000297], [-0.20319999999999983, -0.49040000000000106], [-0.10320000000000018, -0.5200000000000031], [0, -0.5304000000000002], [0, -0.2856000000000005], [-0.029600000000000293, -0.2839999999999998], [-0.0591999999999997, -0.27920000000000034], [-0.08800000000000008, -0.27200000000000024], [-0.1160000000000001, -0.2607999999999997], [-0.14239999999999986, -0.2472000000000003], [-0.16800000000000015, -0.23119999999999985], [-0.0849000000000002, -0.11699999999999999], [-0.057399999999999896, -0.13269999999999982], [-0.026999999999999913, -0.14210000000000012], [0.0045999999999999375, -0.14450000000000007], [0.03590000000000004, -0.1399999999999999], [0.06559999999999988, -0.12890000000000001], [0.09220000000000006, -0.11139999999999994], [0.11439999999999984, -0.08860000000000001], [0.13090000000000002, -0.0616], [0.14110000000000023, -0.0316], [0.14460000000000006, 0], [0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0],
	              y: [0]
	            },
	            i: {
	              x: [1],
	              y: [1]
	            }
	          }, {
	            t: 66,
	            s: [{
	              c: true,
	              v: [[9.6105, 0], [235.476, 0], [237.139, 0.1611], [238.7381, 0.6387], [240.2119, 1.4143], [241.5033, 2.4584], [242.5637, 3.73], [243.3513, 5.1812], [243.8363, 6.7558], [244, 8.3934], [244, 55.6065], [243.8363, 57.2441], [243.3513, 58.8187], [242.5637, 60.2699], [241.5033, 61.5415], [240.2119, 62.5856], [238.7381, 63.3612], [237.139, 63.8388], [235.476, 64], [13.3293, 64], [11.6663, 63.8389], [10.0672, 63.3613], [8.5934, 62.5858], [7.302, 61.5417], [6.2416, 60.2701], [5.454, 58.8189], [4.969, 57.2443], [4.8053, 55.6067], [4.8053, 9.2438], [4.7584, 8.3667], [4.6195, 7.4997], [4.3885, 6.652], [4.0697, 5.832], [3.6648, 5.0497], [3.1798, 4.3136], [2.4025, 3.2597], [2.176, 2.8683], [2.0416, 2.4376], [2.0059, 1.9885], [2.0705, 1.5426], [2.2324, 1.1211], [2.4837, 0.7446], [2.8123, 0.4311], [3.2023, 0.1958], [3.6353, 0.05], [4.09, 0.0006], [9.6107, 0.0004], [9.6105, 0]],
	              i: [[0, 0], [-0.5651, 0], [-0.5541, -0.1083], [-0.5225, -0.2132], [-0.4697, -0.3089], [-0.3998, -0.3937], [-0.3137, -0.4625], [-0.2165, -0.5145], [-0.11, -0.5456], [0, -0.5565], [0, -0.5565], [0.11, -0.5456], [0.2165, -0.5145], [0.3137, -0.4625], [0.3998, -0.3937], [0.4697, -0.3089], [0.5225, -0.2132], [0.5541, -0.1083], [0.5651, 0], [0.5651, 0], [0.5541, 0.1083], [0.5225, 0.2132], [0.4697, 0.3089], [0.3998, 0.3937], [0.3137, 0.4625], [0.2165, 0.5145], [0.11, 0.5456], [0, 0.5565], [0, 0.2996], [0.0315, 0.298], [0.0631, 0.2929], [0.0938, 0.2854], [0.1236, 0.2736], [0.1517, 0.2594], [0.179, 0.2426], [0.0905, 0.1227], [0.0612, 0.1392], [0.0288, 0.1491], [-0.0049, 0.1516], [-0.0382, 0.1469], [-0.0699, 0.1352], [-0.0982, 0.1169], [-0.1219, 0.093], [-0.1395, 0.0646], [-0.1503, 0.0332], [-0.1541, 0], [0, 0], [0, 0]],
	              o: [[0, 0], [0.5651400000000137, 0], [0.5540599999999927, 0.10828000000000002], [0.5225199999999859, 0.21319], [0.4696700000000078, 0.30888000000000004], [0.3997799999999927, 0.39365000000000006], [0.31368000000000507, 0.4624799999999998], [0.21650999999999954, 0.5145200000000001], [0.10996000000000095, 0.5455699999999997], [0, 0.5564900000000002], [0, 0.5564899999999966], [-0.10996000000000095, 0.5455699999999979], [-0.21650999999999954, 0.5145199999999974], [-0.31368000000000507, 0.46247999999999934], [-0.3997799999999927, 0.39365000000000094], [-0.4696700000000078, 0.30888000000000204], [-0.5225199999999859, 0.21318999999999733], [-0.5540599999999927, 0.1082800000000006], [-0.5651400000000137, 0], [-0.5651399999999995, 0], [-0.5540599999999998, -0.1082800000000006], [-0.5225200000000001, -0.21318999999999733], [-0.4696700000000007, -0.30888000000000204], [-0.3997799999999998, -0.39365000000000094], [-0.31367999999999974, -0.46247999999999934], [-0.21651000000000042, -0.5145199999999974], [-0.10996000000000006, -0.5455699999999979], [0, -0.5564899999999966], [0, -0.29964999999999975], [-0.03153999999999968, -0.2979699999999994], [-0.06308000000000025, -0.29293000000000013], [-0.09375999999999962, -0.28537999999999997], [-0.12360000000000015, -0.2736299999999998], [-0.15173000000000014, -0.25936000000000003], [-0.17899999999999983, -0.24256999999999973], [-0.0904600000000002, -0.12274999999999991], [-0.0611600000000001, -0.13922999999999996], [-0.028770000000000184, -0.14909000000000017], [0.0049000000000001265, -0.15161000000000002], [0.03825000000000012, -0.14688999999999997], [0.06990000000000007, -0.13524000000000003], [0.0982400000000001, -0.11687999999999998], [0.12189000000000005, -0.09295999999999999], [0.1394700000000002, -0.06462999999999999], [0.15033999999999992, -0.03315], [0.15406999999999993, 0], [0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 115,
	    hd: false,
	    nm: "Rectangle 1",
	    parent: 113,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [30],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100]
	        }]
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 1,
	          k: [{
	            t: 48,
	            s: [{
	              c: true,
	              v: [[9.0197, 0], [221, 0], [222.5608, 0.1536], [224.0616, 0.6088], [225.4448, 1.348], [226.6568, 2.3432], [227.652, 3.5552], [228.3912, 4.9384], [228.8464, 6.4392], [229, 8], [229, 53], [228.8464, 54.5608], [228.3912, 56.0616], [227.652, 57.4448], [226.6568, 58.6568], [225.4448, 59.652], [224.0616, 60.3912], [222.5608, 60.8464], [221, 61], [12.5099, 61], [10.9491, 60.8464], [9.4483, 60.3912], [8.0651, 59.652], [6.8531, 58.6568], [5.8579, 57.4448], [5.1187, 56.0616], [4.6635, 54.5608], [4.5099, 53], [4.5099, 8.8104], [4.4659, 7.9744], [4.3355, 7.148], [4.1187, 6.34], [3.8195, 5.5584], [3.4395, 4.8128], [2.9843, 4.1112], [2.2548, 3.1067], [2.0422, 2.7336], [1.9161, 2.3231], [1.8826, 1.895], [1.9432, 1.47], [2.0951, 1.0683], [2.3309, 0.7094], [2.6393, 0.4106], [3.0053, 0.1863], [3.4117, 0.0473], [3.8384, 0.0002], [9.0197, 0], [9.0197, 0]],
	              i: [[0, 0], [-0.5304, 0], [-0.52, -0.1032], [-0.4904, -0.2032], [-0.4408, -0.2944], [-0.3752, -0.3752], [-0.2944, -0.4408], [-0.2032, -0.4904], [-0.1032, -0.52], [0, -0.5304], [0, -0.5304], [0.1032, -0.52], [0.2032, -0.4904], [0.2944, -0.4408], [0.3752, -0.3752], [0.4408, -0.2944], [0.4904, -0.2032], [0.52, -0.1032], [0.5304, 0], [0.5304, 0], [0.52, 0.1032], [0.4904, 0.2032], [0.4408, 0.2944], [0.3752, 0.3752], [0.2944, 0.4408], [0.2032, 0.4904], [0.1032, 0.52], [0, 0.5304], [0, 0.2856], [0.0296, 0.284], [0.0592, 0.2792], [0.088, 0.272], [0.116, 0.2608], [0.1424, 0.2472], [0.168, 0.2312], [0.0849, 0.117], [0.0574, 0.1327], [0.027, 0.1421], [-0.0046, 0.1445], [-0.0359, 0.14], [-0.0656, 0.1289], [-0.0922, 0.1114], [-0.1144, 0.0886], [-0.1309, 0.0616], [-0.1411, 0.0316], [-0.1446, 0], [0, 0], [0, 0]],
	              o: [[0, 0], [0.530399999999986, 0], [0.5200000000000102, 0.10319999999999999], [0.49039999999999395, 0.20320000000000005], [0.44079999999999586, 0.2944], [0.37520000000000664, 0.3752], [0.294399999999996, 0.44079999999999986], [0.2032000000000096, 0.49040000000000017], [0.10319999999998686, 0.5199999999999996], [0, 0.5304000000000002], [0, 0.5304000000000002], [-0.10319999999998686, 0.5200000000000031], [-0.2032000000000096, 0.49040000000000106], [-0.294399999999996, 0.44080000000000297], [-0.37520000000000664, 0.37519999999999953], [-0.44079999999999586, 0.2944000000000031], [-0.49039999999999395, 0.2032000000000025], [-0.5200000000000102, 0.10320000000000107], [-0.530399999999986, 0], [-0.5304000000000002, 0], [-0.5199999999999996, -0.10320000000000107], [-0.4903999999999993, -0.2032000000000025], [-0.4408000000000003, -0.2944000000000031], [-0.3752000000000004, -0.37519999999999953], [-0.29440000000000044, -0.44080000000000297], [-0.20319999999999983, -0.49040000000000106], [-0.10320000000000018, -0.5200000000000031], [0, -0.5304000000000002], [0, -0.2856000000000005], [-0.029600000000000293, -0.2839999999999998], [-0.0591999999999997, -0.27920000000000034], [-0.08800000000000008, -0.27200000000000024], [-0.1160000000000001, -0.2607999999999997], [-0.14239999999999986, -0.2472000000000003], [-0.16800000000000015, -0.23119999999999985], [-0.0849000000000002, -0.11699999999999999], [-0.057399999999999896, -0.13269999999999982], [-0.026999999999999913, -0.14210000000000012], [0.0045999999999999375, -0.14450000000000007], [0.03590000000000004, -0.1399999999999999], [0.06559999999999988, -0.12890000000000001], [0.09220000000000006, -0.11139999999999994], [0.11439999999999984, -0.08860000000000001], [0.13090000000000002, -0.0616], [0.14110000000000023, -0.0316], [0.14460000000000006, 0], [0, 0], [0, 0]]
	            }],
	            o: {
	              x: [0],
	              y: [0]
	            },
	            i: {
	              x: [1],
	              y: [1]
	            }
	          }, {
	            t: 66,
	            s: [{
	              c: true,
	              v: [[9.6105, 0], [235.476, 0], [237.139, 0.1611], [238.7381, 0.6387], [240.2119, 1.4143], [241.5033, 2.4584], [242.5637, 3.73], [243.3513, 5.1812], [243.8363, 6.7558], [244, 8.3934], [244, 55.6065], [243.8363, 57.2441], [243.3513, 58.8187], [242.5637, 60.2699], [241.5033, 61.5415], [240.2119, 62.5856], [238.7381, 63.3612], [237.139, 63.8388], [235.476, 64], [13.3293, 64], [11.6663, 63.8389], [10.0672, 63.3613], [8.5934, 62.5858], [7.302, 61.5417], [6.2416, 60.2701], [5.454, 58.8189], [4.969, 57.2443], [4.8053, 55.6067], [4.8053, 9.2438], [4.7584, 8.3667], [4.6195, 7.4997], [4.3885, 6.652], [4.0697, 5.832], [3.6648, 5.0497], [3.1798, 4.3136], [2.4025, 3.2597], [2.176, 2.8683], [2.0416, 2.4376], [2.0059, 1.9885], [2.0705, 1.5426], [2.2324, 1.1211], [2.4837, 0.7446], [2.8123, 0.4311], [3.2023, 0.1958], [3.6353, 0.05], [4.09, 0.0006], [9.6107, 0.0004], [9.6105, 0]],
	              i: [[0, 0], [-0.5651, 0], [-0.5541, -0.1083], [-0.5225, -0.2132], [-0.4697, -0.3089], [-0.3998, -0.3937], [-0.3137, -0.4625], [-0.2165, -0.5145], [-0.11, -0.5456], [0, -0.5565], [0, -0.5565], [0.11, -0.5456], [0.2165, -0.5145], [0.3137, -0.4625], [0.3998, -0.3937], [0.4697, -0.3089], [0.5225, -0.2132], [0.5541, -0.1083], [0.5651, 0], [0.5651, 0], [0.5541, 0.1083], [0.5225, 0.2132], [0.4697, 0.3089], [0.3998, 0.3937], [0.3137, 0.4625], [0.2165, 0.5145], [0.11, 0.5456], [0, 0.5565], [0, 0.2996], [0.0315, 0.298], [0.0631, 0.2929], [0.0938, 0.2854], [0.1236, 0.2736], [0.1517, 0.2594], [0.179, 0.2426], [0.0905, 0.1227], [0.0612, 0.1392], [0.0288, 0.1491], [-0.0049, 0.1516], [-0.0382, 0.1469], [-0.0699, 0.1352], [-0.0982, 0.1169], [-0.1219, 0.093], [-0.1395, 0.0646], [-0.1503, 0.0332], [-0.1541, 0], [0, 0], [0, 0]],
	              o: [[0, 0], [0.5651400000000137, 0], [0.5540599999999927, 0.10828000000000002], [0.5225199999999859, 0.21319], [0.4696700000000078, 0.30888000000000004], [0.3997799999999927, 0.39365000000000006], [0.31368000000000507, 0.4624799999999998], [0.21650999999999954, 0.5145200000000001], [0.10996000000000095, 0.5455699999999997], [0, 0.5564900000000002], [0, 0.5564899999999966], [-0.10996000000000095, 0.5455699999999979], [-0.21650999999999954, 0.5145199999999974], [-0.31368000000000507, 0.46247999999999934], [-0.3997799999999927, 0.39365000000000094], [-0.4696700000000078, 0.30888000000000204], [-0.5225199999999859, 0.21318999999999733], [-0.5540599999999927, 0.1082800000000006], [-0.5651400000000137, 0], [-0.5651399999999995, 0], [-0.5540599999999998, -0.1082800000000006], [-0.5225200000000001, -0.21318999999999733], [-0.4696700000000007, -0.30888000000000204], [-0.3997799999999998, -0.39365000000000094], [-0.31367999999999974, -0.46247999999999934], [-0.21651000000000042, -0.5145199999999974], [-0.10996000000000006, -0.5455699999999979], [0, -0.5564899999999966], [0, -0.29964999999999975], [-0.03153999999999968, -0.2979699999999994], [-0.06308000000000025, -0.29293000000000013], [-0.09375999999999962, -0.28537999999999997], [-0.12360000000000015, -0.2736299999999998], [-0.15173000000000014, -0.25936000000000003], [-0.17899999999999983, -0.24256999999999973], [-0.0904600000000002, -0.12274999999999991], [-0.0611600000000001, -0.13922999999999996], [-0.028770000000000184, -0.14909000000000017], [0.0049000000000001265, -0.15161000000000002], [0.03825000000000012, -0.14688999999999997], [0.06990000000000007, -0.13524000000000003], [0.0982400000000001, -0.11687999999999998], [0.12189000000000005, -0.09295999999999999], [0.1394700000000002, -0.06462999999999999], [0.15033999999999992, -0.03315], [0.15406999999999993, 0], [0, 0], [0, 0]]
	            }]
	          }]
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [115, 31]
	        },
	        s: {
	          a: 0,
	          k: [460, 124]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 116,
	    hd: false,
	    nm: "Ellipse 1 - Null",
	    parent: 111,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 36]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 117,
	    hd: false,
	    nm: "Ellipse 1",
	    parent: 116,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[25, 12.5], [24.3888, 16.3625], [22.6125, 19.8475], [19.8475, 22.6125], [16.3625, 24.3888], [12.5, 25], [8.6375, 24.3888], [5.1525, 22.6125], [2.3875, 19.8475], [0.6113, 16.3625], [0, 12.5], [0.6113, 8.6375], [2.3875, 5.1525], [5.1525, 2.3875], [8.6375, 0.6113], [12.5, 0], [16.3625, 0.6113], [19.8475, 2.3875], [22.6125, 5.1525], [24.3888, 8.6375], [25, 12.5], [25, 12.5]],
	            i: [[0, 0], [0.405, -1.2475], [0.7713, -1.0612], [1.0613, -0.7712], [1.2475, -0.405], [1.3112, 0], [1.2475, 0.405], [1.0613, 0.7713], [0.7713, 1.0613], [0.405, 1.2475], [0, 1.3112], [-0.405, 1.2475], [-0.7712, 1.0613], [-1.0612, 0.7713], [-1.2475, 0.405], [-1.3112, 0], [-1.2475, -0.405], [-1.0612, -0.7712], [-0.7712, -1.0612], [-0.405, -1.2475], [0, -1.3112], [0, 0]],
	            o: [[0, 1.3111999999999995], [-0.40500000000000114, 1.2474999999999987], [-0.7712000000000003, 1.0612999999999992], [-1.0611999999999995, 0.7713000000000001], [-1.2475000000000005, 0.40500000000000114], [-1.3111999999999995, 0], [-1.2475000000000005, -0.40500000000000114], [-1.0611999999999995, -0.7712000000000003], [-0.7711999999999999, -1.0611999999999995], [-0.405, -1.2475000000000005], [0, -1.3111999999999995], [0.405, -1.2475000000000005], [0.7713000000000001, -1.0611999999999995], [1.0613000000000001, -0.7711999999999999], [1.2475000000000005, -0.405], [1.3111999999999995, 0], [1.2474999999999987, 0.405], [1.0612999999999992, 0.7713000000000001], [0.7713000000000001, 1.0613000000000001], [0.40500000000000114, 1.2475000000000005], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 118,
	    hd: false,
	    nm: "Ellipse 1",
	    parent: 116,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[25, 12.5], [24.3888, 16.3625], [22.6125, 19.8475], [19.8475, 22.6125], [16.3625, 24.3888], [12.5, 25], [8.6375, 24.3888], [5.1525, 22.6125], [2.3875, 19.8475], [0.6113, 16.3625], [0, 12.5], [0.6113, 8.6375], [2.3875, 5.1525], [5.1525, 2.3875], [8.6375, 0.6113], [12.5, 0], [16.3625, 0.6113], [19.8475, 2.3875], [22.6125, 5.1525], [24.3888, 8.6375], [25, 12.5], [25, 12.5]],
	            i: [[0, 0], [0.405, -1.2475], [0.7713, -1.0612], [1.0613, -0.7712], [1.2475, -0.405], [1.3112, 0], [1.2475, 0.405], [1.0613, 0.7713], [0.7713, 1.0613], [0.405, 1.2475], [0, 1.3112], [-0.405, 1.2475], [-0.7712, 1.0613], [-1.0612, 0.7713], [-1.2475, 0.405], [-1.3112, 0], [-1.2475, -0.405], [-1.0612, -0.7712], [-0.7712, -1.0612], [-0.405, -1.2475], [0, -1.3112], [0, 0]],
	            o: [[0, 1.3111999999999995], [-0.40500000000000114, 1.2474999999999987], [-0.7712000000000003, 1.0612999999999992], [-1.0611999999999995, 0.7713000000000001], [-1.2475000000000005, 0.40500000000000114], [-1.3111999999999995, 0], [-1.2475000000000005, -0.40500000000000114], [-1.0611999999999995, -0.7712000000000003], [-0.7711999999999999, -1.0611999999999995], [-0.405, -1.2475000000000005], [0, -1.3111999999999995], [0.405, -1.2475000000000005], [0.7713000000000001, -1.0611999999999995], [1.0613000000000001, -0.7711999999999999], [1.2475000000000005, -0.405], [1.3111999999999995, 0], [1.2474999999999987, 0.405], [1.0612999999999992, 0.7713000000000001], [0.7713000000000001, 1.0613000000000001], [0.40500000000000114, 1.2475000000000005], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [13, 13]
	        },
	        s: {
	          a: 0,
	          k: [52, 52]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "Anim 23",
	  fr: 60,
	  id: "ljwkeu91tjou2oicjs",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 119,
	    hd: false,
	    nm: "Anim 23 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 258.0000014305115,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 120,
	    ty: 0,
	    nm: "A 3",
	    refId: "ljwkeu91ewddnaz26q",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 174.00000071525574,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 121,
	    ty: 0,
	    nm: "A 2",
	    refId: "ljwkeu97566xvua8q9p",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 96,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 114.00000071525574,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 240.0000014305115,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 258.0000014305115,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 122,
	    ty: 0,
	    nm: "A 1",
	    refId: "ljwkeu9dma2e2m1rlsa",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 48,
	          s: [4],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 66,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 174.00000071525574,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 192.00000143051147,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 172,
	    ip: 0,
	    op: 258.0000014305115,
	    st: 0,
	    hd: false,
	    bm: 0
	  }]
	}];
	var layers = [{
	  ty: 3,
	  ddd: 0,
	  ind: 119,
	  hd: false,
	  nm: "Anim 23 - Null",
	  ks: {
	    a: {
	      a: 0,
	      k: [0, 0]
	    },
	    o: {
	      a: 0,
	      k: 100
	    },
	    p: {
	      a: 0,
	      k: [0, 0]
	    },
	    r: {
	      a: 0,
	      k: 0
	    },
	    s: {
	      a: 0,
	      k: [100, 100]
	    },
	    sk: {
	      a: 0,
	      k: 0
	    },
	    sa: {
	      a: 0,
	      k: 0
	    }
	  },
	  st: 0,
	  ip: 0,
	  op: 258.0000014305115,
	  bm: 0,
	  sr: 1
	}, {
	  ddd: 0,
	  ind: 2,
	  ty: 0,
	  nm: "Anim 23",
	  refId: "ljwkeu91tjou2oicjs",
	  sr: 1,
	  ks: {
	    a: {
	      a: 0,
	      k: [0, 0]
	    },
	    p: {
	      a: 0,
	      k: [0, 0]
	    },
	    s: {
	      a: 0,
	      k: [100, 100]
	    },
	    sk: {
	      a: 0,
	      k: 0
	    },
	    sa: {
	      a: 0,
	      k: 0
	    },
	    r: {
	      a: 0,
	      k: 0
	    },
	    o: {
	      a: 0,
	      k: 100
	    }
	  },
	  ao: 0,
	  w: 428,
	  h: 172,
	  ip: 0,
	  op: 258.0000014305115,
	  st: 0,
	  hd: false,
	  bm: 0,
	  ef: []
	}];
	var meta = {
	  a: "",
	  d: "",
	  tc: "",
	  g: "Aninix"
	};
	var GroupChatAnimation = {
	  nm: nm,
	  v: v,
	  fr: fr,
	  ip: ip,
	  op: op,
	  w: w,
	  h: h,
	  ddd: ddd,
	  markers: markers,
	  assets: assets,
	  layers: layers,
	  meta: meta
	};

	// @vue/component
	const GroupChatPromo = {
	  components: {
	    PromoPopup,
	    MessengerButton: im_v2_component_elements.Button
	  },
	  emits: ['continue', 'close'],
	  data() {
	    return {};
	  },
	  computed: {
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    ButtonSize: () => im_v2_component_elements.ButtonSize
	  },
	  mounted() {
	    ui_lottie.Lottie.loadAnimation({
	      animationData: GroupChatAnimation,
	      container: this.$refs.animationContainer,
	      renderer: 'svg',
	      loop: true,
	      autoplay: true
	    });
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<PromoPopup @close="$emit('close')">
			<div class="bx-im-group-chat-promo__container">
				<div class="bx-im-group-chat-promo__header">
					<div class="bx-im-group-chat-promo__title">
						{{ loc('IM_RECENT_CREATE_CHAT_PROMO_GROUP_CHAT_TITLE') }}
					</div>
					<div class="bx-im-group-chat-promo__close" @click="$emit('close')"></div>
				</div>
				<div class="bx-im-group-chat-promo__content">
					<div class="bx-im-group-chat-promo__content_image" ref="animationContainer"></div>
					<div class="bx-im-group-chat-promo__content_item">
						<div class="bx-im-group-chat-promo__content_icon --like"></div>
						<div class="bx-im-group-chat-promo__content_text">
							{{ loc('IM_RECENT_CREATE_CHAT_PROMO_GROUP_CHAT_DESCRIPTION_1') }}
						</div>
					</div>
					<div class="bx-im-group-chat-promo__content_item">
						<div class="bx-im-group-chat-promo__content_icon --chat"></div>
						<div class="bx-im-group-chat-promo__content_text">
							{{ loc('IM_RECENT_CREATE_CHAT_PROMO_GROUP_CHAT_DESCRIPTION_2') }}
						</div>
					</div>
					<div class="bx-im-group-chat-promo__content_item">
						<div class="bx-im-group-chat-promo__content_icon --group"></div>
						<div class="bx-im-group-chat-promo__content_text">
							{{ loc('IM_RECENT_CREATE_CHAT_PROMO_GROUP_CHAT_DESCRIPTION_3') }}
						</div>
					</div>
				</div>
				<div class="bx-im-group-chat-promo__button-panel">
					<MessengerButton
						:size="ButtonSize.XL"
						:color="ButtonColor.Primary"
						:isRounded="true" 
						:text="loc('IM_RECENT_CREATE_CHAT_PROMO_GROUP_CHAT_CONTINUE')"
						@click="$emit('continue')"
					/>
					<MessengerButton
						:size="ButtonSize.XL"
						:color="ButtonColor.Link"
						:isRounded="true"
						:text="loc('IM_RECENT_CREATE_CHAT_PROMO_GROUP_CHAT_CANCEL')"
						@click="$emit('close')"
					/>
				</div>
			</div>
		</PromoPopup>
	`
	};

	var nm$1 = "Anim 8";
	var v$1 = "5.9.6";
	var fr$1 = 60;
	var ip$1 = 0;
	var op$1 = 227.00000715255737;
	var w$1 = 428;
	var h$1 = 149;
	var ddd$1 = 0;
	var markers$1 = [];
	var assets$1 = [{
	  nm: "Frame 1684947",
	  fr: 60,
	  id: "421:359",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 4,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 5,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "A 2",
	  fr: 60,
	  id: "421:392",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 6,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 7,
	    hd: false,
	    nm: "A 2 - Null",
	    parent: 6,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 54.00000214576721,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 72.00000286102295,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 54.00000214576721,
	          s: [115, 28],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 72.00000286102295,
	          s: [115, 12],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [115, 12],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [115, 28]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 8,
	    ty: 0,
	    nm: "Frame 1684947",
	    td: 1,
	    refId: "421:359",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 9,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 54.00000214576721,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 72.00000286102295,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "A 4",
	  fr: 60,
	  id: "421:389",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 15,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 16,
	    hd: false,
	    nm: "A 4 - Null",
	    parent: 15,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 126.00000500679016,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 144.0000057220459,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 126.00000500679016,
	          s: [115, 94],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 144.0000057220459,
	          s: [115, 78],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [115, 78],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [115, 94]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 17,
	    ty: 0,
	    nm: "Frame 1684947",
	    td: 1,
	    refId: "421:359",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 18,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 126.00000500679016,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 144.0000057220459,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "A 3",
	  fr: 60,
	  id: "421:386",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 24,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 25,
	    hd: false,
	    nm: "A 3 - Null",
	    parent: 24,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 90.00000357627869,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 108.00000429153442,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 90.00000357627869,
	          s: [12, 94],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 108.00000429153442,
	          s: [12, 78],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [12, 78],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [12, 94]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 26,
	    ty: 0,
	    nm: "Frame 1684947",
	    td: 1,
	    refId: "421:359",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 27,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 90.00000357627869,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 108.00000429153442,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "A 1",
	  fr: 60,
	  id: "421:383",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 33,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 34,
	    hd: false,
	    nm: "A 1 - Null",
	    parent: 33,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 18.000000715255737,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 36.000001430511475,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 18.000000715255737,
	          s: [12, 28],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 36.000001430511475,
	          s: [12, 12],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [12, 12],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [12, 28]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 35,
	    ty: 0,
	    nm: "Frame 1684947",
	    td: 1,
	    refId: "421:359",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 36,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 18.000000715255737,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 36.000001430511475,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "A 5",
	  fr: 60,
	  id: "421:360",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 42,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 43,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 42,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 44,
	    ty: 0,
	    nm: "Frame 1684947",
	    td: 1,
	    refId: "421:359",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 45,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "Frame 1684937",
	  fr: 60,
	  id: "421:365",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 46,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 47,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 46,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 48,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 47,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 49,
	    ty: 0,
	    nm: "A 5",
	    td: 1,
	    refId: "421:360",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 50,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "Frame 1684941",
	  fr: 60,
	  id: "421:378",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 51,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 52,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 51,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 53,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 52,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 54,
	    hd: false,
	    nm: "Frame 1684941 - Null",
	    parent: 53,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 42]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 55,
	    ty: 0,
	    nm: "Frame 1684937",
	    td: 1,
	    refId: "421:365",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 56,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "Frame 1684940",
	  fr: 60,
	  id: "421:374",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 64,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 65,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 64,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 66,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 65,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 67,
	    hd: false,
	    nm: "Frame 1684940 - Null",
	    parent: 66,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 28]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 68,
	    ty: 0,
	    nm: "Frame 1684937",
	    td: 1,
	    refId: "421:365",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 69,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "Frame 1684939",
	  fr: 60,
	  id: "421:370",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 77,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 78,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 77,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 79,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 78,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 80,
	    hd: false,
	    nm: "Frame 1684939 - Null",
	    parent: 79,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 81,
	    ty: 0,
	    nm: "Frame 1684937",
	    td: 1,
	    refId: "421:365",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 82,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "Frame 1684938",
	  fr: 60,
	  id: "421:366",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 90,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 91,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 90,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 92,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 91,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 93,
	    hd: false,
	    nm: "Frame 1684938 - Null",
	    parent: 92,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 94,
	    ty: 0,
	    nm: "Frame 1684937",
	    td: 1,
	    refId: "421:365",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 95,
	    hd: false,
	    nm: "Anim 8 - Mask",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [428, 0], [428, 0], [428, 149], [428, 149], [0, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    tt: 1
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - 100",
	  fr: 60,
	  id: "ljwjxj6c3a61gv3e5vl",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 103,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 104,
	    hd: false,
	    nm: "A 2 - Null",
	    parent: 103,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 54.00000214576721,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 72.00000286102295,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 54.00000214576721,
	          s: [115, 28],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 72.00000286102295,
	          s: [115, 12],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [115, 12],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [115, 28]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 105,
	    hd: false,
	    nm: "1 101 - Null",
	    parent: 104,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [29.000100000000003, 10]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 106,
	    hd: false,
	    nm: "Union - Null",
	    parent: 105,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 107,
	    hd: false,
	    nm: "Union",
	    parent: 106,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 2,
	      it: [{
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 108,
	    hd: false,
	    nm: "Union",
	    parent: 106,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 2,
	      it: [{
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [0, 0]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 109,
	    hd: false,
	    nm: "Combined Shape - Null",
	    parent: 105,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [9.2095, 7.6427]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 110,
	    hd: false,
	    nm: "Combined Shape",
	    parent: 109,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20.0198, 23.2876], [21.5338, 20.5799], [21.213, 18.9459], [17.211, 16.1041], [14.6771, 14.9724], [14.5325, 13.9929], [13.6777, 13.8652], [13.6046, 12.7335], [14.5222, 10.4061], [15.5948, 9.1849], [15.2122, 7.1292], [15.2122, 3.1002], [7.9106, 1.3193], [6.173, 5.9406], [6.6613, 7.2427], [6.2658, 8.7207], [6.4021, 9.4414], [7.2315, 10.4362], [8.3201, 12.7769], [8.3937, 13.8561], [7.4679, 13.966], [7.3948, 14.8478], [6.2015, 15.4558], [4.99, 16.069], [0.3998, 19.0457], [0.0439, 20.6886], [1.5166, 23.2605], [10.1333, 24.7147], [11.4856, 24.7147], [20.0201, 23.2877], [20.0198, 23.2876]],
	            i: [[0, 0], [0.2283, 1.1626], [0, 0], [2.6542, 0.6737], [0.7799, 0.5037], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.2013, 1.3357], [-0.9134, -1.4471], [0, 0], [0, 0], [-0.1485, -0.5525], [-0.0098, -0.25], [0, 0], [0, 0], [0, 0], [0, 0], [0.0611, -0.2899], [0.3261, -0.1854], [0.5473, -0.2355], [0.4038, -1.5743], [0.1289, -0.6583], [-1.0766, -0.3582], [-3.0767, -0.066], [0, 0], [-2.6034, 0.8568], [0, 0]],
	            o: [[1.125399999999999, -0.37039000000000044], [0, 0], [-0.16122000000000014, -1.0214600000000011], [-0.8992299999999993, -0.24619000000000035], [-0.17055000000000042, -0.09566000000000052], [0, 0], [0, -0.07174999999999976], [1.0227900000000005, -0.3373600000000003], [0.6495499999999996, 0.3536400000000004], [0.7682600000000015, -2.1880000000000006], [0.20133000000000045, -1.3357200000000002], [-0.5116499999999995, -4.43095], [-2.25138, -0.40707000000000004], [0, 0], [-0.67685, 0.43095000000000017], [0.06189999999999962, 0.23032000000000075], [0.04717000000000038, 1.2547899999999998], [0.048210000000000086, 2.0709599999999995], [0.19543, 1.3005899999999997], [0, 0], [0.01252999999999993, 0.2957699999999992], [-0.5379300000000002, 0.23534000000000077], [-0.3338099999999997, 0.1898099999999996], [-2.09016, 0.8991100000000003], [-0.09288000000000002, 0.36208999999999847], [-0.21804, 1.1134500000000003], [2.6247399999999996, 0.8733299999999993], [0, 0], [3.044600000000001, -0.0653100000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 111,
	    hd: false,
	    nm: "Combined Shape",
	    parent: 109,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20.0198, 23.2876], [21.5338, 20.5799], [21.213, 18.9459], [17.211, 16.1041], [14.6771, 14.9724], [14.5325, 13.9929], [13.6777, 13.8652], [13.6046, 12.7335], [14.5222, 10.4061], [15.5948, 9.1849], [15.2122, 7.1292], [15.2122, 3.1002], [7.9106, 1.3193], [6.173, 5.9406], [6.6613, 7.2427], [6.2658, 8.7207], [6.4021, 9.4414], [7.2315, 10.4362], [8.3201, 12.7769], [8.3937, 13.8561], [7.4679, 13.966], [7.3948, 14.8478], [6.2015, 15.4558], [4.99, 16.069], [0.3998, 19.0457], [0.0439, 20.6886], [1.5166, 23.2605], [10.1333, 24.7147], [11.4856, 24.7147], [20.0201, 23.2877], [20.0198, 23.2876]],
	            i: [[0, 0], [0.2283, 1.1626], [0, 0], [2.6542, 0.6737], [0.7799, 0.5037], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.2013, 1.3357], [-0.9134, -1.4471], [0, 0], [0, 0], [-0.1485, -0.5525], [-0.0098, -0.25], [0, 0], [0, 0], [0, 0], [0, 0], [0.0611, -0.2899], [0.3261, -0.1854], [0.5473, -0.2355], [0.4038, -1.5743], [0.1289, -0.6583], [-1.0766, -0.3582], [-3.0767, -0.066], [0, 0], [-2.6034, 0.8568], [0, 0]],
	            o: [[1.125399999999999, -0.37039000000000044], [0, 0], [-0.16122000000000014, -1.0214600000000011], [-0.8992299999999993, -0.24619000000000035], [-0.17055000000000042, -0.09566000000000052], [0, 0], [0, -0.07174999999999976], [1.0227900000000005, -0.3373600000000003], [0.6495499999999996, 0.3536400000000004], [0.7682600000000015, -2.1880000000000006], [0.20133000000000045, -1.3357200000000002], [-0.5116499999999995, -4.43095], [-2.25138, -0.40707000000000004], [0, 0], [-0.67685, 0.43095000000000017], [0.06189999999999962, 0.23032000000000075], [0.04717000000000038, 1.2547899999999998], [0.048210000000000086, 2.0709599999999995], [0.19543, 1.3005899999999997], [0, 0], [0.01252999999999993, 0.2957699999999992], [-0.5379300000000002, 0.23534000000000077], [-0.3338099999999997, 0.1898099999999996], [-2.09016, 0.8991100000000003], [-0.09288000000000002, 0.36208999999999847], [-0.21804, 1.1134500000000003], [2.6247399999999996, 0.8733299999999993], [0, 0], [3.044600000000001, -0.0653100000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [10.79045, 12.3573]
	        },
	        s: {
	          a: 0,
	          k: [43.1618, 49.4292]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] 1 102",
	  fr: 60,
	  id: "ljwjxj6chl2m21i8oo8",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 112,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 113,
	    hd: false,
	    nm: "A 2 - Null",
	    parent: 112,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 54.00000214576721,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 72.00000286102295,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 54.00000214576721,
	          s: [115, 28],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 72.00000286102295,
	          s: [115, 12],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [115, 12],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [115, 28]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 114,
	    ty: 0,
	    nm: "1 103",
	    refId: "ljwjxj6c3a61gv3e5vl",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 115,
	    hd: false,
	    nm: "Rectangle 3467754 - Null",
	    parent: 113,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 116,
	    hd: false,
	    nm: "Rectangle 3467754",
	    parent: 115,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[89, 0], [90.1706, 0.1152], [91.2962, 0.4566], [92.3336, 1.011], [93.2426, 1.7574], [93.989, 2.6664], [94.5434, 3.7038], [94.8848, 4.8294], [95, 6], [95, 53], [94.8848, 54.1706], [94.5434, 55.2962], [93.989, 56.3336], [93.2426, 57.2426], [92.3336, 57.989], [91.2962, 58.5434], [90.1706, 58.8848], [89, 59], [6, 59], [4.8294, 58.8848], [3.7038, 58.5434], [2.6664, 57.989], [1.7574, 57.2426], [1.011, 56.3336], [0.4566, 55.2962], [0.1152, 54.1706], [0, 53], [0, 6], [0.1152, 4.8294], [0.4566, 3.7038], [1.011, 2.6664], [1.7574, 1.7574], [2.6664, 1.011], [3.7038, 0.4566], [4.8294, 0.1152], [6, 0], [89, 0], [89, 0]],
	            i: [[0, 0], [-0.39, -0.0774], [-0.3678, -0.1524], [-0.3306, -0.2208], [-0.2814, -0.2814], [-0.2208, -0.3306], [-0.1524, -0.3678], [-0.0774, -0.39], [0, -0.3978], [0, -0.3978], [0.0774, -0.39], [0.1524, -0.3678], [0.2208, -0.3306], [0.2814, -0.2814], [0.3306, -0.2208], [0.3678, -0.1524], [0.39, -0.0774], [0.3978, 0], [0.3978, 0], [0.39, 0.0774], [0.3678, 0.1524], [0.3306, 0.2208], [0.2814, 0.2814], [0.2208, 0.3306], [0.1524, 0.3678], [0.0774, 0.39], [0, 0.3978], [0, 0.3978], [-0.0774, 0.39], [-0.1524, 0.3678], [-0.2208, 0.3306], [-0.2814, 0.2814], [-0.3306, 0.2208], [-0.3678, 0.1524], [-0.39, 0.0774], [-0.3978, 0], [-0.3978, 0], [0, 0]],
	            o: [[0.3978000000000037, 0], [0.39000000000000057, 0.0774], [0.36780000000000257, 0.15239999999999998], [0.330600000000004, 0.22079999999999989], [0.281400000000005, 0.2814000000000001], [0.220799999999997, 0.3306], [0.1524000000000001, 0.3677999999999999], [0.07739999999999725, 0.3899999999999997], [0, 0.39780000000000015], [0, 0.3977999999999966], [-0.07739999999999725, 0.39000000000000057], [-0.1524000000000001, 0.36780000000000257], [-0.220799999999997, 0.3305999999999969], [-0.281400000000005, 0.2813999999999979], [-0.330600000000004, 0.220799999999997], [-0.36780000000000257, 0.1524000000000001], [-0.39000000000000057, 0.07739999999999725], [-0.3978000000000037, 0], [-0.39780000000000015, 0], [-0.3899999999999997, -0.07739999999999725], [-0.3677999999999999, -0.1524000000000001], [-0.3306, -0.220799999999997], [-0.2814000000000001, -0.2813999999999979], [-0.2208, -0.3305999999999969], [-0.15239999999999998, -0.36780000000000257], [-0.0774, -0.39000000000000057], [0, -0.3977999999999966], [0, -0.39780000000000015], [0.0774, -0.3899999999999997], [0.15239999999999998, -0.3677999999999999], [0.22079999999999989, -0.3306], [0.2814000000000001, -0.2814000000000001], [0.3306, -0.2208], [0.3677999999999999, -0.15239999999999998], [0.3899999999999997, -0.0774], [0.39780000000000015, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 117,
	    hd: false,
	    nm: "Rectangle 3467754",
	    parent: 115,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[89, 0], [90.1706, 0.1152], [91.2962, 0.4566], [92.3336, 1.011], [93.2426, 1.7574], [93.989, 2.6664], [94.5434, 3.7038], [94.8848, 4.8294], [95, 6], [95, 53], [94.8848, 54.1706], [94.5434, 55.2962], [93.989, 56.3336], [93.2426, 57.2426], [92.3336, 57.989], [91.2962, 58.5434], [90.1706, 58.8848], [89, 59], [6, 59], [4.8294, 58.8848], [3.7038, 58.5434], [2.6664, 57.989], [1.7574, 57.2426], [1.011, 56.3336], [0.4566, 55.2962], [0.1152, 54.1706], [0, 53], [0, 6], [0.1152, 4.8294], [0.4566, 3.7038], [1.011, 2.6664], [1.7574, 1.7574], [2.6664, 1.011], [3.7038, 0.4566], [4.8294, 0.1152], [6, 0], [89, 0], [89, 0]],
	            i: [[0, 0], [-0.39, -0.0774], [-0.3678, -0.1524], [-0.3306, -0.2208], [-0.2814, -0.2814], [-0.2208, -0.3306], [-0.1524, -0.3678], [-0.0774, -0.39], [0, -0.3978], [0, -0.3978], [0.0774, -0.39], [0.1524, -0.3678], [0.2208, -0.3306], [0.2814, -0.2814], [0.3306, -0.2208], [0.3678, -0.1524], [0.39, -0.0774], [0.3978, 0], [0.3978, 0], [0.39, 0.0774], [0.3678, 0.1524], [0.3306, 0.2208], [0.2814, 0.2814], [0.2208, 0.3306], [0.1524, 0.3678], [0.0774, 0.39], [0, 0.3978], [0, 0.3978], [-0.0774, 0.39], [-0.1524, 0.3678], [-0.2208, 0.3306], [-0.2814, 0.2814], [-0.3306, 0.2208], [-0.3678, 0.1524], [-0.39, 0.0774], [-0.3978, 0], [-0.3978, 0], [0, 0]],
	            o: [[0.3978000000000037, 0], [0.39000000000000057, 0.0774], [0.36780000000000257, 0.15239999999999998], [0.330600000000004, 0.22079999999999989], [0.281400000000005, 0.2814000000000001], [0.220799999999997, 0.3306], [0.1524000000000001, 0.3677999999999999], [0.07739999999999725, 0.3899999999999997], [0, 0.39780000000000015], [0, 0.3977999999999966], [-0.07739999999999725, 0.39000000000000057], [-0.1524000000000001, 0.36780000000000257], [-0.220799999999997, 0.3305999999999969], [-0.281400000000005, 0.2813999999999979], [-0.330600000000004, 0.220799999999997], [-0.36780000000000257, 0.1524000000000001], [-0.39000000000000057, 0.07739999999999725], [-0.3978000000000037, 0], [-0.39780000000000015, 0], [-0.3899999999999997, -0.07739999999999725], [-0.3677999999999999, -0.1524000000000001], [-0.3306, -0.220799999999997], [-0.2814000000000001, -0.2813999999999979], [-0.2208, -0.3305999999999969], [-0.15239999999999998, -0.36780000000000257], [-0.0774, -0.39000000000000057], [0, -0.3977999999999966], [0, -0.39780000000000015], [0.0774, -0.3899999999999997], [0.15239999999999998, -0.3677999999999999], [0.22079999999999989, -0.3306], [0.2814000000000001, -0.2814000000000001], [0.3306, -0.2208], [0.3677999999999999, -0.15239999999999998], [0.3899999999999997, -0.0774], [0.39780000000000015, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [48, 30]
	        },
	        s: {
	          a: 0,
	          k: [192, 120]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] 104",
	  fr: 60,
	  id: "ljwjxj6jlu3zrp0u77n",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 118,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 119,
	    hd: false,
	    nm: "A 4 - Null",
	    parent: 118,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 126.00000500679016,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 144.0000057220459,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 126.00000500679016,
	          s: [115, 94],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 144.0000057220459,
	          s: [115, 78],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [115, 78],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [115, 94]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 120,
	    hd: false,
	    nm: "1 105",
	    parent: 119,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [27, 10]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 121,
	    hd: false,
	    nm: "Union - Null",
	    parent: 120,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 122,
	    hd: false,
	    nm: "Union",
	    parent: 121,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 2,
	      it: [{
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 123,
	    hd: false,
	    nm: "Union",
	    parent: 121,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 2,
	      it: [{
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [0, 0]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 124,
	    hd: false,
	    nm: "Combined Shape - Null",
	    parent: 120,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [9.2095, 7.6427]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 125,
	    hd: false,
	    nm: "Combined Shape",
	    parent: 124,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20.0198, 23.2876], [21.5338, 20.5799], [21.213, 18.9459], [17.211, 16.1041], [14.6771, 14.9724], [14.5325, 13.9929], [13.6777, 13.8652], [13.6046, 12.7335], [14.5222, 10.4061], [15.5948, 9.1849], [15.2122, 7.1292], [15.2122, 3.1002], [7.9106, 1.3193], [6.173, 5.9406], [6.6613, 7.2427], [6.2658, 8.7207], [6.4021, 9.4414], [7.2315, 10.4362], [8.3201, 12.7769], [8.3937, 13.8561], [7.4679, 13.966], [7.3948, 14.8478], [6.2015, 15.4558], [4.99, 16.069], [0.3998, 19.0457], [0.0439, 20.6886], [1.5166, 23.2605], [10.1333, 24.7147], [11.4856, 24.7147], [20.0201, 23.2877], [20.0198, 23.2876]],
	            i: [[0, 0], [0.2283, 1.1626], [0, 0], [2.6542, 0.6737], [0.7799, 0.5037], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.2013, 1.3357], [-0.9134, -1.4471], [0, 0], [0, 0], [-0.1485, -0.5525], [-0.0098, -0.25], [0, 0], [0, 0], [0, 0], [0, 0], [0.0611, -0.2899], [0.3261, -0.1854], [0.5473, -0.2355], [0.4038, -1.5743], [0.1289, -0.6583], [-1.0766, -0.3582], [-3.0767, -0.066], [0, 0], [-2.6034, 0.8568], [0, 0]],
	            o: [[1.125399999999999, -0.37039000000000044], [0, 0], [-0.16122000000000014, -1.0214600000000011], [-0.8992299999999993, -0.24619000000000035], [-0.17055000000000042, -0.09566000000000052], [0, 0], [0, -0.07174999999999976], [1.0227900000000005, -0.3373600000000003], [0.6495499999999996, 0.3536400000000004], [0.7682600000000015, -2.1880000000000006], [0.20133000000000045, -1.3357200000000002], [-0.5116499999999995, -4.43095], [-2.25138, -0.40707000000000004], [0, 0], [-0.67685, 0.43095000000000017], [0.06189999999999962, 0.23032000000000075], [0.04717000000000038, 1.2547899999999998], [0.048210000000000086, 2.0709599999999995], [0.19543, 1.3005899999999997], [0, 0], [0.01252999999999993, 0.2957699999999992], [-0.5379300000000002, 0.23534000000000077], [-0.3338099999999997, 0.1898099999999996], [-2.09016, 0.8991100000000003], [-0.09288000000000002, 0.36208999999999847], [-0.21804, 1.1134500000000003], [2.6247399999999996, 0.8733299999999993], [0, 0], [3.044600000000001, -0.0653100000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 126,
	    hd: false,
	    nm: "Combined Shape",
	    parent: 124,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20.0198, 23.2876], [21.5338, 20.5799], [21.213, 18.9459], [17.211, 16.1041], [14.6771, 14.9724], [14.5325, 13.9929], [13.6777, 13.8652], [13.6046, 12.7335], [14.5222, 10.4061], [15.5948, 9.1849], [15.2122, 7.1292], [15.2122, 3.1002], [7.9106, 1.3193], [6.173, 5.9406], [6.6613, 7.2427], [6.2658, 8.7207], [6.4021, 9.4414], [7.2315, 10.4362], [8.3201, 12.7769], [8.3937, 13.8561], [7.4679, 13.966], [7.3948, 14.8478], [6.2015, 15.4558], [4.99, 16.069], [0.3998, 19.0457], [0.0439, 20.6886], [1.5166, 23.2605], [10.1333, 24.7147], [11.4856, 24.7147], [20.0201, 23.2877], [20.0198, 23.2876]],
	            i: [[0, 0], [0.2283, 1.1626], [0, 0], [2.6542, 0.6737], [0.7799, 0.5037], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.2013, 1.3357], [-0.9134, -1.4471], [0, 0], [0, 0], [-0.1485, -0.5525], [-0.0098, -0.25], [0, 0], [0, 0], [0, 0], [0, 0], [0.0611, -0.2899], [0.3261, -0.1854], [0.5473, -0.2355], [0.4038, -1.5743], [0.1289, -0.6583], [-1.0766, -0.3582], [-3.0767, -0.066], [0, 0], [-2.6034, 0.8568], [0, 0]],
	            o: [[1.125399999999999, -0.37039000000000044], [0, 0], [-0.16122000000000014, -1.0214600000000011], [-0.8992299999999993, -0.24619000000000035], [-0.17055000000000042, -0.09566000000000052], [0, 0], [0, -0.07174999999999976], [1.0227900000000005, -0.3373600000000003], [0.6495499999999996, 0.3536400000000004], [0.7682600000000015, -2.1880000000000006], [0.20133000000000045, -1.3357200000000002], [-0.5116499999999995, -4.43095], [-2.25138, -0.40707000000000004], [0, 0], [-0.67685, 0.43095000000000017], [0.06189999999999962, 0.23032000000000075], [0.04717000000000038, 1.2547899999999998], [0.048210000000000086, 2.0709599999999995], [0.19543, 1.3005899999999997], [0, 0], [0.01252999999999993, 0.2957699999999992], [-0.5379300000000002, 0.23534000000000077], [-0.3338099999999997, 0.1898099999999996], [-2.09016, 0.8991100000000003], [-0.09288000000000002, 0.36208999999999847], [-0.21804, 1.1134500000000003], [2.6247399999999996, 0.8733299999999993], [0, 0], [3.044600000000001, -0.0653100000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [10.79045, 12.3573]
	        },
	        s: {
	          a: 0,
	          k: [43.1618, 49.4292]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] 1 106",
	  fr: 60,
	  id: "ljwjxj6jrcsugfpibo",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 127,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 128,
	    hd: false,
	    nm: "A 4 - Null",
	    parent: 127,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 126.00000500679016,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 144.0000057220459,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 126.00000500679016,
	          s: [115, 94],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 144.0000057220459,
	          s: [115, 78],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [115, 78],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [115, 94]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 129,
	    ty: 0,
	    nm: "1 107",
	    refId: "ljwjxj6jlu3zrp0u77n",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 130,
	    hd: false,
	    nm: "Rectangle 3467754 - Null",
	    parent: 128,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      },
	      p: {
	        a: 0,
	        k: [95, 59]
	      },
	      r: {
	        a: 0,
	        k: 180
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 131,
	    hd: false,
	    nm: "Rectangle 3467754",
	    parent: 130,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[89, 0], [90.1706, 0.1152], [91.2962, 0.4566], [92.3336, 1.011], [93.2426, 1.7574], [93.989, 2.6664], [94.5434, 3.7038], [94.8848, 4.8294], [95, 6], [95, 53], [94.8848, 54.1706], [94.5434, 55.2962], [93.989, 56.3336], [93.2426, 57.2426], [92.3336, 57.989], [91.2962, 58.5434], [90.1706, 58.8848], [89, 59], [6, 59], [4.8294, 58.8848], [3.7038, 58.5434], [2.6664, 57.989], [1.7574, 57.2426], [1.011, 56.3336], [0.4566, 55.2962], [0.1152, 54.1706], [0, 53], [0, 6], [0.1152, 4.8294], [0.4566, 3.7038], [1.011, 2.6664], [1.7574, 1.7574], [2.6664, 1.011], [3.7038, 0.4566], [4.8294, 0.1152], [6, 0], [89, 0], [89, 0]],
	            i: [[0, 0], [-0.39, -0.0774], [-0.3678, -0.1524], [-0.3306, -0.2208], [-0.2814, -0.2814], [-0.2208, -0.3306], [-0.1524, -0.3678], [-0.0774, -0.39], [0, -0.3978], [0, -0.3978], [0.0774, -0.39], [0.1524, -0.3678], [0.2208, -0.3306], [0.2814, -0.2814], [0.3306, -0.2208], [0.3678, -0.1524], [0.39, -0.0774], [0.3978, 0], [0.3978, 0], [0.39, 0.0774], [0.3678, 0.1524], [0.3306, 0.2208], [0.2814, 0.2814], [0.2208, 0.3306], [0.1524, 0.3678], [0.0774, 0.39], [0, 0.3978], [0, 0.3978], [-0.0774, 0.39], [-0.1524, 0.3678], [-0.2208, 0.3306], [-0.2814, 0.2814], [-0.3306, 0.2208], [-0.3678, 0.1524], [-0.39, 0.0774], [-0.3978, 0], [-0.3978, 0], [0, 0]],
	            o: [[0.3978000000000037, 0], [0.39000000000000057, 0.0774], [0.36780000000000257, 0.15239999999999998], [0.330600000000004, 0.22079999999999989], [0.281400000000005, 0.2814000000000001], [0.220799999999997, 0.3306], [0.1524000000000001, 0.3677999999999999], [0.07739999999999725, 0.3899999999999997], [0, 0.39780000000000015], [0, 0.3977999999999966], [-0.07739999999999725, 0.39000000000000057], [-0.1524000000000001, 0.36780000000000257], [-0.220799999999997, 0.3305999999999969], [-0.281400000000005, 0.2813999999999979], [-0.330600000000004, 0.220799999999997], [-0.36780000000000257, 0.1524000000000001], [-0.39000000000000057, 0.07739999999999725], [-0.3978000000000037, 0], [-0.39780000000000015, 0], [-0.3899999999999997, -0.07739999999999725], [-0.3677999999999999, -0.1524000000000001], [-0.3306, -0.220799999999997], [-0.2814000000000001, -0.2813999999999979], [-0.2208, -0.3305999999999969], [-0.15239999999999998, -0.36780000000000257], [-0.0774, -0.39000000000000057], [0, -0.3977999999999966], [0, -0.39780000000000015], [0.0774, -0.3899999999999997], [0.15239999999999998, -0.3677999999999999], [0.22079999999999989, -0.3306], [0.2814000000000001, -0.2814000000000001], [0.3306, -0.2208], [0.3677999999999999, -0.15239999999999998], [0.3899999999999997, -0.0774], [0.39780000000000015, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 132,
	    hd: false,
	    nm: "Rectangle 3467754",
	    parent: 130,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[89, 0], [90.1706, 0.1152], [91.2962, 0.4566], [92.3336, 1.011], [93.2426, 1.7574], [93.989, 2.6664], [94.5434, 3.7038], [94.8848, 4.8294], [95, 6], [95, 53], [94.8848, 54.1706], [94.5434, 55.2962], [93.989, 56.3336], [93.2426, 57.2426], [92.3336, 57.989], [91.2962, 58.5434], [90.1706, 58.8848], [89, 59], [6, 59], [4.8294, 58.8848], [3.7038, 58.5434], [2.6664, 57.989], [1.7574, 57.2426], [1.011, 56.3336], [0.4566, 55.2962], [0.1152, 54.1706], [0, 53], [0, 6], [0.1152, 4.8294], [0.4566, 3.7038], [1.011, 2.6664], [1.7574, 1.7574], [2.6664, 1.011], [3.7038, 0.4566], [4.8294, 0.1152], [6, 0], [89, 0], [89, 0]],
	            i: [[0, 0], [-0.39, -0.0774], [-0.3678, -0.1524], [-0.3306, -0.2208], [-0.2814, -0.2814], [-0.2208, -0.3306], [-0.1524, -0.3678], [-0.0774, -0.39], [0, -0.3978], [0, -0.3978], [0.0774, -0.39], [0.1524, -0.3678], [0.2208, -0.3306], [0.2814, -0.2814], [0.3306, -0.2208], [0.3678, -0.1524], [0.39, -0.0774], [0.3978, 0], [0.3978, 0], [0.39, 0.0774], [0.3678, 0.1524], [0.3306, 0.2208], [0.2814, 0.2814], [0.2208, 0.3306], [0.1524, 0.3678], [0.0774, 0.39], [0, 0.3978], [0, 0.3978], [-0.0774, 0.39], [-0.1524, 0.3678], [-0.2208, 0.3306], [-0.2814, 0.2814], [-0.3306, 0.2208], [-0.3678, 0.1524], [-0.39, 0.0774], [-0.3978, 0], [-0.3978, 0], [0, 0]],
	            o: [[0.3978000000000037, 0], [0.39000000000000057, 0.0774], [0.36780000000000257, 0.15239999999999998], [0.330600000000004, 0.22079999999999989], [0.281400000000005, 0.2814000000000001], [0.220799999999997, 0.3306], [0.1524000000000001, 0.3677999999999999], [0.07739999999999725, 0.3899999999999997], [0, 0.39780000000000015], [0, 0.3977999999999966], [-0.07739999999999725, 0.39000000000000057], [-0.1524000000000001, 0.36780000000000257], [-0.220799999999997, 0.3305999999999969], [-0.281400000000005, 0.2813999999999979], [-0.330600000000004, 0.220799999999997], [-0.36780000000000257, 0.1524000000000001], [-0.39000000000000057, 0.07739999999999725], [-0.3978000000000037, 0], [-0.39780000000000015, 0], [-0.3899999999999997, -0.07739999999999725], [-0.3677999999999999, -0.1524000000000001], [-0.3306, -0.220799999999997], [-0.2814000000000001, -0.2813999999999979], [-0.2208, -0.3305999999999969], [-0.15239999999999998, -0.36780000000000257], [-0.0774, -0.39000000000000057], [0, -0.3977999999999966], [0, -0.39780000000000015], [0.0774, -0.3899999999999997], [0.15239999999999998, -0.3677999999999999], [0.22079999999999989, -0.3306], [0.2814000000000001, -0.2814000000000001], [0.3306, -0.2208], [0.3677999999999999, -0.15239999999999998], [0.3899999999999997, -0.0774], [0.39780000000000015, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [48, 30]
	        },
	        s: {
	          a: 0,
	          k: [192, 120]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - 108",
	  fr: 60,
	  id: "ljwjxj6poy9cnhv837q",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 133,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 134,
	    hd: false,
	    nm: "A 3 - Null",
	    parent: 133,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 90.00000357627869,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 108.00000429153442,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 90.00000357627869,
	          s: [12, 94],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 108.00000429153442,
	          s: [12, 78],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [12, 78],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [12, 94]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 135,
	    hd: false,
	    nm: "1 109",
	    parent: 134,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [27, 10]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 136,
	    hd: false,
	    nm: "Union - Null",
	    parent: 135,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 137,
	    hd: false,
	    nm: "Union",
	    parent: 136,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 2,
	      it: [{
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 138,
	    hd: false,
	    nm: "Union",
	    parent: 136,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 2,
	      it: [{
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [0, 0]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 139,
	    hd: false,
	    nm: "Combined Shape - Null",
	    parent: 135,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [9.2095, 7.6427]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 140,
	    hd: false,
	    nm: "Combined Shape",
	    parent: 139,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20.0198, 23.2876], [21.5338, 20.5799], [21.213, 18.9459], [17.211, 16.1041], [14.6771, 14.9724], [14.5325, 13.9929], [13.6777, 13.8652], [13.6046, 12.7335], [14.5222, 10.4061], [15.5948, 9.1849], [15.2122, 7.1292], [15.2122, 3.1002], [7.9106, 1.3193], [6.173, 5.9406], [6.6613, 7.2427], [6.2658, 8.7207], [6.4021, 9.4414], [7.2315, 10.4362], [8.3201, 12.7769], [8.3937, 13.8561], [7.4679, 13.966], [7.3948, 14.8478], [6.2015, 15.4558], [4.99, 16.069], [0.3998, 19.0457], [0.0439, 20.6886], [1.5166, 23.2605], [10.1333, 24.7147], [11.4856, 24.7147], [20.0201, 23.2877], [20.0198, 23.2876]],
	            i: [[0, 0], [0.2283, 1.1626], [0, 0], [2.6542, 0.6737], [0.7799, 0.5037], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.2013, 1.3357], [-0.9134, -1.4471], [0, 0], [0, 0], [-0.1485, -0.5525], [-0.0098, -0.25], [0, 0], [0, 0], [0, 0], [0, 0], [0.0611, -0.2899], [0.3261, -0.1854], [0.5473, -0.2355], [0.4038, -1.5743], [0.1289, -0.6583], [-1.0766, -0.3582], [-3.0767, -0.066], [0, 0], [-2.6034, 0.8568], [0, 0]],
	            o: [[1.125399999999999, -0.37039000000000044], [0, 0], [-0.16122000000000014, -1.0214600000000011], [-0.8992299999999993, -0.24619000000000035], [-0.17055000000000042, -0.09566000000000052], [0, 0], [0, -0.07174999999999976], [1.0227900000000005, -0.3373600000000003], [0.6495499999999996, 0.3536400000000004], [0.7682600000000015, -2.1880000000000006], [0.20133000000000045, -1.3357200000000002], [-0.5116499999999995, -4.43095], [-2.25138, -0.40707000000000004], [0, 0], [-0.67685, 0.43095000000000017], [0.06189999999999962, 0.23032000000000075], [0.04717000000000038, 1.2547899999999998], [0.048210000000000086, 2.0709599999999995], [0.19543, 1.3005899999999997], [0, 0], [0.01252999999999993, 0.2957699999999992], [-0.5379300000000002, 0.23534000000000077], [-0.3338099999999997, 0.1898099999999996], [-2.09016, 0.8991100000000003], [-0.09288000000000002, 0.36208999999999847], [-0.21804, 1.1134500000000003], [2.6247399999999996, 0.8733299999999993], [0, 0], [3.044600000000001, -0.0653100000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 141,
	    hd: false,
	    nm: "Combined Shape",
	    parent: 139,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20.0198, 23.2876], [21.5338, 20.5799], [21.213, 18.9459], [17.211, 16.1041], [14.6771, 14.9724], [14.5325, 13.9929], [13.6777, 13.8652], [13.6046, 12.7335], [14.5222, 10.4061], [15.5948, 9.1849], [15.2122, 7.1292], [15.2122, 3.1002], [7.9106, 1.3193], [6.173, 5.9406], [6.6613, 7.2427], [6.2658, 8.7207], [6.4021, 9.4414], [7.2315, 10.4362], [8.3201, 12.7769], [8.3937, 13.8561], [7.4679, 13.966], [7.3948, 14.8478], [6.2015, 15.4558], [4.99, 16.069], [0.3998, 19.0457], [0.0439, 20.6886], [1.5166, 23.2605], [10.1333, 24.7147], [11.4856, 24.7147], [20.0201, 23.2877], [20.0198, 23.2876]],
	            i: [[0, 0], [0.2283, 1.1626], [0, 0], [2.6542, 0.6737], [0.7799, 0.5037], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.2013, 1.3357], [-0.9134, -1.4471], [0, 0], [0, 0], [-0.1485, -0.5525], [-0.0098, -0.25], [0, 0], [0, 0], [0, 0], [0, 0], [0.0611, -0.2899], [0.3261, -0.1854], [0.5473, -0.2355], [0.4038, -1.5743], [0.1289, -0.6583], [-1.0766, -0.3582], [-3.0767, -0.066], [0, 0], [-2.6034, 0.8568], [0, 0]],
	            o: [[1.125399999999999, -0.37039000000000044], [0, 0], [-0.16122000000000014, -1.0214600000000011], [-0.8992299999999993, -0.24619000000000035], [-0.17055000000000042, -0.09566000000000052], [0, 0], [0, -0.07174999999999976], [1.0227900000000005, -0.3373600000000003], [0.6495499999999996, 0.3536400000000004], [0.7682600000000015, -2.1880000000000006], [0.20133000000000045, -1.3357200000000002], [-0.5116499999999995, -4.43095], [-2.25138, -0.40707000000000004], [0, 0], [-0.67685, 0.43095000000000017], [0.06189999999999962, 0.23032000000000075], [0.04717000000000038, 1.2547899999999998], [0.048210000000000086, 2.0709599999999995], [0.19543, 1.3005899999999997], [0, 0], [0.01252999999999993, 0.2957699999999992], [-0.5379300000000002, 0.23534000000000077], [-0.3338099999999997, 0.1898099999999996], [-2.09016, 0.8991100000000003], [-0.09288000000000002, 0.36208999999999847], [-0.21804, 1.1134500000000003], [2.6247399999999996, 0.8733299999999993], [0, 0], [3.044600000000001, -0.0653100000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [10.79045, 12.3573]
	        },
	        s: {
	          a: 0,
	          k: [43.1618, 49.4292]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] 1 110",
	  fr: 60,
	  id: "ljwjxj6pu0pb3tkdx2a",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 142,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 143,
	    hd: false,
	    nm: "A 3 - Null",
	    parent: 142,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 90.00000357627869,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 108.00000429153442,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 90.00000357627869,
	          s: [12, 94],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 108.00000429153442,
	          s: [12, 78],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [12, 78],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [12, 94]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 144,
	    ty: 0,
	    nm: "1 111",
	    refId: "ljwjxj6poy9cnhv837q",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 145,
	    hd: false,
	    nm: "Rectangle 3467754 - Null",
	    parent: 143,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      },
	      p: {
	        a: 0,
	        k: [0, 59]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 146,
	    hd: false,
	    nm: "Rectangle 3467754",
	    parent: 145,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[89, 0], [90.1706, 0.1152], [91.2962, 0.4566], [92.3336, 1.011], [93.2426, 1.7574], [93.989, 2.6664], [94.5434, 3.7038], [94.8848, 4.8294], [95, 6], [95, 53], [94.8848, 54.1706], [94.5434, 55.2962], [93.989, 56.3336], [93.2426, 57.2426], [92.3336, 57.989], [91.2962, 58.5434], [90.1706, 58.8848], [89, 59], [6, 59], [4.8294, 58.8848], [3.7038, 58.5434], [2.6664, 57.989], [1.7574, 57.2426], [1.011, 56.3336], [0.4566, 55.2962], [0.1152, 54.1706], [0, 53], [0, 6], [0.1152, 4.8294], [0.4566, 3.7038], [1.011, 2.6664], [1.7574, 1.7574], [2.6664, 1.011], [3.7038, 0.4566], [4.8294, 0.1152], [6, 0], [89, 0], [89, 0]],
	            i: [[0, 0], [-0.39, -0.0774], [-0.3678, -0.1524], [-0.3306, -0.2208], [-0.2814, -0.2814], [-0.2208, -0.3306], [-0.1524, -0.3678], [-0.0774, -0.39], [0, -0.3978], [0, -0.3978], [0.0774, -0.39], [0.1524, -0.3678], [0.2208, -0.3306], [0.2814, -0.2814], [0.3306, -0.2208], [0.3678, -0.1524], [0.39, -0.0774], [0.3978, 0], [0.3978, 0], [0.39, 0.0774], [0.3678, 0.1524], [0.3306, 0.2208], [0.2814, 0.2814], [0.2208, 0.3306], [0.1524, 0.3678], [0.0774, 0.39], [0, 0.3978], [0, 0.3978], [-0.0774, 0.39], [-0.1524, 0.3678], [-0.2208, 0.3306], [-0.2814, 0.2814], [-0.3306, 0.2208], [-0.3678, 0.1524], [-0.39, 0.0774], [-0.3978, 0], [-0.3978, 0], [0, 0]],
	            o: [[0.3978000000000037, 0], [0.39000000000000057, 0.0774], [0.36780000000000257, 0.15239999999999998], [0.330600000000004, 0.22079999999999989], [0.281400000000005, 0.2814000000000001], [0.220799999999997, 0.3306], [0.1524000000000001, 0.3677999999999999], [0.07739999999999725, 0.3899999999999997], [0, 0.39780000000000015], [0, 0.3977999999999966], [-0.07739999999999725, 0.39000000000000057], [-0.1524000000000001, 0.36780000000000257], [-0.220799999999997, 0.3305999999999969], [-0.281400000000005, 0.2813999999999979], [-0.330600000000004, 0.220799999999997], [-0.36780000000000257, 0.1524000000000001], [-0.39000000000000057, 0.07739999999999725], [-0.3978000000000037, 0], [-0.39780000000000015, 0], [-0.3899999999999997, -0.07739999999999725], [-0.3677999999999999, -0.1524000000000001], [-0.3306, -0.220799999999997], [-0.2814000000000001, -0.2813999999999979], [-0.2208, -0.3305999999999969], [-0.15239999999999998, -0.36780000000000257], [-0.0774, -0.39000000000000057], [0, -0.3977999999999966], [0, -0.39780000000000015], [0.0774, -0.3899999999999997], [0.15239999999999998, -0.3677999999999999], [0.22079999999999989, -0.3306], [0.2814000000000001, -0.2814000000000001], [0.3306, -0.2208], [0.3677999999999999, -0.15239999999999998], [0.3899999999999997, -0.0774], [0.39780000000000015, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 147,
	    hd: false,
	    nm: "Rectangle 3467754",
	    parent: 145,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[89, 0], [90.1706, 0.1152], [91.2962, 0.4566], [92.3336, 1.011], [93.2426, 1.7574], [93.989, 2.6664], [94.5434, 3.7038], [94.8848, 4.8294], [95, 6], [95, 53], [94.8848, 54.1706], [94.5434, 55.2962], [93.989, 56.3336], [93.2426, 57.2426], [92.3336, 57.989], [91.2962, 58.5434], [90.1706, 58.8848], [89, 59], [6, 59], [4.8294, 58.8848], [3.7038, 58.5434], [2.6664, 57.989], [1.7574, 57.2426], [1.011, 56.3336], [0.4566, 55.2962], [0.1152, 54.1706], [0, 53], [0, 6], [0.1152, 4.8294], [0.4566, 3.7038], [1.011, 2.6664], [1.7574, 1.7574], [2.6664, 1.011], [3.7038, 0.4566], [4.8294, 0.1152], [6, 0], [89, 0], [89, 0]],
	            i: [[0, 0], [-0.39, -0.0774], [-0.3678, -0.1524], [-0.3306, -0.2208], [-0.2814, -0.2814], [-0.2208, -0.3306], [-0.1524, -0.3678], [-0.0774, -0.39], [0, -0.3978], [0, -0.3978], [0.0774, -0.39], [0.1524, -0.3678], [0.2208, -0.3306], [0.2814, -0.2814], [0.3306, -0.2208], [0.3678, -0.1524], [0.39, -0.0774], [0.3978, 0], [0.3978, 0], [0.39, 0.0774], [0.3678, 0.1524], [0.3306, 0.2208], [0.2814, 0.2814], [0.2208, 0.3306], [0.1524, 0.3678], [0.0774, 0.39], [0, 0.3978], [0, 0.3978], [-0.0774, 0.39], [-0.1524, 0.3678], [-0.2208, 0.3306], [-0.2814, 0.2814], [-0.3306, 0.2208], [-0.3678, 0.1524], [-0.39, 0.0774], [-0.3978, 0], [-0.3978, 0], [0, 0]],
	            o: [[0.3978000000000037, 0], [0.39000000000000057, 0.0774], [0.36780000000000257, 0.15239999999999998], [0.330600000000004, 0.22079999999999989], [0.281400000000005, 0.2814000000000001], [0.220799999999997, 0.3306], [0.1524000000000001, 0.3677999999999999], [0.07739999999999725, 0.3899999999999997], [0, 0.39780000000000015], [0, 0.3977999999999966], [-0.07739999999999725, 0.39000000000000057], [-0.1524000000000001, 0.36780000000000257], [-0.220799999999997, 0.3305999999999969], [-0.281400000000005, 0.2813999999999979], [-0.330600000000004, 0.220799999999997], [-0.36780000000000257, 0.1524000000000001], [-0.39000000000000057, 0.07739999999999725], [-0.3978000000000037, 0], [-0.39780000000000015, 0], [-0.3899999999999997, -0.07739999999999725], [-0.3677999999999999, -0.1524000000000001], [-0.3306, -0.220799999999997], [-0.2814000000000001, -0.2813999999999979], [-0.2208, -0.3305999999999969], [-0.15239999999999998, -0.36780000000000257], [-0.0774, -0.39000000000000057], [0, -0.3977999999999966], [0, -0.39780000000000015], [0.0774, -0.3899999999999997], [0.15239999999999998, -0.3677999999999999], [0.22079999999999989, -0.3306], [0.2814000000000001, -0.2814000000000001], [0.3306, -0.2208], [0.3677999999999999, -0.15239999999999998], [0.3899999999999997, -0.0774], [0.39780000000000015, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [48, 30]
	        },
	        s: {
	          a: 0,
	          k: [192, 120]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - 112",
	  fr: 60,
	  id: "ljwjxj6uq8g8sixr9h",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 148,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 149,
	    hd: false,
	    nm: "A 1 - Null",
	    parent: 148,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 18.000000715255737,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 36.000001430511475,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 18.000000715255737,
	          s: [12, 28],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 36.000001430511475,
	          s: [12, 12],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [12, 12],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [12, 28]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 150,
	    hd: false,
	    nm: "1 113 - Null",
	    parent: 149,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [27, 10]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 151,
	    hd: false,
	    nm: "Union - Null",
	    parent: 150,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 152,
	    hd: false,
	    nm: "Union",
	    parent: 151,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 2,
	      it: [{
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 153,
	    hd: false,
	    nm: "Union",
	    parent: 151,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 2,
	      it: [{
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [0, 0]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 154,
	    hd: false,
	    nm: "Combined Shape - Null",
	    parent: 150,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [9.2095, 7.6427]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 155,
	    hd: false,
	    nm: "Combined Shape",
	    parent: 154,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20.0198, 23.2876], [21.5338, 20.5799], [21.213, 18.9459], [17.211, 16.1041], [14.6771, 14.9724], [14.5325, 13.9929], [13.6777, 13.8652], [13.6046, 12.7335], [14.5222, 10.4061], [15.5948, 9.1849], [15.2122, 7.1292], [15.2122, 3.1002], [7.9106, 1.3193], [6.173, 5.9406], [6.6613, 7.2427], [6.2658, 8.7207], [6.4021, 9.4414], [7.2315, 10.4362], [8.3201, 12.7769], [8.3937, 13.8561], [7.4679, 13.966], [7.3948, 14.8478], [6.2015, 15.4558], [4.99, 16.069], [0.3998, 19.0457], [0.0439, 20.6886], [1.5166, 23.2605], [10.1333, 24.7147], [11.4856, 24.7147], [20.0201, 23.2877], [20.0198, 23.2876]],
	            i: [[0, 0], [0.2283, 1.1626], [0, 0], [2.6542, 0.6737], [0.7799, 0.5037], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.2013, 1.3357], [-0.9134, -1.4471], [0, 0], [0, 0], [-0.1485, -0.5525], [-0.0098, -0.25], [0, 0], [0, 0], [0, 0], [0, 0], [0.0611, -0.2899], [0.3261, -0.1854], [0.5473, -0.2355], [0.4038, -1.5743], [0.1289, -0.6583], [-1.0766, -0.3582], [-3.0767, -0.066], [0, 0], [-2.6034, 0.8568], [0, 0]],
	            o: [[1.125399999999999, -0.37039000000000044], [0, 0], [-0.16122000000000014, -1.0214600000000011], [-0.8992299999999993, -0.24619000000000035], [-0.17055000000000042, -0.09566000000000052], [0, 0], [0, -0.07174999999999976], [1.0227900000000005, -0.3373600000000003], [0.6495499999999996, 0.3536400000000004], [0.7682600000000015, -2.1880000000000006], [0.20133000000000045, -1.3357200000000002], [-0.5116499999999995, -4.43095], [-2.25138, -0.40707000000000004], [0, 0], [-0.67685, 0.43095000000000017], [0.06189999999999962, 0.23032000000000075], [0.04717000000000038, 1.2547899999999998], [0.048210000000000086, 2.0709599999999995], [0.19543, 1.3005899999999997], [0, 0], [0.01252999999999993, 0.2957699999999992], [-0.5379300000000002, 0.23534000000000077], [-0.3338099999999997, 0.1898099999999996], [-2.09016, 0.8991100000000003], [-0.09288000000000002, 0.36208999999999847], [-0.21804, 1.1134500000000003], [2.6247399999999996, 0.8733299999999993], [0, 0], [3.044600000000001, -0.0653100000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 156,
	    hd: false,
	    nm: "Combined Shape",
	    parent: 154,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[20.0198, 23.2876], [21.5338, 20.5799], [21.213, 18.9459], [17.211, 16.1041], [14.6771, 14.9724], [14.5325, 13.9929], [13.6777, 13.8652], [13.6046, 12.7335], [14.5222, 10.4061], [15.5948, 9.1849], [15.2122, 7.1292], [15.2122, 3.1002], [7.9106, 1.3193], [6.173, 5.9406], [6.6613, 7.2427], [6.2658, 8.7207], [6.4021, 9.4414], [7.2315, 10.4362], [8.3201, 12.7769], [8.3937, 13.8561], [7.4679, 13.966], [7.3948, 14.8478], [6.2015, 15.4558], [4.99, 16.069], [0.3998, 19.0457], [0.0439, 20.6886], [1.5166, 23.2605], [10.1333, 24.7147], [11.4856, 24.7147], [20.0201, 23.2877], [20.0198, 23.2876]],
	            i: [[0, 0], [0.2283, 1.1626], [0, 0], [2.6542, 0.6737], [0.7799, 0.5037], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0.2013, 1.3357], [-0.9134, -1.4471], [0, 0], [0, 0], [-0.1485, -0.5525], [-0.0098, -0.25], [0, 0], [0, 0], [0, 0], [0, 0], [0.0611, -0.2899], [0.3261, -0.1854], [0.5473, -0.2355], [0.4038, -1.5743], [0.1289, -0.6583], [-1.0766, -0.3582], [-3.0767, -0.066], [0, 0], [-2.6034, 0.8568], [0, 0]],
	            o: [[1.125399999999999, -0.37039000000000044], [0, 0], [-0.16122000000000014, -1.0214600000000011], [-0.8992299999999993, -0.24619000000000035], [-0.17055000000000042, -0.09566000000000052], [0, 0], [0, -0.07174999999999976], [1.0227900000000005, -0.3373600000000003], [0.6495499999999996, 0.3536400000000004], [0.7682600000000015, -2.1880000000000006], [0.20133000000000045, -1.3357200000000002], [-0.5116499999999995, -4.43095], [-2.25138, -0.40707000000000004], [0, 0], [-0.67685, 0.43095000000000017], [0.06189999999999962, 0.23032000000000075], [0.04717000000000038, 1.2547899999999998], [0.048210000000000086, 2.0709599999999995], [0.19543, 1.3005899999999997], [0, 0], [0.01252999999999993, 0.2957699999999992], [-0.5379300000000002, 0.23534000000000077], [-0.3338099999999997, 0.1898099999999996], [-2.09016, 0.8991100000000003], [-0.09288000000000002, 0.36208999999999847], [-0.21804, 1.1134500000000003], [2.6247399999999996, 0.8733299999999993], [0, 0], [3.044600000000001, -0.0653100000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [10.79045, 12.3573]
	        },
	        s: {
	          a: 0,
	          k: [43.1618, 49.4292]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] 1 114",
	  fr: 60,
	  id: "ljwjxj6uv4vvu04bo6c",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 157,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 158,
	    hd: false,
	    nm: "A 1 - Null",
	    parent: 157,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 18.000000715255737,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 36.000001430511475,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 18.000000715255737,
	          s: [12, 28],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 36.000001430511475,
	          s: [12, 12],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [12, 12],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [12, 28]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 159,
	    ty: 0,
	    nm: "1 115",
	    refId: "ljwjxj6uq8g8sixr9h",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 160,
	    hd: false,
	    nm: "Rectangle 3467754 - Null",
	    parent: 158,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 161,
	    hd: false,
	    nm: "Rectangle 3467754",
	    parent: 160,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[89, 0], [90.1706, 0.1152], [91.2962, 0.4566], [92.3336, 1.011], [93.2426, 1.7574], [93.989, 2.6664], [94.5434, 3.7038], [94.8848, 4.8294], [95, 6], [95, 53], [94.8848, 54.1706], [94.5434, 55.2962], [93.989, 56.3336], [93.2426, 57.2426], [92.3336, 57.989], [91.2962, 58.5434], [90.1706, 58.8848], [89, 59], [6, 59], [4.8294, 58.8848], [3.7038, 58.5434], [2.6664, 57.989], [1.7574, 57.2426], [1.011, 56.3336], [0.4566, 55.2962], [0.1152, 54.1706], [0, 53], [0, 6], [0.1152, 4.8294], [0.4566, 3.7038], [1.011, 2.6664], [1.7574, 1.7574], [2.6664, 1.011], [3.7038, 0.4566], [4.8294, 0.1152], [6, 0], [89, 0], [89, 0]],
	            i: [[0, 0], [-0.39, -0.0774], [-0.3678, -0.1524], [-0.3306, -0.2208], [-0.2814, -0.2814], [-0.2208, -0.3306], [-0.1524, -0.3678], [-0.0774, -0.39], [0, -0.3978], [0, -0.3978], [0.0774, -0.39], [0.1524, -0.3678], [0.2208, -0.3306], [0.2814, -0.2814], [0.3306, -0.2208], [0.3678, -0.1524], [0.39, -0.0774], [0.3978, 0], [0.3978, 0], [0.39, 0.0774], [0.3678, 0.1524], [0.3306, 0.2208], [0.2814, 0.2814], [0.2208, 0.3306], [0.1524, 0.3678], [0.0774, 0.39], [0, 0.3978], [0, 0.3978], [-0.0774, 0.39], [-0.1524, 0.3678], [-0.2208, 0.3306], [-0.2814, 0.2814], [-0.3306, 0.2208], [-0.3678, 0.1524], [-0.39, 0.0774], [-0.3978, 0], [-0.3978, 0], [0, 0]],
	            o: [[0.3978000000000037, 0], [0.39000000000000057, 0.0774], [0.36780000000000257, 0.15239999999999998], [0.330600000000004, 0.22079999999999989], [0.281400000000005, 0.2814000000000001], [0.220799999999997, 0.3306], [0.1524000000000001, 0.3677999999999999], [0.07739999999999725, 0.3899999999999997], [0, 0.39780000000000015], [0, 0.3977999999999966], [-0.07739999999999725, 0.39000000000000057], [-0.1524000000000001, 0.36780000000000257], [-0.220799999999997, 0.3305999999999969], [-0.281400000000005, 0.2813999999999979], [-0.330600000000004, 0.220799999999997], [-0.36780000000000257, 0.1524000000000001], [-0.39000000000000057, 0.07739999999999725], [-0.3978000000000037, 0], [-0.39780000000000015, 0], [-0.3899999999999997, -0.07739999999999725], [-0.3677999999999999, -0.1524000000000001], [-0.3306, -0.220799999999997], [-0.2814000000000001, -0.2813999999999979], [-0.2208, -0.3305999999999969], [-0.15239999999999998, -0.36780000000000257], [-0.0774, -0.39000000000000057], [0, -0.3977999999999966], [0, -0.39780000000000015], [0.0774, -0.3899999999999997], [0.15239999999999998, -0.3677999999999999], [0.22079999999999989, -0.3306], [0.2814000000000001, -0.2814000000000001], [0.3306, -0.2208], [0.3677999999999999, -0.15239999999999998], [0.3899999999999997, -0.0774], [0.39780000000000015, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 162,
	    hd: false,
	    nm: "Rectangle 3467754",
	    parent: 160,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[89, 0], [90.1706, 0.1152], [91.2962, 0.4566], [92.3336, 1.011], [93.2426, 1.7574], [93.989, 2.6664], [94.5434, 3.7038], [94.8848, 4.8294], [95, 6], [95, 53], [94.8848, 54.1706], [94.5434, 55.2962], [93.989, 56.3336], [93.2426, 57.2426], [92.3336, 57.989], [91.2962, 58.5434], [90.1706, 58.8848], [89, 59], [6, 59], [4.8294, 58.8848], [3.7038, 58.5434], [2.6664, 57.989], [1.7574, 57.2426], [1.011, 56.3336], [0.4566, 55.2962], [0.1152, 54.1706], [0, 53], [0, 6], [0.1152, 4.8294], [0.4566, 3.7038], [1.011, 2.6664], [1.7574, 1.7574], [2.6664, 1.011], [3.7038, 0.4566], [4.8294, 0.1152], [6, 0], [89, 0], [89, 0]],
	            i: [[0, 0], [-0.39, -0.0774], [-0.3678, -0.1524], [-0.3306, -0.2208], [-0.2814, -0.2814], [-0.2208, -0.3306], [-0.1524, -0.3678], [-0.0774, -0.39], [0, -0.3978], [0, -0.3978], [0.0774, -0.39], [0.1524, -0.3678], [0.2208, -0.3306], [0.2814, -0.2814], [0.3306, -0.2208], [0.3678, -0.1524], [0.39, -0.0774], [0.3978, 0], [0.3978, 0], [0.39, 0.0774], [0.3678, 0.1524], [0.3306, 0.2208], [0.2814, 0.2814], [0.2208, 0.3306], [0.1524, 0.3678], [0.0774, 0.39], [0, 0.3978], [0, 0.3978], [-0.0774, 0.39], [-0.1524, 0.3678], [-0.2208, 0.3306], [-0.2814, 0.2814], [-0.3306, 0.2208], [-0.3678, 0.1524], [-0.39, 0.0774], [-0.3978, 0], [-0.3978, 0], [0, 0]],
	            o: [[0.3978000000000037, 0], [0.39000000000000057, 0.0774], [0.36780000000000257, 0.15239999999999998], [0.330600000000004, 0.22079999999999989], [0.281400000000005, 0.2814000000000001], [0.220799999999997, 0.3306], [0.1524000000000001, 0.3677999999999999], [0.07739999999999725, 0.3899999999999997], [0, 0.39780000000000015], [0, 0.3977999999999966], [-0.07739999999999725, 0.39000000000000057], [-0.1524000000000001, 0.36780000000000257], [-0.220799999999997, 0.3305999999999969], [-0.281400000000005, 0.2813999999999979], [-0.330600000000004, 0.220799999999997], [-0.36780000000000257, 0.1524000000000001], [-0.39000000000000057, 0.07739999999999725], [-0.3978000000000037, 0], [-0.39780000000000015, 0], [-0.3899999999999997, -0.07739999999999725], [-0.3677999999999999, -0.1524000000000001], [-0.3306, -0.220799999999997], [-0.2814000000000001, -0.2813999999999979], [-0.2208, -0.3305999999999969], [-0.15239999999999998, -0.36780000000000257], [-0.0774, -0.39000000000000057], [0, -0.3977999999999966], [0, -0.39780000000000015], [0.0774, -0.3899999999999997], [0.15239999999999998, -0.3677999999999999], [0.22079999999999989, -0.3306], [0.2814000000000001, -0.2814000000000001], [0.3306, -0.2208], [0.3677999999999999, -0.15239999999999998], [0.3899999999999997, -0.0774], [0.39780000000000015, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [48, 30]
	        },
	        s: {
	          a: 0,
	          k: [192, 120]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684941 - Null / Frame 1684937 - Null / Rectangle 3467758 - Null / Rectangle 3467758 / Rectangle 3467758",
	  fr: 60,
	  id: "ljwjxj71niq5y7fz1c",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 163,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 164,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 163,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 165,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 164,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 166,
	    hd: false,
	    nm: "Frame 1684941 - Null",
	    parent: 165,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 42]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 167,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 166,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [16, 4]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 168,
	    hd: false,
	    nm: "Rectangle 3467758 - Null",
	    parent: 167,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 169,
	    hd: false,
	    nm: "Rectangle 3467758",
	    parent: 168,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5, 0], [25.5, 0], [27, 1.5], [27, 1.5], [25.5, 3], [1.5, 3], [0, 1.5], [0, 1.5], [1.5, 0], [1.5, 0]],
	            i: [[0, 0], [0, 0], [0, -0.8284], [0, 0], [0.8284, 0], [0, 0], [0, 0.8284], [0, 0], [-0.8284, 0], [0, 0]],
	            o: [[0, 0], [0.8284300000000009, 0], [0, 0], [0, 0.82843], [0, 0], [-0.82843, 0], [0, 0], [0, -0.82843], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 170,
	    hd: false,
	    nm: "Rectangle 3467758",
	    parent: 168,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5, 0], [25.5, 0], [27, 1.5], [27, 1.5], [25.5, 3], [1.5, 3], [0, 1.5], [0, 1.5], [1.5, 0], [1.5, 0]],
	            i: [[0, 0], [0, 0], [0, -0.8284], [0, 0], [0.8284, 0], [0, 0], [0, 0.8284], [0, 0], [-0.8284, 0], [0, 0]],
	            o: [[0, 0], [0.8284300000000009, 0], [0, 0], [0, 0.82843], [0, 0], [-0.82843, 0], [0, 0], [0, -0.82843], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [14, 2]
	        },
	        s: {
	          a: 0,
	          k: [56, 8]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684941 - Null / Frame 1684937 / Ellipse 256 - Null / Ellipse 256 / Ellipse 256",
	  fr: 60,
	  id: "ljwjxj71utvgbljp81",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 171,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 172,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 171,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 173,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 172,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 174,
	    hd: false,
	    nm: "Frame 1684941 - Null",
	    parent: 173,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 42]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 175,
	    ty: 0,
	    nm: "Frame 1684937",
	    refId: "ljwjxj71niq5y7fz1c",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 176,
	    hd: false,
	    nm: "Ellipse 256 - Null",
	    parent: 174,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 80.0000011920929
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 177,
	    hd: false,
	    nm: "Ellipse 256",
	    parent: 176,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 80.0000011920929
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 5.5], [10.7311, 7.1995], [9.9495, 8.7329], [8.7329, 9.9495], [7.1995, 10.7311], [5.5, 11], [3.8005, 10.7311], [2.2671, 9.9495], [1.0505, 8.7329], [0.269, 7.1995], [0, 5.5], [0.269, 3.8005], [1.0505, 2.2671], [2.2671, 1.0505], [3.8005, 0.269], [5.5, 0], [7.1995, 0.269], [8.7329, 1.0505], [9.9495, 2.2671], [10.7311, 3.8005], [11, 5.5], [11, 5.5]],
	            i: [[0, 0], [0.1782, -0.5489], [0.3394, -0.4669], [0.467, -0.3393], [0.5489, -0.1782], [0.577, 0], [0.5489, 0.1782], [0.467, 0.3394], [0.3394, 0.467], [0.1782, 0.5489], [0, 0.577], [-0.1782, 0.5489], [-0.3393, 0.467], [-0.4669, 0.3394], [-0.5489, 0.1782], [-0.5769, 0], [-0.5489, -0.1782], [-0.4669, -0.3393], [-0.3393, -0.4669], [-0.1782, -0.5489], [0, -0.5769], [0, 0]],
	            o: [[0, 0.577], [-0.17820000000000036, 0.5488999999999997], [-0.3392999999999997, 0.4670000000000005], [-0.46690000000000076, 0.3393999999999995], [-0.5488999999999997, 0.17820000000000036], [-0.5769000000000002, 0], [-0.5489000000000002, -0.17820000000000036], [-0.46689999999999987, -0.3392999999999997], [-0.33929999999999993, -0.46690000000000076], [-0.1782, -0.5488999999999997], [0, -0.5769000000000002], [0.17820000000000003, -0.5489000000000002], [0.3393999999999999, -0.46689999999999987], [0.4670000000000001, -0.33929999999999993], [0.5489000000000002, -0.1782], [0.577, 0], [0.5488999999999997, 0.17820000000000003], [0.4670000000000005, 0.3393999999999999], [0.3393999999999995, 0.4670000000000001], [0.17820000000000036, 0.5489000000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 178,
	    hd: false,
	    nm: "Ellipse 256",
	    parent: 176,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 80.0000011920929
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 5.5], [10.7311, 7.1995], [9.9495, 8.7329], [8.7329, 9.9495], [7.1995, 10.7311], [5.5, 11], [3.8005, 10.7311], [2.2671, 9.9495], [1.0505, 8.7329], [0.269, 7.1995], [0, 5.5], [0.269, 3.8005], [1.0505, 2.2671], [2.2671, 1.0505], [3.8005, 0.269], [5.5, 0], [7.1995, 0.269], [8.7329, 1.0505], [9.9495, 2.2671], [10.7311, 3.8005], [11, 5.5], [11, 5.5]],
	            i: [[0, 0], [0.1782, -0.5489], [0.3394, -0.4669], [0.467, -0.3393], [0.5489, -0.1782], [0.577, 0], [0.5489, 0.1782], [0.467, 0.3394], [0.3394, 0.467], [0.1782, 0.5489], [0, 0.577], [-0.1782, 0.5489], [-0.3393, 0.467], [-0.4669, 0.3394], [-0.5489, 0.1782], [-0.5769, 0], [-0.5489, -0.1782], [-0.4669, -0.3393], [-0.3393, -0.4669], [-0.1782, -0.5489], [0, -0.5769], [0, 0]],
	            o: [[0, 0.577], [-0.17820000000000036, 0.5488999999999997], [-0.3392999999999997, 0.4670000000000005], [-0.46690000000000076, 0.3393999999999995], [-0.5488999999999997, 0.17820000000000036], [-0.5769000000000002, 0], [-0.5489000000000002, -0.17820000000000036], [-0.46689999999999987, -0.3392999999999997], [-0.33929999999999993, -0.46690000000000076], [-0.1782, -0.5488999999999997], [0, -0.5769000000000002], [0.17820000000000003, -0.5489000000000002], [0.3393999999999999, -0.46689999999999987], [0.4670000000000001, -0.33929999999999993], [0.5489000000000002, -0.1782], [0.577, 0], [0.5488999999999997, 0.17820000000000003], [0.4670000000000005, 0.3393999999999999], [0.3393999999999995, 0.4670000000000001], [0.17820000000000036, 0.5489000000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [6, 6]
	        },
	        s: {
	          a: 0,
	          k: [24, 24]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684940 - Null / Frame 1684937 - Null / Rectangle 3467758 - Null / Rectangle 3467758 / Rectangle 3467758",
	  fr: 60,
	  id: "ljwjxj7513hzkrcqsm9",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 179,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 180,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 179,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 181,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 180,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 182,
	    hd: false,
	    nm: "Frame 1684940 - Null",
	    parent: 181,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 28]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 183,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 182,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [16, 4]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 184,
	    hd: false,
	    nm: "Rectangle 3467758 - Null",
	    parent: 183,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 185,
	    hd: false,
	    nm: "Rectangle 3467758",
	    parent: 184,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5, 0], [39.5, 0], [41, 1.5], [41, 1.5], [39.5, 3], [1.5, 3], [0, 1.5], [0, 1.5], [1.5, 0], [1.5, 0]],
	            i: [[0, 0], [0, 0], [0, -0.8284], [0, 0], [0.8284, 0], [0, 0], [0, 0.8284], [0, 0], [-0.8284, 0], [0, 0]],
	            o: [[0, 0], [0.8284299999999973, 0], [0, 0], [0, 0.82843], [0, 0], [-0.82843, 0], [0, 0], [0, -0.82843], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 186,
	    hd: false,
	    nm: "Rectangle 3467758",
	    parent: 184,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5, 0], [39.5, 0], [41, 1.5], [41, 1.5], [39.5, 3], [1.5, 3], [0, 1.5], [0, 1.5], [1.5, 0], [1.5, 0]],
	            i: [[0, 0], [0, 0], [0, -0.8284], [0, 0], [0.8284, 0], [0, 0], [0, 0.8284], [0, 0], [-0.8284, 0], [0, 0]],
	            o: [[0, 0], [0.8284299999999973, 0], [0, 0], [0, 0.82843], [0, 0], [-0.82843, 0], [0, 0], [0, -0.82843], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [21, 2]
	        },
	        s: {
	          a: 0,
	          k: [84, 8]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684940 - Null / Frame 1684937 / Ellipse 256 - Null / Ellipse 256 / Ellipse 256",
	  fr: 60,
	  id: "ljwjxj74h1h5lnmreo",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 187,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 188,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 187,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 189,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 188,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 190,
	    hd: false,
	    nm: "Frame 1684940 - Null",
	    parent: 189,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 28]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 191,
	    ty: 0,
	    nm: "Frame 1684937",
	    refId: "ljwjxj7513hzkrcqsm9",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 192,
	    hd: false,
	    nm: "Ellipse 256 - Null",
	    parent: 190,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 193,
	    hd: false,
	    nm: "Ellipse 256",
	    parent: 192,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 5.5], [10.7311, 7.1995], [9.9495, 8.7329], [8.7329, 9.9495], [7.1995, 10.7311], [5.5, 11], [3.8005, 10.7311], [2.2671, 9.9495], [1.0505, 8.7329], [0.269, 7.1995], [0, 5.5], [0.269, 3.8005], [1.0505, 2.2671], [2.2671, 1.0505], [3.8005, 0.269], [5.5, 0], [7.1995, 0.269], [8.7329, 1.0505], [9.9495, 2.2671], [10.7311, 3.8005], [11, 5.5], [11, 5.5]],
	            i: [[0, 0], [0.1782, -0.5489], [0.3394, -0.4669], [0.467, -0.3393], [0.5489, -0.1782], [0.577, 0], [0.5489, 0.1782], [0.467, 0.3394], [0.3394, 0.467], [0.1782, 0.5489], [0, 0.577], [-0.1782, 0.5489], [-0.3393, 0.467], [-0.4669, 0.3394], [-0.5489, 0.1782], [-0.5769, 0], [-0.5489, -0.1782], [-0.4669, -0.3393], [-0.3393, -0.4669], [-0.1782, -0.5489], [0, -0.5769], [0, 0]],
	            o: [[0, 0.577], [-0.17820000000000036, 0.5488999999999997], [-0.3392999999999997, 0.4670000000000005], [-0.46690000000000076, 0.3393999999999995], [-0.5488999999999997, 0.17820000000000036], [-0.5769000000000002, 0], [-0.5489000000000002, -0.17820000000000036], [-0.46689999999999987, -0.3392999999999997], [-0.33929999999999993, -0.46690000000000076], [-0.1782, -0.5488999999999997], [0, -0.5769000000000002], [0.17820000000000003, -0.5489000000000002], [0.3393999999999999, -0.46689999999999987], [0.4670000000000001, -0.33929999999999993], [0.5489000000000002, -0.1782], [0.577, 0], [0.5488999999999997, 0.17820000000000003], [0.4670000000000005, 0.3393999999999999], [0.3393999999999995, 0.4670000000000001], [0.17820000000000036, 0.5489000000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 10
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 194,
	    hd: false,
	    nm: "Ellipse 256",
	    parent: 192,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 5.5], [10.7311, 7.1995], [9.9495, 8.7329], [8.7329, 9.9495], [7.1995, 10.7311], [5.5, 11], [3.8005, 10.7311], [2.2671, 9.9495], [1.0505, 8.7329], [0.269, 7.1995], [0, 5.5], [0.269, 3.8005], [1.0505, 2.2671], [2.2671, 1.0505], [3.8005, 0.269], [5.5, 0], [7.1995, 0.269], [8.7329, 1.0505], [9.9495, 2.2671], [10.7311, 3.8005], [11, 5.5], [11, 5.5]],
	            i: [[0, 0], [0.1782, -0.5489], [0.3394, -0.4669], [0.467, -0.3393], [0.5489, -0.1782], [0.577, 0], [0.5489, 0.1782], [0.467, 0.3394], [0.3394, 0.467], [0.1782, 0.5489], [0, 0.577], [-0.1782, 0.5489], [-0.3393, 0.467], [-0.4669, 0.3394], [-0.5489, 0.1782], [-0.5769, 0], [-0.5489, -0.1782], [-0.4669, -0.3393], [-0.3393, -0.4669], [-0.1782, -0.5489], [0, -0.5769], [0, 0]],
	            o: [[0, 0.577], [-0.17820000000000036, 0.5488999999999997], [-0.3392999999999997, 0.4670000000000005], [-0.46690000000000076, 0.3393999999999995], [-0.5488999999999997, 0.17820000000000036], [-0.5769000000000002, 0], [-0.5489000000000002, -0.17820000000000036], [-0.46689999999999987, -0.3392999999999997], [-0.33929999999999993, -0.46690000000000076], [-0.1782, -0.5488999999999997], [0, -0.5769000000000002], [0.17820000000000003, -0.5489000000000002], [0.3393999999999999, -0.46689999999999987], [0.4670000000000001, -0.33929999999999993], [0.5489000000000002, -0.1782], [0.577, 0], [0.5488999999999997, 0.17820000000000003], [0.4670000000000005, 0.3393999999999999], [0.3393999999999995, 0.4670000000000001], [0.17820000000000036, 0.5489000000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 10
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [6, 6]
	        },
	        s: {
	          a: 0,
	          k: [24, 24]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684939 - Null / Frame 1684937 - Null / Rectangle 3467758 - Null / Rectangle 3467758 / Rectangle 3467758",
	  fr: 60,
	  id: "ljwjxj78wxf1mfb0soa",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 195,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 196,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 195,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 197,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 196,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 198,
	    hd: false,
	    nm: "Frame 1684939 - Null",
	    parent: 197,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 199,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 198,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [16, 4]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 200,
	    hd: false,
	    nm: "Rectangle 3467758 - Null",
	    parent: 199,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 201,
	    hd: false,
	    nm: "Rectangle 3467758",
	    parent: 200,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5, 0], [20.5, 0], [22, 1.5], [22, 1.5], [20.5, 3], [1.5, 3], [0, 1.5], [0, 1.5], [1.5, 0], [1.5, 0]],
	            i: [[0, 0], [0, 0], [0, -0.8284], [0, 0], [0.8284, 0], [0, 0], [0, 0.8284], [0, 0], [-0.8284, 0], [0, 0]],
	            o: [[0, 0], [0.8284300000000009, 0], [0, 0], [0, 0.82843], [0, 0], [-0.82843, 0], [0, 0], [0, -0.82843], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 202,
	    hd: false,
	    nm: "Rectangle 3467758",
	    parent: 200,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5, 0], [20.5, 0], [22, 1.5], [22, 1.5], [20.5, 3], [1.5, 3], [0, 1.5], [0, 1.5], [1.5, 0], [1.5, 0]],
	            i: [[0, 0], [0, 0], [0, -0.8284], [0, 0], [0.8284, 0], [0, 0], [0, 0.8284], [0, 0], [-0.8284, 0], [0, 0]],
	            o: [[0, 0], [0.8284300000000009, 0], [0, 0], [0, 0.82843], [0, 0], [-0.82843, 0], [0, 0], [0, -0.82843], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [11.5, 2]
	        },
	        s: {
	          a: 0,
	          k: [46, 8]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684939 - Null / Frame 1684937 / Ellipse 256 - Null / Ellipse 256 / Ellipse 256",
	  fr: 60,
	  id: "ljwjxj7704sldcylg1er",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 203,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 204,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 203,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 205,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 204,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 206,
	    hd: false,
	    nm: "Frame 1684939 - Null",
	    parent: 205,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 207,
	    ty: 0,
	    nm: "Frame 1684937",
	    refId: "ljwjxj78wxf1mfb0soa",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 208,
	    hd: false,
	    nm: "Ellipse 256 - Null",
	    parent: 206,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 80.0000011920929
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 209,
	    hd: false,
	    nm: "Ellipse 256",
	    parent: 208,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 80.0000011920929
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 5.5], [10.7311, 7.1995], [9.9495, 8.7329], [8.7329, 9.9495], [7.1995, 10.7311], [5.5, 11], [3.8005, 10.7311], [2.2671, 9.9495], [1.0505, 8.7329], [0.269, 7.1995], [0, 5.5], [0.269, 3.8005], [1.0505, 2.2671], [2.2671, 1.0505], [3.8005, 0.269], [5.5, 0], [7.1995, 0.269], [8.7329, 1.0505], [9.9495, 2.2671], [10.7311, 3.8005], [11, 5.5], [11, 5.5]],
	            i: [[0, 0], [0.1782, -0.5489], [0.3394, -0.4669], [0.467, -0.3393], [0.5489, -0.1782], [0.577, 0], [0.5489, 0.1782], [0.467, 0.3394], [0.3394, 0.467], [0.1782, 0.5489], [0, 0.577], [-0.1782, 0.5489], [-0.3393, 0.467], [-0.4669, 0.3394], [-0.5489, 0.1782], [-0.5769, 0], [-0.5489, -0.1782], [-0.4669, -0.3393], [-0.3393, -0.4669], [-0.1782, -0.5489], [0, -0.5769], [0, 0]],
	            o: [[0, 0.577], [-0.17820000000000036, 0.5488999999999997], [-0.3392999999999997, 0.4670000000000005], [-0.46690000000000076, 0.3393999999999995], [-0.5488999999999997, 0.17820000000000036], [-0.5769000000000002, 0], [-0.5489000000000002, -0.17820000000000036], [-0.46689999999999987, -0.3392999999999997], [-0.33929999999999993, -0.46690000000000076], [-0.1782, -0.5488999999999997], [0, -0.5769000000000002], [0.17820000000000003, -0.5489000000000002], [0.3393999999999999, -0.46689999999999987], [0.4670000000000001, -0.33929999999999993], [0.5489000000000002, -0.1782], [0.577, 0], [0.5488999999999997, 0.17820000000000003], [0.4670000000000005, 0.3393999999999999], [0.3393999999999995, 0.4670000000000001], [0.17820000000000036, 0.5489000000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 210,
	    hd: false,
	    nm: "Ellipse 256",
	    parent: 208,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 80.0000011920929
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 5.5], [10.7311, 7.1995], [9.9495, 8.7329], [8.7329, 9.9495], [7.1995, 10.7311], [5.5, 11], [3.8005, 10.7311], [2.2671, 9.9495], [1.0505, 8.7329], [0.269, 7.1995], [0, 5.5], [0.269, 3.8005], [1.0505, 2.2671], [2.2671, 1.0505], [3.8005, 0.269], [5.5, 0], [7.1995, 0.269], [8.7329, 1.0505], [9.9495, 2.2671], [10.7311, 3.8005], [11, 5.5], [11, 5.5]],
	            i: [[0, 0], [0.1782, -0.5489], [0.3394, -0.4669], [0.467, -0.3393], [0.5489, -0.1782], [0.577, 0], [0.5489, 0.1782], [0.467, 0.3394], [0.3394, 0.467], [0.1782, 0.5489], [0, 0.577], [-0.1782, 0.5489], [-0.3393, 0.467], [-0.4669, 0.3394], [-0.5489, 0.1782], [-0.5769, 0], [-0.5489, -0.1782], [-0.4669, -0.3393], [-0.3393, -0.4669], [-0.1782, -0.5489], [0, -0.5769], [0, 0]],
	            o: [[0, 0.577], [-0.17820000000000036, 0.5488999999999997], [-0.3392999999999997, 0.4670000000000005], [-0.46690000000000076, 0.3393999999999995], [-0.5488999999999997, 0.17820000000000036], [-0.5769000000000002, 0], [-0.5489000000000002, -0.17820000000000036], [-0.46689999999999987, -0.3392999999999997], [-0.33929999999999993, -0.46690000000000076], [-0.1782, -0.5488999999999997], [0, -0.5769000000000002], [0.17820000000000003, -0.5489000000000002], [0.3393999999999999, -0.46689999999999987], [0.4670000000000001, -0.33929999999999993], [0.5489000000000002, -0.1782], [0.577, 0], [0.5488999999999997, 0.17820000000000003], [0.4670000000000005, 0.3393999999999999], [0.3393999999999995, 0.4670000000000001], [0.17820000000000036, 0.5489000000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [6, 6]
	        },
	        s: {
	          a: 0,
	          k: [24, 24]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684938 - Null / Frame 1684937 - Null / Rectangle 3467758 - Null / Rectangle 3467758 / Rectangle 3467758",
	  fr: 60,
	  id: "ljwjxj7bvp481hc3ogh",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 211,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 212,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 211,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 213,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 212,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 214,
	    hd: false,
	    nm: "Frame 1684938 - Null",
	    parent: 213,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 215,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 214,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [16, 4]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 216,
	    hd: false,
	    nm: "Rectangle 3467758 - Null",
	    parent: 215,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 217,
	    hd: false,
	    nm: "Rectangle 3467758",
	    parent: 216,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5, 0], [31.5, 0], [33, 1.5], [33, 1.5], [31.5, 3], [1.5, 3], [0, 1.5], [0, 1.5], [1.5, 0], [1.5, 0]],
	            i: [[0, 0], [0, 0], [0, -0.8284], [0, 0], [0.8284, 0], [0, 0], [0, 0.8284], [0, 0], [-0.8284, 0], [0, 0]],
	            o: [[0, 0], [0.8284299999999973, 0], [0, 0], [0, 0.82843], [0, 0], [-0.82843, 0], [0, 0], [0, -0.82843], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 218,
	    hd: false,
	    nm: "Rectangle 3467758",
	    parent: 216,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 20.000000298023224
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1.5, 0], [31.5, 0], [33, 1.5], [33, 1.5], [31.5, 3], [1.5, 3], [0, 1.5], [0, 1.5], [1.5, 0], [1.5, 0]],
	            i: [[0, 0], [0, 0], [0, -0.8284], [0, 0], [0.8284, 0], [0, 0], [0, 0.8284], [0, 0], [-0.8284, 0], [0, 0]],
	            o: [[0, 0], [0.8284299999999973, 0], [0, 0], [0, 0.82843], [0, 0], [-0.82843, 0], [0, 0], [0, -0.82843], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 30
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [17, 2]
	        },
	        s: {
	          a: 0,
	          k: [68, 8]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684938 - Null / Frame 1684937 / Ellipse 256 - Null / Ellipse 256 / Ellipse 256",
	  fr: 60,
	  id: "ljwjxj7aen5ny1ibdkh",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 219,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 220,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 219,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 221,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 220,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 222,
	    hd: false,
	    nm: "Frame 1684938 - Null",
	    parent: 221,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 223,
	    ty: 0,
	    nm: "Frame 1684937",
	    refId: "ljwjxj7bvp481hc3ogh",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 224,
	    hd: false,
	    nm: "Ellipse 256 - Null",
	    parent: 222,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 225,
	    hd: false,
	    nm: "Ellipse 256",
	    parent: 224,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 5.5], [10.7311, 7.1995], [9.9495, 8.7329], [8.7329, 9.9495], [7.1995, 10.7311], [5.5, 11], [3.8005, 10.7311], [2.2671, 9.9495], [1.0505, 8.7329], [0.269, 7.1995], [0, 5.5], [0.269, 3.8005], [1.0505, 2.2671], [2.2671, 1.0505], [3.8005, 0.269], [5.5, 0], [7.1995, 0.269], [8.7329, 1.0505], [9.9495, 2.2671], [10.7311, 3.8005], [11, 5.5], [11, 5.5]],
	            i: [[0, 0], [0.1782, -0.5489], [0.3394, -0.4669], [0.467, -0.3393], [0.5489, -0.1782], [0.577, 0], [0.5489, 0.1782], [0.467, 0.3394], [0.3394, 0.467], [0.1782, 0.5489], [0, 0.577], [-0.1782, 0.5489], [-0.3393, 0.467], [-0.4669, 0.3394], [-0.5489, 0.1782], [-0.5769, 0], [-0.5489, -0.1782], [-0.4669, -0.3393], [-0.3393, -0.4669], [-0.1782, -0.5489], [0, -0.5769], [0, 0]],
	            o: [[0, 0.577], [-0.17820000000000036, 0.5488999999999997], [-0.3392999999999997, 0.4670000000000005], [-0.46690000000000076, 0.3393999999999995], [-0.5488999999999997, 0.17820000000000036], [-0.5769000000000002, 0], [-0.5489000000000002, -0.17820000000000036], [-0.46689999999999987, -0.3392999999999997], [-0.33929999999999993, -0.46690000000000076], [-0.1782, -0.5488999999999997], [0, -0.5769000000000002], [0.17820000000000003, -0.5489000000000002], [0.3393999999999999, -0.46689999999999987], [0.4670000000000001, -0.33929999999999993], [0.5489000000000002, -0.1782], [0.577, 0], [0.5488999999999997, 0.17820000000000003], [0.4670000000000005, 0.3393999999999999], [0.3393999999999995, 0.4670000000000001], [0.17820000000000036, 0.5489000000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 10
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 226,
	    hd: false,
	    nm: "Ellipse 256",
	    parent: 224,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[11, 5.5], [10.7311, 7.1995], [9.9495, 8.7329], [8.7329, 9.9495], [7.1995, 10.7311], [5.5, 11], [3.8005, 10.7311], [2.2671, 9.9495], [1.0505, 8.7329], [0.269, 7.1995], [0, 5.5], [0.269, 3.8005], [1.0505, 2.2671], [2.2671, 1.0505], [3.8005, 0.269], [5.5, 0], [7.1995, 0.269], [8.7329, 1.0505], [9.9495, 2.2671], [10.7311, 3.8005], [11, 5.5], [11, 5.5]],
	            i: [[0, 0], [0.1782, -0.5489], [0.3394, -0.4669], [0.467, -0.3393], [0.5489, -0.1782], [0.577, 0], [0.5489, 0.1782], [0.467, 0.3394], [0.3394, 0.467], [0.1782, 0.5489], [0, 0.577], [-0.1782, 0.5489], [-0.3393, 0.467], [-0.4669, 0.3394], [-0.5489, 0.1782], [-0.5769, 0], [-0.5489, -0.1782], [-0.4669, -0.3393], [-0.3393, -0.4669], [-0.1782, -0.5489], [0, -0.5769], [0, 0]],
	            o: [[0, 0.577], [-0.17820000000000036, 0.5488999999999997], [-0.3392999999999997, 0.4670000000000005], [-0.46690000000000076, 0.3393999999999995], [-0.5488999999999997, 0.17820000000000036], [-0.5769000000000002, 0], [-0.5489000000000002, -0.17820000000000036], [-0.46689999999999987, -0.3392999999999997], [-0.33929999999999993, -0.46690000000000076], [-0.1782, -0.5488999999999997], [0, -0.5769000000000002], [0.17820000000000003, -0.5489000000000002], [0.3393999999999999, -0.46689999999999987], [0.4670000000000001, -0.33929999999999993], [0.5489000000000002, -0.1782], [0.577, 0], [0.5488999999999997, 0.17820000000000003], [0.4670000000000005, 0.3393999999999999], [0.3393999999999995, 0.4670000000000001], [0.17820000000000036, 0.5489000000000002], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 10
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [6, 6]
	        },
	        s: {
	          a: 0,
	          k: [24, 24]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 5 - Null / Frame 1684937 - Null / Frame 1684941 / Frame 1684940 / Frame 1684939 / Frame 1684938",
	  fr: 60,
	  id: "ljwjxj7155m8q0q86ig",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 227,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 228,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 227,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 229,
	    hd: false,
	    nm: "Frame 1684937 - Null",
	    parent: 228,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [13.5, 14]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 230,
	    ty: 0,
	    nm: "Frame 1684941",
	    refId: "ljwjxj71utvgbljp81",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ddd: 0,
	    ind: 231,
	    ty: 0,
	    nm: "Frame 1684940",
	    refId: "ljwjxj74h1h5lnmreo",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ddd: 0,
	    ind: 232,
	    ty: 0,
	    nm: "Frame 1684939",
	    refId: "ljwjxj7704sldcylg1er",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ddd: 0,
	    ind: 233,
	    ty: 0,
	    nm: "Frame 1684938",
	    refId: "ljwjxj7aen5ny1ibdkh",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }]
	}, {
	  nm: "[GROUP] Frame 1684937 / Rectangle 3467764 - Null / Rectangle 3467764 / Rectangle 3467764 / Rectangle 3467763 - Null / Rectangle 3467763 / Rectangle 3467763 / Rectangle 3467762 - Null / Rectangle 3467762 / Rectangle 3467762 / Rectangle 3467761 - Null / Rectangle 3467761 / Rectangle 3467761",
	  fr: 60,
	  id: "ljwjxj70gqxtpz5o59u",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 234,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 235,
	    hd: false,
	    nm: "A 5 - Null",
	    parent: 234,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      },
	      p: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [128.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 180.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 210.00000715255737,
	          s: [224.5, 0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          },
	          ti: [0, 0],
	          to: [0, 0]
	        }, {
	          t: 228.00000715255737,
	          s: [128.5, 0]
	        }]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 236,
	    ty: 0,
	    nm: "Frame 1684937",
	    refId: "ljwjxj7155m8q0q86ig",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 237,
	    hd: false,
	    nm: "Rectangle 3467764 - Null",
	    parent: 235,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      },
	      p: {
	        a: 0,
	        k: [93.5, 105]
	      },
	      r: {
	        a: 0,
	        k: -180
	      },
	      s: {
	        a: 0,
	        k: [100, -100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 238,
	    hd: false,
	    nm: "Rectangle 3467764",
	    parent: 237,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1, 0], [46, 0], [50, 4], [50, 11], [46, 15], [4, 15], [0, 11], [0, 1], [1, 0], [1, 0]],
	            i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-0.5523, 0], [0, 0]],
	            o: [[0, 0], [2.209139999999998, 0], [0, 0], [0, 2.2091399999999997], [0, 0], [-2.20914, 0], [0, 0], [0, -0.55228], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 239,
	    hd: false,
	    nm: "Rectangle 3467764",
	    parent: 237,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 44.999998807907104
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1, 0], [46, 0], [50, 4], [50, 11], [46, 15], [4, 15], [0, 11], [0, 1], [1, 0], [1, 0]],
	            i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-0.5523, 0], [0, 0]],
	            o: [[0, 0], [2.209139999999998, 0], [0, 0], [0, 2.2091399999999997], [0, 0], [-2.20914, 0], [0, 0], [0, -0.55228], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 20
	        },
	        c: {
	          a: 0,
	          k: [0.3215686274509804, 0.3607843137254902, 0.4117647058823529, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [25.5, 8]
	        },
	        s: {
	          a: 0,
	          k: [102, 32]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 240,
	    hd: false,
	    nm: "Rectangle 3467763 - Null",
	    parent: 235,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 89.99999761581421
	      },
	      p: {
	        a: 0,
	        k: [13.5, 124]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 241,
	    hd: false,
	    nm: "Rectangle 3467763",
	    parent: 240,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 89.99999761581421
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1, 0], [48, 0], [52, 4], [52, 11], [48, 15], [4, 15], [0, 11], [0, 1], [1, 0], [1, 0]],
	            i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-0.5523, 0], [0, 0]],
	            o: [[0, 0], [2.209139999999998, 0], [0, 0], [0, 2.2091399999999997], [0, 0], [-2.20914, 0], [0, 0], [0, -0.55228], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 94
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 242,
	    hd: false,
	    nm: "Rectangle 3467763",
	    parent: 240,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 89.99999761581421
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1, 0], [48, 0], [52, 4], [52, 11], [48, 15], [4, 15], [0, 11], [0, 1], [1, 0], [1, 0]],
	            i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-0.5523, 0], [0, 0]],
	            o: [[0, 0], [2.209139999999998, 0], [0, 0], [0, 2.2091399999999997], [0, 0], [-2.20914, 0], [0, 0], [0, -0.55228], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 94
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [26.5, 8]
	        },
	        s: {
	          a: 0,
	          k: [106, 32]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 243,
	    hd: false,
	    nm: "Rectangle 3467762 - Null",
	    parent: 235,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 89.99999761581421
	      },
	      p: {
	        a: 0,
	        k: [13.5, 81]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 244,
	    hd: false,
	    nm: "Rectangle 3467762",
	    parent: 243,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 89.99999761581421
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1, 0], [69, 0], [73, 4], [73, 16], [69, 20], [4, 20], [0, 16], [0, 1], [1, 0], [1, 0]],
	            i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-0.5523, 0], [0, 0]],
	            o: [[0, 0], [2.209140000000005, 0], [0, 0], [0, 2.2091400000000014], [0, 0], [-2.20914, 0], [0, 0], [0, -0.55228], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 94
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 245,
	    hd: false,
	    nm: "Rectangle 3467762",
	    parent: 243,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 89.99999761581421
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[1, 0], [69, 0], [73, 4], [73, 16], [69, 20], [4, 20], [0, 16], [0, 1], [1, 0], [1, 0]],
	            i: [[0, 0], [0, 0], [0, -2.2091], [0, 0], [2.2091, 0], [0, 0], [0, 2.2091], [0, 0], [-0.5523, 0], [0, 0]],
	            o: [[0, 0], [2.209140000000005, 0], [0, 0], [0, 2.2091400000000014], [0, 0], [-2.20914, 0], [0, 0], [0, -0.55228], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 94
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [37, 10.5]
	        },
	        s: {
	          a: 0,
	          k: [148, 42]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 246,
	    hd: false,
	    nm: "Rectangle 3467761 - Null",
	    parent: 235,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 247,
	    hd: false,
	    nm: "Rectangle 3467761",
	    parent: 246,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [102, 0], [102, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.9333333333333333, 0.9490196078431372, 0.9568627450980393, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 248,
	    hd: false,
	    nm: "Rectangle 3467761",
	    parent: 246,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[0, 0], [102, 0], [102, 149], [0, 149], [0, 0]],
	            i: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]],
	            o: [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [0.9333333333333333, 0.9490196078431372, 0.9568627450980393, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [51.5, 75]
	        },
	        s: {
	          a: 0,
	          k: [206, 300]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: [{
	      nm: "DropShadow",
	      ty: 25,
	      en: 1,
	      ef: [{
	        ty: 2,
	        v: {
	          a: 0,
	          k: [0, 0, 0, 1]
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 0,
	          k: 5
	        }
	      }, {
	        ty: 1,
	        v: {
	          a: 0,
	          k: 1.5707963267948966
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 0,
	          k: -2
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 0,
	          k: 4
	        }
	      }]
	    }]
	  }]
	}, {
	  nm: "[FRAME] Frame 1684947 - Null / A 2 / A 4 / A 3 / A 1 / Rectangle 3467765 - Null / Rectangle 3467765 / Rectangle 3467765 / A 5",
	  fr: 60,
	  id: "ljwjxj6cer3cc1ml337",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 249,
	    hd: false,
	    nm: "Frame 1684947 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [54.5, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 250,
	    ty: 0,
	    nm: "A 2",
	    refId: "ljwjxj6chl2m21i8oo8",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 54.00000214576721,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 72.00000286102295,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 251,
	    ty: 0,
	    nm: "A 4",
	    refId: "ljwjxj6jrcsugfpibo",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 126.00000500679016,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 144.0000057220459,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 252,
	    ty: 0,
	    nm: "A 3",
	    refId: "ljwjxj6pu0pb3tkdx2a",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 90.00000357627869,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 108.00000429153442,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ddd: 0,
	    ind: 253,
	    ty: 0,
	    nm: "A 1",
	    refId: "ljwjxj6uv4vvu04bo6c",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 18.000000715255737,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 36.000001430511475,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }, {
	    ty: 3,
	    ddd: 0,
	    ind: 254,
	    hd: false,
	    nm: "Rectangle 3467765 - Null",
	    parent: 249,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 255,
	    hd: false,
	    nm: "Rectangle 3467765",
	    parent: 254,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[10, 0], [214, 0], [222, 8], [222, 141], [214, 149], [10, 149], [0, 139], [0, 10], [10, 0], [10, 0]],
	            i: [[0, 0], [0, 0], [0, -4.4183], [0, 0], [4.4183, 0], [0, 0], [0, 5.5229], [0, 0], [-5.5228, 0], [0, 0]],
	            o: [[0, 0], [4.41828000000001, 0], [0, 0], [0, 4.41828000000001], [0, 0], [-5.52285, 0], [0, 0], [0, -5.52285], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: []
	  }, {
	    ty: 4,
	    ddd: 0,
	    ind: 256,
	    hd: false,
	    nm: "Rectangle 3467765",
	    parent: 254,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1,
	    shapes: [{
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "sh",
	        nm: "Path",
	        hd: false,
	        ks: {
	          a: 0,
	          k: {
	            c: true,
	            v: [[10, 0], [214, 0], [222, 8], [222, 141], [214, 149], [10, 149], [0, 139], [0, 10], [10, 0], [10, 0]],
	            i: [[0, 0], [0, 0], [0, -4.4183], [0, 0], [4.4183, 0], [0, 0], [0, 5.5229], [0, 0], [-5.5228, 0], [0, 0]],
	            o: [[0, 0], [4.41828000000001, 0], [0, 0], [0, 4.41828000000001], [0, 0], [-5.52285, 0], [0, 0], [0, -5.52285], [0, 0], [0, 0]]
	          }
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 100
	        },
	        c: {
	          a: 0,
	          k: [1, 1, 1, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }, {
	      ty: "gr",
	      nm: "Group",
	      hd: false,
	      np: 3,
	      it: [{
	        ty: "rc",
	        nm: "Rectangle",
	        hd: false,
	        p: {
	          a: 0,
	          k: [111.5, 75]
	        },
	        s: {
	          a: 0,
	          k: [446, 300]
	        },
	        r: {
	          a: 0,
	          k: 0
	        }
	      }, {
	        ty: "fl",
	        o: {
	          a: 0,
	          k: 0
	        },
	        c: {
	          a: 0,
	          k: [0, 1, 0, 1]
	        },
	        nm: "Fill",
	        hd: false,
	        r: 1
	      }, {
	        ty: "tr",
	        a: {
	          a: 0,
	          k: [0, 0]
	        },
	        p: {
	          a: 0,
	          k: [0, 0]
	        },
	        s: {
	          a: 0,
	          k: [100, 100]
	        },
	        sk: {
	          a: 0,
	          k: 0
	        },
	        sa: {
	          a: 0,
	          k: 0
	        },
	        r: {
	          a: 0,
	          k: 0
	        },
	        o: {
	          a: 0,
	          k: 100
	        }
	      }]
	    }],
	    ef: [{
	      nm: "DropShadow",
	      ty: 25,
	      en: 1,
	      ef: [{
	        ty: 2,
	        v: {
	          a: 0,
	          k: [0, 0, 0, 1]
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 0,
	          k: 5
	        }
	      }, {
	        ty: 1,
	        v: {
	          a: 0,
	          k: 1.5707963267948966
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 0,
	          k: -2
	        }
	      }, {
	        ty: 0,
	        v: {
	          a: 0,
	          k: 4
	        }
	      }]
	    }]
	  }, {
	    ddd: 0,
	    ind: 257,
	    ty: 0,
	    nm: "A 5",
	    refId: "ljwjxj70gqxtpz5o59u",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 1,
	        k: [{
	          t: 162.00000643730164,
	          s: [0],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [0.58],
	            y: [1]
	          }
	        }, {
	          t: 180.00000715255737,
	          s: [100],
	          o: {
	            x: [0.5],
	            y: [0.35]
	          },
	          i: {
	            x: [0.15],
	            y: [1]
	          }
	        }, {
	          t: 210.00000715255737,
	          s: [100],
	          o: {
	            x: [0],
	            y: [0]
	          },
	          i: {
	            x: [1],
	            y: [1]
	          }
	        }, {
	          t: 228.00000715255737,
	          s: [0]
	        }]
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0
	  }]
	}, {
	  nm: "[FRAME] Anim 8 - Null / Frame 1684947",
	  fr: 60,
	  id: "ljwjxj6bl8jb19cpd3h",
	  layers: [{
	    ty: 3,
	    ddd: 0,
	    ind: 258,
	    hd: false,
	    nm: "Anim 8 - Null",
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      o: {
	        a: 0,
	        k: 100
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      }
	    },
	    st: 0,
	    ip: 0,
	    op: 228.00000715255737,
	    bm: 0,
	    sr: 1
	  }, {
	    ddd: 0,
	    ind: 259,
	    ty: 0,
	    nm: "Frame 1684947",
	    refId: "ljwjxj6cer3cc1ml337",
	    sr: 1,
	    ks: {
	      a: {
	        a: 0,
	        k: [0, 0]
	      },
	      p: {
	        a: 0,
	        k: [0, 0]
	      },
	      s: {
	        a: 0,
	        k: [100, 100]
	      },
	      sk: {
	        a: 0,
	        k: 0
	      },
	      sa: {
	        a: 0,
	        k: 0
	      },
	      r: {
	        a: 0,
	        k: 0
	      },
	      o: {
	        a: 0,
	        k: 100
	      }
	    },
	    ao: 0,
	    w: 428,
	    h: 149,
	    ip: 0,
	    op: 228.00000715255737,
	    st: 0,
	    hd: false,
	    bm: 0,
	    ef: []
	  }]
	}];
	var layers$1 = [{
	  ddd: 0,
	  ind: 1,
	  ty: 0,
	  nm: "Anim 8",
	  refId: "ljwjxj6bl8jb19cpd3h",
	  sr: 1,
	  ks: {
	    a: {
	      a: 0,
	      k: [0, 0]
	    },
	    p: {
	      a: 0,
	      k: [0, 0]
	    },
	    s: {
	      a: 0,
	      k: [100, 100]
	    },
	    sk: {
	      a: 0,
	      k: 0
	    },
	    sa: {
	      a: 0,
	      k: 0
	    },
	    r: {
	      a: 0,
	      k: 0
	    },
	    o: {
	      a: 0,
	      k: 100
	    }
	  },
	  ao: 0,
	  w: 428,
	  h: 149,
	  ip: 0,
	  op: 228.00000715255737,
	  st: 0,
	  hd: false,
	  bm: 0,
	  ef: []
	}];
	var meta$1 = {
	  a: "",
	  d: "",
	  tc: "",
	  g: "Aninix"
	};
	var ConferenceAnimation = {
	  nm: nm$1,
	  v: v$1,
	  fr: fr$1,
	  ip: ip$1,
	  op: op$1,
	  w: w$1,
	  h: h$1,
	  ddd: ddd$1,
	  markers: markers$1,
	  assets: assets$1,
	  layers: layers$1,
	  meta: meta$1
	};

	// @vue/component
	const ConferencePromo = {
	  components: {
	    PromoPopup,
	    MessengerButton: im_v2_component_elements.Button
	  },
	  emits: ['continue', 'close'],
	  data() {
	    return {};
	  },
	  computed: {
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    ButtonSize: () => im_v2_component_elements.ButtonSize
	  },
	  mounted() {
	    ui_lottie.Lottie.loadAnimation({
	      animationData: ConferenceAnimation,
	      container: this.$refs.animationContainer,
	      renderer: 'svg',
	      loop: true,
	      autoplay: true
	    });
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<PromoPopup @close="$emit('close')">
			<div class="bx-im-group-chat-promo__container">
				<div class="bx-im-group-chat-promo__header">
					<div class="bx-im-group-chat-promo__title">
						{{ loc('IM_RECENT_CREATE_CHAT_PROMO_CONFERENCE_TITLE') }}
					</div>
					<div class="bx-im-group-chat-promo__close" @click="$emit('close')"></div>
				</div>
				<div class="bx-im-group-chat-promo__content">
					<div class="bx-im-group-chat-promo__content_image" ref="animationContainer"></div>
					<div class="bx-im-group-chat-promo__content_item">
						<div class="bx-im-group-chat-promo__content_icon --camera"></div>
						<div class="bx-im-group-chat-promo__content_text">
							{{ loc('IM_RECENT_CREATE_CHAT_PROMO_CONFERENCE_DESCRIPTION_1') }}
						</div>
					</div>
					<div class="bx-im-group-chat-promo__content_item">
						<div class="bx-im-group-chat-promo__content_icon --link"></div>
						<div class="bx-im-group-chat-promo__content_text">
							{{ loc('IM_RECENT_CREATE_CHAT_PROMO_CONFERENCE_DESCRIPTION_2') }}
						</div>
					</div>
					<div class="bx-im-group-chat-promo__content_item">
						<div class="bx-im-group-chat-promo__content_icon --like"></div>
						<div class="bx-im-group-chat-promo__content_text">
							{{ loc('IM_RECENT_CREATE_CHAT_PROMO_CONFERENCE_DESCRIPTION_3') }}
						</div>
					</div>
				</div>
				<div class="bx-im-group-chat-promo__button-panel">
					<MessengerButton
						:size="ButtonSize.XL"
						:color="ButtonColor.Primary"
						:isRounded="true" 
						:text="loc('IM_RECENT_CREATE_CHAT_PROMO_GROUP_CHAT_CONTINUE')"
						@click="$emit('continue')"
					/>
					<MessengerButton
						:size="ButtonSize.XL"
						:color="ButtonColor.Link"
						:isRounded="true"
						:text="loc('IM_RECENT_CREATE_CHAT_PROMO_GROUP_CHAT_CANCEL')"
						@click="$emit('close')"
					/>
				</div>
			</div>
		</PromoPopup>
	`
	};

	// @vue/component
	const CreateChatPromo = {
	  name: 'CreateChatPromo',
	  components: {
	    GroupChatPromo,
	    ConferencePromo
	  },
	  props: {
	    chatType: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['continue', 'close'],
	  data() {
	    return {};
	  },
	  computed: {
	    DialogType: () => im_v2_const.DialogType
	  },
	  template: `
		<GroupChatPromo v-if="chatType === DialogType.chat" @close="$emit('close')" @continue="$emit('continue')" />
		<ConferencePromo v-else-if="chatType === DialogType.videoconf" @close="$emit('close')" @continue="$emit('continue')" />
	`
	};

	const PromoByChatType = {
	  [im_v2_const.DialogType.chat]: im_v2_const.PromoId.createGroupChat,
	  [im_v2_const.DialogType.videoconf]: im_v2_const.PromoId.createConference
	};

	// @vue/component
	const CreateChatMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements.MessengerMenu,
	    MenuItem: im_v2_component_elements.MenuItem,
	    CreateChatHelp,
	    CreateChatPromo,
	    GroupChatPromo
	  },
	  data() {
	    return {
	      showPopup: false,
	      chatTypeToCreate: '',
	      showPromo: false
	    };
	  },
	  computed: {
	    DialogType: () => im_v2_const.DialogType,
	    MenuItemIcon: () => im_v2_component_elements.MenuItemIcon,
	    menuConfig() {
	      return {
	        id: 'im-create-chat-menu',
	        width: 255,
	        bindElement: this.$refs.icon || {},
	        offsetTop: 4,
	        padding: 0
	      };
	    }
	  },
	  methods: {
	    onChatCreateClick(type) {
	      this.chatTypeToCreate = type;
	      const promoBannerIsNeeded = im_v2_lib_promo.PromoManager.getInstance().needToShow(this.getPromoType());
	      if (promoBannerIsNeeded) {
	        this.showPromo = true;
	        this.showPopup = false;
	        return;
	      }
	      this.startChatCreation();
	      this.showPopup = false;
	    },
	    onPromoContinueClick() {
	      im_v2_lib_promo.PromoManager.getInstance().markAsWatched(this.getPromoType());
	      this.startChatCreation();
	      this.showPromo = false;
	      this.showPopup = false;
	      this.chatTypeToCreate = '';
	    },
	    startChatCreation() {
	      const {
	        name: currentLayoutName,
	        entityId: currentLayoutChatType
	      } = this.$store.getters['application/getLayout'];
	      if (currentLayoutName === im_v2_const.Layout.createChat.name && currentLayoutChatType === this.chatTypeToCreate) {
	        return;
	      }
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      this.$store.dispatch('application/setLayout', {
	        layoutName: im_v2_const.Layout.createChat.name,
	        entityId: this.chatTypeToCreate
	      });
	    },
	    getPromoType() {
	      var _PromoByChatType$this;
	      return (_PromoByChatType$this = PromoByChatType[this.chatTypeToCreate]) != null ? _PromoByChatType$this : '';
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			class="bx-im-list-container-recent__create-chat_icon"
			:class="{'--active': showPopup}"
			@click="showPopup = true"
			ref="icon"
		></div>
		<MessengerMenu v-if="showPopup" :config="menuConfig" @close="showPopup = false">
			<MenuItem
				:icon="MenuItemIcon.chat"
				:title="loc('IM_RECENT_CREATE_GROUP_CHAT_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_GROUP_CHAT_SUBTITLE')"
				@click="onChatCreateClick(DialogType.chat)"
			/>
			<MenuItem
				:icon="MenuItemIcon.conference"
				:title="loc('IM_RECENT_CREATE_CONFERENCE_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_CONFERENCE_SUBTITLE')"
				@click="onChatCreateClick(DialogType.videoconf)"
			/>
			<MenuItem
				:icon="MenuItemIcon.channel"
				:title="loc('IM_RECENT_CREATE_CHANNEL_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_CHANNEL_SUBTITLE_V2')"
				:disabled="true"
			/>
			<template #footer>
				<CreateChatHelp @articleOpen="showPopup = false" />
			</template>
		</MessengerMenu>
		<CreateChatPromo
			v-if="showPromo"
			:chatType="chatTypeToCreate"
			@continue="onPromoContinueClick"
			@close="showPromo = false"
		/>
	`
	};

	// @vue/component
	const RecentListContainer = {
	  name: 'RecentListContainer',
	  components: {
	    HeaderMenu,
	    CreateChatMenu,
	    ChatSearchInput: im_v2_component_search_chatSearchInput.ChatSearchInput,
	    SearchResult: im_v2_component_search_searchResult.SearchResult,
	    RecentList: im_v2_component_list_elementList_recent.RecentList,
	    SearchExperimental: im_v2_component_search_searchExperimental.SearchExperimental
	  },
	  emits: ['selectEntity'],
	  data() {
	    return {
	      searchMode: false,
	      unreadOnlyMode: false,
	      searchQuery: ''
	    };
	  },
	  computed: {
	    UnreadRecentService: () => im_v2_provider_service.UnreadRecentService
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('List: Recent container created');
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.openSearch, this.onOpenSearch);
	    main_core.Event.bind(document, 'mousedown', this.onDocumentClick);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.openSearch, this.onOpenSearch);
	    main_core.Event.unbind(document, 'mousedown', this.onDocumentClick);
	  },
	  methods: {
	    onChatClick(dialogId) {
	      this.$emit('selectEntity', {
	        layoutName: im_v2_const.Layout.chat.name,
	        entityId: dialogId
	      });
	    },
	    onOpenSearch() {
	      this.searchMode = true;
	    },
	    onCloseSearch() {
	      this.searchMode = false;
	      this.searchQuery = '';
	    },
	    onUpdateSearch(query) {
	      this.searchMode = true;
	      this.searchQuery = query;
	    },
	    onDocumentClick(event) {
	      const clickOnRecentContainer = event.composedPath().includes(this.$refs['recent-container']);
	      if (!clickOnRecentContainer) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.search.close);
	      }
	    }
	  },
	  template: `
				<div class="bx-im-list-container-recent__scope bx-im-list-container-recent__container" ref="recent-container">
					<div class="bx-im-list-container-recent__header_container">
						<HeaderMenu @showUnread="unreadOnlyMode = true" />
						<div class="bx-im-list-container-recent__search-input_container">
							<ChatSearchInput 
								:searchMode="searchMode" 
								@openSearch="onOpenSearch"
								@closeSearch="onCloseSearch"
								@updateSearch="onUpdateSearch"
							/>
						</div>
						<CreateChatMenu />
					</div>
					<div class="bx-im-list-container-recent__elements_container">
						<div class="bx-im-list-container-recent__elements">
							<SearchExperimental 
								v-show="searchMode" 
								:searchMode="searchMode" 
								:searchQuery="searchQuery" 
								:searchConfig="{}"
							/>
							<RecentList v-show="!searchMode && !unreadOnlyMode" @chatClick="onChatClick" key="recent" />
		<!--					<RecentList-->
		<!--						v-if="!searchMode && unreadOnlyMode"-->
		<!--						:recentService="UnreadRecentService.getInstance()"-->
		<!--						@chatClick="onChatClick"-->
		<!--						key="unread"-->
		<!--					/>-->
						</div>
					</div>
				</div>
	`
	};

	exports.RecentListContainer = RecentListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Event,BX,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component,BX.Messenger.v2.Component,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.UI,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=recent-container.bundle.js.map
