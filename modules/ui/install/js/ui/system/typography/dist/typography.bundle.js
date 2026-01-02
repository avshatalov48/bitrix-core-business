/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.System = this.BX.UI.System || {};
(function (exports,main_core) {
	'use strict';

	const createTypographyElement = (tag, text, baseClass, size, options = {}) => {
	  const element = main_core.Dom.create(tag, {
	    text
	  });
	  applyClasses(element, baseClass, size, options);
	  return element;
	};
	const applyClasses = (element, baseClass, size, options = {}) => {
	  main_core.Dom.addClass(element, [baseClass, `--${size}`]);

	  // Модификаторы
	  if (options.accent) {
	    main_core.Dom.addClass(element, '--accent');
	  }
	  if (options.align) {
	    main_core.Dom.addClass(element, `--align-${options.align}`);
	  }
	  if (options.transform) {
	    main_core.Dom.addClass(element, `--${options.transform}`);
	  }
	  if (options.wrap) {
	    main_core.Dom.addClass(element, `--${options.wrap}`);
	  }
	  if (options.className) {
	    addCustomClasses(element, options.className);
	  }
	};
	const addCustomClasses = (element, className) => {
	  if (main_core.Type.isString(className)) {
	    main_core.Dom.addClass(element, className);
	  } else if (main_core.Type.isArray(className)) {
	    main_core.Dom.addClass(element, className);
	  } else if (main_core.Type.isPlainObject(className)) {
	    Object.entries(className).forEach(([key, value]) => {
	      if (value) {
	        main_core.Dom.addClass(element, key);
	      }
	    });
	  }
	};

	var _getTag = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTag");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	class Headline {
	  static render(text, options = {}) {
	    return babelHelpers.classPrivateFieldLooseBase(Headline, _render)[_render](text, options);
	  }
	}
	function _getTag2(size, customTag) {
	  return customTag || 'div';
	}
	function _render2(text, options = {}) {
	  const tag = babelHelpers.classPrivateFieldLooseBase(Headline, _getTag)[_getTag](options.size, options.tag);
	  return createTypographyElement(tag, text, 'ui-headline', options.size, options);
	}
	Object.defineProperty(Headline, _render, {
	  value: _render2
	});
	Object.defineProperty(Headline, _getTag, {
	  value: _getTag2
	});

	var _render$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	class Text {
	  static render(text, options = {}) {
	    return babelHelpers.classPrivateFieldLooseBase(Text, _render$1)[_render$1](text, options);
	  }
	}
	function _render2$1(text, options = {}) {
	  var _options$size;
	  const tag = options.tag || 'span';
	  const size = (_options$size = options.size) != null ? _options$size : 'md';
	  return createTypographyElement(tag, text, 'ui-text', size, options);
	}
	Object.defineProperty(Text, _render$1, {
	  value: _render2$1
	});

	// @vue/component
	const Text$1 = {
	  name: 'BText',
	  inheritAttrs: false,
	  props: {
	    size: {
	      type: String,
	      required: true,
	      validator: value => ['2xl', 'xl', 'lg', 'md', 'sm', 'xs', '2xs', '3xs', '4xs'].includes(value)
	    },
	    accent: {
	      type: Boolean,
	      default: false
	    },
	    tag: {
	      type: String,
	      default: 'span'
	    },
	    align: {
	      type: String,
	      default: null,
	      validator: value => value === null || ['left', 'center', 'right', 'justify'].includes(value)
	    },
	    transform: {
	      type: String,
	      default: null,
	      validator: value => value === null || ['uppercase', 'lowercase', 'capitalize'].includes(value)
	    },
	    wrap: {
	      type: String,
	      default: null,
	      validator: value => value === null || ['truncate', 'break-words', 'break-all'].includes(value)
	    },
	    className: {
	      type: [String, Array, Object],
	      default: null
	    }
	  },
	  computed: {
	    classes() {
	      return ['ui-text', `--${this.size}`, {
	        '--accent': this.accent,
	        [`--align-${this.align}`]: this.align,
	        [`--${this.transform}`]: this.transform,
	        [`--${this.wrap}`]: this.wrap
	      }, this.className];
	    }
	  },
	  template: `
		<component
			:is="tag"
			:class="classes"
			v-bind="$attrs"
		>
			<slot/>
		</component>
	`
	};
	const Text2Xl = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: '2xl'
	    }
	  }
	};
	const TextXl = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: 'xl'
	    }
	  }
	};
	const TextLg = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: 'lg'
	    }
	  }
	};
	const TextMd = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: 'md'
	    }
	  }
	};
	const TextSm = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: 'sm'
	    }
	  }
	};
	const TextXs = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: 'xs'
	    }
	  }
	};
	const Text2Xs = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: '2xs'
	    }
	  }
	};
	const Text3Xs = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: '3xs'
	    }
	  }
	};
	const Text4Xs = {
	  extends: Text$1,
	  props: {
	    size: {
	      default: '4xs'
	    }
	  }
	};

	// @vue/component
	const Headline$1 = {
	  name: 'BHeadline',
	  inheritAttrs: false,
	  props: {
	    size: {
	      type: String,
	      required: true,
	      validator: value => ['xl', 'lg', 'md', 'sm', 'xs'].includes(value)
	    },
	    accent: {
	      type: Boolean,
	      default: false
	    },
	    tag: {
	      type: String,
	      default: 'div'
	    },
	    align: {
	      type: String,
	      default: null,
	      validator: value => value === null || ['left', 'center', 'right', 'justify'].includes(value)
	    },
	    transform: {
	      type: String,
	      default: null,
	      validator: value => value === null || ['uppercase', 'lowercase', 'capitalize'].includes(value)
	    },
	    wrap: {
	      type: String,
	      default: null,
	      validator: value => value === null || ['truncate', 'break-words', 'break-all'].includes(value)
	    },
	    className: {
	      type: [String, Array, Object],
	      default: null
	    }
	  },
	  computed: {
	    classes() {
	      return ['ui-headline', `--${this.size}`, {
	        '--accent': this.accent,
	        [`--align-${this.align}`]: this.align,
	        [`--${this.transform}`]: this.transform,
	        [`--${this.wrap}`]: this.wrap
	      }, this.className];
	    }
	  },
	  template: `
		<component
			:is="tag"
			:class="classes"
			v-bind="$attrs"
		>
			<slot/>
		</component>
	`
	};
	const HeadlineXl = {
	  extends: Headline$1,
	  props: {
	    size: {
	      default: 'xl'
	    }
	  }
	};
	const HeadlineLg = {
	  extends: Headline$1,
	  props: {
	    size: {
	      default: 'lg'
	    }
	  }
	};
	const HeadlineMd = {
	  extends: Headline$1,
	  props: {
	    size: {
	      default: 'md'
	    }
	  }
	};
	const HeadlineSm = {
	  extends: Headline$1,
	  props: {
	    size: {
	      default: 'sm'
	    }
	  }
	};
	const HeadlineXs = {
	  extends: Headline$1,
	  props: {
	    size: {
	      default: 'xs'
	    }
	  }
	};



	var index = /*#__PURE__*/Object.freeze({
		Text: Text$1,
		Text2Xl: Text2Xl,
		TextXl: TextXl,
		TextLg: TextLg,
		TextMd: TextMd,
		TextSm: TextSm,
		TextXs: TextXs,
		Text2Xs: Text2Xs,
		Text3Xs: Text3Xs,
		Text4Xs: Text4Xs,
		Headline: Headline$1,
		HeadlineXl: HeadlineXl,
		HeadlineLg: HeadlineLg,
		HeadlineMd: HeadlineMd,
		HeadlineSm: HeadlineSm,
		HeadlineXs: HeadlineXs
	});

	exports.Headline = Headline;
	exports.Text = Text;
	exports.Vue = index;

}((this.BX.UI.System.Typography = this.BX.UI.System.Typography || {}),BX));
//# sourceMappingURL=typography.bundle.js.map
