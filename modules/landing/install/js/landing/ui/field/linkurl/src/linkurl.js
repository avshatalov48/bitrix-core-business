import { Text } from 'landing.ui.field.textfield';
import { Dom, Event, Tag, Type } from 'main.core';
import { Dialog } from 'ui.entity-selector';

import 'ui.fonts.opensans';
import 'ui.design-tokens';

import './css/style.css';

export class LinkUrl extends Text
{
	static TYPE_BLOCK = "block";
	static TYPE_PAGE = "landing";
	static TYPE_CRM_FORM = "crmFormPopup";
	static TYPE_CRM_PHONE = "crmPhone";
	static TYPE_SYSTEM = "system";
	static TYPE_CATALOG = "catalog";
	static TYPE_CATALOG_ELEMENT = "element";
	static TYPE_CATALOG_SECTION = "section";
	static TYPE_DISK_FILE = "diskFile";
	static TYPE_USER = "user";

	static TYPE_HREF_START = "selectActions:";
	static TYPE_HREF_PAGE = "page:";
	static TYPE_HREF_BLOCK = "block:";
	static TYPE_HREF_CRM_FORM = "form:";
	static TYPE_HREF_PRODUCT = "product:";
	static TYPE_HREF_TEL = "tel:";
	static TYPE_HREF_SMS = "sms:";
	static TYPE_HREF_MAILTO = "mailto:";
	static TYPE_HREF_SKYPE = "skype:";
	static TYPE_HREF_LINK = "";
	static TYPE_HREF_FILE = "file:";
	static TYPE_HREF_USER = "user:";
	static DELETE_TYPE_HREF = "deleteTypeHref";

	constructor(data)
	{
		super(data);

		/**
		 * Href value matchers
		 */
		this.matchers = {
			catalogElement: new RegExp("^(product:)?#catalogElement([0-9]+)"),
			catalogSection: new RegExp("^(product:)?#catalogSection([0-9]+)"),
			catalog: new RegExp("^#Section([0-9]+)"),
			element: new RegExp("^#Element([0-9]+)"),
			block: new RegExp("^(block:)?#block([0-9]+)"),
			page: new RegExp("^(page:)?#landing([0-9]+)"),
			crmForm: new RegExp("^(form:)?#crmFormPopup([0-9]+)"),
			crmPhone: new RegExp("^(tel:)?#crmPhone([0-9]+)"),
			diskFile: new RegExp("^(file:)?#diskFile([0-9]+)"),
			user: new RegExp("^(user:)?#user([0-9]+)"),
			system: new RegExp("^#system_[a-z_-]+"),
			pageOld: new RegExp("^#landing([0-9]+)"),
		};

		this.typePostfix = {
			skype: '?chat',
		};
		this.typeHrefs = {
			page: LinkUrl.TYPE_HREF_PAGE,
			block: LinkUrl.TYPE_HREF_BLOCK,
			form: LinkUrl.TYPE_HREF_CRM_FORM,
			product: LinkUrl.TYPE_HREF_PRODUCT,
			file: LinkUrl.TYPE_HREF_FILE,
			start: LinkUrl.TYPE_HREF_START,
			user: LinkUrl.TYPE_HREF_USER,
		};

		Dom.addClass(this.layout, "landing-ui-field-link-url");
		this.requestOptions = data.options || {};
		this.disableBlocks = Type.isBoolean(data.disableBlocks) ? data.disableBlocks : false;
		this.disallowType = Type.isBoolean(data.disallowType) ? data.disallowType : false;
		this.iblocks = Type.isArray(data.iblocks) ? data.iblocks : null;
		this.allowedTypes = Type.isArray(data.allowedTypes) ? data.allowedTypes : [LinkUrl.TYPE_BLOCK, LinkUrl.TYPE_PAGE];
		if (this.allowedTypes.length === 1)
		{
			this.constantType = this.allowedTypes[0];
			this.constantTypeData = data.typeData;
		}
		this.allowedCatalogEntityTypes = Type.isArray(data.allowedCatalogEntityTypes) ? data.allowedCatalogEntityTypes : null;
		this.onInitHandler = Type.isFunction(data.onInit) ? data.onInit : (function() {});
		this.onNewPageHandler = Type.isFunction(data.onNewPage) ? data.onNewPage : (function() {});
		this.enableAreas = data.enableAreas;
		this.customPlaceholder = data.customPlaceholder;
		this.detailPageMode = data.detailPageMode === true;
		this.sourceField = data.sourceField;
		this.currentPageOnly = data.currentPageOnly;
		this.panelTitle = data.panelTitle;

		this.onListShow = this.onListShow.bind(this, this.requestOptions);
		this.onTypeChange = this.onTypeChange.bind(this);
		this.onListItemClick = this.onListItemClick.bind(this);

		this.popup = null;
		this.dynamic = null;
		this.value = null;

		this.hrefTypeSwithcer = this.createTypeSwitcher();
		this.hrefTypeSwithcerValue = this.getHrefStringType();
		this.grid = this.createGridLayout();
		this.gridLeftCell = this.grid.querySelector("[class*=\"left\"]");
		this.gridCenterCell = this.grid.querySelector("[class*=\"center\"]");
		this.gridRightCell = this.grid.querySelector("[class*=\"right\"]");

		Dom.remove(this.hrefTypeSwithcer.header);
		Dom.append(this.hrefTypeSwithcer.layout, this.gridLeftCell);
		if (this.getHrefStringType() === LinkUrl.TYPE_HREF_START)
		{
			this.gridCenterCell.hidden = true;
			this.gridRightCell.hidden = true;
		}
		Dom.append(this.input, this.gridCenterCell);
		Dom.append(this.grid, this.layout);

		if (data.settingMode)
		{
			Dom.addClass(this.gridCenterCell, "setting-mode");
		}

		if (!Type.isUndefined(this.constantType))
		{
			this.rightData = this.getRightData();
			if (this.rightData.button)
			{
				const button = this.createCenterCellButton(this.rightData.button);
				Dom.append(button.layout, this.gridCenterCell);
			}
			this.contentEditable = false;
		}

		this.hrefTypeSwithcer.subscribe('onChange', () => {
			this.rightData = this.getRightData();
			this.input.hidden = this.rightData.hideInput === true;
			this.gridCenterCell.hidden = false;
			this.gridRightCell.hidden = false;
			let button;
			if (this.rightData.button)
			{
				button = this.createCenterCellButton(this.rightData.button);
			}
			this.emit('buildCenter',
				{
					button: button,
				});
			this.emit('selectAction',
				{
					hrefStringType: this.getHrefStringType(),
					right: this.rightData,
				});
			if (this.hrefTypeSwithcer.getValue() === LinkUrl.DELETE_TYPE_HREF)
			{
				this.deleteTypeHref();
			}

			//clear input when type is changed
			if (this.hrefTypeSwithcerValue !== this.hrefTypeSwithcer.getValue())
			{
				this.input.innerHTML = '';
				this.setValue("");
				this.hrefTypeSwithcerValue = this.hrefTypeSwithcer.getValue();
			}

			const typeData = this.getTypeData(this.hrefTypeSwithcer.getValue());
			this.setEditPrevented(false);
			this.contentEditable = typeData.contentEditable;
		});

		const type = this.getHrefStringType();
		this.setHrefPlaceholderByType(type);
		this.setHrefTypeSwitcherValue(type);
		this.removeHrefTypeFromHrefString();
		this.makeDisplayedHrefValue();

		if (!Type.isUndefined(this.constantType))
		{
			if (this.content === '')
			{
				this.input.innerText = '';
				Dom.addClass(this.input, "landing-ui-field-input-empty");
			}
		}

		if (this.disallowType)
		{
			Dom.addClass(this.gridLeftCell, "grid-dissallow");
		}
	}

