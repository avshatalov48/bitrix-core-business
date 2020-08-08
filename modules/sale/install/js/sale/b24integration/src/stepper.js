export class Stepper
{
    constructor(props = {})
    {
        this.ownerId = !!props.ownerId ? props.ownerId:0;
        this.ownerTypeId = !!props.ownerTypeId ? props.ownerTypeId:0;

    }

    progress(list, total = 0, start = 0)
    {
        if (!list || list.length <= 0)
        {
            throw new Error('list must be defined');
        }

        Stepper.isSuccess = true;

        this.batchFetch(list, total, start)
            .then(
                batch => {
                    this.activityAdds(batch)
                        .then(
                            () => this.clientAdds(batch)
                                .then(
                                    () => this.dealUpdateContacts(this.ownerId, batch)
                                        .then(
                                            () => Stepper.getFulfillPromise(),
                                            () => {throw new Error('batchFetch dealUpdateContacts Error')}),
                                    () => {throw new Error('batchFetch clientAdds Error')}),
                            (activity) => this.continueProcess(activity))// reject to call progress again
                        .then(
                            () => this.nextBatch(batch)
                                .then(
                                    ()=>{},
                                    ()=>Stepper.labelFinish(batch)),
                            () => {throw new Error('progress Error')})},
                () => {throw new Error('batchFetch Error')}
            );
    }

    continueProcess(response)
    {
        let errors = !!response.errors ? response.errors:{};
        if(Object.values(errors).length>0)
        {
            throw new Error('continueProcess Error')
        }
        else
        {
            return Stepper.getFulfillPromise()
        }
    }

    batchFetch(list, total, start)
    {
        return BX.ajax.runAction('sale.integration.stepper.activityBatch', {
            data: {
                list: list,
                total: total,
                start: start
            }})
    }
    activityAdds(response)
    {
        let data = !!response.data ? response.data:{};

        Stepper.progressBar(data);
        Stepper.messageBar(data);

        if(!!data.process)
        {
            /*
            * Object.values(data.process.items).length = n && Object.values(data.process.list).length = 0 //start - one step
            * Object.values(data.process.items).length = 0 && Object.values(data.process.list).length = n // skip step
            *
            * */
            //

            if(Object.values(data.process.items).length>0)
            {
                return BX.ajax.runAction('sale.integration.scenarios.activityAddsFromOrderList', {data: {params: data.process.items}});
            }
            else if(Object.values(data.process.list).length>0)
            {
                return Stepper.getRejectPromise();
            }
            else if(!!data.error && data.error.length>0)
            {
                // продолжаем выполнение, т.к. текст ошибки на данном шаге выведен. пропускаем шаг
                return Stepper.getRejectPromise();
            }


        }
        throw new Error('activityAdds Error')
    }

    prepareContactFields(response)
    {
        if(!!response.status && response.status === 'success')
        {
            let data = !!response.data ? response.data:{};

            if(!!data.process && Object.values(data.process.items).length>0)
            {
                return BX.ajax.runAction('sale.integration.scenarios.resolveContactFieldsValuesFromOrderList', {data: {params: data.process.items}})
            }
        }
        throw new Error('clientAdds Error')
    }

    clientAdds(batch)
    {
        return this.prepareContactFields(batch) //->из исходных данных получили список по локальным пользователям
            .then(
                list => this.contactRelationVoid(list) //->возвращает локальный список пользователей у которых связь с удаленной сущностью отсутвует/не корректна
                    .then(
                        addList => this.contactAdds(addList) //->добавили контакты в удаленную сиситему, обновили связи на локальной. с этого момента для пользователей из batch локально храниться актальная таблица связок к удаленным сущностям
                            .then(
                                () => Stepper.getFulfillPromise(),
                                () => {throw new Error('clientAdds contactAdds Error')}),
                        () => {throw new Error('clientAdds contactRelationVoid Error')}),
                () => {throw new Error('clientAdds prepareContactFields Error')}
                );
    }
    contactRelationVoid(list)
    {
        if(!!list.status && list.status === 'success')
        {
            let data = !!list.data ? list.data:{};

            //если данных нет пропускаем вызов и возвращаем fulfill promise
            if(!!data.result && Object.values(data.result).length>0)
            {
                return BX.ajax.runAction('sale.integration.scenarios.resolveUserTypeIAfterComparingRemotelyRelationFromOrderList', {data: {params: data.result}})
            }
            else
            {
                return Stepper.getFulfillPromise(list)
            }
        }

        throw new Error('contactRelationVoid Error')
    }
    contactAdds(addList)
    {
        if(!!addList.status && addList.status === 'success')
        {
            let data = !!addList.data ? addList.data:{};

            //если данных нет пропускаем вызов и возвращаем fulfill promise
            if(!!data.result && Object.values(data.result).length>0)
            {
                return BX.ajax.runAction('sale.integration.scenarios.contactAddsFromOrderList', {data: {params: data.result}})
            }
            else
            {
                return Stepper.getFulfillPromise()
            }
        }

        throw new Error('contactAdds Error')
    }

    dealUpdateContacts(dealId, batch)
    {
        return this.prepareContactFields(batch) //->из исходных данных получили список по локальным пользователям
            .then(
                (list) => this.dealContactItemsUpdate(dealId, list), //->обновляем пользоватлей в сделке (обогощаем сделку контактами)
                () => {throw new Error('dealUpdateContacts prepareContactFields Error')});
    }

    dealContactItemsUpdate(dealId, list)
    {
        if(!!list.status && list.status === 'success')
        {
            let data = !!list.data ? list.data:{};

            //если данных нет пропускаем вызов и возвращаем fulfill promise
            // (например когда в заказе указана компания, а запрашиваются данные клинта-Контакта)
            if(!!data.result && Object.values(data.result).length>0)
            {
                return this.dealContactItemsGet(dealId)
                    .then(
                        (items)=>this.dealContactAdds(dealId, {list:list, items:items}),
                        () => {throw new Error('dealUpdateContacts dealContactAdds Error')})
            }
            else
            {
                return Stepper.getFulfillPromise()
            }
        }

        throw new Error('dealUpdateContacts dealContactItemsUpdate Error')
    }

    dealContactAdds(dealId, params)
    {
        // метод должен вызываться когда гарантровано есть список пльзоватлей из БУС для обогощения сделки
        // если у сделки есть контакты, то обогощаем их пользователями
        // если у сделки нет контактов добавляем всех пользователей

        let users = !!params.list ? params.list:{};
        let contacts = !!params.items ? params.items:{};

        if(!!users.status && users.status === 'success' &&
            !!contacts.status && contacts.status === 'success')
        {
            let dataUsers = !!users.data ? users.data:{};
            let dataContacts = !!contacts.data ? contacts.data:{};

            if(!!dataUsers.result && !!dataContacts.result)
            {
                if(Object.values(dataContacts.result).length>0)
                {
                    return BX.ajax.runAction('sale.integration.scenarios.dealContactUpdates', {data: {id: dealId, items: dataUsers.result, contacts: dataContacts.result}})
                }
                else
                {
                    return BX.ajax.runAction('sale.integration.scenarios.dealContactAdds', {data: {id: dealId, items: dataUsers.result}})
                }
            }
        }

        throw new Error('dealContactAdds Error')
    }
    dealContactItemsGet(dealId)
    {
        return BX.ajax.runAction('sale.integration.scenarios.dealContactItemsGet', {data: {id: dealId}})
    }
    dealUpdate(response)
    {
        if(!!response.status && response.status === 'success')
        {
            let data = !!response.data ? response.data:{};

            if(!!data.process && data.process.items.length>0)
            {
                return BX.ajax.runAction('sale.integration.scenarios.dealupdatecontacts', {data: {id:this.ownerId, params: data.process.items}})
            }
        }
        throw new Error('dealUpdate Error')
    }

    nextBatch(response)
    {
        if(!!response.status && response.status === 'success')
        {
            let data = !!response.data ? response.data:{};

            if(!!data.process &&
                !!data.process.list &&
                !!data.process.items &&
                !!data.process.total &&
                !!data.process.start)
            {
                if (Object.values(data.process.items).length > 0 && Object.values(data.process.list).length > 0)
                {
                    this.progress(data.process.list, data.process.total, data.process.start);
                    return Stepper.getFulfillPromise();
                }
                if (Object.values(data.process.items).length === 0 && Object.values(data.process.list).length > 0)
                {
                    this.progress(data.process.list, data.process.total, data.process.start);
                    return Stepper.getFulfillPromise();
                }
                else if(Object.values(data.process.list).length <= 0)
                {
                    // finish process batch
                    return Stepper.getRejectPromise();
                }
            }
        }
        throw new Error('nextBatch Error')
    }

    static progressBar(data)
    {
        if(!!data.progress)
        {
            BX.ajax.runAction('sale.integration.stepper.progressbar', {data:{value: data.progress}})
                .then(
                    response => Stepper.render('progress', response.data),
                    () => {throw new Error('ProgressBar failure!')}
                );
        }
    }
    static labelFinish(response)
    {
        let data = !!response.data ? response.data:{};

        if(!!data.finish)
        {
            //BX.closeWait();

            BX.ajax.runAction('sale.integration.stepper.messageOK', {})
                .then(
                    response => {
                        Stepper.render('finish', response.data);
                        if(Stepper.isSuccess) {
                            Stepper.closeApplication()}},
                    () => {throw new Error('MessagebyType OK failure!')}
                );
        }
    }
    static messageBar(data)
    {
        if (!!data.error)
        {
            Stepper.isSuccess = false;

            BX.ajax.runAction('sale.integration.stepper.messagebytype', {
                data: {
                    message: data.error,
                    type: 'ERROR'
                }})
                .then(
                    response => {
                        let div = BX.create('DIV');
                        div.innerHTML = response.data;
                        BX('progress_error').appendChild(div);
                    },
                    () => {throw new Error('MessagebyType ERROR failure!')}
                );
        }
    }
    static render(element, result)
    {
        BX.adjust(BX(element), {html: result});
    }

    static getFulfillPromise(params={})
    {
        let promise = new BX.Promise();
        promise.fulfill(params);
        return promise;
    }
    static getFulfillPromise_setTimeout()
    {
        let promise = new BX.Promise();

        setTimeout(() => {
            promise.fulfill(this);
        }, 2000);

        return promise;
    }
    static getRejectPromise()
    {
        let promise = new BX.Promise();

        promise.reject(this);
        return promise;
    }

    static closeApplication()
    {
        setTimeout(() => {
            BX24.closeApplication();
        }, 500);
    }
}