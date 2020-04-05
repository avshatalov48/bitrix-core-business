(function() {

	'use strict';

	BX.namespace('BX.Landing');

	/**
	 * For change domain name.
	 */
	BX.Landing.EditDomainForm = function (node, params)
	{
		this.domain = node.querySelector('.ui-domain-input-btn-js');
		this.postfix = node.querySelectorAll('.ui-postfix');
		this.domains = node.querySelectorAll('.ui-domainname');
		this.content = params.content || '';
		this.messages = params.messages || {};
		this.popup = BX.Landing.UI.Tool.ActionDialog.getInstance();

		BX.bind(this.domain, 'click', BX.delegate(this.editDomain, this));
	};
	BX.Landing.EditDomainForm.prototype =
	{
		editDomain: function (event)
		{
			event.stopPropagation();

			var promise = this.popup.show({
				title: this.messages.title,
				content: this.content,
				contentColor: 'grey'
			});
			this.content.style.display = 'block';

			promise
				.then(function()
					{
						var domainName = '';
						for (var i = 0, c = this.postfix.length; i < c; i++)
						{
							if (
								this.postfix[i].checked &&
								typeof this.domains[i] !== 'undefined'
							)
							{
								this.domains[i].value = BX.util.trim(this.domains[i].value);
								if (this.domains[i].value !== '')
								{
									domainName = this.domains[i].value + this.postfix[i].value;
								}
							}
						}
						if (domainName === '')
						{
							alert(this.messages.errorEmpty);
							this.editDomain(event);
						}
						else
						{
							BX('ui-domainname-title').textContent = domainName;
							BX('ui-domainname-text').value = domainName;
						}
					}.bind(this),
					function()
					{
					}
				);
		}
	};

	/**
	 * For edit title.
	 */
	BX.Landing.EditTitleForm = function (node, additionalWidth, isEventTargetNode, display)
	{
		this.btn = node.querySelector('.ui-title-input-btn-js');
		this.label = node.querySelector('.ui-editable-field-label-js');
		this.input = node.querySelector('.ui-editable-field-input-js');
		this.additionalWidth = additionalWidth || 0;
		this.input.IsWidthSet = false;
		this.display = display;

		this.hideInput = this.hideInput.bind(this);
		this.showInput = this.showInput.bind(this);

		if(isEventTargetNode) {
			BX.bind(node, 'click', this.showInput);
		} else {
			BX.bind(this.btn, 'click', this.showInput);
		}

		this.input.setAttribute("data-height", this.label.offsetHeight);
	};

	BX.Landing.EditTitleForm.prototype =
	{
		showInput : function (event)
		{
			event.stopPropagation();

			if(!this.input.IsWidthSet) {
				this.input.style.width = this.label.offsetWidth + this.additionalWidth + 17 + 'px';
			}

			if(this.input.tagName === 'TEXTAREA')
			{
				this.input.style.height = this.input.getAttribute("data-height") + 'px';
			}
			this.label.style.display = 'none';
			this.btn.style.display = 'none';
			this.input.style.display = 'block';

			this.input.focus();

			this.input.IsWidthSet = true;

			BX.bind(document, 'click', this.hideInput);
		},
		hideInput : function (event)
		{
			if(event.target === this.input)
				return;

			this.label.textContent = this.input.value;

			if (this.display) {
				this.label.style.display = 'inline-block';
			} else {
				this.label.style.display = 'inline';
			}

			this.input.style.display = 'none';
			this.btn.style.display = 'inline-block';

			this.input.IsWidthSet = false;
			this.input.setAttribute("data-height", this.label.offsetHeight);

			BX.unbind(document, 'click', this.hideInput);
		}
	};

	/**
	 * For additional fields.
	 */
	BX.Landing.ToggleFormFields = function (node)
	{
		this.form = node;
		this.toggleBtn = node.querySelector('.landing-form-collapse-block-js');
		this.formInner = node.querySelector('.landing-form-inner-js');
		this.tableWparp = node.querySelector('.landing-form-table-wrap-js');
		this.startHeight = 0;
		this.endHeight = 0;
		this.isHidden = true;

		this.clickHandler = this.clickHandler.bind(this);
		this.setHeightAuto = this.setHeightAuto.bind(this);
		this.removeClassName = this.removeClassName.bind(this);

		BX.bind(this.toggleBtn, 'click', this.clickHandler);
	};
	BX.Landing.ToggleFormFields.prototype =
	{
		showRows : function ()
		{
			this.startHeight = this.formInner.offsetHeight;
			this.formInner.style.height = this.startHeight + 'px';
			this.form.classList.add('landing-form-collapsed-open');
			this.endHeight = this.tableWparp.offsetHeight + parseInt(BX.style(this.tableWparp, 'marginBottom'));
			this.formInner.style.height = this.endHeight + 'px';

			BX.bind(this.formInner, 'transitionend', this.setHeightAuto);

			this.isHidden = false;
		},
		closeRows  : function ()
		{
			this.formInner.style.height = this.endHeight + 'px';

			setTimeout(function () {
				this.formInner.style.height = this.startHeight + 'px';
			}.bind(this),70);

			BX.bind(this.formInner, 'transitionend', this.removeClassName);

			this.isHidden = true;
		},
		clickHandler : function ()
		{
			if(this.isHidden)
				this.showRows();
			else
				this.closeRows();
		},
		setHeightAuto : function ()
		{
			this.formInner.style.height = 'auto';
			BX.unbind(this.formInner, 'transitionend', this.setHeightAuto);
		},
		removeClassName : function ()
		{
			this.form.classList.remove('landing-form-collapsed-open');
			BX.unbind(this.formInner, 'transitionend', this.removeClassName);
		}
	};

	/**
	 * Colorpicker.
	 */
	BX.Landing.ColorPicker = function(node)
	{
		this.picker = new BX.ColorPicker({
			bindElement: node,
			popupOptions: {angle: false, offsetTop: 5},
			onColorSelected: this.onColorSelected.bind(this),
			colors: this.setColors()
		});

		this.colorPickerNode = node;

		this.colorIcon = node.querySelector('.ui-colorpicker-color-js');
		this.clearBtn = node.querySelector('.ui-colorpicker-clear');
		this.input = node.querySelector('.landing-colorpicker-inp-js');

		BX.bind(this.colorPickerNode, 'click', this.show.bind(this));
		BX.bind(this.clearBtn, 'click', this.clear.bind(this));

	};
	BX.Landing.ColorPicker.prototype =
	{
		onColorSelected : function (color)
		{
			this.colorPickerNode.classList.add('ui-colorpicker-selected');
			this.colorIcon.style.backgroundColor = color;
			this.input.value = color;
		},
		show : function (event)
		{
			if(event.target == this.clearBtn)
				return;

			this.picker.open();
		},
		clear : function ()
		{
			this.colorPickerNode.classList.remove('ui-colorpicker-selected');
			this.input.value = '';
			this.picker.setSelectedColor(null);
		},
		setColors :function () {
			return [
				["#f5f5f5", "#eeeeee", "#e0e0e0", "#9e9e9e", "#757575", "#616161", "#212121"],
				["#cfd8dc", "#b0bec5", "#90a4ae", "#607d8b", "#546e7a", "#455a64", "#263238"],
				["#d7ccc8", "#bcaaa4", "#a1887f", "#795548", "#6d4c41", "#5d4037", "#3e2723"],
				["#ffccbc", "#ffab91", "#ff8a65", "#ff5722", "#f4511e", "#e64a19", "#bf360c"],
				["#ffe0b2", "#ffcc80", "#ffb74d", "#ff9800", "#fb8c00", "#f57c00", "#e65100"],
				["#ffecb3", "#ffe082", "#ffd54f", "#ffc107", "#ffb300", "#ffa000", "#ff6f00"],
				["#fff9c4", "#fff59d", "#fff176", "#ffeb3b", "#fdd835", "#fbc02d", "#f57f17"],
				["#f0f4c3", "#e6ee9c", "#dce775", "#cddc39", "#c0ca33", "#afb42b", "#827717"],
				["#dcedc8", "#c5e1a5", "#aed581", "#8bc34a", "#7cb342", "#689f38", "#33691e"],
				["#c8e6c9", "#a5d6a7", "#81c784", "#4caf50", "#43a047", "#388e3c", "#1b5e20"],
				["#b2dfdb", "#80cbc4", "#4db6ac", "#009688", "#00897b", "#00796b", "#004d40"],
				["#b2ebf2", "#80deea", "#4dd0e1", "#00bcd4", "#00acc1", "#0097a7", "#006064"],
				["#b3e5fc", "#81d4fa", "#4fc3f7", "#03a9f4", "#039be5", "#0288d1", "#01579b"],
				["#bbdefb", "#90caf9", "#64b5f6", "#2196f3", "#1e88e5", "#1976d2", "#0d47a1"],
				["#c5cae9", "#9fa8da", "#7986cb", "#3f51b5", "#3949ab", "#303f9f", "#1a237e"],
				["#d1c4e9", "#b39ddb", "#9575cd", "#673ab7", "#5e35b1", "#512da8", "#311b92"],
				["#e1bee7", "#ce93d8", "#ba68c8", "#9c27b0", "#8e24aa", "#7b1fa2", "#4a148c"],
				["#f8bbd0", "#f48fb1", "#f06292", "#e91e63", "#d81b60", "#c2185b", "#880e4f"],
				["#ffcdd2", "#ef9a9a", "#e57373", "#f44336", "#e53935", "#d32f2f", "#b71c1c"]
			].map(function(item, index, arr) {
				return arr.map(function(row) {
					return row[index];
				});
			})
		}
	};

	/**
	 * Some additional JS.
	 */
	BX.Landing.CustomFields = function (list)
	{
		list.forEach(function(item)
		{
			BX.bind(item.field, 'keyup', function ()
			{
				if(item.length)
				{
					if(item.field.value.length <= item.length)
					{
						item.node.textContent = item.field.value;
					}
					else
					{
						item.node.textContent = item.field.value.substring(0, item.length);
					}
				}
				else {
					item.node.textContent = item.field.value;
				}
			});
		})
	};

	/**
	 * Domain name popup.
	 */
	BX.Landing.DomainNamePopup = function(params)
	{
		var isAvailableDomain = null;
		var isDeletedDomain = null;
		var messages = params.messages || {};
		var dialog = new BX.Landing.EditDomainForm(BX('ui-editable-domain'), {
			messages: {
				title: messages.title || '',
				errorEmpty: messages.errorEmpty || ''
			},
			content: BX('ui-editable-domain-content')
		});
		BX.addCustomEvent(dialog.popup.popup, 'onPopupShow', function(obj)
		{
			var domainInput = obj.contentContainer.querySelectorAll('.ui-domainname');
			var textNode1 = obj.contentContainer.querySelector('#landing-form-domain-name-text');
			var textNode2 = obj.contentContainer.querySelector('#landing-form-domain-any-name-text');
			var domainRadioBtn = obj.contentContainer.querySelectorAll('.ui-radio');
			var saveBtn = BX('action_dialog_confirm');

			var onKeyUp = function(event) {
				handlerDomainName(event.target.value, event.target);
			};

			var handlerDomainName = function(value, target) {

				var domainName = value;
				var postfix = BX.data(target, 'postfix');


				// fill instruction
				var fillInstruction = function(domainName)
				{
					var domainParts = domainName.split('.');
					var domainRe = /^(com|net|org)\.[a-z]{2}$/;

					textNode2.parentNode.style.display = 'none';

					textNode1.textContent = domainName ? domainName : 'landing.mydomain';

					if (
						(domainParts.length === 2) ||
						(domainParts.length === 3 && domainParts[0] === 'www') ||
						(domainParts.length === 3 && (domainParts[1] + '.' + domainParts[2]).match(domainRe))
					)
					{
						textNode2.parentNode.style.display = 'table-row';
						if ((domainParts.length === 3 && domainParts[0] === 'www'))
						{
							textNode2.textContent = domainParts[1] + '.' + domainParts[2];
						}
						else
						{

							textNode1.textContent = 'www.' + domainName;
							textNode2.textContent = domainName;
						}
					}

					textNode1.textContent = BX.util.trim(textNode1.textContent) + '.';
					textNode2.textContent = BX.util.trim(textNode2.textContent) + '.';
				};


				BX.ajax({
					url: '/bitrix/tools/landing/ajax.php?action=Domain::check',
					method: 'POST',
					data: {
						data: {
							domain: domainName + postfix,
							filter: {
								'!ID': params.domainId
							}
						},
						sessid: BX.message('bitrix_sessid')
					},
					dataType: 'json',
					onsuccess: function (data) {
						// fill instructions for custom domain
						if (
							//postfix === '' &&
							data.result &&
							data.result.domain
						)
						{
							fillInstruction(data.result.domain);
						}
						isAvailableDomain = data.result.available;
						isDeletedDomain = data.result.deleted;

						for (var i = 0, c = domainRadioBtn.length; i < c; i++)
						{
							if(domainRadioBtn[i].checked) {

								var currentInput = domainRadioBtn[i].nextElementSibling.querySelector(".ui-domainname");
								var domainStatus = domainRadioBtn[i].nextElementSibling.querySelector(".landing-site-name-status");

								var maxlength = currentInput.getAttribute('maxlength');
								var currentLength = data.result.domain.length;

								// check available symbols for subdomain
								if(domainRadioBtn[i].getAttribute('id') === "landing-domain-name-1"){
									var domain = currentInput.value;

									if(!(domain === "") && !(/^[\w_\-]+$/.test(domain))) {
										addDisableClass(currentInput);
										domainStatus.textContent = BX.message('LANDING_DOMAIN_INCORRECT');

										return;

									} else {
										removeDisableClass(currentInput);
										domainStatus.textContent = "";
									}
								}

								//check max length and availability domain name
								if(currentLength >= maxlength && maxlength !== null) {
									addDisableClass(currentInput);
									domainStatus.textContent = BX.message('LANDING_DOMAIN_LIMIT_LENGTH');
								} else {
									saveBtn.classList.remove('btn-disabled');

									if(currentInput.classList.contains("ui-domainname-unavailable")) {
										currentInput.classList.remove('ui-domainname-unavailable');
										domainStatus.textContent = '';
									}

									highlight(isAvailableDomain, isDeletedDomain, currentInput, domainStatus);

								}
							}
						}

					}
				});

			};
			// keyup domain name
			for (var i = 0, c = domainInput.length; i < c; i++) {

				var inp = domainInput[i];
				inp.addEventListener('keyup', BX.debounce(onKeyUp.bind(this), 300));

				inp.addEventListener('focus', function(event){
					event.target.parentNode.previousElementSibling.checked = true;
					var targetStatus = event.target.parentNode.querySelector(".landing-site-name-status");
					runDomainCheck(event.target, targetStatus);
				});

			}



			for (var a = 0, b = domainRadioBtn.length; a < b; a++) {
				domainRadioBtn[a].addEventListener('click', function(event){
					var targetInput = event.target.nextElementSibling.querySelector(".ui-domainname");
					var targetStatus = event.target.nextElementSibling.querySelector(".landing-site-name-status");
					runDomainCheck(targetInput, targetStatus);

				});
			}

			var runDomainCheck = function(targetInput, targetStatus)
			{
				findUnselectedItem();

				isAvailableDomain = null;
				isDeletedDomain = null;
				handlerDomainName(targetInput.value, targetInput);

				if(isAvailableDomain !== null && isDeletedDomain !== null) {
					highlight(isAvailableDomain, isDeletedDomain, targetInput, targetStatus);
				}

			};

			var highlight = function(isAvailableDomain, isDeletedDomain, item, status)
			{
				if(isAvailableDomain) {
					item.classList.add("ui-domainname-available");
					removeDisableClass(item);
					status.textContent = "";

				} else {
					if(isDeletedDomain) {
						status.textContent = BX.message('LANDING_DOMAIN_EXIST2');
					} else {
						status.textContent = BX.message('LANDING_DOMAIN_EXIST');
					}
					addDisableClass(item);
					item.classList.remove("ui-domainname-available");
				}

			};

			var findUnselectedItem = function()
			{

				for (var i = 0, c = domainRadioBtn.length; i < c; i++) {
					if(!(domainRadioBtn[i].checked)) {
						var uncheckedInput = domainRadioBtn[i].nextElementSibling.querySelector(".ui-domainname");
						var uncheckedStatus = domainRadioBtn[i].nextElementSibling.querySelector(".landing-site-name-status");
						resetHighlight(uncheckedInput, uncheckedStatus);
					}
				}
			};

			var resetHighlight = function(item, status)
			{
				item.classList.remove('ui-domainname-available');
				item.classList.remove('ui-domainname-unavailable');
				status.textContent = "";
			};

			var addDisableClass = function(item)
			{
				item.classList.add("ui-domainname-unavailable");
				saveBtn.classList.add('btn-disabled');
			};

			var removeDisableClass = function(item)
			{
				item.classList.remove("ui-domainname-unavailable");
				saveBtn.classList.remove('btn-disabled');
			};

			BX.fireEvent(inp, 'keyup');

			// domain type
			var inpList = obj.contentContainer.querySelectorAll('input.ui-domainname');
			for(var i=0; i<inpList.length; i++)
			{
				BX.bind(inpList[i], 'focus', function ()
				{
					this.parentNode.parentNode.querySelector('input').checked = true;
				})
			}
		});
	};

	/**
	 * Favicon change.
	 */
	BX.Landing.Favicon = function()
	{
		var editLink = BX('landing-form-favicon-change');
		var editInput = BX('landing-form-favicon-input');
		var editValue = BX('landing-form-favicon-value');
		var editForm = BX('landing-form-favicon-form');
		var editSrc = BX('landing-form-favicon-src');
		var editError = BX('landing-form-favicon-error');

		if (!editForm || !editInput ||!editLink)
		{
			return;
		}

		// open file dialog
		BX.bind(editLink, 'click', function(e)
		{
			BX.fireEvent(editInput, 'click');
			BX.PreventDefault(e);
		});
		// upload picture
		BX.bind(editInput, 'change', function(e)
		{
			BX.ajax.submitAjax(editForm, {
				method: 'POST',
				dataType: 'json',
				onsuccess: function (data) {
					if (
						data.type === 'success' &&
						typeof data.result !== 'undefined' &&
						data.result !== false
					)
					{
						editValue.value = data.result.id;
						editSrc.setAttribute('src', data.result.src);
					}
					else
					{
						editError.style.color = 'red';
					}
				}
			});
			BX.PreventDefault(e);
		});
	};

	/**
	 * Custom 404.
	 */
	BX.Landing.Custom404 = function()
	{
		var select = BX('landing-form-404-select');
		BX.bind(select, 'change', function ()
		{
			if(this.value === '')
			{
				this.parentNode.style.height = getComputedStyle(this.parentNode).height;
				BX('checkbox-404-use').checked = false;
			}
		});
		BX.bind(BX('checkbox-404-use'), 'change', function ()
		{
			if(!this.checked)
			{
				select.value = ''
			}
		});
	};

	/**
	 * Custom 503.
	 */
	BX.Landing.Custom503 = function()
	{
		var select = BX('landing-form-503-select');
		BX.bind(select, 'change', function ()
		{
			if(this.value === '')
			{
				this.parentNode.style.height = getComputedStyle(this.parentNode).height;
				BX('checkbox-503-use').checked = false;
			}
		});
		BX.bind(BX('checkbox-503-use'), 'change', function ()
		{
			if(!this.checked)
			{
				select.value = ''
			}
		});
	};

	/**
	 * Copyright on/off.
	 */
	BX.Landing.Copyright = function()
	{
		BX.bind(BX('checkbox-copyright'), 'change', function ()
		{
			var formAction = BX('landing-site-set-form').getAttribute('action');
			formAction = formAction.replace(/&feature_copyright=[YN]/, '');
			formAction += '&feature_copyright=' + (this.checked ? 'Y' : 'N');
			BX('landing-site-set-form').setAttribute('action', formAction)
		});
	};

	/**
	 * Layout.
	 */
	BX.Landing.Layout = function(params)
	{
		var layoutBlockContainer = document.querySelector('.landing-form-layout-block-container');
		var area = [];
		var layouts = document.querySelectorAll('.landing-form-layout-item');
		var detailLayoutContainer = document.querySelector('.landing-form-layout-detail');
		var layoutForm = document.querySelector('.landing-form-page-layout');
		layouts = Array.prototype.slice.call(layouts, 0);
		params.messages = params.messages || {};

		createBlocks(layouts[0].dataset.block);

		layouts.forEach(function (item)
		{
			item.addEventListener('click', handleLayoutClick.bind(this));
		});

		function handleLayoutClick(event) {
			var layoutItem = event.target.parentNode;

			var layoutItemSelected = document.querySelector('.landing-form-layout-item-selected');
			if(layoutItemSelected) {
				layoutItemSelected.classList.remove('landing-form-layout-item-selected');
			}

			changeLayout (layoutItem.dataset.block, layoutItem.dataset.layout);
		}

		function changeLayout(block, layout)
		{
			layoutForm.classList.remove('landing-form-page-layout-short');
			detailLayoutContainer.classList.remove('landing-form-layout-detail-hidden');

			createBlocks(block);

			if (typeof layout !== 'undefined')
			{
				changeLayoutImg(layout);
			}

			BX('layout-tplrefs').value = '';
		}

		if (typeof params.areasCount !== 'undefined')
		{
			changeLayout(params.areasCount, params.current);
		}

		function changeLayoutImg(layout)
		{

			var layoutDetail = document.querySelectorAll('.landing-form-layout-img');
			for (var i = 0; i < layoutDetail.length; i++)
			{
				if (layoutDetail[i].dataset.layout === layout)
				{
					layoutDetail[i].style.display = 'block';
				}
				else
				{
					layoutDetail[i].style.display = 'none';
				}
			}
		}

		function createBlocks(blocks)
		{
			var saveRefs = BX('layout-tplrefs').value.split(',');
			area = [];
			layoutBlockContainer.innerHTML = '';
			for (var i = 0; i < blocks; i++)
			{
				var block = BX.create('div', {
					attrs: {
						className: 'landing-form-layout-block-item'
					}
				});

				var numberBlock = i + 1;
				var linkContent = '';

				if (
					typeof saveRefs[i] !== 'undefined' &&
					saveRefs[i].indexOf(':') !== -1
				)
				{
					linkContent = parseInt(saveRefs[i].split(':')[1]);
					if (linkContent > 0)
					{
						linkContent = '#landing' + linkContent;
					}
					else
					{
						linkContent = '';
					}
				}

				var layoutField = new BX.Landing.UI.Field.LinkURL({
					title: params.messages.area + ' #' + numberBlock,
					content: linkContent,
					textOnly: true,
					disableCustomURL: true,
					disableBlocks: true,
					disallowType: true,
					enableAreas: true,
					allowedTypes: [
						BX.Landing.UI.Field.LinkURL.TYPE_PAGE
					],
					options: {
						siteId: params.siteId,
						landingId: params.landingId,
						filter: {
							'=TYPE': params.type
						}
					},
					onInput: function()
					{
						var refs = '';
						for (var i= 0, c = area.length; i < c; i++)
						{
							refs += (i+1) + ':' +
									(area[i].getValue() ? area[i].getValue().substr(8) : 0) +
									',';
						}
						BX('layout-tplrefs').value = refs;
					}
				});

				area[i] = layoutField;
				block.appendChild(layoutField.layout);
				layoutBlockContainer.appendChild(block);
			}
		}

		var tplCheck = BX('layout-tplrefs-check');

		tplCheck.addEventListener('click', handleCheckBoxClick.bind(this));

		function handleCheckBoxClick()
		{

				BX('layout-tplrefs').value = '';
				detailLayoutContainer.classList.add('landing-form-layout-detail--hidden');
				layoutForm.classList.add('landing-form-page-layout-short');

				var inputs = document.querySelectorAll('.layout-switcher');
				inputs = Array.prototype.slice.call(inputs, 0);

				inputs.forEach(function (item)
				{
					item.checked = false;
				});
		}

		var arrowContainer = document.querySelector('.landing-form-select-buttons');
		var layoutContainer = document.querySelector('.landing-form-list-inner');
		var layoutWithoutRight = BX('layout-radio-6');
		arrowContainer.addEventListener('click', handleArrowClick.bind(this));

		function handleArrowClick(event) {
			if(event.target.classList.contains('landing-form-select-next')) {
				layoutContainer.classList.add('landing-form-list-inner-prev');
			} else {
				layoutContainer.classList.remove('landing-form-list-inner-prev');
			}
		}

		if(layoutWithoutRight.checked) {
			layoutContainer.classList.add('landing-form-list-inner-prev');
		}
	};

	/**
	 * GA metrika.
	 */

	BX.Landing.Metrika = function()
	{
		var inputGa = BX('field-gacounter_counter-use');
		var inputGaClick = BX('field-gacounter_send_click-use');
		var inputGaShow = BX('field-gacounter_send_show-use');

		if(inputGa.value === '') {
			inputGaClick.disabled = true;
			inputGaShow.disabled = true;
		}

		inputGa.addEventListener('input', onInput.bind(this));

		function onInput() {
			if(inputGa.value === '') {
				inputGaClick.disabled = true;
				inputGaClick.checked = false;

				inputGaShow.disabled = true;
				inputGaShow.checked = false;
			} else {
				inputGaClick.disabled = false;
				inputGaShow.disabled = false;
			}
		}
	};

	/**
	 * Change save button.
	 */

	BX.Landing.SaveBtn = function(saveBtn)
	{
		saveBtn.addEventListener('click', changeSaveBtn.bind(this));

		function changeSaveBtn() {
			saveBtn.classList.add('ui-btn-clock');
			saveBtn.style.cursor = "default";
			saveBtn.style.pointerEvents = "none";
		}
	};

	/**
	 * Change iblock select.
	 */

	BX.Landing.IblockSelect = function()
	{
		this.section = BX("row_section_id");
		this.init(this.section);
	};

	BX.Landing.IblockSelect.prototype = {

		init: function(section) {
			if(!BX("settings_iblock_id").value) {
				section.classList.add("landing-form-field-section-hidden");
			} else {
				section.classList.remove("landing-form-field-section-hidden");
			}
		}
	}

})();