	/**
	 * Sets iblocks list
	 * @param {{name: string, value: int|string}[]} iblocks
	 */
	setIblocks(iblocks)
	{
		this.iblocks = Type.isArray(iblocks) ? iblocks : null;
	}

	createCenterCellButton(data)
	{
		let actionClick;
		if (data.hasOwnProperty('action'))
		{
			actionClick = this.onListShow.bind(this, data.action);
		}
		else
		{
			actionClick = data.onclick;
		}
		const buttonClasses = `landing-ui-button-grid-center-cell ${data.className || ''}`;
		return new BX.Landing.UI.Button.BaseButton("center_cell_button", {
			className: buttonClasses,
			text: data.text,
			onClick: actionClick
		});
	}

	/**
	 * Makes displayed value placeholder
	 */
	makeDisplayedHrefValue()
	{
		const hrefValue = this.getValue();
		let placeholderType = this.getPlaceholderType();
		if (!Type.isUndefined(this.constantType))
		{
			placeholderType = this.constantType;
		}
		let valuePromise;

		switch (placeholderType)
		{
			case LinkUrl.TYPE_BLOCK:
				valuePromise = this.getBlockData(hrefValue);
				break;
			case LinkUrl.TYPE_PAGE:
			case LinkUrl.TYPE_HREF_PAGE:
				valuePromise = this.getPageData(hrefValue);
				break;
			case LinkUrl.TYPE_CRM_FORM:
				valuePromise = this.getCrmFormData(hrefValue);
				break;
			case LinkUrl.TYPE_CRM_PHONE:
				valuePromise = this.getCrmPhoneData(hrefValue);
				break;
			case LinkUrl.TYPE_CATALOG_ELEMENT:
				valuePromise = this.getCatalogElementData(hrefValue);
				break;
			case LinkUrl.TYPE_CATALOG_SECTION:
				valuePromise = this.getCatalogSectionData(hrefValue);
				break;
			case LinkUrl.TYPE_DISK_FILE:
				valuePromise = this.getDiskFileData(hrefValue);
				break;
			case LinkUrl.TYPE_USER:
				valuePromise = this.getUserData(hrefValue);
				break;
			case LinkUrl.TYPE_SYSTEM:
				valuePromise = this.getSystemPage(hrefValue);
				break;
			case LinkUrl.TYPE_CATALOG:
				valuePromise = this.getCatalog(hrefValue);
				break;
		}

		if (valuePromise)
		{
			valuePromise
				.then(BX.Landing.Utils.proxy(this.createPlaceholder, this))
				.then(function(data) {
					this.setValue(data, true);
					if (!this.inited)
					{
						this.inited = true;
						this.onInitHandler();
					}
					return data;
				}.bind(this))
				.catch(function() {});
		}
	}

	/**
	 * Gets placeholder data
	 * @param {string} [hrefValue]
	 * @return {Promise<Object>}
	 */
	getPlaceholderData(hrefValue)
	{
		hrefValue = hrefValue || this.getValue();
		const placeholderType = this.getPlaceholderType(hrefValue);
		let valuePromise = Promise.resolve({});

		switch (placeholderType)
		{
			case LinkUrl.TYPE_BLOCK:
				valuePromise = this.getBlockData(hrefValue);
				break;
			case LinkUrl.TYPE_PAGE:
				valuePromise = this.getPageData(hrefValue);
				break;
			case LinkUrl.TYPE_CATALOG_ELEMENT:
				valuePromise = this.getCatalogElementData(hrefValue);
				break;
			case LinkUrl.TYPE_CATALOG_SECTION:
				valuePromise = this.getCatalogSectionData(hrefValue);
				break;
			case LinkUrl.TYPE_DISK_FILE:
				valuePromise = this.getDiskFileData(hrefValue);
				break;
			case LinkUrl.TYPE_USER:
				valuePromise = this.getUserData(hrefValue);
				break;
			case LinkUrl.TYPE_SYSTEM:
				valuePromise = this.getSystemPage(hrefValue);
				break;
		}

		return valuePromise;
	}

	/**
	 * Removes type prefix from href value
	 */
	removeHrefTypeFromHrefString()
	{
		const clearHref = this.getValue()
			.replace(new RegExp(this.getHrefStringType(), "g"), "");
		this.setValue(clearHref, true);
	}

