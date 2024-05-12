;if (!BX.getClass('BX.Bizproc.FieldType')) (function(BX)
{
	'use strict';
	BX.namespace('BX.Bizproc');

	var isMultiple = function(property)
	{
		return (property.Multiple === true)
	};

	const isSelectable = function(property)
	{
		return (property.AllowSelection !== false)
	};

	var toMultipleValue = function(value)
	{
		if (!BX.type.isArray(value))
		{
			return [value];
		}
		return value;
	};

	var normalizeDateValue = function(value)
	{
		if (BX.Type.isArray(value))
		{
			return value.map((dateValue) => {
				return dateValue ? dateValue.replace(/(\s\[-?[0-9]+\])$/, '') : ''
			});
		}

		return value ? value.replace(/(\s\[-?[0-9]+\])$/, '') : '';
	};

	const normalizeTimeValue = (value, property) => {
		const result = toMultipleValue(value);
		if (isMultiple(property))
		{
			return result;
		}

		return result.join(',');
	};

	var getOptions = function (property)
	{
		return property.Options ? property.Options : {};
	};

	var getPlaceholder = function (property)
	{
		let placeholder = '';
		if (!BX.Type.isUndefined(property.Placeholder))
		{
			placeholder = String(property.Placeholder);
		}

		return placeholder;
	}

	var FieldType = {
		isBaseType: function(type)
		{
			switch (type)
			{
				case 'bool':
				case 'UF:boolean':
				case 'select':
				case 'internalselect':
				case 'date':
				case 'UF:date':
				case 'datetime':
				case 'text':
				case 'int':
				case 'double':
				case 'string':
				case 'user':
				case 'time':
					return true;
			}

			return false;
		},

		/**
		 * @private
		 * @param documentType
		 * @param {Array<{
		 *     property: Object,
		 *     fieldName: string,
		 *     value: any,
		 *     controlId: string,
		 * }>} controlsData
		 * @param {'public' | 'designer' | ''} renderMode
		 */
		renderControlCollection: function(documentType, controlsData, renderMode)
		{
			let renderedControls = {};
			if (BX.Type.isArrayFilled(controlsData))
			{
				const chunks = [];
				const chunkSize = 100;

				let controlIndex = 0;
				while (controlIndex < controlsData.length)
				{
					const afterLastItemIndex = Math.min(controlIndex + chunkSize, controlsData.length);

					chunks.push(controlsData.slice(controlIndex, afterLastItemIndex));
					controlIndex = afterLastItemIndex;
				}

				const isPublicMode = !renderMode || renderMode === 'public';
				const promises = [];
				for (const controlsChunk of chunks)
				{
					const controls = this.renderControlCollectionInner(documentType, controlsChunk, renderMode);
					renderedControls = { ...renderedControls, ...controls.rendered };

					promises.push(
						BX.ajax.runAction(
							'bizproc.fieldtype.renderControlCollection',
							{
								json: {
									documentType,
									controlsData: controls.toLoad.map((toLoad) => ({
										property: toLoad.data.property,
										params: {
											Field: { Field: toLoad.data.fieldName, Form: 'sfa_form' },
											Value: (toLoad.data.value || ''),
											Als: isPublicMode ? 0 : 1,
											RenderMode: renderMode === 'designer' ? 'designer' : 'public',
										},
									})),
								},
							},
						).then((response) => {
							const rendered = response.data?.html;

							if (BX.Type.isArray(rendered))
							{
								rendered.forEach((renderedControl, controlId) => {
									const controlNode = controls.toLoad[controlId].node;

									BX.Dom.clean(controlNode);
									BX.Runtime.html(controlNode, renderedControl).then(() => {
										if (isPublicMode)
										{
											this.initControl(controlNode, controls.toLoad[controlId].data.property);
										}
										else if (!BX.Type.isNil(BX.Bizproc.Selector))
										{
											BX.Bizproc.Selector.initSelectors(controlNode);
										}
									});
								});
							}
						}).catch((response) => {
							if (!BX.Type.isArrayFilled(response.errors))
							{
								return;
							}

							const error = response.errors[0];
							if (BX.Type.isStringFilled(error.message))
							{
								BX.UI.Dialogs.MessageBox.alert(error.message);
							}

							if (BX.Type.isPlainObject(error.customData) && BX.Type.isStringFilled(error.customData.reason))
							{
								console.error(error.customData.reason);
							}
						}),
					);
				}

				Promise.all(promises).catch(() => {});
			}

			return renderedControls;
		},

		/**
		 * @private
		 * @param documentType
		 * @param {Array<{
		 *     property: Object,
		 *     fieldName: string,
		 *     value: any,
		 *     controlId: string,
		 * }>} controlsData
		 * @param {'public' | 'designer' | ''} renderMode
		 * @returns {{
		 *     rendered: Object<string, HTMLElement>
		 *     toLoad: Array<{ node: HTMLElement, data: Object }>
		 * }}
		 */
		renderControlCollectionInner: function(documentType, controlsData, renderMode)
		{
			const controls = {
				rendered: {},
				toLoad: [],
			};

			const isPublicMode = !renderMode || renderMode === 'public';
			controlsData.forEach((data) => {
				const hasRenderer = BX.Type.isStringFilled(this.getRenderFunctionName(data.property));

				let node = null;
				if (!isPublicMode || !hasRenderer)
				{
					node = BX.Dom.create('div', { text: '...' });
					controls.toLoad.push({
						node,
						data,
					});
				}
				else
				{
					node = this.renderControl(
						documentType,
						data.property,
						data.fieldName,
						data.value,
						renderMode,
					);
				}

				controls.rendered[data.controlId] = node;
			});

			return controls;
		},

		renderControl: function (documentType, property, fieldName, value, renderMode) {
			if (!renderMode || renderMode === 'public')
			{
				return this.renderControlPublic(documentType, property, fieldName, value);
			}
			if (renderMode === 'designer')
			{
				return this.renderControlDesigner(documentType, property, fieldName, value);
			}

			return BX.create('div', {text: 'incorrect render mode'});
		},

		renderControlPublic: function (documentType, property, fieldName, value, needInit)
		{
			var node,
				renderer = this.getRenderFunctionName(property);

			if (!BX.Type.isBoolean(needInit))
			{
				needInit = true;
			}

			if (BX.type.isString(documentType))
			{
				documentType = documentType.split('@');
			}

			if (renderer)
			{
				if (isMultiple(property) && property.Type !== 'select')
				{
					var subNodes = [], me = this;

					toMultipleValue(value).forEach(function(v)
					{
						subNodes.push(
							me[renderer](property, fieldName, v, documentType)
						);
					});

					if (subNodes.length <= 0)
					{
						subNodes.push(
							me[renderer](property, fieldName, null, documentType)
						);
					}

					node = this.wrapMultipleControls(property, fieldName, subNodes);
				}
				else
				{
					node = BX.create('div', {
						children: [this[renderer](property, fieldName, value, documentType)]
					});
				}
			}
			else
			{
				node = BX.create('div', {text: '...'});
				needInit = false;

				BX.ajax.runAction(
					'bizproc.fieldtype.renderControl',
					{
						data: {
							documentType,
							property,
							params: {
								Field: {Field: fieldName, Form: 'sfa_form'},
								Value: (value || ''),
								Als: 0,
								RenderMode: 'public',
							}
						}
					}
				).then(
					(response) => {
						BX.Runtime.html(node, response.data.html).then(() => {
							this.initControl(node, property);
						});
					},
					(response) => {
						BX.UI.Dialogs.MessageBox.alert(response.errors[0].message);
					});
			}

			if (needInit && node)
			{
				this.initControl(node, property);
			}

			return node;
		},
		renderControlDesigner: function (documentType, property, fieldName, value)
		{
			var node = BX.create('div', {text: '...'});

			BX.ajax.runAction(
				'bizproc.fieldtype.renderControl',
				{
					data: {
						documentType,
						property,
						params: {
							Field: {Field: fieldName, Form: 'sfa_form'},
							Value: (value || ''),
							Als: 1,
							RenderMode: 'designer',
						}
					}
				}
			).then(
				(response) => {
					BX.Runtime.html(node, response.data.html).then(() => {
						if (typeof BX.Bizproc.Selector !== 'undefined')
						{
							BX.Bizproc.Selector.initSelectors(node);
						}
					});
				},
				(response) => {
					BX.UI.Dialogs.MessageBox.alert(response.errors[0].message);
				});

			return node;
		},
		formatValuePrintable: function(property, value)
		{
			let result;
			switch (property['Type'])
			{
				case 'bool':
				case 'UF:boolean':
					result = BX.message(
						value === 'Y' ? 'BIZPROC_JS_BP_FIELD_TYPE_YES' : 'BIZPROC_JS_BP_FIELD_TYPE_NO'
					);
					break;

				case 'select':
				case 'internalselect':
					var options = property['Options'] || {};
					if (BX.type.isArray(value))
					{
						result = [];
						value.forEach(function(v)
						{
							result.push(options[v]);
						});
						result = result.join(', ');
					}
					else
					{
						result = options[value];
					}

					break;

				case 'date':
				case 'UF:date':
				case 'datetime':
					result = normalizeDateValue(value);
					break;
				case 'text':
				case 'int':
				case 'double':
				case 'string':
					result = value.toString();
					break;
				case 'user':
					result = [];
					var i, name, pair, matches, pairs = BX.Type.isArray(value) ? value : value.split(',');

					const isExpressionProperty = (expression, property) => {
						return (
							property.BaseType === 'user'
							&& (
								property.Expression === expression
								|| property.SystemExpression === expression
							)
						);
					};

					for (i = 0; i < pairs.length; ++i)
					{
						pair = BX.util.trim(pairs[i]);
						if (matches = pair.match(/(.*)\[([A-Z]{0,2}\d+)\]/))
						{
							name = BX.util.trim(matches[1]);
							result.push(name);
						}
						else
						{
							const expression = pair;

							let field = this.getDocumentFields()
								.find((property) => isExpressionProperty(expression, property))
							;
							if (!BX.Type.isNil(field))
							{
								result.push(field.Name || expression);

								continue;
							}

							field = this.getGlobals().find((property) => isExpressionProperty(expression, property));
							if (!BX.Type.isNil(field))
							{
								result.push(field.Name || expression);

								continue;
							}

							result.push(expression);
						}
					}
					result = result.join(', ');
					break;
				case 'UF:address':
					let address = value;
					if (BX.Type.isArrayFilled(address))
					{
						address = address[0] ?? '';
					}

					if (BX.Type.isStringFilled(address))
					{
						const addressMatches = address.match(/(.*)\|[\d.]*;[\d.]*\|?\d*/); // address|0;0|1
						if (addressMatches)
						{
							address = String(addressMatches[1]).trim();
						}

						result = address;
					}
					else
					{
						result = '';
					}

					break;
				case 'time':
					result = normalizeTimeValue(value, property);
					break;
				default:
					if (BX.type.isString(value))
					{
						result = value;
					}
					else
					{
						result = '(?)';
					}

					break;
			}

			return result;
		},

		/**
		 * System functions
		 */
		getRenderFunctionName: function(property)
		{
			var renderer;
			switch (property['Type'])
			{
				case 'B':
				case 'bool':
				case 'UF:boolean':
					renderer = 'createBoolNode';
					break;

				case 'date':
				case 'UF:date':
				case 'datetime':
				case 'S:Date':
				case 'S:DateTime':
					renderer = 'createDateNode';
					break;

				case 'L':
				case 'select':
				case 'internalselect':
					renderer = 'createSelectNode';
					break;

				case 'T':
				case 'text':
					renderer = 'createTextNode';
					break;

				case 'N':
				case 'int':
				case 'double':
					renderer = 'createNumericNode';
					break;

				case 'S':
				case 'string':
					renderer = 'createStringNode';
					break;

				case 'F':
				case 'file':
					renderer = 'createFileNode';
					break;

				case 'time':
					renderer = 'createTimeNode';
					break;
			}
			return renderer;
		},
		wrapMultipleControls: function(property, fieldName, controls)
		{
			var wrapper = BX.create('div', {children: controls});

			var btn = BX.create('a', {
				attrs: {
					className: 'bizproc-type-control-clone-btn'
				},
				text: BX.message('BIZPROC_JS_BP_FIELD_TYPE_ADD'),
				events: {
					click: function(event)
					{
						event.preventDefault();
						FieldType.cloneControl(property, fieldName, this.parentNode)
					}
				}
			});

			wrapper.appendChild(BX.create('div', {children: [btn]}));

			return wrapper;
		},
		cloneControl: function(property, name, node)
		{
			var renderer = this.getRenderFunctionName(property);

			if (renderer)
			{
				var controlNode = this[renderer](property, name);

				if (controlNode && node.parentNode)
				{
					var wrapper = BX.create('div', {children: [controlNode]});
					this.initControl(wrapper, property);
					node.parentNode.insertBefore(wrapper, node);
				}
			}
		},
		createControlOptions: function (property, callbackFunction)
		{
			var options = getOptions(property);
			var str = '';
			for (var i in options)
			{
				if (String(i) !== String(options[i]))
				{
					str += '[' + i + ']' + options[i];
				}
				else
				{
					str += options[i];
				}
				str += '\n';
			}
			var rnd = BX.util.getRandomString(3);
			var textarea = BX.create('textarea', {
				attrs: {
					id: "bizproc_fieldtype_select_form_options_" + rnd
				}
			});
			textarea.innerHTML = BX.util.htmlspecialchars(str);

			var me = this;
			var button = BX.create('button', {
				attrs: {
					type: 'button'
				},
				text: BX.Loc.getMessage('BIZPROC_JS_BP_FIELD_TYPE_SELECT_OPTIONS3'),
				events: {
					click: function () {
						callbackFunction(me.parseSelectFormOptions(rnd));
					}
				}
			});

			var wrapper = BX.create('div');
			wrapper.appendChild(textarea);
			wrapper.appendChild(BX.create('br'));
			wrapper.innerHTML += BX.Loc.getMessage('BIZPROC_JS_BP_FIELD_TYPE_SELECT_OPTIONS1');
			wrapper.appendChild(BX.create('br'));
			wrapper.innerHTML += BX.Loc.getMessage('BIZPROC_JS_BP_FIELD_TYPE_SELECT_OPTIONS2');
			wrapper.appendChild(BX.create('br'));
			wrapper.appendChild(button);

			return wrapper;
		},
		parseSelectFormOptions: function(rnd)
		{
			var result = {};
			var str = document.getElementById('bizproc_fieldtype_select_form_options_' + rnd).value;
			if (!str)
			{
				return result;
			}

			var rows = str.split(/[\r\n]+/);
			var pattern = /\[([^\]]+)].+/;

			for (var i in rows)
			{
				var row = BX.util.trim(rows[i]);
				if (row.length > 0)
				{
					var matches = row.match(pattern);
					if (matches)
					{
						var position = row.indexOf(']');
						result[matches[1]] = row.substr(position + 1);
					}
					else
					{
						result[row] = row;
					}
				}
			}

			return result;
		},
		createBoolNode: function(property, fieldName, value)
		{
			var yesLabel = BX.message('BIZPROC_JS_BP_FIELD_TYPE_YES');
			var noLabel = BX.message('BIZPROC_JS_BP_FIELD_TYPE_NO');

			yesLabel = yesLabel.charAt(0).toUpperCase() + yesLabel.slice(1);
			noLabel = noLabel.charAt(0).toUpperCase() + noLabel.slice(1);

			var node = BX.create('select', {
				attrs: {
					className: 'bizproc-type-control bizproc-type-control-bool'
						+ (isMultiple(property) ? ' bizproc-type-control-multiple' : '')
				},
				props: {
					name: fieldName + (isMultiple(property) ? '[]' : '')
				},
				children: [
					BX.create('option', {
						props: {value: ''},
						text: BX.message('BIZPROC_JS_BP_FIELD_TYPE_NOT_SELECTED')
					})
				]
			});
			var optionY = BX.create('option', {
				props: {value: 'Y'},
				text: yesLabel
			});

			if (value === 'Y' || value === 1 || value === '1')
			{
				optionY.setAttribute('selected', 'selected');
			}

			var optionN = BX.create('option', {
				props: {value: 'N'},
				text: noLabel
			});

			if (value === 'N' || value === 0 || value === '0')
			{
				optionN.setAttribute('selected', 'selected');
			}

			node.appendChild(optionY);
			node.appendChild(optionN);

			return node;
		},
		createDateNode: function(property, fieldName, value)
		{
			var type = property['Type'];
			if (type === 'UF:date' || type === 'S:Date')
			{
				type = 'date';
			}
			if (type === 'S:DateTime')
			{
				type = 'datetime';
			}

			var input = BX.create('input', {
				attrs: {
					className: 'bizproc-type-control bizproc-type-control-' + type
						+ (isMultiple(property) ? ' bizproc-type-control-multiple' : ''),
					'data-role': isSelectable(property) ? 'inline-selector-target' : '',
					'data-selector-type': type,
					placeholder: getPlaceholder(property),
				},
				props: {
					type: 'text',
					name: fieldName + (isMultiple(property) ? '[]' : ''),
					value: normalizeDateValue(value)
				}
			});

			var designer = BX.getClass('BX.Bizproc.Automation.Designer') && BX.Bizproc.Automation.Designer.getInstance();
			var dlg = designer && (designer.getRobotSettingsDialog() || designer.getTriggerSettingsDialog());
			if (!dlg)
			{
				var img = BX.create('img', {
					attrs: {
						src: '/bitrix/js/main/core/images/calendar-icon.gif',
						className: 'calendar-icon',
						border: '0'
					},
					events: {
						click: function(e)
						{
							e.preventDefault();
							BX.calendar({
								node: this,
								field: input,
								bTime: (type === 'datetime'),
								bHideTime: (type === 'date')
							});
						}
					}
				});

				var lc;

				if (property['Settings'] && property['Settings']['timezones'])
				{
					lc = BX.create('select', {
						props: {name: 'tz_' + (fieldName + (isMultiple(property) ? '[]' : ''))},
						attrs: {className: 'bizproc-type-control-date-lc'}
					});

					property['Settings']['timezones'].forEach(function(zone)
					{
						var option = BX.create('option', {
							props: {value: zone.value},
							text: zone.text
						});
						if (zone.value === 'current')
						{
							option.setAttribute('selected', 'selected');
						}
						lc.appendChild(option);
					});
				}

				return BX.create('div', {children: [input, img, lc]});
			}

			return input;
		},
		createNumericNode: function(property, fieldName, value)
		{
			return BX.create('input', {
				attrs: {
					className: 'bizproc-type-control bizproc-type-control-int'
						+ (isMultiple(property) ? ' bizproc-type-control-multiple' : ''),
					'data-role': isSelectable(property) ? 'inline-selector-target' : '',
					placeholder: getPlaceholder(property),
				},
				props: {
					type: 'text',
					name: fieldName + (isMultiple(property) ? '[]' : ''),
					value: BX.Type.isNil(value) ? '' : value.toString(),
				}
			});
		},
		createStringNode: function(property, fieldName, value)
		{
			return BX.create('input', {
				attrs: {
					className: 'bizproc-type-control bizproc-type-control-string'
						+ (isMultiple(property) ? ' bizproc-type-control-multiple' : ''),
					'data-role': isSelectable(property) ? 'inline-selector-target' : '',
					placeholder: getPlaceholder(property),
				},
				props: {
					type: 'text',
					name: fieldName + (isMultiple(property) ? '[]' : ''),
					value: (value || '')
				}
			});
		},
		createFileNode: function(property, fieldName, value)
		{
			var designer = BX.getClass('BX.Bizproc.Automation.Designer') && BX.Bizproc.Automation.Designer.getInstance();
			var dlg = designer && designer.getRobotSettingsDialog();
			if (!dlg)
			{
				var input = BX.create('input', {
					props: {
						type: 'file',
						name: fieldName + (isMultiple(property) ? '[]' : ''),
						value: (value || '')
					},
					events: {
						change: function()
						{
							this.nextSibling.textContent = BX.Bizproc.FieldType.File.parseLabel(this.value);
						}
					}
				});

				var buttonWrapper = BX.create('span', {
					children: [BX.create('span', {
						attrs: {
							className: 'webform-small-button'
						},
						text: BX.message('BIZPROC_JS_BP_FIELD_TYPE_CHOOSE_FILE')
					})]
				});

				return BX.create('div', {
					children: [
						buttonWrapper,
						input,
						BX.create('span', {
							attrs: {
								className: 'bizproc-type-control-file-label'
							}
						})
					],
					attrs: {
						className: 'bizproc-type-control bizproc-type-control-file'
							+ (isMultiple(property) ? ' bizproc-type-control-multiple' : '')
					}
				});
			}

			return BX.create('input', {
				attrs: {
					className: 'bizproc-type-control bizproc-type-control-file-selectable'
						+ (isMultiple(property) ? ' bizproc-type-control-multiple' : ''),
					'data-role': isSelectable(property) ? 'inline-selector-target' : '',
					'data-selector-type': 'file'
				},
				props: {
					type: 'text',
					name: fieldName + (isMultiple(property) ? '[]' : ''),
					value: (value || '')
				}
			});
		},
		createTextNode: function(property, fieldName, value)
		{
			return BX.create('textarea', {
				attrs: {
					className: 'bizproc-type-control bizproc-type-control-text'
						+ (isMultiple(property) ? ' bizproc-type-control-multiple' : ''),
					'data-role': isSelectable(property) ? 'inline-selector-target' : '',
					rows: 5,
					cols: 40,
					placeholder: getPlaceholder(property),
				},
				props: {
					name: fieldName + (isMultiple(property) ? '[]' : ''),
					value: (value || '')
				},
			});
		},
		createSelectNode: function(property, fieldName, value, documentType)
		{
			var isEqual = function(needle, haystack)
			{
				if (!needle || !haystack)
				{
					return false;
				}

				if (BX.type.isArray(haystack))
				{
					return BX.util.in_array(needle, haystack);
				}

				return (needle.toString() === haystack.toString());
			};

			var option, node = BX.create('select', {
				attrs: {
					className: 'bizproc-type-control bizproc-type-control-select'
						+ (isMultiple(property) ? ' bizproc-type-control-multiple' : '')
				},
				props: {
					name: fieldName + (isMultiple(property) ? '[]' : '')
				},
			});

			var getDefaultOption = function()
			{
				return BX.create('option', {
					props: { value: '' },
					text: property.EmptyValueText || BX.message('BIZPROC_JS_BP_FIELD_TYPE_NOT_SELECTED')
				});
			}

			if (isMultiple(property))
			{
				node.setAttribute('multiple', 'multiple');
				node.setAttribute('size', '5');
			}
			option = getDefaultOption();
			if (BX.Type.isNil(value) || value.length === 0)
			{
				option.setAttribute('selected', 'selected');
			}
			node.appendChild(option);

			const renderOptions = (options) =>
			{
				options.forEach((optionValue, i) => {

					let currentValue = i;
					let text = optionValue;

					if (BX.Type.isPlainObject(optionValue))
					{
						currentValue = optionValue.value;
						text = optionValue.name;
					}

					const optionElement = BX.create('option', {
						props: {value: currentValue},
						text: BX.Text.decode(text)
					});

					if (isEqual(currentValue, value))
					{
						optionElement.setAttribute('selected', 'selected');
					}

					node.appendChild(optionElement);
				});
			}

			if (BX.type.isPlainObject(property['Options']))
			{
				const options = [];
				for (const key in property['Options'])
				{
					if (!property['Options'].hasOwnProperty(key))
					{
						continue;
					}
					options.push({value: key, name: property['Options'][key]});
				}
				renderOptions(options);
			}
			else if (BX.type.isArray(property['Options']))
			{
				renderOptions(property['Options']);
			}
			else if (
				property.Settings
				&& property.Settings.OptionsLoader
				&& property.Settings.OptionsLoader.type === 'component'
			)
			{
				const loaderConfig = property.Settings.OptionsLoader;
				const loadOption = BX.create('option', {
					props: { value: '...' },
					text: '...',
				});
				node.appendChild(loadOption);

				BX.ajax.runComponentAction(
					loaderConfig.component,
					loaderConfig.action,
					{
						mode: loaderConfig.mode || undefined,
						data: {
							documentType,
							property,
						}
					}
				).then(
					(response) =>
					{
						if (BX.Type.isArray(response.data.options))
						{
							BX.Dom.remove(loadOption);
							renderOptions(response.data.options);
						}
					}
				);
			}

			return node;
		},
		createTimeNode: function(property, fieldName, value)
		{
			const input = BX.Dom.create('INPUT', {
				attrs: {
					type: 'text',
					autocomplete: 'off',
					'data-role': isSelectable(property) ? 'inline-selector-time' : '',
					'data-selector-type': 'time',
				},
				props: {
					className: `bizproc-type-control bizproc-type-control-time${isMultiple(property) ? ' bizproc-type-control-multiple' : ''}`,
					name: fieldName + (isMultiple(property) ? '[]' : ''),
					value: value || '',
				},
			});

			return BX.Dom.create('DIV', { children: [input] });
		},
		initControl: function(controlNode, property)
		{
			var designer = BX.getClass('BX.Bizproc.Automation.Designer') && BX.Bizproc.Automation.Designer.getInstance();
			var dlg;
			var childControlNodes = controlNode.querySelectorAll('[data-role]');
			if (designer && designer.getRobotSettingsDialog())
			{
				dlg = designer.getRobotSettingsDialog();
				dlg.template.initRobotSettingsControls(dlg.robot, controlNode);
			}
			else if (designer && designer.getTriggerSettingsDialog())
			{
				dlg = designer.getTriggerSettingsDialog();
				dlg.triggerManager.initSettingsDialogControls(controlNode);
			}
			else if (property && property['Type'] === 'user' && BX.Bizproc.UserSelector)
			{
				BX.Bizproc.UserSelector.decorateNode(controlNode.querySelector('[data-role="user-selector"]'));
			}
			else if (childControlNodes.length > 0)
			{
				const context =
					BX.Bizproc.Automation
					&& BX.Bizproc.Automation.tryGetGlobalContext
					&& BX.Bizproc.Automation.tryGetGlobalContext()
				;
				if (context)
				{
					childControlNodes.forEach(function(node)
					{
						var selector = BX.Bizproc.Automation.SelectorManager.createSelectorByRole(
							node.getAttribute('data-role'),
							{
								context: new BX.Bizproc.Automation.SelectorContext({
									fields: BX.clone(context.document.getFields()),
									useSwitcherMenu: context.get('showTemplatePropertiesMenuOnSelecting'),
									rootGroupTitle: context.document.title,
									userOptions: context.userOptions
								})
							},
						);
						if (selector && node.parentNode)
						{
							node.parentNode.replaceChild(selector.renderWith(node), node);
						}
					});
				}
			}
		},
		getDocumentFields: function()
		{
			const designer = BX.getClass('BX.Bizproc.Automation.Designer') && BX.Bizproc.Automation.Designer.getInstance();
			const component = designer && designer.component;
			if (component)
			{
				return component.data['DOCUMENT_FIELDS'];
			}
			if (BX.getClass('BX.Bizproc.Automation.API.documentFields'))
			{
				return BX.Bizproc.Automation.API.documentFields;
			}

			return [];
		},
		getDocumentUserGroups: function()
		{
			if (BX.getClass('BX.Bizproc.Automation.API.documentUserGroups'))
			{
				return BX.Bizproc.Automation.API.documentUserGroups;
			}

			return [];
		},
		getGlobals: function ()
		{
			const context =
				BX.Bizproc.Automation
				&& BX.Bizproc.Automation.tryGetGlobalContext
				&& BX.Bizproc.Automation.tryGetGlobalContext()
			;

			return (
				context && context.automationGlobals
					? context.automationGlobals.globalVariables.concat(context.automationGlobals.globalConstants)
					: []
			);
		},
	};

	FieldType.File = {
		parseLabel: function(str)
		{
			var i;
			if (str.lastIndexOf('\\'))
			{
				i = str.lastIndexOf('\\')+1;
			}
			else
			{
				i = str.lastIndexOf('/')+1;
			}
			return str.slice(i);
		}
	};


	BX.Bizproc.FieldType = FieldType;
})(window.BX || window.top.BX);
