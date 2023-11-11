this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	class Ul {
	  constructor() {
	    this.popup = null;
	    this.nodeEditSubmit = null;
	    this.nodeEditLi = [];
	    this.content = [];

	    // get all LI with BIU tags
	    const ulLi = BX.findChildren(this.node, {
	      tag: 'li'
	    });
	    for (let i = 0, c = ulLi.length; i < c; i++) {
	      let liContent = '';
	      for (let j = 0, cc = ulLi[i].childNodes.length; j < cc; j++) {
	        if (ulLi[i].childNodes[j].nodeType === 1 && (ulLi[i].childNodes[j].tagName === 'B' || ulLi[i].childNodes[j].tagName === 'I' || ulLi[i].childNodes[j].tagName === 'U')) {
	          liContent += ulLi[i].childNodes[j].outerHTML;
	        } else if (ulLi[i].childNodes[j].nodeType === 3 && BX.util.trim(ulLi[i].childNodes[j].textContent) !== '') {
	          liContent += ' #VAL# ';
	        }
	      }
	      this.content.push({
	        content: BX.util.trim(ulLi[i].textContent),
	        original: liContent
	      });
	    }
	    BX.bind(this.node, 'click', BX.delegate(this.onClick, this));
	  }

	  /**
	   * Save content for Node.
	   * @returns {void}
	   */
	  saveContent() {
	    let wasChanged = false;
	    let isNull = true;
	    // change any li or not
	    for (let i = 0, c = this.nodeEditLi.length; i < c; i++) {
	      if (this.nodeEditLi[i] === null) {
	        wasChanged = true;
	        break;
	      } else {
	        const value = BX.util.trim(this.nodeEditLi[i].value);
	        isNull = false;
	        if (main_core.Type.isUndefined(this.content[i]) || this.content[i].content !== value) {
	          wasChanged = true;
	          break;
	        }
	      }
	    }

	    // save content
	    if (!isNull && wasChanged) {
	      BX.cleanNode(this.node);
	      this.content = [];
	      for (let i = 0, c = this.nodeEditLi.length; i < c; i++) {
	        if (this.nodeEditLi[i] === null) {
	          this.content.push(false);
	        } else {
	          const value = BX.util.trim(this.nodeEditLi[i].value);
	          const original = BX.data(this.nodeEditLi[i], 'original');
	          this.content.push({
	            content: value,
	            original
	          });
	          this.node.appendChild(BX.create('li', {
	            html: original.replace('#VAL#', BX.util.htmlspecialchars(value))
	          }));
	        }
	      }
	      this.markAsChanged();
	    }
	  }

	  /**
	   * Return element for add new li item.
	   * @returns {HTMLElement}
	   */
	  getAddLiButton(i) {
	    return BX.create('input', {
	      attrs: {
	        type: 'button',
	        value: '+'
	      },
	      dataset: {
	        i: i
	      },
	      events: {
	        click: BX.delegate(function () {
	          const button = BX.proxy_context;
	          const int = parseInt(BX.data(button, 'i'), 10);
	          const newLi = BX.create('input', {
	            dataset: {
	              original: this.content[i].original
	            },
	            attrs: {
	              type: 'text'
	            }
	          });
	          BX.insertAfter(BX.create('div', {
	            children: [newLi, this.getAddLiButton(int + 1), this.getRemoveLiButton(int + 1)]
	          }), button.parentNode);
	          this.nodeEditLi.splice(int + 1, 0, newLi);
	          BX.focus(newLi);
	        }, this)
	      }
	    });
	  }

	  /**
	   * Return element for remove li item.
	   * @returns {HTMLElement}
	   */
	  getRemoveLiButton(i) {
	    return BX.create('input', {
	      attrs: {
	        type: 'button',
	        value: '-'
	      },
	      dataset: {
	        i
	      },
	      events: {
	        click: BX.delegate(function () {
	          const button = BX.proxy_context;
	          this.nodeEditLi[BX.data(button, 'i')] = null;
	          BX.remove(button.parentNode);
	        }, this)
	      }
	    });
	  }

	  /**
	   * Return nodes for edit content.
	   * @param {Boolean} showButton False if not show save button.
	   * @returns {Array | HTMLElement}
	   */
	  getEditNodes(showButton) {
	    let li = [];
	    const editLi = [];
	    this.nodeEditLi = [];

	    // edit li
	    for (let i = 0, c = this.content.length; i < c; i++) {
	      li = BX.create('input', {
	        dataset: {
	          original: this.content[i].original
	        },
	        attrs: {
	          type: 'text',
	          value: BX.util.trim(this.content[i].content)
	        }
	      });
	      this.nodeEditLi.push(li);
	      editLi.push(BX.create('div', {
	        children: [li, this.getAddLiButton(i), this.getRemoveLiButton(i)]
	      }));
	    }

	    // save button
	    if (showButton !== false) {
	      this.nodeEditSubmit = BX.create('input', {
	        attrs: {
	          type: 'button',
	          value: 'Save'
	        },
	        events: {
	          click: function () {
	            this.saveContent();
	            this.popup.close();
	          }.bind(this)
	        }
	      });
	    }
	    if (showButton !== false) {
	      editLi.push(this.nodeEditSubmit);
	    }
	    return editLi;
	  }

	  /**
	   * Click on field - edit mode.
	   * @param {MouseEvent} e
	   * @returns {void}
	   */
	  onClick(e) {
	    this.popup = BX.PopupWindowManager.create('landing_node_img', BX.proxy_context, {
	      closeIcon: false,
	      autoHide: true,
	      closeByEsc: true,
	      contentColor: 'white',
	      angle: true,
	      offsetLeft: 15,
	      overlay: {
	        backgroundColor: '#cdcdcd',
	        opacity: '.1'
	      },
	      events: {
	        onPopupClose: function () {
	          this.popup.destroy();
	        }.bind(this)
	      }
	    });

	    // popup content
	    this.popup.setContent(BX.create('div', {
	      children: this.getEditNodes()
	    }));
	    this.popup.show();
	    return BX.PreventDefault(e);
	  }

	  /*
	   * Get tags for show Node in settings form.
	   * @returns {Array}
	   */
	  getSettingsForm() {
	    return [{
	      name: this.getName(),
	      node: BX.create('div', {
	        children: this.getEditNodes(false)
	      })
	    }];
	  }
	  getValue() {}
	  setValue() {}
	  getField() {
	    return new BX.Landing.UI.Field.BaseField({
	      selector: this.selector,
	      title: this.manifest.name
	    });
	  }
	}

	exports.Ul = Ul;

}((this.BX.Landing.Node = this.BX.Landing.Node || {}),BX));
//# sourceMappingURL=ul.bundle.js.map