	/**
	 * Sets type switcher value
	 * @param type
	 */
	setHrefTypeSwitcherValue(type)
	{
		if (type === LinkUrl.TYPE_HREF_START)
		{
			this.gridCenterCell.hidden = true;
			this.gridRightCell.hidden = true;
			this.emit('deleteAction');
		}
		else
		{
			this.gridCenterCell.hidden = false;
			this.gridRightCell.hidden = false;
		}
		this.hrefTypeSwithcer.setValue(type);
	}

	/**
	 * Gets selected href type (From type switcher)
	 * @return {string}
	 */
	getSelectedHrefType()
	{
		return this.hrefTypeSwithcer.getValue();
	}

	getRightData()
	{
		let type = this.hrefTypeSwithcer.getValue();
		if (!Type.isUndefined(this.constantType))
		{
			type = this.constantType;
		}
		const data = this.getTypeData(type);
		const title = this.getRightTitle(data);
		const items = this.getRightItems(data);
		const button = this.getRightButton(data);
		const hideInput = this.getRightHideInput(data);
		const idPopup = '';
		return {
			title,
			items,
			hideInput,
			button,
			idPopup,
		};
	}

	getRightTitle(data)
	{
		return data.title;
	}

	getRightItems(data)
	{
		return data.items;
	}

	getRightHideInput(data)
	{
		return data.hideInput;
	}

	getRightButton(data)
	{
		return data.button;
	}

	getTypeData(type)
	{
		if (!Type.isUndefined(this.constantTypeData))
		{
			return this.constantTypeData;
		}

		const data = {};
		const buttonClasses = 'fa fa-chevron-right';
		switch (type)
		{
			case LinkUrl.TYPE_HREF_PAGE:
			case LinkUrl.TYPE_PAGE:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_PAGE");
				data.items =  {
					"_self": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_SELF"),
					"_blank": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_BLANK"),
					"_popup": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_POPUP"),
				};
				data.button = {
					'className': buttonClasses,
					'text': '',
					'action': LinkUrl.TYPE_PAGE,
				};
				data.hideInput = false;
				data.contentEditable = false;
				break;
			case LinkUrl.TYPE_HREF_BLOCK:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_BLOCK");
				data.items =  {
					"_self": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_SELF"),
					"_blank": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_BLANK"),
					"_popup": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_POPUP"),
				};
				data.button = {
					'className': buttonClasses,
					'text': '',
					'action': LinkUrl.TYPE_BLOCK,
				};
				data.hideInput = false;
				data.contentEditable = false;
				break;
			case LinkUrl.TYPE_HREF_CRM_FORM:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_CRM_FORM");
				data.button = {
					'className': buttonClasses,
					'text': '',
					'action': LinkUrl.TYPE_CRM_FORM,
				};
				data.hideInput = false;
				data.contentEditable = false;
				break;
			case LinkUrl.TYPE_HREF_PRODUCT:
			case LinkUrl.TYPE_CATALOG:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_PRODUCT");
				data.button = {
					'className': buttonClasses,
					'text': '',
					'action': LinkUrl.TYPE_CATALOG_SECTION,
				};
				data.hideInput = false;
				data.contentEditable = false;
				break;
			case LinkUrl.TYPE_HREF_TEL:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_TEL");
				data.items =  {
					"_blank": '',
				};
				data.button = {
					'className': buttonClasses,
					'text': '',
					'action': LinkUrl.TYPE_CRM_PHONE,
				};
				data.contentEditable = true;
				data.hideInput = false;
				data.needValidate = 'phone';
				break;
			case LinkUrl.TYPE_HREF_SMS:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_SMS");
				data.hideInput = false;
				data.needValidate = 'phone';
				data.contentEditable = true;
				break;
			case LinkUrl.TYPE_HREF_SKYPE:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_SKYPE");
				data.hideInput = false;
				data.needValidate = 'skype';
				data.contentEditable = true;
				break;
			case LinkUrl.TYPE_HREF_MAILTO:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_MAILTO");
				data.items =  {
					"_blank": "",
				};
				data.hideInput = false;
				data.needValidate = 'mail';
				data.contentEditable = true;
				break;
			case LinkUrl.TYPE_HREF_LINK:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_LINK");
				data.items =  {
					"_self": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_SELF"),
					"_blank": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_BLANK"),
					"_popup": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_POPUP"),
				};
				data.hideInput = false;
				data.contentEditable = true;
				break;
			case LinkUrl.TYPE_HREF_FILE:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_FILE");
				data.items =  {
					"_blank": '',
				};
				data.button = {
					'className': buttonClasses,
					'text': '',
					'onclick': this.onDiskFileShow.bind(this),
				};
				data.hideInput = false;
				data.contentEditable = false;
				break;
			case LinkUrl.TYPE_HREF_USER:
				data.title = BX.Landing.Loc.getMessage("LANDING_LINK_URL_TITLE_USER");
				data.button = {
					'className': buttonClasses,
					'text': '',
					'onclick': this.onUserListShow.bind(this),
				};
				data.hideInput = false;
				data.contentEditable = false;
				break;
		}

		return data;
	}

