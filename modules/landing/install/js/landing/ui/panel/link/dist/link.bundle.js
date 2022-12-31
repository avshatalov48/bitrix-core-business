this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
(function (exports,main_core,landing_ui_panel_content) {
	'use strict';

	class Link extends landing_ui_panel_content.Content {
	  constructor() {
	    super();
	    this.instance = null;
	    this.attr = BX.Landing.Utils.attr;
	    this.join = BX.Landing.Utils.join;
	    this.random = BX.Landing.Utils.random;
	    this.setTextContent = BX.Landing.Utils.setTextContent;
	    this.isPlainObject = BX.Landing.Utils.isPlainObject;
	    this.isString = BX.Landing.Utils.isString;
	    this.textToPlaceholders = BX.Landing.Utils.textToPlaceholders;
	    this.findParent = BX.Landing.Utils.findParent;
	    this.escapeText = BX.Landing.Utils.escapeText;
	    BX.Landing.UI.Panel.Content.apply(this, arguments);
	    this.layout.classList.add("landing-ui-panel-link");
	    this.overlay.classList.add("landing-ui-panel-link");
	    this.appendFooterButton(new BX.Landing.UI.Button.BaseButton("save_block_content", {
	      text: BX.Landing.Loc.getMessage("BLOCK_SAVE"),
	      onClick: this.save.bind(this),
	      className: "landing-ui-button-content-save"
	    }));
	    this.appendFooterButton(new BX.Landing.UI.Button.BaseButton("cancel_block_content", {
	      text: BX.Landing.Loc.getMessage("BLOCK_CANCEL"),
	      onClick: this.hide.bind(this),
	      className: "landing-ui-button-content-cancel"
	    }));
	    document.body.appendChild(this.layout);
	  }

	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new BX.Landing.UI.Panel.Link("link_panel", {
	        title: BX.Landing.Loc.getMessage("LANDING_EDIT_LINK")
	      });
	    }

	    return this.instance;
	  }

	  show(node) {
	    var form;
	    this.title.innerHTML = BX.Landing.Loc.getMessage("LANDING_EDIT_LINK");

	    if (!!node && node instanceof BX.Landing.Block.Node.Link) {
	      this.node = node;
	      form = new BX.Landing.UI.Form.BaseForm({
	        title: this.node.manifest.name
	      });
	      this.field = this.node.getField();
	      form.addField(this.field);
	      this.clear();
	      this.appendForm(form);
	      BX.Landing.UI.Panel.Content.show.call(this);
	      BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	    }

	    if (!!node && (node instanceof BX.Landing.Block.Node.Text || node instanceof BX.Landing.UI.Field.Text)) {
	      this.range = document.getSelection().getRangeAt(0);
	      this.node = node;
	      this.textField = BX.Landing.UI.Field.BaseField.currentField;

	      if (!!this.textField && this.textField.isEditable()) {
	        this.node = this.textField;
	      }

	      var link = this.range.cloneContents().querySelector("a");

	      if (!link) {
	        link = this.findParent(this.range.startContainer, {
	          tagName: "A"
	        });
	      }

	      var href = "";
	      var target = "";

	      if (link) {
	        href = link.getAttribute("href");
	        target = link.getAttribute("target") || "_self";
	      } else {
	        this.title.innerHTML = BX.Landing.Loc.getMessage("LANDING_CREATE_LINK");
	      }

	      form = new BX.Landing.UI.Form.BaseForm({
	        title: ""
	      });
	      BX.remove(form.header);
	      var allowedTypes = [BX.Landing.UI.Field.LinkURL.TYPE_BLOCK, BX.Landing.UI.Field.LinkURL.TYPE_PAGE];

	      if (BX.Landing.Main.getInstance().options.params.type === 'STORE') {
	        allowedTypes.push(BX.Landing.UI.Field.LinkURL.TYPE_CATALOG);
	      }

	      this.field = new BX.Landing.UI.Field.Link({
	        title: BX.Landing.Loc.getMessage("FIELD_LINK_TEXT_LABEL"),
	        content: {
	          text: this.textToPlaceholders(this.escapeText(link ? link.innerText : this.range.toString())),
	          href: this.escapeText(href),
	          target: this.escapeText(target)
	        },
	        options: {
	          siteId: BX.Landing.Main.getInstance().options.site_id,
	          landingId: BX.Landing.Main.getInstance().id,
	          filter: {
	            '=TYPE': BX.Landing.Main.getInstance().options.params.type
	          }
	        },
	        allowedTypes: allowedTypes
	      });
	      form.addField(this.field);
	      this.clear();
	      this.appendForm(form);
	      BX.Landing.UI.Panel.Content.show.call(this);
	    }
	  }

	  save() {
	    if (this.field.isChanged()) {
	      if (!!this.node && this.node instanceof BX.Landing.Block.Node.Link) {
	        this.node.setValue(this.field.getValue());
	      } else {
	        var value = this.field.getValue();
	        document.getSelection().removeAllRanges();
	        document.getSelection().addRange(this.range);
	        this.node.enableEdit();
	        var tmpHref = this.escapeText(this.join(value.href, this.random()));
	        var selection = document.getSelection();
	        document.execCommand("createLink", false, tmpHref);
	        var link = selection.anchorNode.parentElement.parentElement.parentElement.querySelector(this.join("[href=\"", tmpHref, "\"]"));

	        if (link) {
	          this.attr(link, "href", value.href);
	          this.attr(link, "target", value.target);

	          if (this.isString(value.text)) {
	            if (value.text.includes("{{name}}")) {
	              this.field.hrefInput.getPlaceholderData(value.href).then(function (placeholdersData) {
	                link.innerHTML = value.text.replace(new RegExp("{{name}}"), "<span data-placeholder=\"name\">" + placeholdersData.name + "</span>");
	              }.bind(this));
	            } else {
	              this.setTextContent(link, value.text);
	            }
	          }

	          if (this.isPlainObject(value.attrs)) {
	            this.attr(link, value.attrs);
	          }
	        }
	      }
	    }

	    this.hide();
	  }

	}

	exports.Link = Link;

}((this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {}),BX,BX.Landing.UI.Panel));
//# sourceMappingURL=link.bundle.js.map
