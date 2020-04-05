<?
$MESS ['BP_DBLA_NAME'] = "Двухэтапное утверждение";
$MESS ['BP_DBLA_DESC'] = "Рекомендуется для ситуаций утверждения документа с предварительной экспертной оценкой. В рамках процесса на первом этапе документ утверждается экспертом. Если им документ не утвержден, то он возвращается на доработку. Если утвержден, то документ передается для принятия решения группой сотрудников простым большинством голосов. Если документ не принят на втором этапе голосования, то он возвращается автору на доработку и повторяется процесс утверждения.";
$MESS ['BP_DBLA_T'] = "Последовательный бизнес-процесс";
$MESS ['BP_DBLA_TASK'] = "Необходимо утвердить документ \"{=Document:NAME}\"";
$MESS ['BP_DBLA_TASK_DESC'] = "Вы должны утвердить или отклонить документ \"{=Document:NAME}\".

Для утверждения документа перейдите по ссылке: #BASE_HREF##TASK_URL#

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BP_DBLA_M'] = "Почтовое сообщение";
$MESS ['BP_DBLA_APPROVE'] = "Проголосуйте, пожалуйста, за документ.";
$MESS ['BP_DBLA_APPROVE_TEXT'] = "Вам необходимо проголосовать за документ \"{=Document:NAME}\".

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BP_DBLA_APPROVE_TITLR'] = "Утверждение документа 1 этап";
$MESS ['BP_DBLA_S'] = "Последовательность действий";
$MESS ['BP_DBLA_MAIL_SUBJ'] = "Документ принят на 1-ом этапе";
$MESS ['BP_DBLA_MAIL_TEXT'] = "Первый этап утверждения документа \"{=Document:NAME}\" завершен.

Документ принят.

{=ApproveActivity1:Comments}";
$MESS ['BP_DBLA_MAIL2_SUBJ'] = "Необходимо проголосовать за \"{=Document:NAME}\"";
$MESS ['BP_DBLA_MAIL2_TEXT'] = "Вы должны утвердить или отклонить документ \"{=Document:NAME}\".

Для утверждения документа перейдите по ссылке: #BASE_HREF##TASK_URL#

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BP_DBLA_APPROVE2'] = "Проголосуйте, пожалуйста, за документ.";
$MESS ['BP_DBLA_APPROVE2_TEXT'] = "Вам необходимо проголосовать за документ \"{=Document:NAME}\".

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BP_DBLA_APPROVE2_TITLE'] = "Утверждение документа 2 этап";
$MESS ['BP_DBLA_MAIL3_SUBJ'] = "Голосование по \"{=Document:NAME}: Документ принят";
$MESS ['BP_DBLA_MAIL3_TEXT'] = "Голосование по документу \"{=Document:NAME}\" завершено.

Документ принят {=ApproveActivity2:ApprovedPercent}% голосов.

Утвердили документ: {=ApproveActivity2:ApprovedCount}
Отклонили документ: {=ApproveActivity2:NotApprovedCount}

{=ApproveActivity2:Comments}";
$MESS ['BP_DBLA_APP'] = "Утвержден";
$MESS ['BP_DBLA_APP_S'] = "Статус: Утвержден";
$MESS ['BP_DBLA_PUB_TITLE'] = "Публикация документа";
$MESS ['BP_DBLA_NAPP'] = "Голосование по \"{=Document:NAME}: Документ отклонен";
$MESS ['BP_DBLA_NAPP_TEXT'] = "Голосование по документу \"{=Document:NAME}\" завершено.

Документ отклонен.

Утвердили документ: {=ApproveActivity2:ApprovedCount}
Отклонили документ: {=ApproveActivity2:NotApprovedCount}

{=ApproveActivity2:Comments}";
$MESS ['BP_DBLA_NAPP_DRAFT'] = "Отправлен на доработку";
$MESS ['BP_DBLA_NAPP_DRAFT_S'] = "Статус: Отправлен на доработку";
$MESS ['BP_DBLA_MAIL4_SUBJ'] = "Утверждение {=Document:NAME}: Документ отклонен";
$MESS ['BP_DBLA_MAIL4_TEXT'] = "Первый этап утверждения документа \"{=Document:NAME}\" завершен.

Документ отклонен.

{=ApproveActivity1:Comments}";
$MESS ['BP_DBLA_PARAM1'] = "Утверждающие на 1-ом этапе";
$MESS ['BP_DBLA_PARAM2'] = "Утверждающие на 2-ом этапе";
?>