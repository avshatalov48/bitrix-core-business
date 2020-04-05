<?
$MESS["LIBTA_NAME"] = "Название";
$MESS["LIBTA_TYPE"] = "Тип";
$MESS["LIBTA_TYPE_ADV"] = "Реклама";
$MESS["LIBTA_TYPE_EX"] = "Представительские";
$MESS["LIBTA_TYPE_C"] = "Компенсируемые";
$MESS["LIBTA_TYPE_D"] = "Прочее";
$MESS["LIBTA_CREATED_BY"] = "Кем создан";
$MESS["LIBTA_DATE_CREATE"] = "Дата создания";
$MESS["LIBTA_FILE"] = "Файл (копия счета)";
$MESS["LIBTA_NUM_DATE"] = "Номер счета и дата";
$MESS["LIBTA_SUM"] = "Сумма";
$MESS["LIBTA_PAID"] = "Оплачен";
$MESS["LIBTA_PAID_NO"] = "Нет";
$MESS["LIBTA_PAID_YES"] = "Да";
$MESS["LIBTA_BDT"] = "Статья бюджета";
$MESS["LIBTA_DATE_PAY"] = "Дата оплаты (заполняет бухгалтер)";
$MESS["LIBTA_NUM_PP"] = "Номер п/п (заполняет бухгалтер)";
$MESS["LIBTA_DOCS"] = "Копии документов";
$MESS["LIBTA_DOCS_YES"] = "Есть";
$MESS["LIBTA_DOCS_NO"] = "Нет";
$MESS["LIBTA_APPROVED"] = "Утвержден";
$MESS["LIBTA_APPROVED_R"] = "Отказано";
$MESS["LIBTA_APPROVED_N"] = "Не согласовано";
$MESS["LIBTA_APPROVED_Y"] = "Согласовано";
$MESS["LIBTA_T_PBP"] = "Последовательный бизнес-процесс";
$MESS["LIBTA_T_SPA1"] = "Установка прав: автору";
$MESS["LIBTA_T_PDA1"] = "Публикация документа";
$MESS["LIBTA_STATE1"] = "На утверждении";
$MESS["LIBTA_T_SSTA1"] = "Статус: на утверждении";
$MESS["LIBTA_T_ASFA1"] = "Установка поля \"Утвержден\" документа";
$MESS["LIBTA_T_SVWA1"] = "Установка утверждающего";
$MESS["LIBTA_T_WHILEA1"] = "Цикл согласования";
$MESS["LIBTA_T_SA0"] = "Последовательность действий";
$MESS["LIBTA_T_IFELSEA1"] = "Дошли до руководства";
$MESS["LIBTA_T_IFELSEBA1"] = "Да";
$MESS["LIBTA_T_ASFA2"] = "Установка поля \"Утвержден\" документа";
$MESS["LIBTA_T_IFELSEBA2"] = "Нет";
$MESS["LIBTA_T_GUAX1"] = "Выбор начальника";
$MESS["LIBTA_T_SVWA2"] = "Установка утверждающего";
$MESS["LIBTA_T_SPAX1"] = "Установка прав: утверждающему чтение";
$MESS["LIBTA_SMA_MESSAGE_1"] = "Прошу утвердить счет
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Сумма: {=Document:PROPERTY_SUM}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_1"] = "Сообщение: запрос утверждения счета";
$MESS["LIBTA_XMA_MESSAGES_1"] = "КП: Счет на утверждение";
$MESS["LIBTA_XMA_MESSAGET_1"] = "Прошу утвердить счет

Кем создан: {=Document:CREATED_BY_PRINTABLE}
Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}

{=Variable:Link}{=Document:ID}/


Список заданий по бизнес-процессам:
{=Variable:TasksLink}";
$MESS["LIBTA_T_XMA_MESSAGES_1"] = "Сообщение: утверждение счета";
$MESS["LIBTA_AAQN1"] = "Утверждение счета \"{=Document:NAME}\"";
$MESS["LIBTA_AAQD1"] = "Вам необходимо утвердить или отклонить счет