	/**
	 * Get link type
	 * @return {string}
	 */
	getHrefStringType()
	{
		const segment = this.getValueText();
		let type = LinkUrl.TYPE_HREF_START;

		if (!Type.isUndefined(this.constantType))
		{
			return this.constantType;
		}

		const foundHrefStringType = this.matchHrefStringType(segment);
		if (foundHrefStringType !== null)
		{
			return foundHrefStringType
		}

		//for blocks with default href="#"
		if (segment === '#')
		{
			return type;
		}

		const setHrefTypes = [
			LinkUrl.TYPE_HREF_START,
			LinkUrl.TYPE_HREF_PAGE,
			LinkUrl.TYPE_HREF_BLOCK,
			LinkUrl.TYPE_HREF_CRM_FORM,
			LinkUrl.TYPE_HREF_PRODUCT,
			LinkUrl.TYPE_HREF_TEL,
			LinkUrl.TYPE_HREF_SMS,
			LinkUrl.TYPE_HREF_MAILTO,
			LinkUrl.TYPE_HREF_SKYPE,
			LinkUrl.TYPE_HREF_FILE,
			LinkUrl.TYPE_HREF_USER,
		];

		const isFindHrefType = setHrefTypes.some(function(hrefType) {
			return segment.includes(hrefType);
		});
		if (segment !== '' && segment !== '#' && !isFindHrefType)
		{
			return LinkUrl.TYPE_HREF_LINK;
		}

		const segmentType = BX.Landing.Utils.join(segment.split(":")[0], ":");
		if (segment.length !== segmentType.length)
		{
			switch (segmentType)
			{
				case LinkUrl.TYPE_HREF_PAGE:
					type = LinkUrl.TYPE_HREF_PAGE;
					break;
				case LinkUrl.TYPE_HREF_BLOCK:
					type = LinkUrl.TYPE_HREF_BLOCK;
					break;
				case LinkUrl.TYPE_HREF_CRM_FORM:
					type = LinkUrl.TYPE_HREF_CRM_FORM;
					break;
				case LinkUrl.TYPE_HREF_PRODUCT:
					type = LinkUrl.TYPE_HREF_PRODUCT;
					break;
				case LinkUrl.TYPE_HREF_TEL:
					type = LinkUrl.TYPE_HREF_TEL;
					break;
				case LinkUrl.TYPE_HREF_SMS:
					type = LinkUrl.TYPE_HREF_SMS;
					break;
				case LinkUrl.TYPE_HREF_SKYPE:
					type = LinkUrl.TYPE_HREF_SKYPE;
					break;
				case LinkUrl.TYPE_HREF_MAILTO:
					type = LinkUrl.TYPE_HREF_MAILTO;
					break;
				case LinkUrl.TYPE_HREF_LINK:
					type = LinkUrl.TYPE_HREF_LINK;
					break;
				case LinkUrl.TYPE_HREF_FILE:
					type = LinkUrl.TYPE_HREF_FILE;
					break;
				case LinkUrl.TYPE_HREF_USER:
					type = LinkUrl.TYPE_HREF_USER;
					break;
			}
		}

		return type;
	}

	/**
	 * Match type href for old values
	 * @param {string} value
	 */
	matchHrefStringType(value)
	{
		if (this.matchers.catalogElement.test(value))
		{
			return LinkUrl.TYPE_HREF_PRODUCT;
		}
		if (this.matchers.catalogSection.test(value))
		{
			return LinkUrl.TYPE_HREF_PRODUCT;
		}
		if (this.matchers.block.test(value))
		{
			return LinkUrl.TYPE_HREF_BLOCK;
		}
		if (this.matchers.pageOld.test(value))
		{
			return LinkUrl.TYPE_HREF_PAGE;
		}
		if (this.matchers.crmForm.test(value))
		{
			return LinkUrl.TYPE_HREF_CRM_FORM;
		}
		if (this.matchers.crmPhone.test(value))
		{
			return LinkUrl.TYPE_HREF_TEL;
		}
		if (this.matchers.diskFile.test(value))
		{
			return LinkUrl.TYPE_HREF_FILE;
		}

		return null;
	}

	/**
	 * Sets placeholder by href type
	 * @param {string} type
	 */
	setHrefPlaceholderByType(type)
	{
		let placeholder = this.placeholder;

		switch (type)
		{
			case LinkUrl.TYPE_HREF_PAGE:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_BUTTON_PAGE");
				break;
			case LinkUrl.TYPE_HREF_BLOCK:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_BUTTON_BLOCK");
				break;
			case LinkUrl.TYPE_HREF_CRM_FORM:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_BUTTON_CRM");
				break;
			case LinkUrl.TYPE_HREF_LINK:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_PLACEHOLDER_URL");
				break;
			case LinkUrl.TYPE_HREF_TEL:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_PLACEHOLDER_PHONE");
				break;
			case LinkUrl.TYPE_HREF_SKYPE:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_PLACEHOLDER_SKYPE");
				break;
			case LinkUrl.TYPE_HREF_SMS:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_PLACEHOLDER_PHONE");
				break;
			case LinkUrl.TYPE_HREF_MAILTO:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_PLACEHOLDER_EMAIL");
				break;
			case LinkUrl.TYPE_HREF_FILE:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_BUTTON_FILE");
				break;
			case LinkUrl.TYPE_HREF_USER:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_BUTTON_USER");
				break;
			case LinkUrl.TYPE_HREF_PRODUCT:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_BUTTON_PRODUCT");
				break;
			case LinkUrl.TYPE_CATALOG:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_BUTTON_CATALOG");
				break;
			case LinkUrl.TYPE_PAGE:
				placeholder = BX.Landing.Loc.getMessage("LANDING_LINK_URL_BUTTON_PAGE_SHORT");
				break;
		}

		Dom.attr(this.input, "data-placeholder", placeholder);
	}

	/**
	 * Gets placeholder type
	 * @param {string} [hrefValue]
	 * @return {string}
	 */
	getPlaceholderType(hrefValue)
	{
		hrefValue = hrefValue || this.getValue();

		if (this.matchers.block.test(hrefValue))
		{
			return LinkUrl.TYPE_BLOCK;
		}

		if (this.matchers.page.test(hrefValue))
		{
			return LinkUrl.TYPE_PAGE;
		}

		if (this.matchers.crmForm.test(hrefValue))
		{
			return LinkUrl.TYPE_CRM_FORM;
		}

		if (this.matchers.crmPhone.test(hrefValue))
		{
			return LinkUrl.TYPE_CRM_PHONE;
		}

		if (this.matchers.catalogElement.test(hrefValue))
		{
			return LinkUrl.TYPE_CATALOG_ELEMENT;
		}

		if (this.matchers.catalogSection.test(hrefValue))
		{
			return LinkUrl.TYPE_CATALOG_SECTION;
		}

		if (this.matchers.diskFile.test(hrefValue))
		{
			return LinkUrl.TYPE_DISK_FILE;
		}

		if (this.matchers.user.test(hrefValue))
		{
			return LinkUrl.TYPE_USER;
		}

		if (this.matchers.system.test(hrefValue))
		{
			return LinkUrl.TYPE_SYSTEM;
		}

		return LinkUrl.TYPE_HREF_LINK;
	}

