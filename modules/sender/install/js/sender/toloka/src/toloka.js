import Autocomplete from './autocomplete/autocomplete';
import { Popup } from 'main.popup';

export class Toloka
{
	#page = BX.Sender.Page;
	#helper = BX.Sender.Helper;
	#context;
	#actionUri;
	#isFrame;
	#prettyDateFormat;
	#isSaved;
	#isRegistered;
	#isOutside;
	#mess;
	#letterTile;
	#selectorNode;
	#editorNode;
	#titleNode;
	#loginNode;
	#formNode;
	#oauthCodeNode;
	#filterNode;
	#filterData;
	#filterId;
	#filter;
	#isAvailable;
	#ajaxAction;
	#messageFields = null;
	#templateChangeButton;
	#buttonsNode;
	#templateNameNode;
	#templateTypeNode;
	#templateIdNode;
	#templateData;
	#REGION_BY_IP = 'REGION_BY_IP';
	#REGION_BY_PHONE = 'REGION_BY_PHONE';

	constructor()
	{
	}

	static create(settings)
	{
		const self = new Toloka();
		self.initialize(settings);
		return self;
	}

	bindEvents()
	{
		this._expireInNode.addEventListener('change', this.validateRequiredFields.bind(this));
		if (BX.Sender.Template && BX.Sender.Template.Selector)
		{
			const selector = BX.Sender.Template.Selector;
			BX.addCustomEvent(selector, selector.events.templateSelect, this.onTemplateSelect.bind(this));
			BX.addCustomEvent(selector, selector.events.selectorClose, this.closeTemplateSelector.bind(this));
		}

		if(this._saveBtn)
		{
			BX.bind(
				this._saveBtn,
				'click',
				this.applyChanges.bind(this)
			);
		}

		if (this.#templateChangeButton)
		{
			BX.bind(this.#templateChangeButton, 'click', this.showTemplateSelector.bind(this));
		}

		if (this.#isSaved)
		{
			top.BX.onCustomEvent(top, 'sender-letter-edit-change', [this.letterTile]);
			this.#page.slider.close();

			if (this.#isOutside)
			{
				BX.UI.Notification.Center.notify({
					content: this.#mess.outsideSaveSuccess,
					autoHideDelay: 5000
				});
			}
		}

		this.initWidget();

		const filter = this.getFilter();
		filter.getAddPresetButton().style.display = 'none';

		filter.getPreset().getPresets().forEach(preset => {
			preset.style.display = 'none';
		})

		BX.bind(filter.getResetButton(), 'click', this.reInitAddressWidget.bind(this));

		const clearFilterBtn = document.querySelector('.main-ui-delete');
		BX.bind(clearFilterBtn, 'click', this.reInitAddressWidget.bind(this));
	}

	initialize(params)
	{
		this.#context = BX(params.containerId);
		this.#filterData = [];
		this.#filterData[this.#REGION_BY_IP] = {
			region: []
		};
		this.#filterData[this.#REGION_BY_PHONE] = {
			region: []
		};

		this.#filterId = 'toloka-filter-connector';
		this.#filterNode = document.getElementById(`${this.#filterId}_search_container`);
		this.#filter = this.getFilter();
		this.#templateChangeButton = BX('SENDER_TOLOKA_BUTTON_CHANGE');
		this.#helper.changeDisplay(this.#templateChangeButton, false);

		this.#actionUri = params.actionUri;
		this.#ajaxAction = new BX.AjaxAction(this.#actionUri);
		this.#isFrame = params.isFrame || false;
		this.#prettyDateFormat = params.prettyDateFormat;
		this.#isSaved = params.isSaved || false;
		this.#isRegistered = params.isRegistered || false;
		this.#isOutside = params.isOutside || false;
		this.#isAvailable = params.isAvailable || true;
		this.#mess = params.mess;
		this.#letterTile = params.letterTile || {};
		this.#templateData = [];
		this.#messageFields = this.objectKeysToLowerCase(JSON.parse(params.preset));
		this.optionData = [];

		this.prepareNodes();
		this.buildDispatchNodes();

		this._filterNode = [];
		this._regionInput = [];
		this._autocomplete = [];
		this.bindEvents();

		this.#helper.titleEditor.init({
			dataNode: this.#titleNode,
			disabled: false,
			defaultTitle: this.getPatternTitle(this.#mess.name)
		});

		this.#page.initButtons();

		if (this.isMSBrowser())
		{
			this.#context.classList.add('bx-sender-letter-ms-ie');
		}

		if (!this.#isRegistered)
		{
			this.#loginNode.style = '';
			this.#formNode.style = 'display:none;';
		}
	}

	prepareNodes()
	{
		this.#selectorNode = this.#helper.getNode('template-selector', this.#context);
		this.#editorNode = this.#helper.getNode('editor', this.#context);
		this.#titleNode = this.#helper.getNode('title', this.#context);
		this.#loginNode = this.#helper.getNode('login', this.#context);
		this.#formNode = this.#helper.getNode('sender-toloka-form', this.#context);
		this.#oauthCodeNode = this.#helper.getNode('toloka-oauth-code', this.#context);

		this.#buttonsNode = this.#helper.getNode('letter-buttons', this.#context);
		this.#templateNameNode = this.#helper.getNode('template-name', this.#editorNode);
		this.#templateTypeNode = this.#helper.getNode('template-type', this.#editorNode);
		this.#templateIdNode = this.#helper.getNode('template-id', this.#editorNode);

		this._projectNode = document.getElementById('CONFIGURATION_PROJECT_ID');
		this._poolNode = document.getElementById('CONFIGURATION_POOL_ID');
		this._taskSuiteNode = document.getElementById('CONFIGURATION_TASK_SUITE_ID');
		this._descriptionNode = document.getElementById('CONFIGURATION_DESCRIPTION');
		this._instructionNode = document.getElementById('CONFIGURATION_INSTRUCTION');
		this._tasksNode = document.getElementById('CONFIGURATION_TASKS');
		this._overlapNode = document.getElementById('CONFIGURATION_OVERLAP');
		this._adultContentNode = document.getElementById('CONFIGURATION_ADULT_CONTENT');
		this._priceNode = document.getElementById('CONFIGURATION_PRICE');
		this._expireInNode = document.getElementById('CONFIGURATION_EXPIRE_IN');
		this._saveBtn = document.getElementById('ui-button-panel-save');


		this._projectNode.parentNode.parentNode.style = 'display:none';
		this._poolNode.parentNode.parentNode.style = 'display:none';
		this._taskSuiteNode.parentNode.parentNode.style = 'display:none';
	}

	reInitAddressWidget()
	{
		if(this._filterNode[this.#REGION_BY_IP] && this._autocomplete[this.#REGION_BY_IP])
		{
			this._autocomplete[this.#REGION_BY_IP].removeAutocompleteNode();
			this._autocomplete[this.#REGION_BY_IP] = null;
		}

		if(this._filterNode[this.#REGION_BY_PHONE] && this._autocomplete[this.#REGION_BY_PHONE])
		{
			this._autocomplete[this.#REGION_BY_PHONE].removeAutocompleteNode();
			this._autocomplete[this.#REGION_BY_PHONE] = null;
		}

		this.initWidget();
	}

	initWidget()
	{
		if (this.#filterNode)
		{
			BX.bind(
				this.#filterNode,
				'click',
				this.initAddressWidget.bind(this, this.#REGION_BY_IP)
			);
			BX.bind(
				this.#filterNode,
				'click',
				this.initAddressWidget.bind(this, this.#REGION_BY_PHONE)
			);
			BX.bind(
				this.getFilter().getPopup().popupContainer,
				'click',
				this.initAddressWidget.bind(this, this.#REGION_BY_IP)
			);
			BX.bind(
				this.getFilter().getPopup().popupContainer,
				'click',
				this.initAddressWidget.bind(this, this.#REGION_BY_PHONE)
			);
		}
	}

	initAddressWidget(name, event)
	{
		if(event.target && this.getFilter().getSearch().isSquareRemoveButton(event.target))
		{
			this.reInitAddressWidget();
		}


		this._filterNode[name] = document.querySelectorAll(`.main-ui-filter-field-container-list > div[data-name=${name}]`)[0];

		if(!this._filterNode[name])
		{
			if(this._autocomplete[name])
			{
				this._autocomplete[name].removeAutocompleteNode();
				this._autocomplete[this.#REGION_BY_IP] = null;
			}

			return;
		}

		if (this._autocomplete[name])
		{
			return;
		}

		const self = this;
		this.optionData[name] = this.optionData[name] || [];

		this._autocomplete[name] = new Autocomplete(this._filterNode[name], {
			options: this.optionData[name],
			multiple: true,
			autocomplete: true,
			onChange: (value, preparedValue) => {
				self.#filterData[name] = value;
				this.#filter.getFieldByName(name).ITEMS = preparedValue;
				this.#filter.getFieldByName(name).VALUE = preparedValue;
			}
		});
		this._regionInput[name] = document.querySelectorAll(`input[data-name=autocomplete-${name}]`)[0];

		BX.bind(
			this._regionInput[name],
			'keyup',
			this.getLocationList.bind(this, name)
		);
	}

	register()
	{
		const self = this;
		this.#ajaxAction.request({
			action: 'registerOAuth',
			onsuccess: (response) => {
				self.#loginNode.style = 'display:none;';
				self.#formNode.style = '';
			},
			data: { 'access_code': this.#oauthCodeNode.value }
		});
	}

	isMSBrowser()
	{
		return window.navigator.userAgent.match(/(Trident\/|MSIE|Edge\/)/) !== null;
	}

	getPatternTitle(name)
	{
		return this.#helper.replace(
			this.#mess.patternTitle,
			{
				'name': name,
				'date': BX.date.format(this.#prettyDateFormat)
			}
		);
	}

	onTemplateSelect(template)
	{
		if (this.#templateNameNode)
		{
			this.#templateNameNode.textContent = template.name;
		}
		if (this.#templateTypeNode)
		{
			this.#templateTypeNode.value = template.type;
		}
		if (this.#templateIdNode)
		{
			this.#templateIdNode.value = template.code;
		}

		this.#messageFields = template.messageFields;

		this.buildDispatchNodes();
		this.#titleNode.value = this.getPatternTitle(template.name);

		BX.fireEvent(this.#titleNode, 'change');

		this.closeTemplateSelector();
		window.scrollTo(0, 0);
	}

	buildDispatchNodes()
	{
		const self = this;
		this.#helper.getNodes('dispatch', this.#context).forEach(node => {
			const code = node.getAttribute('data-code');
			for(const field in self.#messageFields)
			{
				if(!self.#messageFields.hasOwnProperty(field))
				{
					continue;
				}

				const data = self.#messageFields[field];
				if (data.code === code && node.innerHTML.length === 0)
				{
					node.innerHTML = data.value;
				}

				self.#templateData[data.code] = data.value;
			}
		});
	}

	closeTemplateSelector()
	{
		this.changeDisplayingTemplateSelector(false);
	}

	showTemplateSelector()
	{
		this.changeDisplayingTemplateSelector(true);
	}

	changeDisplayingTemplateSelector(isShow)
	{
		const classShow = 'bx-sender-letter-show';
		const classHide = 'bx-sender-letter-hide';
		this.#helper.changeClass(this.#selectorNode, classShow, isShow);
		this.#helper.changeClass(this.#selectorNode, classHide, !isShow);

		this.#helper.changeClass(this.#editorNode, classShow, !isShow);
		this.#helper.changeClass(this.#editorNode, classHide, isShow);

		this.#helper.changeDisplay(this.#templateChangeButton, !isShow);
		this.#helper.changeDisplay(this.#buttonsNode, !isShow);

		isShow ? this.#helper.titleEditor.disable() : this.#helper.titleEditor.enable();
	}

	objectKeysToLowerCase(origObj)
	{
		const self = this;
		if(origObj === null)
		{
			return origObj;
		}

		return Object.keys(origObj).reduce(function(newObj, key) {
			const val = origObj[key];
			newObj[key.toLowerCase()] = (typeof val === 'object') ? self.objectKeysToLowerCase(val) : val;
			return newObj;
		}, {})
	}

	getLocationList(name)
	{
		if (this._regionInput[name].value.length < 3)
		{
			return;
		}
		this.usedWords = this.usedWords || [];
		const value = this._regionInput[name].value;

		if(this.usedWords.includes(value))
		{
			return;
		}
		this.usedWords.push(value);

		const self = this;
		this.#ajaxAction.request({
			action: 'getGeoList',
			data: {
				name: value
			},
			onsuccess: response => {
				if(!this.optionData[name])
				{
					this.optionData[name] = [];
				}
				for (const value in response)
				{
					const responseData = response[value];
					if (typeof responseData === 'object' && 'id' in responseData)
					{
						this.optionData[name].push(responseData);
					}
				}

				if (self._autocomplete[name])
				{

					this.optionData[name] = this.optionData[name].reduce((acc, current) => {
						const x = acc.find(item => item.id === current.id);
						if (!x) {
							return acc.concat([current]);
						} else {
							return acc;
						}
					}, []);
					self._autocomplete[name].setOptions(this.optionData[name]);
				}

			}
		});

	}

	validateRequiredFields()
	{
		let success = true;

		[
			this._expireInNode,
			this._priceNode,
			this._tasksNode
		].every(element => {
			if(!this.validateField(element))
			{
				success = false;
				return false;
			}
		});

		if(!success)
		{
			this.removeLoader();
		}
		return success;
	}

	removeLoader()
	{
		this._saveBtn.classList.remove("ui-btn-wait");
	}

	validateField(field)
	{
		if(!this._validatorPopup)
		{
			this._validatorPopup = new Popup({
				id: "sender-toloka-validator",
				content: `${this.#mess.required}`,
			});
		}

		if(!field.value)
		{
			this._validatorPopup.setBindElement(field);
			this._validatorPopup.show();
			field.classList.add("bx-sender-form-control-danger");
			field.scrollIntoView();
			return false
		}
		this._validatorPopup.close();

		field.classList.remove("bx-sender-form-control-danger");
		return true;
	}

	createProject()
	{
		if(!this.validateRequiredFields())
		{
			return;
		}
		const input_key = Object.keys(this.#templateData['INPUT_VALUE'])[0];
		const output_key = Object.keys(this.#templateData['OUTPUT_VALUE'])[0];
		this.#ajaxAction.request({
			action: 'createProject',
			data: {
				id: this._projectNode.value,
				name: this.#titleNode.value,
				description: this._descriptionNode.value,
				instruction: this._instructionNode.value,
				input_type: this.#templateData['INPUT_VALUE'][input_key],
				input_identificator: input_key,
				output_type: this.#templateData['OUTPUT_VALUE'][output_key],
				output_identificator: output_key,
				markup: this.#templateData['PRESET'].template,
				script: this.#templateData['PRESET'].js,
				styles: this.#templateData['PRESET'].css
			},
			onsuccess: response => {
				this._projectNode.value = response.id;
				this.createPool(response.id);
			},
			onfailure: response => {
				this.removeLoader();
			}
		});
	}

	createPool(projectId)
	{
		const input_key = Object.keys(this.#templateData['INPUT_VALUE'])[0]
		this.#ajaxAction.request({
			action: 'createPool',
			data: {
				id: this._poolNode.value,
				task_suite_id: this._taskSuiteNode.value,
				project_id: projectId,
				private_name: this.#titleNode.value,
				public_description: this._descriptionNode.value,
				may_contain_adult_content: this._adultContentNode.checked,
				reward_per_assignment: this._priceNode.value,
				will_expire: this._expireInNode.value,
				overlap: this._overlapNode.value,
				tasks: this._tasksNode.value,
				identificator: input_key,
				filter: this.#filterData
			},
			onsuccess: response => {
				this._poolNode.value = response.pool_id;
				this._taskSuiteNode.value = response.id;

				const form = this.#context.getElementsByTagName('form');

				if (form && form[0])
				{
					form[0].appendChild(BX.create('input', {
						attrs: {
							type: "hidden",
							name: "apply",
							value: "Y"
						}
					}));

					form[0].submit();
				}
			},
			onfailure: response => {
				this.removeLoader();
			}
		});
	}

	applyChanges(event)
	{
		if (!this.#isAvailable)
		{
			BX.UI.InfoHelper.show('limit_crm_marketing_toloka');
			return;
		}

		this.createProject();
	}

	getFilter()
	{
		const filter = BX.Main.filterManager.getById(this.#filterId);
		if (!filter || !(filter instanceof BX.Main.Filter))
		{
			return null;
		}

		return filter;
	}
}