Название: {=Document:NAME}
Дата создания: {=Document:DATE_CREATE}
Автор: {=Document:CREATED_BY_PRINTABLE}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}
Файл: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_AAQN1"] = "Утверждение";
$MESS["LIBTA_STATE2"] = "Утвержден ({=Variable:Approver_printable})";
$MESS["LIBTA_T_SSTA2"] = "Статус: утвержден";
$MESS["LIBTA_STATE3"] = "Не утвержден ({=Variable:Approver_printable})";
$MESS["LIBTA_T_SSTA3"] = "Статус: не утвержден";
$MESS["LIBTA_T_ASFA3"] = "Установка поля \"Утвержден\" документа";
$MESS["LIBTA_T_IFELSEA2"] = "Счет утвержден";
$MESS["LIBTA_T_IFELSEBA3"] = "Да";
$MESS["LIBTA_SMA_MESSAGE_2"] = "Утверждаю счет

Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_2"] = "Сообщение: счет утвержден";
$MESS["LIBTA_T_SPAX2"] = "Установка прав: подтверждающему оплату";
$MESS["LIBTA_SMA_MESSAGE_3"] = "Прошу подтвердить оплату счета

Утвержден: {=Variable:Approver_printable}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}

{=Variable:Link}{=Document:ID}/

Список заданий:
{=Variable:TasksLink}";
$MESS["LIBTA_T_SMA_MESSAGE_3"] = "Сообщение: запрос подтверждения оплаты";
$MESS["LIBTA_XMA_MESSAGES_2"] = "КП: Подтверждение оплаты счета";
$MESS["LIBTA_XMA_MESSAGET_2"] = "Прошу подтвердить оплату счета

Утвержден: {=Variable:Approver_printable}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}

{=Variable:Link}{=Document:ID}/

Список заданий:
{=Variable:TasksLink}";
$MESS["LIBTA_T_XMA_MESSAGES_2"] = "Сообщение: подтверждение оплаты";
$MESS["LIBTA_STATE4"] = "На подтверждении оплаты";
$MESS["LIBTA_T_SSTA4"] = "Статус: на подтверждении оплаты";
$MESS["LIBTA_AAQN2"] = "Подтвердить оплату счета \"{=Document:NAME}\"";
$MESS["LIBTA_AAQD2"] = "Вам необходимо подтвердить или отклонить оплату счета

Утвержден: {=Variable:Approver_printable}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}
Файл: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_AAQN2"] = "Подтверждение оплаты счета";
$MESS["LIBTA_T_SVWA3"] = "Изменение переменных";
$MESS["LIBTA_STATE5"] = "Оплата подтверждена";
$MESS["LIBTA_T_SSTA5"] = "Статус: оплата подтверждена";
$MESS["LIBTA_SMA_MESSAGE_4"] = "Оплата счета подтверждена

Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_4"] = "Сообщение: оплата подтверждена";
$MESS["LIBTA_T_SPAX3"] = "Установка прав: оплачивающему";
$MESS["LIBTA_SMA_MESSAGE_5"] = "Прошу оплатить счет

Оплата подтверждена: {=Variable:PaymentApprover_printable}
Счет утвержден: {=Variable:Approver_printable}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}

{=Variable:Link}{=Document:ID}/

Список заданий:
{=Variable:TasksLink}";
$MESS["LIBTA_T_SMA_MESSAGE_5"] = "Сообщение: счет на оплату";
$MESS["LIBTA_XMA_MESSAGES_3"] = "КП: Счет на оплату";
$MESS["LIBTA_XMA_MESSAGET_3"] = "Прошу оплатить счет

Оплата подтверждена: {=Variable:PaymentApprover_printable}
Счет утвержден: {=Variable:Approver_printable}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}

{=Variable:Link}{=Document:ID}/

Список заданий:
{=Variable:TasksLink}";
$MESS["LIBTA_T_XMA_MESSAGES_3"] = "Сообщение: счет на оплату";
$MESS["LIBTA_STATE6"] = "Ожидание оплаты";
$MESS["LIBTA_T_SSTA6"] = "Статус: ожидание оплаты";
$MESS["LIBTA_T_ASFA4"] = "Изменение документа";
$MESS["LIBTA_STATE7"] = "Оплачен";
$MESS["LIBTA_T_SSTA7"] = "Статус: счет оплачен";
$MESS["LIBTA_SMA_MESSAGE_6"] = "Счет оплачен. Необходимы документы по счету.

Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}

ВНИМАНИЕ! Документы должны быть сданы в течение 5 дней после оплаты счета!";
$MESS["LIBTA_T_SMA_MESSAGE_6"] = "Сообщение: счет оплачен";
$MESS["LIBTA_T_SPAX4"] = "Установка прав: документирующему";
$MESS["LIBTA_SMA_MESSAGE_7"] = "Документы по счету собраны

Дата оплаты: {=Document:PROPERTY_DATE_PAY}
Номер п/п: {=Document:PROPERTY_NUM_PAY}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}

{=Variable:Link}{=Document:ID}/

