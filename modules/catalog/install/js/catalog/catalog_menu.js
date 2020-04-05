    BX.ready(
        function()
        {
            BX.adminMenu.toggleDynSection = function(padding, cell, module_id, div_id, level, urlBack)
            {
                if (cell.BXLOAD)
                {
                    this.toggleSection(cell, div_id, level);
                    return;
                }

                cell.BXLOAD = true;
                cell.BXLOAD_AJAX = false;

                var img = BX.create('SPAN', {
                    props: {className: 'adm-submenu-loading adm-sub-submenu-block'},
                    style: {marginLeft: parseInt(padding) + 'px'},
                    text: BX.message('JS_CORE_LOADING')
                });

                setTimeout(BX.proxy(function() {
                    if (!cell.BXLOAD_AJAX)
                    {
                        cell.childNodes[1].appendChild(img);
                        this.toggleSection(cell, div_id, level);
                    }
                }, this), 200);
                BX.ajax.get(
                    '/bitrix/tools/catalog/get_catalog_menu.php',
                    {
                        lang: BX.message('LANGUAGE_ID'),
                        admin_mnu_module_id: module_id,
                        admin_mnu_menu_id: div_id,
                        admin_mnu_url_back: urlBack
                    },
                    BX.proxy(function(result)
                    {
                        cell.BXLOAD_AJAX = true;
                        result = BX.util.trim(result);
                        if (result != '')
                        {
                            var toggleExecuted = img.parentNode ? true : false;
                            cell.childNodes[1].innerHTML = result;
                            if (!toggleExecuted)
                                this.toggleSection(cell, div_id, level);
                        }
                        else
                        {
                            img.innerHTML = BX.message('JS_CORE_NO_DATA');
                            if (!img.parentNode)
                            {
                                cell.childNodes[1].appendChild(img);
                                this.toggleSection(cell, div_id, level);
                            }
                        }
                    }, this)
                );

            };
        }
    );