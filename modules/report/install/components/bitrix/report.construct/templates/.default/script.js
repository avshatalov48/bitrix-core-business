BX.namespace('BX.Report');
BX.Report.ReportConstructClass = (function ()
{
    var ReportConstructClass = function(parameters)
    {
        this.ajaxUrl = '/bitrix/components/bitrix/report.construct/ajax.php';
        this.jsClass = parameters.jsClass;
        this.sharingData = parameters.sharingData;
        this.sessionError = Boolean(parameters.sessionError);

        this.sharingBlockId = 'report-sharing-block';
        this.sharingFormDataId = 'report-sharing-form-data';
        this.helpWindow = false;

        this.destFormName = parameters.destFormName || 'report-construct-destFormName';

        this.init();
    };

    var entityToNewShared = {};
    var loadedReadOnlyEntityToNewShared = {};
    var maxAccessName = '';

    ReportConstructClass.prototype.init = function()
    {
        BX.bind(BX('report-title-sharing-help'), 'mouseover', BX.delegate(function(e) {
            this.onHelpShow();
        }, this));
        BX.bind(BX('report-title-sharing-help'), 'mouseout', BX.delegate(function(e) {
            this.onHelpHide();
        }, this));

        if(this.sessionError)
        {
            BX.Report.showModalWithStatusAction({
                status: 'error',
                message: BX('report-list-error').innerHTML
            });
        }

        entityToNewShared = {};
        loadedReadOnlyEntityToNewShared = {};
        maxAccessName = this.sharingData.maxAccess;

        BX(this.sharingBlockId).appendChild(BX.create('div', {
            props: {
                className: 'bx-report-popup-construct-content'
            },
            children: [
                BX.create('table', {
                    props: {
                        id: 'bx-report-popup-shared-people-list',
                        className: 'bx-report-popup-shared-people-list',
                        style: 'display:none;'
                    },
                    children: [
                        BX.create('thead', {
                            html: '<tr>' +
                            '<td class="bx-report-popup-shared-people-list-head-col1">'
                            +BX.message('REPORT_SHARING_NAME_RIGHTS_USER')
                            +'</td>'+
                            '<td class="bx-report-popup-shared-people-list-head-col2">'
                            +BX.message('REPORT_SHARING_NAME_RIGHTS')+
                            '</td><td class="bx-report-popup-shared-people-list-head-col3">'+
                            '</td></tr>'
                        })
                    ]
                }),
                BX.create('div', {
                    props: {
                        id: 'feed-add-post-destination-container',
                        className: 'feed-add-post-destination-wrap report-destination-wrap-construct'
                    },
                    children: [
                        BX.create('span', {
                            props: {
                                className: 'feed-add-post-destination-item'
                            }
                        }),
                        BX.create('span', {
                            props: {
                                id: 'feed-add-post-destination-input-box',
                                className: 'feed-add-destination-input-box'
                            },
                            style: {
                                background: 'transparent'
                            },
                            children: [
                                BX.create('input', {
                                    props: {
                                        type: 'text',
                                        value: '',
                                        id: 'feed-add-post-destination-input',
                                        className: 'feed-add-destination-inp report-destination-input'
                                    }
                                })
                            ]
                        }),
                        BX.create('a', {
                            props: {
                                href: '#',
                                id: 'bx-destination-tag',
                                className: 'feed-add-destination-link'
                            },
                            style: {
                                background: 'transparent'
                            },
                            text: BX.message('REPORT_SHARING_NAME_ADD_RIGHTS_USER'),
                            events: {
                                click: BX.delegate(function () {
                                }, this)
                            }
                        })
                    ]
                })
            ]
        }));

        BX.addCustomEvent('onChangeRightOfSharing',
            BX.proxy(this.onChangeRightOfSharing, this));

        for (var i in this.sharingData.members) {
            if (!this.sharingData.members.hasOwnProperty(i)) {
                continue;
            }
            entityToNewShared[this.sharingData.members[i].entityId] = {
                item: {
                    id: this.sharingData.members[i].entityId,
                    name: this.sharingData.members[i].name,
                    avatar: this.sharingData.members[i].avatar
                },
                type: this.sharingData.members[i].type,
                right: this.sharingData.members[i].right
            };
        }

        BX.SocNetLogDestination.init({
            name : this.destFormName,
            searchInput : BX('feed-add-post-destination-input'),
            bindMainPopup : {
                'node': BX('feed-add-post-destination-container'),
                'offsetTop' : '5px', 'offsetLeft': '15px'
            },
            bindSearchPopup : {
                'node': BX('feed-add-post-destination-container'),
                'offsetTop' : '5px', 'offsetLeft': '15px'
            },
            callback : {
                select : BX.proxy(this.onSelectDestination, this),
                unSelect : BX.proxy(this.onUnSelectDestination, this),
                openDialog : BX.proxy(this.onOpenDialogDestination, this),
                closeDialog : BX.proxy(this.onCloseDialogDestination, this),
                openSearch : BX.proxy(this.onOpenSearchDestination, this),
                closeSearch : BX.proxy(this.onCloseSearchDestination, this)
            },
            items: this.sharingData.destination.items,
            itemsLast: this.sharingData.destination.itemsLast,
            itemsSelected : this.sharingData.destination.itemsSelected
        });

        var BXSocNetLogDestinationFormName = this.destFormName;
        BX.bind(BX('feed-add-post-destination-container'), 'click', function(e){
            BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormName);
            BX.PreventDefault(e);
        });
        BX.bind(BX('feed-add-post-destination-input'), 'keyup',
            BX.proxy(this.onKeyUpDestination, this));
        BX.bind(BX('feed-add-post-destination-input'), 'keydown',
            BX.proxy(this.onKeyDownDestination, this));
    };

    ReportConstructClass.prototype.onSetEntityToForm = function()
    {
        BX(this.sharingFormDataId).innerHTML = '';

        for(var k in entityToNewShared)
        {
            if (!entityToNewShared.hasOwnProperty(k)) {
                continue;
            }
            BX(this.sharingFormDataId).appendChild(
                BX.create('input', {
                    props: { id: 'report-sharing-entity-'+k },
                    attrs: { name: 'sharing_entity['+k+']', value: entityToNewShared[k].right }
                })
            );
        }
    };

    ReportConstructClass.prototype.onDeleteEntityFromForm = function(entityId)
    {
        BX.Report.removeElement(BX('report-sharing-entity-'+entityId));
    };

    ReportConstructClass.prototype.onSelectDestination = function(item, type, search)
    {
        entityToNewShared[item.id] = entityToNewShared[item.id] || {};
        BX.Report.appendNewShared({
            maxAccessName: maxAccessName,
            readOnly: !!loadedReadOnlyEntityToNewShared[item.id],
            destFormName: this.destFormName,
            item: item,
            type: type,
            right: entityToNewShared[item.id].right
        });
        entityToNewShared[item.id] = {
            item: item,
            type: type,
            right: entityToNewShared[item.id].right || 'access_read'
        };
        this.onSetEntityToForm();
        BX.Report.show(BX('bx-report-popup-shared-people-list'));
    };

    ReportConstructClass.prototype.onUnSelectDestination = function (item, type, search)
    {
        var entityId = item.id;

        if(!!loadedReadOnlyEntityToNewShared[entityId])
        {
            return false;
        }

        this.onDeleteEntityFromForm(entityId);

        delete entityToNewShared[entityId];

        var child = BX.findChild(BX('bx-report-popup-shared-people-list'),
            {attribute: {'data-dest-id': '' + entityId + ''}}, true);
        if (child) {
            BX.remove(child);
        }

        if(BX.Report.isEmptyObject(entityToNewShared))
        {
            BX.Report.hide(BX('bx-report-popup-shared-people-list'));
        }
    };

    ReportConstructClass.prototype.onOpenDialogDestination = function()
    {
        BX.style(BX('feed-add-post-destination-input-box'), 'display', 'inline-block');
        BX.style(BX('bx-destination-tag'), 'display', 'none');
        BX.focus(BX('feed-add-post-destination-input'));
        if(BX.SocNetLogDestination.popupWindow)
            BX.SocNetLogDestination.popupWindow.adjustPosition({ forceTop: true });
    };

    ReportConstructClass.prototype.onCloseDialogDestination = function()
    {
        var input = BX('feed-add-post-destination-input');
        if (!BX.SocNetLogDestination.isOpenSearch() && input && input.value.length <= 0)
        {
            BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
            BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
        }
    };

    ReportConstructClass.prototype.onOpenSearchDestination = function()
    {
        if(BX.SocNetLogDestination.popupSearchWindow)
            BX.SocNetLogDestination.popupSearchWindow.adjustPosition({ forceTop: true });
    };

    ReportConstructClass.prototype.onCloseSearchDestination = function()
    {
        var input = BX('feed-add-post-destination-input');
        if (!BX.SocNetLogDestination.isOpenSearch() && input && input.value.length > 0)
        {
            BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
            BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
            BX('feed-add-post-destination-input').value = '';
        }
    };

    ReportConstructClass.prototype.onKeyUpDestination = function (event)
    {
        var BXSocNetLogDestinationFormName = this.destFormName;
        if (event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 ||
            event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 224 || event.keyCode == 91)
            return false;

        if (event.keyCode == 13) {
            BX.SocNetLogDestination.selectFirstSearchItem(BXSocNetLogDestinationFormName);
            return BX.PreventDefault(event);
        }
        if (event.keyCode == 27) {
            BX('feed-add-post-destination-input').value = '';
        }
        else {
            BX.SocNetLogDestination.search(
                BX('feed-add-post-destination-input').value, true, BXSocNetLogDestinationFormName);
        }

        if (BX.SocNetLogDestination.sendEvent && BX.SocNetLogDestination.isOpenDialog())
            BX.SocNetLogDestination.closeDialog();

        if (event.keyCode == 8) {
            BX.SocNetLogDestination.sendEvent = true;
        }
        return BX.PreventDefault(event);
    };

    ReportConstructClass.prototype.onKeyDownDestination = function (event)
    {
        var BXSocNetLogDestinationFormName = this.destFormName;
        if (event.keyCode == 8 && BX('feed-add-post-destination-input').value.length <= 0) {
            BX.SocNetLogDestination.sendEvent = false;
            BX.SocNetLogDestination.deleteLastItem(BXSocNetLogDestinationFormName);
        }

        return true;
    };

    ReportConstructClass.prototype.onChangeRightOfSharing = function(entityId, taskName)
    {
        if(entityToNewShared[entityId])
        {
            entityToNewShared[entityId].right = taskName;
        }
    };

    ReportConstructClass.prototype.export = function(reportId)
    {
        var form = BX.create('form', {
            props: {
                method: 'POST'
            },
            children: [
                BX.create('input', {
                    props: {
                        type: 'hidden',
                        name: 'sessid',
                        value: BX.bitrix_sessid()
                    }
                }),
                BX.create('input', {
                    props: {
                        type: 'text',
                        name: 'EXPORT_REPORT',
                        value: BX.util.htmlspecialchars(reportId)
                    }
                })
            ]
        });

        document.body.appendChild(form);
        BX.submit(form);
    };

    ReportConstructClass.prototype.onHelpShow = function()
    {
        var node = BX.proxy_context, text = node.innerHTML;
        if(BX.type.isNotEmptyString(text))
        {
            if(this.helpWindow)
            {
                this.helpWindow.close();
            }

            var _this = this;
            var popup = new BX.PopupWindow('report-help', node, {
                lightShadow: true,
                autoHide: false,
                darkMode: true,
                offsetLeft: 0,
                offsetTop: 2,
                bindOptions: {position: 'top'},
                zIndex: 200,
                events : {
                    onPopupClose : function() {
                        this.destroy();
                        _this.helpWindow = false;
                    }
                },
                content : BX.create('div', { attrs : {
                    style : 'padding-right: 5px; width: 250px;' }, html: text})
            });
            popup.setAngle({offset:13, position: 'bottom'});
            popup.show();

            this.helpWindow = popup;
        }
    };
    ReportConstructClass.prototype.onHelpHide = function()
    {
        if(this.helpWindow)
        {
            this.helpWindow.close();
        }
    };

    return ReportConstructClass;
})();