Список заданий:
{=Variable:TasksLink}";
$MESS["LIBTA_T_SMA_MESSAGE_7"] = "Сообщение: документы собраны";
$MESS["LIBTA_T_ASFA5"] = "Изменение документа";
$MESS["LIBTA_STATE8"] = "Закрыт";
$MESS["LIBTA_T_SSTA8"] = "Статус: счет закрыт";
$MESS["LIBTA_SMA_MESSAGE_8"] = "Документы получены. БП по счету закрыт.

Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_8"] = "Сообщение: документы получены";
$MESS["LIBTA_STATE9"] = "Оплата отклонена";
$MESS["LIBTA_T_SSTA9"] = "Статус: оплата отклонена";
$MESS["LIBTA_SMA_MESSAGE_9"] = "Оплата счета не подтверждена

Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_9"] = "Сообщение: оплата не подтверждена";
$MESS["LIBTA_T_IFELSEBA4"] = "Нет";
$MESS["LIBTA_SMA_MESSAGE_10"] = "Счет не утвержден

Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_T_SMA_MESSAGE_10"] = "Сообщение: счет не утвержден";
$MESS["LIBTA_T_SPAX5"] = "Установка прав: финальная";
$MESS["LIBTA_V_BK"] = "Бухгалтерия (утверждение оплаты)";
$MESS["LIBTA_V_MNG"] = "Руководство";
$MESS["LIBTA_V_APPRU"] = "Утверждающий";
$MESS["LIBTA_V_BKP"] = "Бухгалтерия (оплата счета)";
$MESS["LIBTA_V_BKD"] = "Бухгалтерия (сбор документы)";
$MESS["LIBTA_V_MAPPR"] = "Руководство (утверждение счета)";
$MESS["LIBTA_V_LINK"] = "Ссылка на список счетов";
$MESS["LIBTA_V_TLINK"] = "Ссылка на список заданий";
$MESS["LIBTA_V_PDATE"] = "Дата оплаты";
$MESS["LIBTA_V_PNUM"] = "Номер п/п";
$MESS["LIBTA_V_APPR"] = "Подтвердил оплату";
$MESS["LIBTA_BP_TITLE"] = "Счета";
$MESS["LIBTA_RIA10_NAME"] = "Оплатить счет \"{=Document:NAME}\"";
$MESS["LIBTA_RIA10_DESCR"] = "Оплатить счет

Оплата подтверждена: {=Variable:PaymentApprover_printable}
Счет утвержден: {=Variable:Approver_printable}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}
Файл: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_RIA10_R1"] = "Дата оплаты";
$MESS["LIBTA_RIA10_R2"] = "Номер п/п";
$MESS["LIBTA_T_RIA10"] = "Оплата счета";
$MESS["LIBTA_RRA15_NAME"] = "Собрать документы по счету \"{=Document:NAME}\"";
$MESS["LIBTA_RRA15_DESCR"] = "Собрать документы по счету

Оплата подтверждена: {=Variable:PaymentApprover_printable}
Счет утвержден: {=Variable:Approver_printable}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}
Файл: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/

ВНИМАНИЕ! Документы должны быть сданы в течение 5 дней после оказания услуг!";
$MESS["LIBTA_RRA15_SM"] = "Сбор документов";
$MESS["LIBTA_RRA15_TASKBUTTON"] = "Документы собраны";
$MESS["LIBTA_T_RRA15"] = "Документы по счету";
$MESS["LIBTA_RRA17_NAME"] = "Подтвердить получение документов по счету \"{=Document:NAME}\"";
$MESS["LIBTA_RRA17_DESCR"] = "Получение документов по счету подтверждаю.

Дата оплаты: {=Document:PROPERTY_DATE_PAY}
Номер п/п: {=Document:PROPERTY_NUM_PAY}
Оплата подтверждена: {=Variable:PaymentApprover_printable}
Счет утвержден: {=Variable:Approver_printable}
Кем создан: {=Document:CREATED_BY_PRINTABLE}
Дата создания: {=Document:DATE_CREATE}
Название: {=Document:NAME}
Тип: {=Document:PROPERTY_TYPE}
Номер и дата счета: {=Document:PROPERTY_NUM_DATE}
Сумма: {=Document:PROPERTY_SUM}
Статья бюджета: {=Document:PROPERTY_BDT}
Файл: {=Variable:Domain}{=Document:PROPERTY_FILE}

{=Variable:Link}{=Document:ID}/";
$MESS["LIBTA_RRA17_BUTTON"] = "Документы получены";
$MESS["LIBTA_T_RRA17_NAME"] = "Документы получены";
$MESS["LIBTA_V_DOMAIN"] = "Домен";
?>