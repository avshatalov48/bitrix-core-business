import {Type} from 'main.core';
import {PanelLink} from 'landing.ui.panel.link';

export class Link extends Node
{
	constructor(options)
	{
		super();

		this.trim = BX.Landing.Utils.trim;
		this.isPlainObject = BX.Landing.Utils.isPlainObject;
		this.isString = BX.Landing.Utils.isString;
		this.textToPlaceholders = BX.Landing.Utils.textToPlaceholders;
		this.create = BX.Landing.Utils.create;
		this.escapeText = BX.Landing.Utils.escapeText;
		this.decodeDataValue = BX.Landing.Utils.decodeDataValue;

		BX.Landing.Block.Node.apply(this, arguments);
		this.type = "link";

		if (!this.isGrouped())
		{
			this.node.addEventListener("click", this.onClick.bind(this));
		}

		if (this.isAllowInlineEdit())
		{
			this.node.setAttribute("title", BX.Landing.Loc.getMessage("LANDING_TITLE_OF_LINK_NODE"));
		}
	}

	onContentUpdate()
	{
		var blockId = this.getBlock().id;

		clearTimeout(this.contentEditTimeout);
		this.contentEditTimeout = setTimeout(function() {
			BX.Landing.History.getInstance().push(
				new BX.Landing.History.Entry({
					block: blockId,
					selector: this.selector,
					command: "editLink",
					undo: this.startValue,
					redo: this.getValue()
				})
			);

			this.startValue = null;
		}.bind(this), 400);

		this.getField().setValue(this.getValue());
	}

	isMenuMode()
	{
		return this.manifest.menuMode === true;
	}

	/**
	 * Handles click event
	 * @param {MouseEvent} event
	 */
	onClick()
	{
		event.preventDefault();

		if (!this.isMenuMode())
		{
			event.stopPropagation();
		}

		if (this.isAllowInlineEdit())
		{
			BX.Landing.UI.Button.ColorAction.hideAll();

			if (!BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				const link = new PanelLink;
				link.getInstance().show(this);
				BX.Landing.UI.Panel.Link.getInstance().show(this);
			}
		}
	}

	/**
	 * Checks that button is prevented
	 * @return {boolean}
	 */
	isPrevented()
	{
		return this.getValue().target === "_popup";
	}

	/**
	 * Sets node value
	 * @param data
	 * @param {?boolean} [preventSave = false]
	 * @param {?boolean} [preventHistory = false]
	 */
	setValue(data, preventSave, preventHistory)
	{
		this.startValue = this.startValue || this.getValue();

		this.preventSave(preventSave);

		if (!this.containsImage() && this.isAllowInlineEdit())
		{
			var field = this.getField(true).hrefInput;

			if (this.isString(data.text) && data.text.includes("{{name}}"))
			{
				field.getPlaceholderData(data.href)
					.then(function(placeholdersData) {
						this.node.innerHTML = data.text.replace(
							new RegExp("{{name}}"),
							"<span data-placeholder=\"name\">"+placeholdersData.name+"</span>"
						);
					}.bind(this));
			}
			else
			{
				if (!this.getField().containsHtml() && !this.manifest.skipContent)
				{
					this.node.innerHTML = this.escapeText(data.text);
				}
			}
		}

		this.node.setAttribute("href", this.decodeDataValue(data.href));
		this.node.setAttribute("target", this.escapeText(data.target));

		if ("attrs" in data)
		{
			for (var attr in data.attrs)
			{
				if (data.attrs.hasOwnProperty(attr))
				{
					this.node.setAttribute(attr, data.attrs[attr]);
				}
			}
		}
		else
		{
			this.node.removeAttribute("data-url");
			this.node.removeAttribute("data-embed");
		}

		this.onChange();

		if (!preventHistory)
		{
			this.onContentUpdate();
		}
	}

	/**
	 * Checks that this node contains image node
	 * @return {boolean}
	 */
	containsImage()
	{
		return !!this.node.firstElementChild && this.node.firstElementChild.tagName === "IMG";
	}

	/**
	 * Gets node value
	 * @return {{text: string, href: string|*, target: string|*}}
	 */
	getValue()
	{
		var value = {
			text: this.textToPlaceholders(this.trim(this.node.innerHTML)),
			href: this.trim(this.node.getAttribute("href")),
			target: this.trim(this.node.getAttribute("target") || "_self")
		};

		if (this.node.getAttribute("data-url"))
		{
			value.attrs = {
				"data-url": this.trim(this.node.getAttribute("data-url"))
			};
		}

		if (this.node.getAttribute("data-dynamic"))
		{
			if (!this.isPlainObject(value.attrs))
			{
				value.attrs = {};
			}

			value.attrs["data-dynamic"] = this.node.getAttribute("data-dynamic");
		}

		if (this.manifest.skipContent)
		{
			value['skipContent'] = true;
			delete value.text;
		}

		return value;
	}

	/**
	 * Gets field
	 * @param {boolean} [preventAdjustValue = false]
	 * @return {BX.Landing.UI.Field.BaseField}
	 */
	getField(preventAdjustValue)
	{
		var value = this.getValue();
		value.text = this.textToPlaceholders(this.create("div", {html: value.text}).innerHTML);

		if (!this.field)
		{
			var allowedTypes = [
				BX.Landing.UI.Field.LinkUrl.TYPE_BLOCK,
				BX.Landing.UI.Field.LinkUrl.TYPE_PAGE,
				BX.Landing.UI.Field.LinkUrl.TYPE_CRM_FORM,
				BX.Landing.UI.Field.LinkUrl.TYPE_CRM_PHONE
			];

			if (BX.Landing.Main.getInstance().options.params.type === BX.Landing.Main.TYPE_STORE)
			{
				allowedTypes.push(BX.Landing.UI.Field.LinkUrl.TYPE_CATALOG);
			}

			if (BX.Landing.Main.getInstance().options.features.includes('diskFile'))
			{
				allowedTypes.push(BX.Landing.UI.Field.LinkUrl.TYPE_DISK_FILE);
			}

			this.field = new BX.Landing.UI.Field.Link({
				title: this.manifest.name,
				selector: this.selector,
				skipContent: this.manifest.skipContent,
				content: value,
				options: {
					siteId: BX.Landing.Main.getInstance().options.site_id,
					landingId: BX.Landing.Main.getInstance().id
				},
				allowedTypes: allowedTypes
			});
		}
		else
		{
			if (!preventAdjustValue)
			{
				this.field.setValue(value);
				this.field.content = value;
				this.field.hrefInput.content = value.href;
				this.field.hrefInput.makeDisplayedHrefValue();
				this.field.hrefInput.setHrefTypeSwitcherValue(
					this.field.hrefInput.getHrefStringType()
				);
				this.field.hrefInput.removeHrefTypeFromHrefString();
			}
		}

		return this.field;
	}
}