	/**
	 * Checks that this field contains url placeholder
	 * @return {boolean}
	 */
	containsPlaceholder()
	{
		return this.input.innerHTML.indexOf("span") !== -1;
	}

	/**
	 * Creates field grid layout
	 * @return {Element}
	 */
	createGridLayout()
	{
		return Tag.render`
			<div class=\"landing-ui-field-link-url-grid --landing-ui-field-link-url__scope\">
				<div class=\"landing-ui-field-link-url-grid-left\"></div>
					<div class=\"landing-ui-field-link-url-grid-center\"></div>
				<div class=\"landing-ui-field-link-url-grid-right\"></div>
			</div>
			`;
	}

	onSelectHrefButtonClick()
	{
		this.popupActions.show();
	}

	/**
	 * Creates type switcher dropdown
	 * @return {BX.Landing.UI.Field.Dropdown}
	 */
	createTypeSwitcher()
	{
		//type = PAGE || STORE || KNOWLEDGE
		const type = BX.Landing.Env.getInstance().getType();
		const items = [
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_SELECT"),
				value: LinkUrl.TYPE_HREF_START,
				hidden: true,
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_PAGE"),
				value: LinkUrl.TYPE_HREF_PAGE,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--b24',
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_BLOCK"),
				value: LinkUrl.TYPE_HREF_BLOCK,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--b24',
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_CRM"),
				value: LinkUrl.TYPE_HREF_CRM_FORM,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--crm',
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_PRODUCT"),
				value: LinkUrl.TYPE_HREF_PRODUCT,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--product',
				type: 'STORE',
			},
			{
				delimiter: true
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_PHONE"),
				value: LinkUrl.TYPE_HREF_TEL,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--phone',
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_SMS"),
				value: LinkUrl.TYPE_HREF_SMS,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--sms',
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_EMAIL"),
				value: LinkUrl.TYPE_HREF_MAILTO,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--mailto',
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_SKYPE"),
				value: LinkUrl.TYPE_HREF_SKYPE,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--skype',
			},
			{
				delimiter: true
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_LINK"),
				value: LinkUrl.TYPE_HREF_LINK,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--link',
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_FILE_MSGVER_1"),
				value: LinkUrl.TYPE_HREF_FILE,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--file',
				type: ['KNOWLEDGE', 'GROUP'],
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_USER"),
				value: LinkUrl.TYPE_HREF_USER,
				className: 'landing-ui-field-link-url-select-action-item fas landing-ui-field-link-url-icon--user',
				type: 'KNOWLEDGE',
			},
			{
				name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_DELETE_ACTION"),
				value: LinkUrl.DELETE_TYPE_HREF,
				className: 'landing-ui-field-link-url-delete-action-item fas',
			},
		];
		let setItems = [];
		items.forEach(function(item) {
			if (
				!item.hasOwnProperty('type')
				|| item.type === type
				|| Type.isArray(item.type) && item.type.includes(type)
			)
			{
				setItems.push(item);
			}
		})

		if (!Type.isUndefined(this.constantType))
		{
			if (this.constantType === LinkUrl.TYPE_CATALOG)
			{
				setItems = [
					{
						name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_SELECT_CATALOG"),
						value: this.constantType,
					},
				];
			}
			if (this.constantType === LinkUrl.TYPE_PAGE)
			{
				setItems = [
					{
						name: BX.Landing.Loc.getMessage("LANDING_LINK_URL_ACTION_SELECT_PAGE"),
						value: this.constantType,
					},
				];
			}
		}

