/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Vue3 = this.BX.UI.Vue3 || {};
(function (exports,main_core,ui_vue3) {
	'use strict';

	const escape = str => String(str).replaceAll(/[\\^$*+?.()|[\]{}]/g, '\\$&');
	function getReplacementRegExp(placeholder) {
	  const closePlaceholder = `${placeholder.slice(0, 1)}/${placeholder.slice(1)}`;
	  return new RegExp(`${escape(placeholder)}.*?${escape(closePlaceholder)}`, 'gmi');
	}
	function getTemplateItems(text, placeholder) {
	  const items = (Array.isArray(placeholder) ? [...placeholder] : [placeholder]).flatMap(templatePlaceholder => {
	    return [...text.matchAll(getReplacementRegExp(templatePlaceholder))].map(exec => ({
	      index: exec.index,
	      placeholder: templatePlaceholder,
	      template: exec[0]
	    }));
	  });
	  if (items.length > 1) {
	    items.sort((a, b) => a.index - b.index);
	  }
	  return items;
	}
	function unfoldTemplate(template, placeholder) {
	  return template.slice(placeholder.length, template.length - placeholder.length - 1);
	}

	function makeRichLocChildren(text, templateItems, context) {
	  const children = [];
	  let index = 0;
	  for (const item of templateItems) {
	    if (item.index > index) {
	      children.push(text.slice(index, item.index));
	      index = item.index;
	    }
	    if (item.index === index) {
	      const placeholder = item.placeholder;
	      const slotName = placeholder.slice(1, -1);
	      if (main_core.Type.isFunction(context.slots[slotName])) {
	        children.push(context.slots[slotName]({
	          text: unfoldTemplate(item.template, placeholder)
	        }));
	      }
	      index += item.template.length;
	    }
	  }
	  if (index < text.length) {
	    children.push(text.slice(index));
	  }
	  return children;
	}
	function RichLoc(props, context) {
	  const templateItems = getTemplateItems(props.text, props.placeholder);
	  const children = makeRichLocChildren(props.text, templateItems, context);
	  return ui_vue3.h(props.tag || 'div', {
	    ...context.attrs
	  }, children);
	}
	const richLocProps = ['text', 'placeholder', 'tag'];
	RichLoc.props = richLocProps;

	exports.RichLoc = RichLoc;

}((this.BX.UI.Vue3.Components = this.BX.UI.Vue3.Components || {}),BX,BX.Vue3));
//# sourceMappingURL=rich-loc.bundle.js.map
