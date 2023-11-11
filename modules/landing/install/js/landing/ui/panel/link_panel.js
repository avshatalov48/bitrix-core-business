;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	var attr = BX.Landing.Utils.attr;
	var join = BX.Landing.Utils.join;
	var random = BX.Landing.Utils.random;
	var setTextContent = BX.Landing.Utils.setTextContent;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isString = BX.Landing.Utils.isString;
	var textToPlaceholders = BX.Landing.Utils.textToPlaceholders;
	var findParent = BX.Landing.Utils.findParent;
	var escapeText = BX.Landing.Utils.escapeText;


	/**
	 * Implements interface for works with links panel
	 * Implements singleton patter
	 * @extends {BX.Landing.UI.Panel.Content}
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Panel.Link = function(id, data)
	{
		BX.Landing.UI.Panel.Content.apply(this, arguments);
		this.layout.classList.add("landing-ui-panel-link");
		this.overlay.classList.add("landing-ui-panel-link");

		this.appendFooterButton(
			new BX.Landing.UI.Button.BaseButton("save_block_content", {
				text: BX.Landing.Loc.getMessage("BLOCK_SAVE"),
				onClick: this.save.bind(this),
				className: "landing-ui-button-content-save"
			})
		);
		this.appendFooterButton(
			new BX.Landing.UI.Button.BaseButton("cancel_block_content", {
				text: BX.Landing.Loc.getMessage("BLOCK_CANCEL"),
				onClick: this.hide.bind(this),
				className: "landing-ui-button-content-cancel"
			})
		);

		window.parent.document.body.appendChild(this.layout);
	};


	/**
	 * Stores instance of BX.Landing.UI.Panel.Link
	 * @type {BX.Landing.UI.Panel.Link}
	 */
	BX.Landing.UI.Panel.Link.instance = null;


	/**
	 * Gets instance of BX.Landing.UI.Panel.Link
	 * @return {BX.Landing.UI.Panel.Link}
	 */
	BX.Landing.UI.Panel.Link.getInstance = function()
	{
		if (!BX.Landing.UI.Panel.Link.instance)
		{
			BX.Landing.UI.Panel.Link.instance = new BX.Landing.UI.Panel.Link("link_panel", {
				title: BX.Landing.Loc.getMessage("LANDING_EDIT_LINK")
			});
		}

		return BX.Landing.UI.Panel.Link.instance;
	};


	BX.Landing.UI.Panel.Link.prototype = {
		constructor: BX.Landing.UI.Panel.Link,
		__proto__: BX.Landing.UI.Panel.Content.prototype,

		show: function(node)
		{
			var form;

			this.title.innerHTML = BX.Landing.Loc.getMessage("LANDING_EDIT_LINK");

			if (!!node && node instanceof BX.Landing.Node.Link)
			{
				this.node = node;
				form = new BX.Landing.UI.Form.BaseForm({title: this.node.manifest.name});
				this.field = this.node.getField();
				form.addField(this.field);

				this.clear();
				this.appendForm(form);
				this.checkReadyToSave();
				BX.Landing.UI.Panel.Content.prototype.show.call(this);
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			}

			if (!!node && (node instanceof BX.Landing.Node.Text || node instanceof BX.Landing.UI.Field.Text))
			{
				this.range = this.contextDocument.getSelection().getRangeAt(0);
				this.node = node;
				this.textField = BX.Landing.UI.Field.BaseField.currentField;

				if (!!this.textField && this.textField.isEditable())
				{
					this.node = this.textField;
				}

				var link = this.range.cloneContents().querySelector("a");

				if (!link)
				{
					link = findParent(this.range.startContainer, {tagName: "A"});
				}

				var href = "";
				var target = "";

				if (link)
				{
					href = link.getAttribute("href");
					target = link.getAttribute("target") || "_self";
				}
				else
				{
					this.title.innerHTML = BX.Landing.Loc.getMessage("LANDING_CREATE_LINK");
				}

				form = new BX.Landing.UI.Form.BaseForm({title: ""});
				BX.remove(form.header);

				var allowedTypes = [
					BX.Landing.UI.Field.LinkUrl.TYPE_BLOCK,
					BX.Landing.UI.Field.LinkUrl.TYPE_PAGE
				];

				if (BX.Landing.Main.getInstance().options.params.type === 'STORE')
				{
					allowedTypes.push(BX.Landing.UI.Field.LinkUrl.TYPE_CATALOG);
				}

				this.field = new BX.Landing.UI.Field.Link({
					title: BX.Landing.Loc.getMessage("FIELD_LINK_TEXT_LABEL"),
					content: {
						text: textToPlaceholders(escapeText(link ? link.innerText : this.range.toString())),
						href: escapeText(href),
						target: escapeText(target)
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
				this.checkReadyToSave();
				BX.Landing.UI.Panel.Content.prototype.show.call(this);
			}
		},


		save: function()
		{
			if (this.field.isChanged())
			{
				if (!!this.node && this.node instanceof BX.Landing.Node.Link)
				{
					this.node.setValue(this.field.getValue());
				}
				else
				{
					var value = this.field.getValue();
					this.contextDocument.getSelection().removeAllRanges();
					this.contextDocument.getSelection().addRange(this.range);
					this.node.enableEdit();

					var tmpHref = escapeText(join(value.href, random()));
					var selection = this.contextDocument.getSelection();

					this.contextDocument.execCommand("createLink", false, tmpHref);

					var link = selection.anchorNode
						.parentElement
						.parentElement
						.parentElement
						.querySelector(join("[href=\"", tmpHref, "\"]"));

					if (link)
					{
						attr(link, "href", value.href);
						attr(link, "target", value.target);

						if (isString(value.text))
						{
							if (value.text.includes("{{name}}"))
							{
								this.field.hrefInput.getPlaceholderData(value.href)
									.then(function(placeholdersData) {
										link.innerHTML = value.text.replace(
											new RegExp("{{name}}"),
											"<span data-placeholder=\"name\">"+placeholdersData.name+"</span>"
										);
									}.bind(this));
							}
							else
							{
								setTextContent(link, value.text);
							}
						}

						if (isPlainObject(value.attrs))
						{
							attr(link, value.attrs);
						}
					}
				}
			}

			this.hide();
		}
	};
})();