		return new BX.Landing.UI.Field.Dropdown({
			items: setItems,
			onValueChange: this.onTypeChange,
			maxHeight: 1000,
			className: 'landing-ui-field-link-url-dropdown-href-type',
			classForTextNode: 'landing-ui-field-input-text',
		});
	}

	/**
	 * Handles link type change event
	 * @param {BX.Landing.UI.Field.Dropdown} field
	 */
	onTypeChange(field)
	{
		const type = field.getValue();

		switch (type)
		{
			case LinkUrl.TYPE_HREF_START:
			case LinkUrl.TYPE_HREF_PAGE:
			case LinkUrl.TYPE_HREF_BLOCK:
			case LinkUrl.TYPE_HREF_CRM_FORM:
			case LinkUrl.TYPE_HREF_PRODUCT:
			case LinkUrl.TYPE_HREF_LINK:
			case LinkUrl.TYPE_HREF_TEL:
			case LinkUrl.TYPE_HREF_SMS:
			case LinkUrl.TYPE_HREF_SKYPE:
			case LinkUrl.TYPE_HREF_MAILTO:
			case LinkUrl.TYPE_HREF_FILE:
			case LinkUrl.TYPE_HREF_USER:
		}

		this.setHrefPlaceholderByType(type);
	}

	/**
	 * Gets block data
	 * @param {string} block - (#block123)
	 * @return {Promise<T>}
	 */
	getBlockData(block)
	{
		const blockId = block.match(/\d+/)[0];
		return BX.Landing.Backend.getInstance()
			.getBlock({blockId: blockId})
			.then(function(result) {
				return (result.type = "block"), result;
			});
	}

	/**
	 * Gets page data
	 * @param {string} page - (#landing123)
	 */
	getPageData(page)
	{
		const match = page.match(/\d+/);
		if (match !== null)
		{
			const pageId = match[0];
			return BX.Landing.Backend.getInstance()
				.getLanding({landingId: pageId})
				.then(function(landing) {
					if (!landing)
					{
						if (BX.Text.toNumber(pageId) === 0)
						{
							this.onNewPageHandler();

							return {
								type: "landing",
								id: 0,
								name: BX.Landing.Loc.getMessage('LANDING_LINK_PLACEHOLDER_NEW_PAGE'),
								siteId: BX.Landing.Main.getInstance().options.site_id
							};
						}
						else
						{
							return null;
						}
					}

					return {
						type: "landing",
						id: landing.ID,
						name: landing.TITLE,
						siteId: landing.SITE_ID
					};
				}.bind(this));
		}
	}

	getCrmFormData(value)
	{
		const formId = value.match(/\d+/)[0];

		return BX.Landing.Backend
			.getInstance()
			.action("Form::getList")
			.then(function(result) {
				const form = result.find(function(item) {
					return String(item.ID) === String(formId);
				});

				if (form)
				{
					return {
						type: "crmFormPopup",
						id: form.ID,
						name: form.NAME
					};
				}

				return null;
			}.bind(this));
	}

	getCrmPhoneData(value)
	{
		return new Promise(function(resolve) {
			const phoneId = value.replace('tel:', '').replace('#crmPhone', '');
			const item = BX.Landing.Env
				.getInstance()
				.getOptions()
				.references
				.find(function(item) {
					return String(item.value) === String(phoneId);
				});

			if (item)
			{
				resolve({
					type: "crmPhone",
					id: item.value,
					name: item.text
				});
			}
			else
			{
				resolve(null);
			}
		}.bind(this));
	}

	/**
	 * Gets system page data
	 * @param {string} page - (#system_([a-z]))
	 */
	getSystemPage(page)
	{
		return this.cache.remember(page, function() {
			const systemCode = this.content.replace("#system_", "");
			const systemPages = BX.Landing.Main.getInstance().options.syspages;

			if (systemCode in systemPages)
			{
				return Promise.resolve({
					type: "system",
					id: "_" + systemCode,
					name: systemPages[systemCode].name
				});
			}

			return Promise.reject();
		}.bind(this));
	}

	/**
	 * Gets catalog element data
	 * @param {string} element
	 */
	getCatalogElementData(element)
	{
		return this.cache.remember(element, function() {
			let elementId = element.match(this.matchers.catalogElement)[2];
			if (!Type.isString(elementId))
			{
				elementId = element.match(this.matchers.catalogElement)[1];
			}
			const requestBody = {elementId: elementId};

			return BX.Landing.Backend.getInstance()
				.action("Utils::getCatalogElement", requestBody);
		}.bind(this));
	}

	/**
	 * Gets catalog section data
	 * @param {string} section
	 */
	getCatalogSectionData(section)
	{
		return this.cache.remember(section, function() {
			let sectionId = section.match(this.matchers.catalogSection)[2];
			if (!Type.isString(sectionId))
			{
				sectionId = element.match(this.matchers.catalogSection)[1];
			}
			const requestBody = {sectionId: sectionId};

			return BX.Landing.Backend.getInstance()
				.action("Utils::getCatalogSection", requestBody);
		}.bind(this));
	}

	getCatalog(section)
	{
		if (
			section === '={$sectionId}'
			|| section === 'selectActions:'
		)
		{
			return null;
		}
		return this.cache.remember(section, function() {
			let matchRes;
			let id;
			let type;
			matchRes = section.match(this.matchers.catalog);
			if (matchRes === null)
			{
				matchRes = section.match(this.matchers.element);
				if (matchRes !== null)
				{
					type = 'Element';
				}
			}
			else
			{
				type = 'Section';
			}
			if (matchRes)
			{
				id = matchRes[1];
			}

			let requestBody = null;
			if (type === 'Section')
			{
				requestBody = {sectionId: id};
			}
			if (type === 'Element')
			{
				requestBody = {elementId: id};
			}
			if (requestBody === null)
			{
				return null;
			}
			const action = 'Utils::getCatalog' + type;

			return BX.Landing.Backend.getInstance()
				.action(action, requestBody);
		}.bind(this));
	}

	/**
	 * Gets disk file data.
	 * @param {string} diskFile
	 */
	getDiskFileData(diskFile)
	{
		return this.cache.remember(diskFile, function() {
			const fileId = diskFile.replace("file:", "").replace("#diskFile", "");

			return BX.Landing.Backend
				.getInstance()
				.action("Block::getFileDisk", {fileId: fileId})
				.then(function(result) {
					if (result)
					{
						return {
							type: LinkUrl.TYPE_DISK_FILE,
							id: result.ID,
							name: result.NAME
						};
					}
					return null;
				}.bind(this));
		}.bind(this));
	}

	/**
	 * Gets user data.
	 * @param {string} userData
	 */
	getUserData(userData)
	{
		const userId = userData.replace("user:", "").replace("#user", "");
		return new Promise(function(resolve) {
			BX.ajax({
				url: '/bitrix/services/main/ajax.php?action=landing.api.user.getUserNameById',
				method: 'POST',
				dataType: 'json',
				data: {
					userId: userId
				},
				onsuccess: function(result) {
					const response = {
						type: LinkUrl.TYPE_USER,
						id: userId,
						name: result.data
					};
					resolve(response);
				},
			});
		}.bind(this));
	}

	deleteTypeHref()
	{
		this.gridCenterCell.hidden = true;
		this.gridRightCell.hidden = true;
		this.setHrefTypeSwitcherValue(LinkUrl.TYPE_HREF_START);
		this.setHrefPlaceholderByType(LinkUrl.TYPE_HREF_START);
		this.emit('deleteAction');
	}

	onSelectButtonClick()
	{
		if (this.allowedTypes.length === 1)
		{
			this.onListShow(this.allowedTypes[0]);
		}
	}

	onListShow(options, type)
	{
		if (this.popup)
		{
			this.popup.close();
		}

		if (
			type === LinkUrl.TYPE_CATALOG_SECTION
			|| type === LinkUrl.TYPE_CATALOG
		)
		{
			let iblocks = this.iblocks;

			if (!Type.isArray(iblocks))
			{
				iblocks = BX.Landing.Main.getInstance().options.iblocks;
			}

			void BX.Landing.UI.Panel.Catalog.getInstance()
				.show(iblocks, this.allowedCatalogEntityTypes)
				.then(this.onListItemClick);

			return;
		}

		options.enableAreas = this.enableAreas;
		options.dynamicMode = true;
		options.currentPageOnly = this.currentPageOnly;
		options.panelTitle = this.panelTitle;

		if (this.detailPageMode)
		{
			options.source = this.sourceField.getValue().source;
			void BX.Landing.UI.Panel.DetailPage.getInstance()
				.show(options)
				.then(this.onListItemClick);
		}
		else
		{
			const panel = BX.Landing.UI.Panel.URLList.getInstance();

			void panel
				.show(type, options)
				.then(this.onListItemClick);
		}
	}

	onDiskFileShow()
	{
		if (this.popup)
		{
			this.popup.close();
		}

		parent.BX.Landing.Connector.Disk.openDialog({
			onSelect: (fileId) => {
				this.getDiskFileData("#diskFile" + fileId)
					.then(function(data)
					{
						this.setValue(this.createPlaceholder(data), true);
					}.bind(this))
				this.setHrefTypeSwitcherValue(LinkUrl.TYPE_HREF_FILE);
			}
		});
	}

	onUserListShow()
	{
		this.dialog = new Dialog({
			targetNode: this.input,
			enableSearch: true,
			context: 'MY_MODULE_CONTEXT',
			entities: [
				{
					id: LinkUrl.TYPE_USER,
				},
				{
					id: 'department',
				},
			],
			events: {
				'Item:onSelect': this.onSelectUser.bind(this)
			},
			multiple: false,
			popupOptions: {
				targetContainer: parent.document.body,
			},
		});
		this.dialog.show();
	}

	onSelectUser()
	{
		const selectedItem = this.dialog.getSelectedItems()[0];
		const item = {
			'name': selectedItem.title.text,
			'type': LinkUrl.TYPE_USER,
			'id': selectedItem.id,
		};
		this.setValue(this.createPlaceholder(item));
		BX.Landing.Utils.fireEvent(this.layout, "input");
		this.setHrefTypeSwitcherValue(item.type + ':');
	}

	/**
	 * Checks that edit mode is prevented
	 * @return {boolean}
	 */
	isEditPrevented()
	{
		if (!Type.isBoolean(this.editPrevented))
		{
			this.editPrevented = this.containsPlaceholder();
		}

		return this.editPrevented;
	}

	/**
	 * Sets edit prevented value
	 * @param {boolean} value
	 */
	setEditPrevented(value)
	{
		this.editPrevented = value;
	}

	/**
	 * Enables edit
	 */
	enableEdit()
	{
		if (!this.isEditPrevented())
		{
			BX.Landing.UI.Field.Text.prototype.enableEdit.apply(this);
		}
	}

	/**
	 * Creates internal url placeholder
	 * @param {{[type]: string, [id]: string|number, name: string, [url]: string, [image]: string, [subType]: string, [chain]: string[]}} options
	 * @returns {Element}
	 */
	createPlaceholder(options)
	{
		Dom.addClass(this.gridCenterCell, "--not-empty");
		if (Type.isString(options))
		{
			return options;
		}

		const placeholder = Tag.render`
			<span class=\"landing-ui-field-url-placeholder\">
				<span class=\"landing-ui-field-url-placeholder-preview\"></span>
				<span class=\"landing-ui-field-url-placeholder-text\">
					${BX.Landing.Utils.encodeDataValue(options.name)}
				</span>
				<span class=\"landing-ui-field-url-placeholder-delete\"></span>
			</span>
		`;

		const placeholderRemove = placeholder
			.querySelector("[class*=\"delete\"]");
		Event.bind(placeholderRemove, "click", this.onPlaceholderRemoveClick.bind(this));


		if (options.type === LinkUrl.TYPE_CATALOG)
		{
			options.chain.push(options.name);
			const title = BX.Landing.Utils.join(options.name, "\n", options.chain.join(' / '));

			Dom.attr(placeholder, {
				"data-dynamic": {
					type: BX.Landing.Utils.join(LinkUrl.TYPE_CATALOG, BX.Landing.Utils.capitalize(options.subType)),
					value: options.id
				},
				"data-placeholder": BX.Landing.Utils.join("#", options.type, BX.Landing.Utils.capitalize(options.subType), options.id),
				"data-url": BX.Landing.Utils.join("#", options.type, BX.Landing.Utils.capitalize(options.subType), options.id)
			});

			placeholder.setAttribute("title", title);

			return placeholder;
		}

		BX.Landing.Utils.attr(placeholder, {
			"data-placeholder": BX.Landing.Utils.join("#", options.type, options.id),
			"data-url": BX.Landing.Utils.join("#", options.type, options.id)
		});

		placeholder.setAttribute("title", options.name);

		return placeholder;
	}

	/**
	 * Handles click event on placeholder remove button
	 * @param event
	 */
	onPlaceholderRemoveClick(event)
	{
		Dom.removeClass(this.gridCenterCell, "--not-empty");
		this.setEditPrevented(false);
		this.enableEdit();
		Dom.remove(event.target.parentNode);
		this.setValue("");
		BX.Landing.Utils.fireEvent(this.layout, "input");
		this.onInputHandler(this.input.innerText);
	}

	/**
	 * Handles click event on catalog panel item
	 * @param {object} item
	 */
	onListItemClick(item)
	{
		let resultPromise = Promise.resolve(item);

		if (item.type === "block")
		{
			resultPromise = this.getBlockData("#block" + item.id);
		}

		resultPromise.then(function(item) {
			this.setValue(this.createPlaceholder(item));
			BX.Landing.Utils.fireEvent(this.layout, "input");
			this.setHrefTypeSwitcherValue(item.type + ':');
		}.bind(this));
	}

	getNewLabel()
	{
		if (!this.newLabel)
		{
			this.newLabel = Dom.create({
				tag: 'div',
				props: {className: 'landing-ui-field-link-new-label'},
				text: BX.Landing.Loc.getMessage('LANDING_LINK_NEW_PAGE_LABEL')
			});
		}

		return this.newLabel;
	}

	showNewLabel()
	{
		BX.Dom.style(this.gridCenterCell, {
			position: 'relative',
			overflow: 'visible',
		});
		BX.Dom.append(this.getNewLabel(), this.gridCenterCell);
	}

	hideNewLabel()
	{
		BX.Dom.style(this.gridCenterCell, 'overflow', null);
		BX.Dom.remove(this.getNewLabel());
	}

	/**
	 * Sets value
	 * @param {object|string} value
	 * @param {boolean} [preventEvent] - Prevents onChange event
	 */
	setValue(value, preventEvent)
	{
		if (Type.isObject(value) && !Type.isNil(value))
		{
			this.disableEdit();
			this.setEditPrevented(true);
			this.input.innerHTML = "";
			Dom.append(value, this.input);
			const dataSet = value['dataset'];
			this.value = dataSet.placeholder;
			this.dynamic = dataSet.dynamic;

			if (this.value === '#landing0')
			{
				this.showNewLabel();
			}
			else
			{
				this.hideNewLabel();
			}

			if (!preventEvent)
			{
				this.onInputHandler(this.input.innerText);
			}
		}
		else if (!Type.isNil(value))
		{
			this.setEditPrevented(false);
			this.input.innerText = this.getInputInnerText(value);
			this.value = null;
			this.dynamic = null;
			this.hideNewLabel();
		}

		if (!preventEvent)
		{
			if (Type.isString(this.value))
			{
				this.getPlaceholderData(this.value)
					.then(function(data) {
						this.onValueChangeHandler(data);
					}.bind(this))
					.catch(function() {

					});
				return;
			}

			this.onValueChangeHandler(null);
		}
	}

	/**
	 * Gets dynamic data
	 * @return {?object}
	 */
	getDynamic()
	{
		return this.dynamic;
	}

	/**
	 * Gets value
	 * @return {string}
	 */
	getValue()
	{
		let valueText = this.value ? this.value : this.input.innerText;
		const selectedHrefType = this.getSelectedHrefType();

		this.validateValue(valueText);
		this.prepareInputField(this.hrefTypeSwithcer.getValue(), valueText);

		if (valueText === '')
		{
			if (
				selectedHrefType === 'catalog'
				|| selectedHrefType === 'landing'
			)
			{
				return '';
			}
			return LinkUrl.TYPE_HREF_START;
		}

		if (
			selectedHrefType === LinkUrl.TYPE_HREF_SKYPE
			&& !valueText.includes(this.typePostfix.skype)
		)
		{
			valueText = valueText + this.typePostfix.skype;
		}

		if (valueText.startsWith(selectedHrefType))
		{
			return valueText;
		}

		if (!Type.isUndefined(this.constantType))
		{
			if (this.constantType === LinkUrl.TYPE_CATALOG)
			{
				if (
					this.matchers.catalogElement.test(valueText)
					|| this.matchers.catalogSection.test(valueText)
					|| this.matchers.catalog.test(valueText)
					|| this.matchers.element.test(valueText)
				)
				{
					return valueText;
				}
				return '';
			}
			if (this.constantType === LinkUrl.TYPE_PAGE)
			{
				return LinkUrl.TYPE_HREF_PAGE + valueText;
			}
		}

		return selectedHrefType + valueText;
	}

	/**
	 * Gets value text
	 * @return {string}
	 */
	getValueText()
	{
		return this.value ? this.value : this.input.innerText;
	}

	validateValue(value)
	{
		if (value.indexOf(':') !== -1)
		{
			value = value.slice(value.indexOf(':') + 1);
		}
		const setRegs = [];
		setRegs['phoneExtended'] = /(^[\d+][\d-\s]{3,25}\d$)|#crmPhone\d+/;
		setRegs['phone'] = /^[\d+][\d-\s]{3,25}\d$/;
		setRegs['mail'] = /^\S+@\S+[.]\S+$/i;
		setRegs['skype'] = /^[a-z\d-.:]{6,32}$/i;
		const type = this.hrefTypeSwithcer.getValue();
		const data = this.getTypeData(type);
		let readyToSave = true;
		if (data.needValidate)
		{
			let reg;
			switch (type)
			{
				case LinkUrl.TYPE_HREF_TEL:
					reg = setRegs['phoneExtended'];
					break;
				case LinkUrl.TYPE_HREF_SMS:
					reg = setRegs['phone'];
					break;
				case LinkUrl.TYPE_HREF_MAILTO:
					reg = setRegs['mail'];
					break;
				case LinkUrl.TYPE_HREF_SKYPE:
					reg = setRegs['skype'];
					break;
			}
			if (reg)
			{
				if (value.length > 0)
				{
					const isValid = reg.test(value);
					if (isValid)
					{
						Dom.removeClass(this.gridCenterCell, "--validate-incorrect");
						Dom.addClass(this.gridCenterCell, "--validate-correct");
					}
					else
					{
						Dom.removeClass(this.gridCenterCell, "--validate-correct");
						Dom.addClass(this.gridCenterCell, "--validate-incorrect");
						readyToSave = false;
					}
				}
				else
				{
					Dom.removeClass(this.gridCenterCell, "--validate-correct");
					Dom.removeClass(this.gridCenterCell, "--validate-incorrect");
				}
			}
		}
		else
		{
			Dom.removeClass(this.gridCenterCell, "--validate-correct");
			Dom.removeClass(this.gridCenterCell, "--validate-incorrect");
		}
		this.emit('readyToSave',
			{
				readyToSave: readyToSave,
			});
	}

	prepareInputField(hrefType, inputValue)
	{
		//if empty field
		const allowedHrefTypes = [
			LinkUrl.TYPE_HREF_PAGE,
			LinkUrl.TYPE_HREF_BLOCK,
			LinkUrl.TYPE_HREF_CRM_FORM,
			LinkUrl.TYPE_HREF_FILE,
			LinkUrl.TYPE_HREF_USER,
			LinkUrl.TYPE_HREF_PRODUCT,
			LinkUrl.TYPE_CATALOG,
			LinkUrl.TYPE_PAGE,
		];
		if (inputValue === '' && allowedHrefTypes.includes(hrefType))
		{
			Dom.addClass(this.input, "landing-ui-field-input-empty");
		}
		else
		{
			Dom.removeClass(this.input, "landing-ui-field-input-empty");
		}
	}

	getInputInnerText(value)
	{
		return this.prepareInputInnerText(value.toString().trim());
	}

	prepareInputInnerText(value)
	{
		if (
			this.getSelectedHrefType() === LinkUrl.TYPE_HREF_SKYPE
			&& value.includes(this.typePostfix.skype)
		)
		{
			value = value.replace(this.typePostfix.skype, '');
		}
		return value;
	}
}
