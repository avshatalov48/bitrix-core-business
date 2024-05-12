<?php

$MESS ['BP_DBLA_NAME'] = "Двухэтапное утверждение";
$MESS ['BP_DBLA_DESC_MSGVER_1'] = "Рекомендуется для ситуаций утверждения документа с предварительной экспертной оценкой. В рамках процесса на первом этапе документ утверждается экспертом. Если им документ не утвержден, то он возвращается на доработку. Если утвержден, то документ передается для принятия решения группой сотрудников простым большинством голосов. Если документ не принят на втором этапе голосования, то он возвращается автору на доработку и повторяется процесс утверждения.";
$MESS ['BP_DBLA_T'] = "Последовательный бизнес-процесс";
$MESS ['BP_DBLA_TASK_MSGVER_1'] = "Необходимо утвердить документ \"{=Document:NAME}\"";
$MESS ['BP_DBLA_TASK_DESC_MSGVER_1'] = "Вы должны утвердить или отклонить документ \"{=Document:NAME}\".

Для утверждения документа перейдите по ссылке: #BASE_HREF##TASK_URL#

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BP_DBLA_M'] = "Почтовое сообщение";
$MESS ['BP_DBLA_APPROVE_MSGVER_1'] = "Проголосуйте, пожалуйста, за документ.";
$MESS ['BP_DBLA_APPROVE_TEXT_MSGVER_1'] = "Вам необходимо проголосовать за документ \"{=Document:NAME}\".

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BP_DBLA_APPROVE_TITLR_MSGVER_1'] = "Утверждение документа 1 этап";
$MESS ['BP_DBLA_S_1'] = "Последовательность действий";
$MESS ['BP_DBLA_MAIL_SUBJ_MSGVER_1'] = "Документ принят на 1-ом этапе";
$MESS ['BP_DBLA_MAIL_TEXT_MSGVER_1'] = "Первый этап утверждения документа \"{=Document:NAME}\" завершен.

Документ принят.

{=ApproveActivity1:Comments}";
$MESS ['BP_DBLA_MAIL2_SUBJ_MSGVER_1'] = "Необходимо проголосовать за \"{=Document:NAME}\"";
$MESS ['BP_DBLA_MAIL2_TEXT_MSGVER_1'] = "Вы должны утвердить или отклонить документ \"{=Document:NAME}\".

Для утверждения документа перейдите по ссылке: #BASE_HREF##TASK_URL#

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BP_DBLA_APPROVE2_MSGVER_1'] = "Проголосуйте, пожалуйста, за документ.";
$MESS ['BP_DBLA_APPROVE2_TEXT_MSGVER_1'] = "Вам необходимо проголосовать за документ \"{=Document:NAME}\".

Автор: {=Document:CREATED_BY_PRINTABLE}";
$MESS ['BP_DBLA_APPROVE2_TITLE_MSGVER_1'] = "Утверждение документа 2 этап";
$MESS ['BP_DBLA_MAIL3_SUBJ_MSGVER_1'] = "Голосование по \"{=Document:NAME}\": Документ принят";
$MESS ['BP_DBLA_MAIL3_TEXT_MSGVER_1'] = "Голосование по документу \"{=Document:NAME}\" завершено.

Документ принят {=ApproveActivity2:ApprovedPercent}% голосов.

Утвердили документ: {=ApproveActivity2:ApprovedCount}
Отклонили документ: {=ApproveActivity2:NotApprovedCount}

{=ApproveActivity2:Comments}";
$MESS ['BP_DBLA_APP'] = "Утвержден";
$MESS ['BP_DBLA_APP_S'] = "Статус: Утвержден";
$MESS ['BP_DBLA_PUB_TITLE_MSGVER_1'] = "Публикация документа";
$MESS ['BP_DBLA_NAPP_MSGVER_1'] = "Голосование по \"{=Document:NAME}\": Документ отклонен";
$MESS ['BP_DBLA_NAPP_TEXT_MSGVER_1'] = "Голосование по документу \"{=Document:NAME}\" завершено.

Документ отклонен.

Утвердили документ: {=ApproveActivity2:ApprovedCount}
Отклонили документ: {=ApproveActivity2:NotApprovedCount}

{=ApproveActivity2:Comments}";
$MESS ['BP_DBLA_NAPP_DRAFT'] = "Отправлен на доработку";
$MESS ['BP_DBLA_NAPP_DRAFT_S'] = "Статус: Отправлен на доработку";
$MESS ['BP_DBLA_MAIL4_SUBJ_MSGVER_1'] = "Утверждение {=Document:NAME}: Документ отклонен";
$MESS ['BP_DBLA_MAIL4_TEXT_MSGVER_1'] = "Первый этап утверждения документа \"{=Document:NAME}\" завершен.

Документ отклонен.

{=ApproveActivity1:Comments}";
$MESS ['BP_DBLA_PARAM1'] = "Утверждающие на 1-ом этапе";
$MESS ['BP_DBLA_PARAM2'] = "Утверждающие на 2-ом этапе";
