function BXBannerTemplate(oConfig)
{
	this.oConfig = oConfig;
	this.MESS = oConfig.lang;
	this.templates = {};
	this.templatesExtended = {};
	this.oldTemplates = {};
	this.tPosition = {};
	this.tCount = {};
	this.aNames = [];
	this.canAdd = {};
	this.canEdit = oConfig.canEdit || false;
	this.nType = BX('banner_type');
	this.nComponentHead = BX('eTemplateComponentHead');
	this.nComponent = BX('eTemplateComponent');
	this.nAddTemplate = BX('eAddTemplateBanner');
	this.nTemplateProperties = BX('eTemplateProperties');
	this.nBannerContainer = BX('ADV_BANNER_PROPERTIES_CONTAINER');
	this.nExtMode = BX('EXTENDED_MODE');
}

BXBannerTemplate.prototype =
{
    show: function (name, ind)
    {
        var template = !!this.nExtMode.checked ? this.templatesExtended[name] : this.templates[name];
        if (typeof ind === 'undefined')
        {
            window.changeTemplateNodes('clean');
            this.nComponent.style.display = '';

            if (this.canAdd[name] && this.canEdit)
                this.nAddTemplate.style.display = '';
            else
                this.nAddTemplate.style.display = 'none';

            if (!this.canEdit)
                BX('eExtMode').style.display = 'none';

            this.nTemplateProperties.style.display = '';
            for (var a in template)
            {
                var curTemplateBanner = template[a],
                    oTProps = curTemplateBanner.nProps,
                    oTPropsHead = curTemplateBanner.nPropsHead,
                    cleanNode = BX.create('div', {
                        props: {
                            className: 'adv-template-banner-container'
                        },
                        children: [
                            BX.create('table', {
                                props: {
                                    className: 'internal'
                                },
                                style: {
                                    width: '100%',
                                    marginBottom: '10px'
                                },
                                children: [
                                    BX.create('thead', {
                                        props: {
                                            className: 'adv-banner-thead'
                                        }
                                    }),
                                    BX.create('tbody', {
                                        props: {
                                            className: 'adv-banner-tbody'
                                        }
                                    })
                                ]
                            })
                        ]
                    });
                var targetNode = cleanNode.firstChild.firstChild;
                targetNode.appendChild(oTPropsHead);
                targetNode = targetNode.nextSibling;
                for (var i in oTProps)
                {
                    if (oTProps.hasOwnProperty(i))
                    {
                        targetNode.appendChild(oTProps[i]);
                        this.appendJS(oTProps[i].innerHTML);
                    }
                }
                this.nBannerContainer.appendChild(cleanNode);
                this.addDraggableItem(cleanNode);
                this.sortItems();
                this.addListenerControlItem(cleanNode);
            }
        }
        else if (ind === false)
        {
            window.changeTemplateNodes('clean');
            this.nComponent.style.display = '';
            if (this.canAdd[name])
                this.nAddTemplate.style.display = '';
            else
                this.nAddTemplate.style.display = 'none';
            this.nTemplateProperties.style.display = '';
            for (var a in template)
            {
                var curTemplateBanner = template[a],
                    oTProps = curTemplateBanner.nProps,
                    oTPropsHead = curTemplateBanner.nPropsHead,
                    oTParams = curTemplateBanner.oParams,
                    cleanNode = BX.create('div', {
                        props: {
                            className: 'adv-template-banner-container'
                        },
                        children: [
                            BX.create('table', {
                                props: {
                                    className: 'internal'
                                },
                                style: {
                                    width: '100%',
                                    marginBottom: '10px'
                                },
                                children: [
                                    BX.create('thead', {
                                        props: {
                                            className: 'adv-banner-thead'
                                        }
                                    }),
                                    BX.create('tbody', {
                                        props: {
                                            className: 'adv-banner-tbody'
                                        }
                                    })
                                ]
                            })
                        ]
                    });
                var targetNode = cleanNode.firstChild.firstChild;
                targetNode.appendChild(oTPropsHead);
                targetNode = targetNode.nextSibling;
                for (var i in oTProps)
                {
                    if (oTProps.hasOwnProperty(i))
                    {
                        targetNode.appendChild(oTProps[i]);
                        if (oTParams[i] && oTParams[i].TYPE == 'HTML')
                            this.appendJS(oTProps[i].innerHTML);
                    }
                }
                this.nBannerContainer.appendChild(cleanNode);
                this.addDraggableItem(cleanNode);
                this.sortItems();
                this.addListenerControlItem(cleanNode);
            }
        }
        else if (parseInt(ind) >= 0)
        {
            if (!!template[ind])
            {
                var curTemplateBanner = template[ind],
                    oTProps = curTemplateBanner.nProps,
                    oTPropsHead = curTemplateBanner.nPropsHead,
                    oTParams = curTemplateBanner.oParams,
                    cleanNode = BX.create('div', {
                        props: {
                            className: 'adv-template-banner-container'
                        },
                        style: {
                            display: 'none'
                        },
                        children: [
                            BX.create('table', {
                                props: {
                                    className: 'internal'
                                },
                                style: {
                                    width: '100%',
                                    marginBottom: '10px'
                                },
                                children: [
                                    BX.create('thead', {
                                        props: {
                                            className: 'adv-banner-thead'
                                        }
                                    }),
                                    BX.create('tbody', {
                                        props: {
                                            className: 'adv-banner-tbody'
                                        }
                                    })
                                ]
                            })
                        ]
                    });

                var targetNode = cleanNode.firstChild.firstChild,
                    positions = this.tPosition[name];

                targetNode.appendChild(oTPropsHead);
                targetNode = targetNode.nextSibling;
                for (var i in oTProps)
                {
                    if (oTProps.hasOwnProperty(i))
                    {
                        targetNode.appendChild(oTProps[i]);
                        if (oTParams[i].TYPE == 'HTML')
                            this.appendJS(oTProps[i].innerHTML);
                    }
                }
                if (!positions[ind])
                {
                    var easing = new BX.easing({
                        duration : 500,
                        start : { height : 0, opacity : 0 },
                        finish : { height : 100, opacity: 100 },
                        transition : BX.easing.transitions.quart,
                        step : function(state){
                            cleanNode.style.opacity = state.opacity/100;
                            cleanNode.style.display = 'block';
                        },
                        complete : function() {
                        }
                    });
                    window.changeTemplateNodes('clean', ind);
                    this.nBannerContainer.appendChild(cleanNode);
                    easing.animate();
                }
                else
                {
                    window.changeTemplateNodes('clean', positions[ind]-1);
                    var insBefore = this.nBannerContainer.children[positions[ind]-1];
                    this.nBannerContainer.insertBefore(cleanNode, insBefore);
                    cleanNode.style.display = 'block';
                }
                this.addDraggableItem(cleanNode);
                this.sortItems();
                this.addListenerControlItem(cleanNode);
            }
        }
    },
    create: function (name, oVal, tNew)
    {
        var data = {
            action: 'getTemplate',
            name: name,
            properties: (!!oVal) ? oVal : '',
            mode: this.nExtMode.checked ? 'Y' : 'N',
            bCopy: this.oConfig.bCopy ? 'Y' : 'N',
            sessid: BX.bitrix_sessid()
        };
        var _this = this;
        if (oVal && oVal.parameters.MODE == 'Y')
        {
            _this.nExtMode.checked = true;
        }
        BX.showWait();
        BX.ajax.post(
            this.oConfig.curPage, data,
            function (res){
                BX.closeWait();
                if(!!res)
                {
                    res = eval('(' + res + ')');
                    var templates = !!_this.nExtMode.checked ? _this.templatesExtended[name] = {} : _this.templates[name] = {}, curValue;
                    var settings = res[0].SETTINGS;
                    _this.canAdd[name] = settings && settings.MULTIPLE && settings.MULTIPLE == 'N' ? false : true;
                    _this.tCount[name] = 0;
                    _this.oldTemplates[name] = {};
                    var properties, propValueHead, propHiddenName;
                    for (var a in res)
                    {
                        //a = multibanner index (0,1..)
                        properties = {};
                        propValueHead = _this.createTemplatePropHead(a);
                        propValueHead.firstChild.setAttribute('style', 'text-align:left !important');
                        propHiddenName = _this.createHiddenNameInput(res[a].BANNER_NAME, a);
                        propValueHead.appendChild(propHiddenName);
                        curValue = !!oVal ? oVal.parameters.PROPS[a] : {};
                        for (var i in res[a].PARAMETERS)
                        {
                            //i = property name ('HEADING'..)
                            if (res[a].PARAMETERS.hasOwnProperty(i))
                            {
                                var propValueNode =
                                    BX.create('td', {
                                        props: {
                                            className: 'adm-detail-content-cell-r'
                                        },
                                        style: {
                                            width: '60%'
                                        }
                                    });
                                var valProp = (!!curValue[i]) ? curValue[i] : false;
                                var propValue = _this.buildProperty(res[a].PARAMETERS[i], valProp, i, tNew, _this.tCount[name]);
                                if (Object.prototype.toString.call(propValue) === '[object Array]')
                                {
                                    for (var ar = 0, cnt = propValue.length; ar < cnt; ar++)
                                        propValueNode.appendChild(propValue[ar]);
                                }
                                else if (typeof propValue === 'string')
                                    propValueNode.innerHTML = propValue;
                                else
                                    propValueNode.appendChild(propValue);

                                if (!!res[a].PARAMETERS[i].TOOLTIP)
                                {
                                    new BX.CHint(
                                        {
                                            hint: res[a].PARAMETERS[i].TOOLTIP,
                                            parent: propValueNode.appendChild(BX.create("I", {props: {className: "bxcompprop-info-btn"}}))
                                        });
                                }

                                properties[i] = BX.create('tr', {
                                    children: [
                                        BX.create('td', {
                                            props: {
                                                className: 'adm-detail-content-cell-l'
                                            },
                                            text: res[a].PARAMETERS[i].NAME + ':',
                                            style: {
                                                width: '40%'
                                            }
                                        }),
                                        propValueNode
                                    ],
                                    props: {
                                        id: 'eTemplateProps_' + a + '_' + i
                                    }
                                });
                                properties[i].setAttribute("valign", "top");
                            }
                        }
                        var settings = res[a].SETTINGS || false;
                        templates[a] = {nProps: properties, oParams: res[a].PARAMETERS, nPropsHead: propValueHead, oSettings: settings};
                        _this.tCount[name]++;
                    }
                    _this.show(name);
                }
                else
                    _this.close();
            });
    },
    createFromDB: function (res)
    {
        if (!!res)
        {
            var properties, propValueHead, propHiddenName,
                name = this.getName(),
                params = res.params,
                val = res.val;
            if (res && res.val && res.val[0] && res.val[0].EXTENDED_MODE == 'Y')
                this.nExtMode.checked = true;
            var templates = !!this.nExtMode.checked ? this.templatesExtended[name] = {} : this.templates[name] = {}, curValue;
            var settings = res.params[0].SETTINGS;
            this.canAdd[name] = settings && settings.MULTIPLE && settings.MULTIPLE == 'N' ? false : true;
            this.tCount[name] = 0;
            this.oldTemplates[name] = {};
            for (var a in params)
            {
                //a = multibanner index (0,1..)
                properties = {};
                propValueHead = this.createTemplatePropHead(a);
                propValueHead.firstChild.setAttribute('style', 'text-align:left !important');
                propHiddenName = this.createHiddenNameInput(params[a].BANNER_NAME, a);
                propValueHead.appendChild(propHiddenName);
                curValue = !!val ? val[a] : {};
                for (var i in params[a].PARAMETERS)
                {
                    //i = property name ('HEADING'..)
                    if (params[a].PARAMETERS.hasOwnProperty(i))
                    {
                        var propValueNode =
                            BX.create('td', {
                                props: {
                                    className: 'adm-detail-content-cell-r'
                                },
                                style: {
                                    width: '60%'
                                }
                            });
                        var valProp = (!!curValue[i]) ? curValue[i] : false;
                        var propValue = this.buildProperty(params[a].PARAMETERS[i], valProp, i, false, this.tCount[name]);
                        if (Object.prototype.toString.call(propValue) === '[object Array]')
                        {
                            for (var ar = 0, cnt = propValue.length; ar < cnt; ar++)
                                propValueNode.appendChild(propValue[ar]);
                        }
                        else if (typeof propValue === 'string')
                            propValueNode.innerHTML = propValue;
                        else
                            propValueNode.appendChild(propValue);

                        if (!!params[a].PARAMETERS[i].TOOLTIP)
                        {
                            new BX.CHint(
                                {
                                    hint: params[a].PARAMETERS[i].TOOLTIP,
                                    parent: propValueNode.appendChild(BX.create("I", {props: {className: "bxcompprop-info-btn"}}))
                                });
                        }

                        properties[i] = BX.create('tr', {
                            children: [
                                BX.create('td', {
                                    props: {
                                        className: 'adm-detail-content-cell-l'
                                    },
                                    text: params[a].PARAMETERS[i].NAME + ':',
                                    style: {
                                        width: '40%'
                                    }
                                }),
                                propValueNode
                            ],
                            props: {
                                id: 'eTemplateProps_' + a + '_' + i
                            }
                        });
                        properties[i].setAttribute("valign", "top");
                    }
                }
                var settings = params[a].SETTINGS || false;
                templates[a] = {nProps: properties, oParams: params[a].PARAMETERS, nPropsHead: propValueHead, oSettings: settings};
                this.tCount[name]++;
            }
            this.show(name);
        }
        else
            this.close();
    },
    refresh: function (ind)
    {
        var curValues = this.getCurValues(ind, this.nExtMode.checked),
            name = this.getName(),
            _this = this,
            data = {
                action: 'refreshTemplate',
                name: name,
                curValues: (!!curValues) ? curValues : '',
                mode: this.nExtMode.checked ? 'Y' : 'N',
                index: ind,
                sessid: BX.bitrix_sessid()
            };
        BX.showWait();
        BX.ajax.post(
            this.oConfig.curPage, data,
            function (res){
                BX.closeWait();
                if(!!res)
                {
                    res = eval('(' + res + ')');
                    var oldTemplate = !!_this.nExtMode.checked ? _this.templatesExtended[name] : _this.templates[name],
                        allTemplateProps = _this.oldTemplates[name],
                        curOldTemplate = oldTemplate[ind],
                        curAllTemplateProps = allTemplateProps[ind] || {},
                        properties = {},
                        parameters = res[0].PARAMETERS;
                    for (var i in parameters)
                    {
                        if (parameters.hasOwnProperty(i))
                        {
                            if (!curOldTemplate.nProps[i] && !curAllTemplateProps[i])
                            {
                                var propValueNode =
                                    BX.create('td', {
                                        props: {
                                            className: 'adm-detail-content-cell-r'
                                        },
                                        style: {
                                            width: '60%'
                                        }
                                    });
                                var propValue = _this.buildProperty(parameters[i], false, i, true, ind);
                                if (Object.prototype.toString.call(propValue) === '[object Array]')
                                {
                                    for (var ar = 0, cnt = propValue.length; ar < cnt; ar++)
                                        propValueNode.appendChild(propValue[ar]);
                                }
                                else if (typeof propValue === 'string')
                                {
                                    propValueNode.innerHTML = propValue;
                                    if (parameters[i].TYPE != 'HTML')
                                        _this.appendJS(propValue);
                                }
                                else
                                    propValueNode.appendChild(propValue);

                                if (!!parameters[i].TOOLTIP)
                                {
                                    new BX.CHint(
                                        {
                                            hint: parameters[i].TOOLTIP,
                                            parent: propValueNode.appendChild(BX.create("I", {props: {className: "bxcompprop-info-btn"}}))
                                        });
                                }

                                properties[i] = BX.create('tr', {
                                    children: [
                                        BX.create('td', {
                                            props: {
                                                className: 'adm-detail-content-cell-l'
                                            },
                                            text: parameters[i].NAME + ':',
                                            style: {
                                                width: '40%'
                                            }
                                        }),
                                        propValueNode
                                    ],
                                    props: {
                                        id: 'eTemplateProps_' + ind + '_' + i
                                    }
                                });
                                properties[i].setAttribute("valign", "top");
                            }
                            else if (curOldTemplate.nProps[i])
                                properties[i] = curOldTemplate.nProps[i];
                            else
                                properties[i] = curAllTemplateProps[i];
                        }
                    }
                    allTemplateProps[ind] = _this.mergeObjects(curOldTemplate.nProps, curAllTemplateProps);
                    curOldTemplate.nProps = properties;
                    curOldTemplate.oParams = parameters;

                    _this.show(name, ind);
                }
                else
                    _this.close();
            });
    },
    addNewTBanner: function ()
    {
        var name = this.getName(),
            data = {
                action: 'getCleanTemplate',
                name: name,
                properties: '',
                mode: this.nExtMode.checked ? 'Y' : 'N',
                index: this.tCount[name],
                sessid: BX.bitrix_sessid()
            };
        var _this = this;
        BX.showWait();
        BX.ajax.post(
            this.oConfig.curPage, data,
            function (res){
                BX.closeWait();
                if(!!res)
                {
                    res = eval('(' + res + ')');
                    var properties = {}, propValueHead, propHiddenName,
                        template = !!_this.nExtMode.checked ? _this.templatesExtended[name] : _this.templates[name];
                    propValueHead = _this.createTemplatePropHead(_this.tCount[name]);
                    propValueHead.firstChild.setAttribute('style', 'text-align:left !important');
                    propHiddenName = _this.createHiddenNameInput('', _this.tCount[name]);
                    propValueHead.appendChild(propHiddenName);
                    for (var i in res[0].PARAMETERS)
                    {
                        if (res[0].PARAMETERS.hasOwnProperty(i))
                        {
                            var propValueNode =
                                BX.create('td', {
                                    props: {
                                        className: 'adm-detail-content-cell-r'
                                    },
                                    style: {
                                        width: '60%'
                                    }
                                });
                            var propValue = _this.buildProperty(res[0].PARAMETERS[i], false, i, true, _this.tCount[name]);
                            if (Object.prototype.toString.call(propValue) === '[object Array]')
                            {
                                for (var ar = 0, cnt = propValue.length; ar < cnt; ar++)
                                    propValueNode.appendChild(propValue[ar]);
                            }
                            else if (typeof propValue === 'string')
                            {
                                propValueNode.innerHTML = propValue;
                                if (res[0].PARAMETERS[i].TYPE != 'HTML')
                                    _this.appendJS(propValue);
                            }
                            else
                                propValueNode.appendChild(propValue);

                            if (!!res[0].PARAMETERS[i].TOOLTIP)
                            {
                                new BX.CHint(
                                    {
                                        hint: res[0].PARAMETERS[i].TOOLTIP,
                                        parent: propValueNode.appendChild(BX.create("I", {props: {className: "bxcompprop-info-btn"}}))
                                    });
                            }

                            properties[i] = BX.create('tr', {
                                children: [
                                    BX.create('td', {
                                        props: {
                                            className: 'adm-detail-content-cell-l'
                                        },
                                        text: res[0].PARAMETERS[i].NAME + ':',
                                        style: {
                                            width: '40%'
                                        }
                                    }),
                                    propValueNode
                                ],
                                props: {
                                    id: 'eTemplateProps_' + _this.tCount[name] + '_' + i
                                }
                            });
                            properties[i].setAttribute("valign", "top");
                        }
                    }
                    template[_this.tCount[name]] = {nProps: properties, oParams: res[0].PARAMETERS, nPropsHead: propValueHead};
                    _this.show(name, _this.tCount[name]);
                    _this.tCount[name]++;
                }
            });
    },
    createTemplatePropHead: function (a)
    {
        var del = this.canEdit && this.canAdd[this.getName()] ? BX.create('span', {
                props: {
                    className: 'adv-banner-delete-button',
                    title: this.MESS.DELETE
                },
                style: {
                    float: 'right',
                    marginRight: '10px',
                    cursor: 'pointer'
                },
                text: 'x'
            }) : {};
        return BX.create('tr', {
            props: {
                id: 'eTemplatePropsHead' + a,
                className: 'heading'
            },
            children: [
                BX.create('td', {
                    props: {
                        colSpan: 2
                    },
                    children: [
                        BX.create('span'),
                        BX.create('span', {
                            props: {
                                className: 'adm-list-table-popup'
                            },
                            style: {
                                float: 'left',
                                height: '15px',
                                cursor: 'auto'
                            }
                        }),
                        del,
                        BX.create('span', {
                            props: {
                                className: 'adv-banner-show-button adv-banner-hide-button'
                            },
                            style: {
                                float: 'right',
                                marginRight: '30px',
                                cursor: 'pointer'
                            },
                            text: this.MESS.HIDE
                        })
                    ]
                })
            ]
        });
    },
    refreshAll: function ()
    {
        var name = this.getName(),
            templateForCurVal = !!this.nExtMode.checked ? this.templatesExtended[name] : this.templates[name],
            curTemplate = !this.nExtMode.checked ? this.templatesExtended[name] : this.templates[name],
            curValues = [],
            mode = !!this.nExtMode.checked,
            fromObject = true,
            difference = this.countObject(curTemplate) - this.countObject(templateForCurVal);
        this.saveNames();
        if (!templateForCurVal)
        {
            templateForCurVal = curTemplate;
            mode = !this.nExtMode.checked;
            fromObject = false;
            difference = 0;
        }
        for (var i in templateForCurVal)
        {
            curValues.push(this.getCurValues(i, mode, fromObject));
        }
        if (difference > 0)
        {
            for (var i = 0; i < difference; i++)
            {
                curValues.push({EXTENDED_MODE: !!this.nExtMode.checked ? 'Y' : 'N'});
            }
        }
        var _this = this,
            data = {
                action: 'refreshAll',
                name: name,
                curValues: (!!curValues) ? curValues : '',
                mode: this.nExtMode.checked ? 'Y' : 'N',
                sessid: BX.bitrix_sessid()
            };
        BX.showWait();
        BX.ajax.post(
            this.oConfig.curPage, data,
            function (res){
                BX.closeWait();
                if(!!res)
                {
                    res = eval('(' + res + ')');
                    var templates = {}, curTemplate, newTemplate;
                    if (!!_this.nExtMode.checked)
                    {
                        newTemplate = _this.templatesExtended[name] ? _this.templatesExtended[name] : {};
                        curTemplate = _this.templates[name];
                    }
                    else
                    {
                        newTemplate = _this.templates[name] ? _this.templates[name] : {};
                        curTemplate = _this.templatesExtended[name];
                    }
                    _this.tCount[name] = 0;
                    _this.oldTemplates[name] = {};
                    var propValueHead, propHiddenName;
                    for (var a in res)
                    {
                        //a = multibanner index (0,1..)
                        var properties = {}, bannerName;
                        propValueHead = _this.createTemplatePropHead(a);
                        propValueHead.firstChild.setAttribute('style', 'text-align:left !important');
                        bannerName = !!res[a].BANNER_NAME ? res[a].BANNER_NAME : (!!_this.aNames[a] ? _this.aNames[a] : '');
                        propHiddenName = _this.createHiddenNameInput(bannerName, a);
                        propValueHead.appendChild(propHiddenName);

                        for (var i in res[a].PARAMETERS)
                        {
                            //i = property name ('HEADING'..)
                            if (res[a].PARAMETERS.hasOwnProperty(i))
                            {
                                if (curTemplate[a].oParams[i] && res[a].PARAMETERS[i].TYPE == curTemplate[a].oParams[i].TYPE && curTemplate[a].nProps[i])
                                {
                                    properties[i] = curTemplate[a].nProps[i];
                                }
                                else if (newTemplate[a] && newTemplate[a].nProps[i])
                                {
                                    properties[i] = newTemplate[a].nProps[i];
                                }
                                else
                                {
                                    var propValueNode =
                                        BX.create('td', {
                                            props: {
                                                className: 'adm-detail-content-cell-r'
                                            },
                                            style: {
                                                width: '60%'
                                            }
                                        });
                                    var propValue = _this.buildProperty(res[a].PARAMETERS[i], false, i, true, _this.tCount[name]);
                                    if (Object.prototype.toString.call(propValue) === '[object Array]')
                                    {
                                        for (var ar = 0, cnt = propValue.length; ar < cnt; ar++)
                                            propValueNode.appendChild(propValue[ar]);
                                    }
                                    else if (typeof propValue === 'string')
                                        propValueNode.innerHTML = propValue;
                                    else
                                        propValueNode.appendChild(propValue);

                                    properties[i] = BX.create('tr', {
                                        children: [
                                            BX.create('td', {
                                                props: {
                                                    className: 'adm-detail-content-cell-l'
                                                },
                                                text: res[a].PARAMETERS[i].NAME + ':',
                                                style: {
                                                    width: '40%'
                                                }
                                            }),
                                            propValueNode
                                        ],
                                        props: {
                                            id: 'eTemplateProps_' + a + '_' + i
                                        }
                                    });
                                    properties[i].setAttribute("valign", "top");
                                }
                            }
                        }
                        templates[a] = {
                            nProps: properties,
                            oParams: res[a].PARAMETERS,
                            nPropsHead: propValueHead,
                            oSettings: res[a].SETTINGS
                        };
                        _this.tCount[name]++;
                    }
                    if (!!_this.nExtMode.checked)
                    {
                        _this.templatesExtended[name] = templates;
                    }
                    else
                    {
                        _this.templates[name] = templates;
                    }
                    _this.show(name, false);
                }
                else
                    _this.close();
            }
        );
    },
    countObject: function (obj)
    {
        var count = 0;
        for (var k in obj) {
            if (obj.hasOwnProperty(k))
            {
                ++count;
            }
        }
        return count;
    },
    createHiddenNameInput: function (name, a)
    {
        return BX.create('input', {
            props: {
                type: 'hidden',
                className: 'adv-hidden-name-input',
                value: name || this.MESS.NAME,
                name: 'TEMPLATE_PROP[' + a + '][BANNER_NAME]'
            }
        });
    },
    mergeObjects: function (target, source)
    {
        target = target || {};
        for (var prop in source)
        {
            if (source.hasOwnProperty(prop))
                target[prop] = source[prop];
        }
        return target;
    },
    select: function (oVal)
    {
        var name = this.getName(oVal),
            tNew = (!oVal) ? true : false,
            template = !!this.nExtMode.checked ? this.templatesExtended[name] : this.templates[name];
        if (!name)
            this.close();
        else if(template)
            this.show(name, false);
        else
            this.create(name, oVal, tNew);
    },
    getName: function (oVal)
    {
        var options = BX.findChild(this.nType, {tagName:"option"}, true, true),
            name = '',
            oVal = oVal || false;

        if (oVal && oVal.parameters.NAME)
            name = oVal.parameters.NAME;
        else
        {
            for (var i in options)
            {
                if (options[i].selected)
                    name = options[i].getAttribute('data-name');
            }
        }
        return name;
    },
    getCurValues: function (ind, ext, fromObject)
    {
        var name = this.getName(),
            oTemplate = !!ext ? this.templatesExtended[name] : this.templates[name],
            oTProps = oTemplate[ind].oParams,
            curValues = {},
            node;
        for (var i in oTProps)
        {
            if (oTProps.hasOwnProperty(i))
            {
                node = fromObject ? oTemplate[ind].nProps[i] : BX('TEMPLATE_PROP_' + ind + '_' + i);
                if (oTProps[i].TYPE == 'LIST')
                {
                    var options = BX.findChildren(node, {tag: 'option'}, true, true);
                    if (options && options.length > 0 )
                    {
                        if (oTProps[i].MULTIPLE == 'Y')
                        {
                            curValues[i] = [];
                            for (var opt in options)
                            {
                                if (options[opt].selected)
                                    curValues[i].push(options[opt].value);
                            }
                        }
                        else
                        {
                            for (var opt in options)
                            {
                                if (options[opt].selected)
                                    curValues[i] = options[opt].value;
                            }
                        }
                    }
                }
                if (oTProps[i].TYPE == 'CHECKBOX')
                {
                    var checkb = fromObject
                        ? node.childNodes[1].childNodes[1]
                        : BX('TEMPLATE_PROP_' + ind + '_'  + i);
                    if (checkb)
                    {
                        if (checkb.type == 'checkbox' && checkb.checked)
                            curValues[i] = 'Y';
                        else
                            curValues[i] = 'N';
                    }
                }
            }
        }
        curValues['EXTENDED_MODE'] = BX('EXTENDED_MODE').checked ? 'Y' : 'N';
        return curValues;
    },
    close: function ()
    {
        this.nComponent.style.display = 'none';
        this.nComponentHead.style.display = 'none';
        this.nAddTemplate.style.display = 'none';
        window.changeTemplateNodes('clean');
    },
    buildProperty: function (propObj, value, name, tNew, index)
    {
        var type = propObj.TYPE.toUpperCase();
        switch(type)
        {
            case "LIST": return this.createListInput(propObj, value, name, tNew, index);
            case "CHECKBOX": return this.createCheckboxInput(propObj, value, name, index);
            case "STRING": return this.createStringInput(propObj,value, name, tNew, index);
            case "COLORPICKER": return this.createColorpicker(propObj, value, name, tNew, index);;
            case "IMAGE": return this.createHTMLString(propObj);
            case "FILE": return this.createHTMLString(propObj);
            case "HTML": return this.createHTMLString(propObj);
            case "PRESET": return this.createPresetSelection(propObj, value, name, tNew, index);
            default: return this.createStringInput(propObj,value, name, tNew, index);
        }
    },
    createListInput: function (propObj, value, name, tNew, index)
    {
        var multiple = (propObj.MULTIPLE && propObj.MULTIPLE == 'Y') ? true : false;
        var ind = parseInt(index) >= 0 ? index : 0,
            id = 'TEMPLATE_PROP_' + ind + '_' + name,
            name = 'TEMPLATE_PROP[' + ind + '][' + name + ']' + (multiple ? '[]' : ''),
            size = parseInt(propObj.SIZE, 10), selected, events,
            _this = this,
            str = '';

        if (!size)
            size = multiple ? 3 : 1;

        if (propObj.REFRESH && propObj.REFRESH == 'Y')
            events = {change: function(){_this.refresh(ind)}};

        var input = BX.create(
            'select', {
                props: {
                    id: id,
                    name: name,
                    multiple: multiple,
                    size: size
                },
                events: events
            });

        if (tNew)
        {
            if (multiple)
            {
                value = [];
                for (var a in propObj.DEFAULT)
                {
                    propObj.DEFAULT[a] = !!propObj.DEFAULT[a] ? propObj.DEFAULT[a] : '';
                    value.push(propObj.DEFAULT[a]);
                }
            }
            else
                value =  !!propObj.DEFAULT ? propObj.DEFAULT : '';
        }

        for (var i in propObj.VALUES)
        {
            selected = false;
            if (multiple)
            {
                for (var k in value)
                {
                    if (i == value[k])
                    {
                        selected = true;
                        if (str.length > 0)
                            str += ', ';
                        str += propObj.VALUES[i];
                        break;
                    }
                }
            }
            else
            {
                selected = (i == value) ? true : false;
                if (selected)
                    str = propObj.VALUES[i];
            }

            if (propObj.VALUES.hasOwnProperty(i))
            {
                var option = BX.create('option', {
                    props : {
                        value: i,
                        selected: selected
                    },
                    text: propObj.VALUES[i]
                });
                input.appendChild(option);
            }
        }

        if (!this.canEdit)
            return jsUtils.htmlspecialchars(str);

        return input;
    },
    createCheckboxInput: function (propObj, value, name, index)
    {
        var events, _this = this, ind = parseInt(index) >= 0 ? index : 0;

        if(!this.canEdit)
        {
            var str;
            if (!value && propObj.DEFAULT == 'Y' || value == 'Y')
                str = this.MESS.YES;
            else
                str = this.MESS.NO;

            return str;
        }

        if (propObj.REFRESH && propObj.REFRESH == 'Y')
            events = {change: function(){_this.refresh(ind)}};
        var inputHid = BX.create(
            'input', {
                props: {
                    type: 'hidden',
                    name: 'TEMPLATE_PROP[' + ind + '][' + name + ']',
                    value: 'N'
                }
            });
        var inputCheck = BX.create(
            'input', {
                props: {
                    id: 'TEMPLATE_PROP_' + ind + '_' + name,
                    type: 'checkbox',
                    name: 'TEMPLATE_PROP[' + ind + '][' + name + ']',
                    value: 'Y'
                },
                events: events
            });
        if(!value && propObj.DEFAULT == 'Y' || value == 'Y')
            inputCheck.checked = true;
        return [inputHid, inputCheck];
    },
    createPresetSelection: function (propObj, value, name, tNew, index)
    {
        if (!propObj.IMAGES) return [];
        var eventInput, eventLabel = {click: function(){_this.selectPreset(this)}},
            arr = [], _this = this,
            ind = parseInt(index) >= 0 ? index : 0;
        var pad = this.oConfig.adminMode ? '3px 3px 0px 3px' : '3px';
        if (propObj.REFRESH && propObj.REFRESH == 'Y')
            eventInput = {change: function(){_this.refresh(ind)}};
        value = !!value ? parseInt(value) : (!!propObj.DEFAULT ? parseInt(propObj.DEFAULT) : 0);
        for (var i in propObj.IMAGES)
        {
            var radio = BX.create(
                'input', {
                    props: {
                        type: 'radio',
                        className: 'input_hidden',
                        name: 'TEMPLATE_PROP[' + ind + '][' + name + ']',
                        id: 'preset' + ind + '_' + i,
                        value: i
                    },
                    events: eventInput
                });
            var label = BX.create(
                this.canEdit ? 'label' : 'div', {
                    children: [
                        BX.create(
                            'img',{
                                props: {
                                    src: propObj.IMAGES[i]
                                },
                                style: {
                                    padding: pad
                                }
                            })
                    ],
                    props: {
                        className: 'preset'
                    },
                    events: this.canEdit ? eventLabel : ''
                });
            label.setAttribute("for", 'preset' + ind + '_' + i);
            if (value == i)
            {
                radio.checked = true;
                BX.addClass(label, 'selected_radio');
            }
            var div = BX.create('div', {style:{display: 'inline-block', margin: '0 5px'}});
            div.appendChild(radio);
            div.appendChild(label);
            arr.push(div);
        }
        return arr;
    },
    selectPreset: function (label)
    {
        var td = label.parentNode.parentNode;
        var labels = BX.findChildren(td, {tagName: 'label', className: 'preset'}, true);
        for (var i in labels)
        {
            BX.removeClass(labels[i], 'selected_radio');
        }
        BX.addClass(label, 'selected_radio');
    },
    createStringInput: function (propObj, value, name, tNew, index)
    {
        var text =  tNew ? (!!propObj.DEFAULT ? propObj.DEFAULT : '') : (!!value ? value : ''),
            cols = parseInt(propObj.COLS) || 20,
            rows = parseInt(propObj.ROWS),
            ind = parseInt(index) >= 0 ? index : 0;

        if (!this.canEdit)
        {
            return jsUtils.htmlspecialchars(text);
        }

        if (rows && rows > 1)
            var input = BX.create(
                'textarea', {
                    props: {
                        name: 'TEMPLATE_PROP[' + ind + '][' + name + ']',
                        cols: cols,
                        rows: rows,
                        value: text
                    }
                });
        else
            var input = BX.create(
                'input', {
                    props: {
                        type: 'text',
                        name: 'TEMPLATE_PROP[' + ind + '][' + name + ']',
                        size: '50',
                        value: text
                    },
                    attrs: {
                        maxlength: '255',
                    }
                });
        return input;
    },
    createColorpicker: function (propObj, value, name, tNew, index)
    {
        var color =  tNew ? (!!propObj.DEFAULT ? propObj.DEFAULT : '000000') : (!!value ? value : ''),
            ind = parseInt(index) >= 0 ? index : 0;

        if (!this.canEdit)
        {
            return BX.create(
                'div',
                {
                    style: {
                        display: 'table'
                    },
                    children: [
                        BX.create(
                            'span', {
                                style: {
                                    display: 'table-cell',
                                    verticalAlign: 'middle'
                                },
                                text: '#' + color
                            }
                        ),
                        BX.create(
                            'span', {
                                style: {
                                    display: 'inline-block',
                                    backgroundColor: '#' + color,
                                    width: '16px',
                                    height: '16px',
                                    border: '1px solid #808080',
                                    marginLeft: '5px'
                                }
                            }
                        )
                    ]
                }
            );
        }

        var input = BX.create(
            "INPUT", {
                props: {
                    size: 10,
                    name: 'TEMPLATE_PROP[' + ind + '][' + name + ']',
                    value: color,
                    type: 'text',
                    id: 'CP_bx_' + ind + '_' + name
                },
                style: {
                    minWidth: '100px',
                    float: 'left',
                    marginRight: '3px'
                }
            }
        );
        var script = BX.create(
            'script',{
                text: "BX.loadScript(\'/bitrix/components/bitrix/main.colorpicker/templates/.default/script.js\',  function(){window.oBXBannerTemplate.createCPObject(\'"+ind+'_'+name+"\', \'"+color+"\')});"
            }
        );
        return [input, script];
    },
    createHTMLString: function (propObj)
    {
        return propObj.HTML;
    },
    appendJS: function (html)
    {
        var patt1 = new RegExp ("<"+"script"+">[^\000]*?<"+"\/"+"script"+">", "ig"), s;
        var code1 = html.match(patt1);
        if (code1)
        {
            for(var i = 0; i < code1.length; i++)
            {
                if (code1[i] != '')
                {
                    s = code1[i].substring(8, code1[i].length-9);
                    window.setTimeout(s, 0);
                }
            }
        }
        var patt2 = new RegExp ("<"+"script"+">[^\000]*?<"+"\/"+"script"+">", "ig"), s;
        var code2 = html.match(patt2);
        if (code2)
        {
            for(var i = 0; i < code2.length; i++)
            {
                if (code2[i] != '')
                {
                    s = code2[i].substring(31, code2[i].length-9);
                    window.setTimeout(s, 0);
                }
            }
        }
    },
    createCPObject: function (name, color)
    {
        BXColorPicker.prototype.Create = function ()
        {
            var _this = this;
            window['bx_colpic_keypress_' + this.fid] = function(e){_this.OnKeyPress(e);};
            window['bx_colpic_click_' + this.fid] = function(e){_this.OnDocumentClick(e);};

            this.pColCont = document.body.appendChild(BX.create("DIV", {props: {className: "bx-colpic-cont"} }));

            BX.ZIndexManager.register(this.pColCont);

            var arColors = [
                '#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#FF00FF', '#FFFFFF', '#EBEBEB', '#E1E1E1', '#D7D7D7', '#CCCCCC', '#C2C2C2', '#B7B7B7', '#ACACAC', '#A0A0A0', '#959595',
                '#EE1D24', '#FFF100', '#00A650', '#00AEEF', '#2F3192', '#ED008C', '#898989', '#7D7D7D', '#707070', '#626262', '#555555', '#464646', '#363636', '#262626', '#111111', '#000000',
                '#F7977A', '#FBAD82', '#FDC68C', '#FFF799', '#C6DF9C', '#A4D49D', '#81CA9D', '#7BCDC9', '#6CCFF7', '#7CA6D8', '#8293CA', '#8881BE', '#A286BD', '#BC8CBF', '#F49BC1', '#F5999D',
                '#F16C4D', '#F68E54', '#FBAF5A', '#FFF467', '#ACD372', '#7DC473', '#39B778', '#16BCB4', '#00BFF3', '#438CCB', '#5573B7', '#5E5CA7', '#855FA8', '#A763A9', '#EF6EA8', '#F16D7E',
                '#EE1D24', '#F16522', '#F7941D', '#FFF100', '#8FC63D', '#37B44A', '#00A650', '#00A99E', '#00AEEF', '#0072BC', '#0054A5', '#2F3192', '#652C91', '#91278F', '#ED008C', '#EE105A',
                '#9D0A0F', '#A1410D', '#A36209', '#ABA000', '#588528', '#197B30', '#007236', '#00736A', '#0076A4', '#004A80', '#003370', '#1D1363', '#450E61', '#62055F', '#9E005C', '#9D0039',
                '#790000', '#7B3000', '#7C4900', '#827A00', '#3E6617', '#045F20', '#005824', '#005951', '#005B7E', '#003562', '#002056', '#0C004B', '#30004A', '#4B0048', '#7A0045', '#7A0026'
            ];

            var
                row, cell, colorCell,
                tbl = BX.create("TABLE", {props:{className: 'bx-colpic-tbl'}}),
                i, l = arColors.length;

            row = tbl.insertRow(-1);
            cell = row.insertCell(-1);
            cell.colSpan = 8;
            var defBut = cell.appendChild(BX.create("SPAN", {props:{className: 'bx-colpic-def-but'}}));
            defBut.innerHTML = window.jsColorPickerMess.DefaultColor;
            defBut.onmouseover = function()
            {
                this.className = 'bx-colpic-def-but bx-colpic-def-but-over';
                colorCell.style.backgroundColor = 'transparent';
            };
            defBut.onmouseout = function(){this.className = 'bx-colpic-def-but';};
            defBut.onclick = function(e){_this.Select(false);}

            colorCell = row.insertCell(-1);
            colorCell.colSpan = 8;
            colorCell.className = 'bx-color-inp-cell';
            colorCell.style.backgroundColor = '#'+color;

            for(i = 0; i < l; i++)
            {
                if (Math.round(i / 16) == i / 16) // new row
                    row = tbl.insertRow(-1);

                cell = row.insertCell(-1);
                cell.innerHTML = '&nbsp;';
                cell.className = 'bx-colpic-col-cell';
                cell.style.backgroundColor = arColors[i];
                cell.id = 'bx_color_id__' + i;

                cell.onmouseover = function (e)
                {
                    this.className = 'bx-colpic-col-cell bx-colpic-col-cell-over';
                    colorCell.style.backgroundColor = arColors[this.id.substring('bx_color_id__'.length)];
                };
                cell.onmouseout = function (e){this.className = 'bx-colpic-col-cell';};
                cell.onclick = function (e)
                {
                    var k = this.id.substring('bx_color_id__'.length);
                    _this.Select(arColors[k]);
                };
            }

            tbl.onmouseout = function (e){colorCell.style.backgroundColor = "#" + _this.oPar.input.value;};
            this.pColCont.appendChild(tbl);
            this.bCreated = true;
        };

        window['COLORPICKER_' + name] = new window.BXColorPicker({
            id: 'CP_bx_' + name,
            input: BX('CP_bx_' + name),
            name: this.MESS.SELECT_COLOR,
            OnSelect: BX.delegate(function(color) {
                if (!color)
                    color = '000000';
                else
                    color = color.substring(1);

                BX('CP_bx_' + name).value = color;
            }, this)
        });
        window['COLORPICKER_' + name].pWnd.style.backgroundPosition = '-280px -21px';
        if (!BX.findChild(BX('CP_bx_' + name).parentNode, {tagName: 'div', className: 'bx-colpic-button-cont'}, false))
        {
            BX('CP_bx_' + name).parentNode.appendChild(window['COLORPICKER_' + name].pCont);
        }
    },
    initDraggableItems: function ()
    {
        var _this = this;
        this.dragdrop = BX.DragDrop.create({
            dragItemClassName: 'adv-template-banner-container',
            dragItemControlClassName: 'adv-banner-thead',
            sortable: {
                rootElem: BX('ADV_BANNER_PROPERTIES_CONTAINER'),
                gagClass: 'advdrag',
                gagHtml: ''
            },
            dragEnd: function(eventObj, dragElement, event){
                _this.sortItems();
                _this.repairEditor(dragElement);
            }
        });
    },
    addDraggableItem: function(item)
    {
        if (!this.dragdrop) return;
        this.dragdrop.addSortableItem(item);
        this.dragdrop.bindDragItem([item]);
    },
    removeDraggableItem: function(item)
    {
        if (!this.dragdrop) return;
        this.dragdrop.removeSortableItem(item);
    },
    sortItems: function()
    {
        var itemList = this.nBannerContainer.children,
            elementPosition = {}, elementSortText, elementNameText, id, name, sort = 1, message, templateName = this.getName();
        for (var i in itemList)
        {
            if (!itemList[i] || !itemList[i].querySelectorAll)
                continue;

            elementSortText = itemList[i].querySelector('.heading');
            elementNameText = itemList[i].querySelector('.adv-hidden-name-input');
            if (elementSortText && elementNameText)
            {
                id = elementSortText.id.substring(elementSortText.id.indexOf('eTemplatePropsHead')+18);
                name = elementNameText.value || this.MESS.NAME;
                elementPosition[id] = sort;
                message = this.canAdd[templateName] ? this.MESS.SLIDE + ' #' + sort + ': <span class="adv-name-text">' + name + '</span>' : '<span class="adv-name-text">' + name + '</span>';
                elementSortText.firstChild.firstChild.innerHTML = message;
                sort++;
            }
            this.tPosition[templateName] = elementPosition;
        }
    },
    saveNames: function()
    {
        var itemList = this.nBannerContainer.children, elementNameText, name;
        this.aNames = [];
        for (var i in itemList)
        {
            if (!itemList[i] || !itemList[i].querySelectorAll)
                continue;

            elementNameText = itemList[i].querySelector('.adv-hidden-name-input');
            if (elementNameText)
            {
                this.aNames.push(elementNameText.value || this.MESS.NAME);
            }
        }
    },
    repairEditor: function(item)
    {
        var container = BX.findChild(item, {'className': 'typearea'}, true);
        if (!container)
            return;
        var id, name;
        var attr;
        for(var i in container.attributes)
        {
            if (!container.attributes[i]) continue;
            attr = container.attributes[i];

            if(attr.nodeName == 'id')
                id = attr.nodeValue;
            else if(attr.nodeName == 'name')
                name = attr.nodeValue;
        }

        var messageHtmlEditor;
        if(window.BXHtmlEditor)
            messageHtmlEditor = window.BXHtmlEditor.Get(name);

        if(!messageHtmlEditor)
            return;

        setTimeout(
            function(){
                messageHtmlEditor.CheckAndReInit();
            }, 100
        );
    },
    addListenerControlItem: function(item)
    {
        if (!item || !item.querySelector)
            return;

        var buttonToggleShow = item.querySelector('.adv-banner-show-button');
        var contToggleShow = item.querySelector('.adv-banner-tbody');
        if (buttonToggleShow && contToggleShow)
        {
            BX.bind(buttonToggleShow, 'click', BX.delegate(function(){
                this.toggleShow(contToggleShow, buttonToggleShow, null, null);
            }, this));
        }

        var buttonDeleteItem = item.querySelector('.adv-banner-delete-button');
        if (buttonDeleteItem)
        {
            BX.bind(buttonDeleteItem, 'click', BX.delegate(function(){
                this.deleteItem(item);
            }, this));
        }

        var showRenameDialogButton = item.querySelector('.adv-name-text').parentNode;
        if(showRenameDialogButton && this.canEdit)
        {
            BX.bind(showRenameDialogButton, 'click', BX.delegate(function(){
                this.showRenameDialog(item, showRenameDialogButton);
            }, this));
            showRenameDialogButton.style.cursor = 'pointer';
        }
    },
    toggleShow: function (body, button, item, isShow)
    {
        if (!body && item)
        {
            body = item.querySelector('.adv-banner-tbody');
        }
        if (!button && item)
        {
            button = item.querySelector('.adv-banner-show-button');
        }

        if (body && button)
        {
            if (isShow === null)
            {
                if (body.style.display == 'none')
                    isShow = true;
                else
                    isShow = false;
            }

            BX.removeClass(button, 'adv-banner-hide-button');
            if(isShow)
            {
                var easing = new BX.easing({
                    duration : 300,
                    start : { opacity : 0 },
                    finish : { opacity : 100 },
                    transition : BX.easing.transitions.quart,
                    step : function(state){
                        body.style.opacity = state.opacity / 100;
                        body.style.display = '';
                    }
                });
                easing.animate();
                button.innerHTML = this.MESS.HIDE;
                BX.addClass(button, 'adv-banner-hide-button');
            }
            else
            {
                body.style.display = 'none';
                button.innerHTML = this.MESS.SHOW;
            }

        }
    },
    deleteItem: function (elementDelete)
    {
        var easing = new BX.easing({
            duration : 500,
            start : {opacity: 100 },
            finish : {opacity : 0 },
            transition : BX.easing.transitions.quart,
            step : function(state){
                elementDelete.style.opacity = state.opacity/100;
            },
            complete : BX.delegate(function() {
                this.removeDraggableItem(elementDelete);
                BX.remove(elementDelete);
                this.sortItems();
            }, this)
        });
        easing.animate();
    },
    showRenameDialog: function(item, button)
    {
        var popupWindow = BX.PopupWindowManager.create(
            'adv-banner-rename-dialog-container',
            button,
            {
                'darkMode': false,
                'closeIcon': true,
                'content': BX('ADV_RENAME_DIALOG'),
                'className': 'adm-workarea',
                'autoHide': true,
                'zIndex': BX.WindowManager ? BX.WindowManager.GetZIndex() + 10 : 0
            }
        );
        popupWindow.close();
        popupWindow.setBindElement(button);

        var btnRenameCancel = BX('ADV_RENAME_DIALOG_BTN_CANCEL');
        var btnRenameSave = BX('ADV_RENAME_DIALOG_BTN_SAVE');

        popupWindow.close();

        BX.unbindAll(btnRenameCancel);
        BX.bind(btnRenameCancel, 'click', function(){popupWindow.close();});

        BX.unbindAll(btnRenameSave);
        BX.bind(btnRenameSave, 'click', BX.delegate(function(){
            this.setNameItem(item);
            this.setNameText(item);
            popupWindow.close();
        }, this));

        this.setNameToDialog(item);
        popupWindow.show();
    },
    setNameToDialog: function(item)
    {
        if(!item || !item.querySelector)
            return;

        var dialogNameValue = BX('ADV_RENAME_DIALOG_VALUE');
        var name = item.querySelector('.adv-hidden-name-input');
        if (!!name)
            dialogNameValue.value = BX.util.trim(name.value);
    },
    setNameItem: function(item)
    {
        if(!item || !item.querySelector)
            return;

        var dialogNameValue = BX('ADV_RENAME_DIALOG_VALUE');
        var name = item.querySelector('.adv-hidden-name-input');

        name.value = BX.util.trim(dialogNameValue.value);
    },
    setNameText: function(item)
    {
        if(!item || !item.querySelector)
            return;

        var nameInput = item.querySelector('.adv-hidden-name-input');
        var nameText = item.querySelector('.adv-name-text');

        nameText.innerHTML = BX.util.trim(nameInput.value);
    }
}