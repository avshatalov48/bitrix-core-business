<?php
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

set_time_limit(36000);


IncludeModuleLangFile(__FILE__);

$title = Loc::getMessage("SALE_CONVERTER_STEP_BY_STEP_MANAGER");

global $APPLICATION, $DB;

$APPLICATION->SetTitle($title);

if (!CModule::IncludeModule('sale'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	CAdminMessage::ShowMessage(array(
		"MESSAGE" => Loc::getMessage('SALE_CONVERTER_MESSAGE_TITLE'),
		"DETAILS" => Loc::getMessage('SALE_CONVERTER_MODULE_NOT_INSTALL'),
		"HTML" => true,
		"TYPE" => "ERROR"
	));
	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	return;
}

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('SALE_CONVERTER_TITLE'));

$stepsBeforeAjax = 4;
$ajax_step = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["is_ajax"] == "Y")
{
	global $DB;

	if (isset($_POST['ajax_step']) && intval($_POST['ajax_step']) > 0)
		$ajax_step = intval($_POST['ajax_step']);

	$result = array();
	$error = '';
	switch ($ajax_step)
	{
		case 0:
			// SITE_STOP
			COption::SetOptionString("main", "site_stopped", "Y");
			
			$result['DATA'] = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_ORDER');
			$result['NEXT_STEP'] = ++$ajax_step;
			break;

		case 1:	
			// ORDER
			$start = microtime(true);
			
			if ($DB->Query("SELECT STATUS_ID FROM b_sale_order WHERE 1=0", true))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order CHANGE STATUS_ID STATUS_ID char(2) not null", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					$DB->Query("DROP INDEX IX_B_SALE_ORDER_6 ON B_SALE_ORDER", true);

					if (!$DB->Query("ALTER TABLE B_SALE_ORDER ALTER COLUMN STATUS_ID char(2) NOT NULL", true))
						$error .= "<br>".$DB->GetErrorMessage();

					$DB->Query("CREATE INDEX IX_B_SALE_ORDER_6 ON B_SALE_ORDER(STATUS_ID)", false);
				}
				elseif ($DB->type == "ORACLE")
				{
					$DB->Query("DROP INDEX IXS_ORDER_STATUS_ID", true);
					
					if (!$DB->Query("ALTER TABLE B_SALE_ORDER MODIFY (STATUS_ID CHAR(2 CHAR))", true))
						$error .= "<br>".$DB->GetErrorMessage();
						
					$DB->Query("CREATE INDEX IXS_ORDER_STATUS_ID ON B_SALE_ORDER(STATUS_ID)", false);
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if ($DB->Query("SELECT DISCOUNT_VALUE FROM b_sale_order WHERE 1=0", true))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order CHANGE COLUMN DISCOUNT_VALUE DISCOUNT_VALUE DECIMAL(18,4) NOT NULL DEFAULT '0.0000'", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{

					$DB->Query("ALTER TABLE b_sale_order DROP CONSTRAINT DF_B_SALE_ORDER_DISCOUNT_VALUE", true);
					
					if (!$DB->Query("ALTER TABLE b_sale_order ALTER COLUMN DISCOUNT_VALUE DECIMAL(18,4) NOT NULL", true))
						$error .= "<br>".$DB->GetErrorMessage();

					if (!$DB->Query("ALTER TABLE b_sale_order ADD CONSTRAINT DF_B_SALE_ORDER_DISCOUNT_VALUE DEFAULT '0.0000'  FOR DISCOUNT_VALUE", true))
						$error .= "<br>".$DB->GetErrorMessage();

				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("ALTER TABLE B_SALE_ORDER MODIFY DISCOUNT_VALUE NUMBER(20,4) DEFAULT 0.0", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if ($DB->Query("SELECT PRICE_DELIVERY FROM b_sale_order WHERE 1=0", true))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order CHANGE COLUMN PRICE_DELIVERY PRICE_DELIVERY DECIMAL(18,4) NOT NULL DEFAULT '0.0000'", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					$DB->Query("ALTER TABLE b_sale_order DROP CONSTRAINT DF_B_SALE_ORDER_PRICE_DELIVERY", true);
					
					if (!$DB->Query("ALTER TABLE b_sale_order ALTER COLUMN PRICE_DELIVERY DECIMAL(18,4) NOT NULL", true))
						$error .= "<br>".$DB->GetErrorMessage();

					if (!$DB->Query("ALTER TABLE b_sale_order ADD CONSTRAINT DF_B_SALE_ORDER_PRICE_DELIVERY DEFAULT '0.0000' FOR PRICE_DELIVERY", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order MODIFY PRICE_DELIVERY NUMBER(20,4) DEFAULT 0.0", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->Query("SELECT PRICE_PAYMENT FROM b_sale_order WHERE 1=0", true))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order
									ADD PRICE_PAYMENT decimal(18,4) not null DEFAULT '0.0000',
									ADD CREATED_BY int(11) null", false)
					)
						$error .= "<br>".$DB->GetErrorMessage();
				}

				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order ADD
									PRICE_PAYMENT DECIMAL(18,4) NOT NULL CONSTRAINT DF_B_S_O_PRICE_PAYMENT DEFAULT '0.0000',
									CREATED_BY INT null
									", false)
					)
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order ADD (
									PRICE_PAYMENT decimal(18,4) DEFAULT 0.0 NOT NULL,
									CREATED_BY number(11) null)", false)
					)
						$error .= "<br>".$DB->GetErrorMessage();
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->Query("SELECT BX_USER_ID FROM b_sale_order WHERE 1=0", true))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order ADD BX_USER_ID varchar(32) null", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order ADD BX_USER_ID VARCHAR(32) NULL", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order ADD BX_USER_ID VARCHAR2(32 CHAR) NULL", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if ($DB->Query("SELECT USER_DESCRIPTION FROM b_sale_order WHERE 1=0", true))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order CHANGE USER_DESCRIPTION USER_DESCRIPTION VARCHAR(2000) NULL", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order ALTER COLUMN USER_DESCRIPTION VARCHAR(2000) NULL", true))
						$error .= "<br>".$DB->GetErrorMessage();

				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order MODIFY USER_DESCRIPTION VARCHAR2(2000 CHAR)", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			$end = microtime(true);
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'alter b_sale_order = '.($end-$start)."\n", FILE_APPEND);
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_PROPS_VALUE');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_ORDER');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 2:	
			// PROPS VALUE
			$start = microtime(true);
			
			if ($DB->TableExists("b_sale_order_props_value"))
			{
				if ($DB->Query("SELECT VALUE FROM b_sale_order_props_value WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props_value CHANGE VALUE VALUE varchar(500) null", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props_value ALTER COLUMN VALUE VARCHAR (500) NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props_value MODIFY VALUE VARCHAR2(500 CHAR)", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}

			$end = microtime(true);
			
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'alter b_sale_order_props_value = '.($end-$start)."\n", FILE_APPEND);
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_PROPS');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_PROPS_VALUE');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 3:	
			// PROPS
			$start = microtime(true);
			
			if ($DB->TableExists("b_sale_order_props"))
			{
				if ($DB->Query("SELECT REQUIED FROM b_sale_order_props WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props CHANGE REQUIED REQUIRED char(1) not null default 'N'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("EXEC sp_rename 'b_sale_order_props.REQUIED', 'REQUIRED', 'COLUMN'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props RENAME COLUMN REQUIED TO REQUIRED", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}

				if ($DB->Query("SELECT DEFAULT_VALUE FROM b_sale_order_props WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props CHANGE DEFAULT_VALUE DEFAULT_VALUE varchar(500) null", true))
							$error .= "<br>".$DB->GetErrorMessage();

					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props ALTER COLUMN DEFAULT_VALUE VARCHAR (500) NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props MODIFY DEFAULT_VALUE VARCHAR2(500 CHAR)", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}

				if (!$DB->Query("SELECT IS_ADDRESS FROM b_sale_order_props WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props ADD IS_ADDRESS char(1) not null default 'N'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props ADD IS_ADDRESS CHAR(1) NOT NULL CONSTRAINT DF_B_SALE_ORDER_PROPS_IS_ADDRESS DEFAULT 'N'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props ADD IS_ADDRESS CHAR(1 CHAR) DEFAULT 'N' NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}

				if (!$DB->Query("SELECT SETTINGS FROM b_sale_order_props WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props ADD SETTINGS varchar(500) null", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props ADD SETTINGS VARCHAR (500) NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_props ADD SETTINGS VARCHAR2(500 CHAR) NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}
			
			// rename sequences
			if ($DB->type == "ORACLE")
			{
				if ($DB->TableExists("b_sale_order_props")
				 && $DB->Query('rename SQ_SALE_ORDER_PROPS to SQ_B_SALE_ORDER_PROPS', true))
				{
					if (! $DB->Query('
						CREATE OR REPLACE TRIGGER B_SALE_ORDER_PROPS_INSERT
						BEFORE INSERT
						ON B_SALE_ORDER_PROPS
						FOR EACH ROW
						BEGIN
							IF :NEW.ID IS NULL THEN
								SELECT SQ_B_SALE_ORDER_PROPS.NEXTVAL INTO :NEW.ID FROM dual;
							END IF;
						END;
					', true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				
				if ($DB->TableExists('b_sale_order_props_group')
				 && $DB->Query('rename SQ_SALE_ORDER_PROPS_GROUP to SQ_B_SALE_ORDER_PROPS_GROUP', true))
				{
					if (!$DB->Query('
						CREATE OR REPLACE TRIGGER B_SALE_OPG_INSERT
						BEFORE INSERT
						ON B_SALE_ORDER_PROPS_GROUP
						FOR EACH ROW
						BEGIN
							IF :NEW.ID IS NULL THEN
								SELECT SQ_B_SALE_ORDER_PROPS_GROUP.NEXTVAL INTO :NEW.ID FROM dual;
							END IF;
						END;
					', true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				
				if ($DB->TableExists('b_sale_order_props_value')
				 && $DB->Query('rename SQ_SALE_ORDER_PROPS_VALUE to SQ_B_SALE_ORDER_PROPS_VALUE', true))
				{
					if (!$DB->Query('
						CREATE OR REPLACE TRIGGER B_SALE_OPV_INSERT
						BEFORE INSERT
						ON B_SALE_ORDER_PROPS_VALUE
						FOR EACH ROW
						BEGIN
							IF :NEW.ID IS NULL THEN
								SELECT SQ_B_SALE_ORDER_PROPS_VALUE.NEXTVAL INTO :NEW.ID FROM dual;
							END IF;
						END;
					', true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				
				if ($DB->TableExists('b_sale_order_props_variant')
				 && $DB->Query('rename SQ_SALE_ORDER_PROPS_VARIANT to SQ_B_SALE_ORDER_PROPS_VARIANT', true))
				{
					if (!$DB->Query('
						CREATE OR REPLACE TRIGGER B_SALE_OPVAR_INSERT
						BEFORE INSERT
						ON B_SALE_ORDER_PROPS_VARIANT
						FOR EACH ROW
						BEGIN
							IF :NEW.ID IS NULL THEN
								SELECT SQ_B_SALE_ORDER_PROPS_VARIANT.NEXTVAL INTO :NEW.ID FROM dual;
							END IF;
						END;
					', true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
			}
			
			$end = microtime(true);
			
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'alter b_sale_order_props = '.($end-$start)."\n", FILE_APPEND);
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_ORDER_CHANGE');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_PROPS');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 4:
			// ORDER CHANGE
			if ($DB->TableExists("b_sale_order_change"))
			{
				if (!$DB->Query("select ENTITY from b_sale_order_change WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_change
										ADD column ENTITY VARCHAR(50) DEFAULT NULL,
										ADD column ENTITY_ID INT DEFAULT NULL", false)
						)
							$error .= "<br>".$DB->GetErrorMessage();
					}

					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_change ADD
										ENTITY VARCHAR(50) NULL,
										ENTITY_ID INT NULL", false)
						)
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_change ADD (
										ENTITY VARCHAR2(50 CHAR) NULL,
										ENTITY_ID NUMBER(18) NULL)", false)
						)
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_TRANSACT');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_ORDER_CHANGE');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
			
		case 5:
			// USER TRANSACT
			if ($DB->TableExists('b_sale_user_transact'))
			{
				if (!$DB->Query("select CURRENT_BUDGET from b_sale_user_transact WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_user_transact ADD COLUMN CURRENT_BUDGET DECIMAL(18,4) NOT NULL DEFAULT '0.0000'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_USER_TRANSACT ADD CURRENT_BUDGET DECIMAL(18,4) NOT NULL CONSTRAINT DF_S_U_T_CURRENT DEFAULT '0.0000'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_USER_TRANSACT ADD CURRENT_BUDGET NUMBER(18,4) DEFAULT 0.0 NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}

				if (!$DB->Query("select PAYMENT_ID from b_sale_user_transact WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if ($DB->Query("ALTER TABLE b_sale_user_transact ADD PAYMENT_ID INT NULL", true))
						{
							$DB->Query("CREATE INDEX IX_S_U_T_PAYMENT_ID ON b_sale_user_transact (PAYMENT_ID)", false);
						}
						else
						{
							$error .= "<br>".$DB->GetErrorMessage();
						}
					}
					elseif ($DB->type == "MSSQL")
					{
						if ($DB->Query("ALTER TABLE B_SALE_USER_TRANSACT ADD PAYMENT_ID INT NULL", true))
						{
							$DB->Query("CREATE INDEX IX_S_U_T_PAYMENT_ID ON B_SALE_USER_TRANSACT (PAYMENT_ID)", false);
						}
						else
						{
							$error .= "<br>".$DB->GetErrorMessage();
						}
					}
					elseif ($DB->type == "ORACLE")
					{
						if ($DB->Query("ALTER TABLE B_SALE_USER_TRANSACT ADD PAYMENT_ID number(18) NULL", true))
						{
							$DB->Query("CREATE INDEX IX_S_U_T_PAYMENT_ID ON B_SALE_USER_TRANSACT (PAYMENT_ID)", false);
						}
						else
						{
							$error .= "<br>".$DB->GetErrorMessage();
						}
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_DISCOUNT');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_TRANSACT');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 6:
			// DISCOUNT AND COUPONS
			if ($DB->TableExists("b_sale_discount_coupon"))
			{
				if (!$DB->Query("select DESCRIPTION from b_sale_discount_coupon WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("alter table b_sale_discount_coupon add DESCRIPTION text null", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("alter table B_SALE_DISCOUNT_COUPON add DESCRIPTION text null", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("alter table B_SALE_DISCOUNT_COUPON add DESCRIPTION clob null", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}

			if (!$DB->TableExists("b_sale_order_discount"))
			{
				if ($DB->type == "MYSQL")
				{
					if (
						!$DB->Query("CREATE TABLE b_sale_order_discount(
								ID int not null auto_increment,
								MODULE_ID varchar(50) not null,
								DISCOUNT_ID int not null,
								NAME varchar(255) not null,
								DISCOUNT_HASH varchar(32) not null,
								CONDITIONS mediumtext null,
								UNPACK mediumtext null,
								ACTIONS mediumtext null,
								APPLICATION mediumtext null,
								USE_COUPONS char(1) not null,
								SORT int not null,
								PRIORITY int not null,
								LAST_DISCOUNT char(1) not null,
								ACTIONS_DESCR mediumtext null,
								primary key (ID),
								INDEX IX_SALE_ORDER_DSC_HASH (DISCOUNT_HASH)
							)
						", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (
						!$DB->Query("CREATE TABLE B_SALE_ORDER_DISCOUNT
							(
								ID int NOT NULL IDENTITY (1, 1),
								MODULE_ID varchar(50) NOT NULL,
								DISCOUNT_ID int NOT NULL,
								NAME varchar(255) NOT NULL,
								DISCOUNT_HASH varchar(32) NOT NULL,
								CONDITIONS text NULL,
								UNPACK text NULL,
								ACTIONS text NULL,
								APPLICATION text NULL,
								USE_COUPONS char(1) NOT NULL,
								SORT int NOT NULL,
								PRIORITY int NOT NULL,
								ACTIONS_DESCR TEXT null,
								LAST_DISCOUNT char(1) NOT NULL
							)
						", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_ORDER_DISCOUNT ADD CONSTRAINT PK_B_SALE_ORDER_DISCOUNT PRIMARY KEY (ID)", false);
						$DB->Query("CREATE INDEX IX_SALE_ORDER_DSC_HASH ON B_SALE_ORDER_DISCOUNT(DISCOUNT_HASH)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (
						!$DB->Query("CREATE TABLE B_SALE_ORDER_DISCOUNT
							(
								ID NUMBER(18) NOT NULL,
								MODULE_ID VARCHAR2(50 CHAR) NOT NULL,
								DISCOUNT_ID NUMBER(18) NOT NULL,
								NAME VARCHAR2(255 CHAR) NOT NULL,
								DISCOUNT_HASH VARCHAR2(32 CHAR) NOT NULL,
								CONDITIONS CLOB NULL,
								UNPACK CLOB NULL,
								ACTIONS CLOB NULL,
								APPLICATION CLOB NULL,
								USE_COUPONS CHAR(1 CHAR) NOT NULL,
								SORT NUMBER(18) NOT NULL,
								PRIORITY NUMBER(18) NOT NULL,
								ACTIONS_DESCR CLOB null,
								LAST_DISCOUNT CHAR(1 CHAR) not NULL,
								PRIMARY KEY (ID)
							)
						", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_DSC_HASH ON B_SALE_ORDER_DISCOUNT(DISCOUNT_HASH)", false);
						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_DISCOUNT INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_ORDER_DSC_INSERT
									BEFORE INSERT
									ON B_SALE_ORDER_DISCOUNT
									FOR EACH ROW
										BEGIN
											IF :NEW.ID IS NULL THEN
												SELECT SQ_B_SALE_ORDER_DISCOUNT.NEXTVAL INTO :NEW.ID FROM dual;
											END IF;
										END;"
							, false);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_order_coupons"))
			{
				if ($DB->type == "MYSQL")
				{
					if (
						!$DB->Query("CREATE TABLE b_sale_order_coupons(
								ID int not null auto_increment,
								ORDER_ID int not null,
								ORDER_DISCOUNT_ID int not null,
								COUPON varchar(32) not null,
								COUPON_ID int not null,
								TYPE int not null,
								DATA text null,
								primary key (ID),
								INDEX IX_SALE_ORDER_CPN_ORDER (ORDER_ID)
							)
						", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (
						!$DB->Query("CREATE TABLE B_SALE_ORDER_COUPONS(
								ID int NOT NULL IDENTITY (1, 1),
								ORDER_ID int NOT NULL,
								ORDER_DISCOUNT_ID int NOT NULL,
								COUPON varchar(32) NOT NULL,
								COUPON_ID int NOT NULL,
								DATA text NULL,
								TYPE int NOT NULL
							)
						", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_CPN_ORDER ON B_SALE_ORDER_COUPONS(ORDER_ID)");
						$DB->Query("ALTER TABLE B_SALE_ORDER_COUPONS ADD CONSTRAINT PK_B_SALE_ORDER_COUPONS PRIMARY KEY (ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (
						!$DB->Query("CREATE TABLE B_SALE_ORDER_COUPONS
							(
								ID NUMBER(18) NOT NULL,
								ORDER_ID NUMBER(18) NOT NULL,
								ORDER_DISCOUNT_ID NUMBER(18) NOT NULL,
								COUPON VARCHAR2(32 CHAR) NOT NULL,
								COUPON_ID NUMBER(18) NOT NULL,
								DATA CLOB NULL,
								TYPE NUMBER(18) NOT NULL,
								PRIMARY KEY (ID)
							)
						", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_CPN_ORDER ON B_SALE_ORDER_COUPONS(ORDER_ID)");

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_COUPONS INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_ORDER_CPN_INSERT
									BEFORE INSERT
									ON B_SALE_ORDER_COUPONS
									FOR EACH ROW
										BEGIN
											IF :NEW.ID IS NULL THEN
												SELECT SQ_B_SALE_ORDER_COUPONS.NEXTVAL INTO :NEW.ID FROM dual;
											END IF;
										END;"
							, false);
					}
				}
			}

			if (!$DB->TableExists("b_sale_order_modules"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_order_modules(
							ID int not null auto_increment,
							ORDER_DISCOUNT_ID int not null,
							MODULE_ID varchar(50) not null,
							primary key (ID),
							INDEX IX_SALE_ORDER_MDL_DSC (ORDER_DISCOUNT_ID)
						)
					", false)
					)
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_MODULES(
								ID int NOT NULL IDENTITY (1, 1),
								ORDER_DISCOUNT_ID int NOT NULL,
								MODULE_ID varchar(50) NOT NULL
							)
					", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_MDL_DSC ON B_SALE_ORDER_MODULES(ORDER_DISCOUNT_ID)");
						$DB->Query("ALTER TABLE B_SALE_ORDER_MODULES ADD CONSTRAINT PK_B_SALE_ORDER_MODULES PRIMARY KEY (ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("
							CREATE TABLE B_SALE_ORDER_MODULES
							(
								ID NUMBER(18) NOT NULL,
								ORDER_DISCOUNT_ID NUMBER(18) NOT NULL,
								MODULE_ID VARCHAR2(50 CHAR) NOT NULL,
								primary key (ID)
							)
					", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_MDL_DSC ON B_SALE_ORDER_MODULES(ORDER_DISCOUNT_ID)");

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_MODULES INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_ORDER_MDL_INSERT
									BEFORE INSERT
									ON B_SALE_ORDER_MODULES
									FOR EACH ROW
										BEGIN
											IF :NEW.ID IS NULL THEN
												SELECT SQ_B_SALE_ORDER_MODULES.NEXTVAL INTO :NEW.ID FROM dual;
											END IF;
										END;", true
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_order_rules"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_order_rules(
							ID int not null auto_increment,
							MODULE_ID varchar(50) not null,
							ORDER_DISCOUNT_ID int not null,
							ORDER_ID int not null,
							ENTITY_TYPE int not null,
							ENTITY_ID int not null,
							ENTITY_VALUE varchar(255) null,
							COUPON_ID int not null,
							APPLY char(1) not null,
							primary key (ID),
							INDEX IX_SALE_ORDER_RULES_ORD (ORDER_ID)
						)", false)
					)
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_RULES
						(
							ID int NOT NULL IDENTITY (1, 1),
							MODULE_ID varchar(50) NOT NULL,
							ORDER_DISCOUNT_ID int NOT NULL,
							ORDER_ID int NOT NULL,
							ENTITY_TYPE int NOT NULL,
							ENTITY_ID int NOT NULL,
							ENTITY_VALUE varchar(255) NULL,
							COUPON_ID int NOT NULL,
							APPLY char(1) NOT NULL
						)")
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_RULES_ORD ON B_SALE_ORDER_RULES(ORDER_ID)");
						$DB->Query("ALTER TABLE B_SALE_ORDER_RULES ADD CONSTRAINT PK_B_SALE_ORDER_RULES PRIMARY KEY (ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_RULES
						(
							ID NUMBER(18) NOT NULL,
							MODULE_ID VARCHAR2(50 CHAR) NOT NULL,
							ORDER_DISCOUNT_ID NUMBER(18) NOT NULL,
							ORDER_ID NUMBER(18) NOT NULL,
							ENTITY_TYPE NUMBER(18) NOT NULL,
							ENTITY_ID NUMBER(18) NOT NULL,
							ENTITY_VALUE VARCHAR2(255 CHAR) NULL,
							COUPON_ID NUMBER(18) NOT NULL,
							APPLY CHAR(1 CHAR) NOT NULL,
							PRIMARY KEY (ID)
						)")
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_RULES_ORD ON B_SALE_ORDER_RULES(ORDER_ID)");

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_RULES INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_ORDER_RLS_INSERT
									BEFORE INSERT
									ON B_SALE_ORDER_RULES
									FOR EACH ROW
										BEGIN
											IF :NEW.ID IS NULL THEN
												SELECT SQ_B_SALE_ORDER_RULES.NEXTVAL INTO :NEW.ID FROM dual;
											END IF;
										END;", true
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error)
					);
				}
			}

			if (!$DB->TableExists("b_sale_order_discount_data"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_order_discount_data(
							ID int not null auto_increment,
							ORDER_ID int not null,
							ENTITY_TYPE int not null,
							ENTITY_ID int not null,
							ENTITY_VALUE varchar(255) null,
							ENTITY_DATA mediumtext not null,
							primary key (ID),
							INDEX IX_SALE_DSC_DATA_CMX (ORDER_ID, ENTITY_TYPE)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_DISCOUNT_DATA(
							ID int NOT NULL IDENTITY (1, 1),
							ORDER_ID int NOT NULL,
							ENTITY_TYPE int NOT NULL,
							ENTITY_ID int NOT NULL,
							ENTITY_VALUE varchar(255) NULL,
							ENTITY_DATA text NOT NULL)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_DSC_DATA_CMX ON B_SALE_ORDER_DISCOUNT_DATA(ORDER_ID, ENTITY_TYPE)");
						$DB->Query("ALTER TABLE B_SALE_ORDER_DISCOUNT_DATA ADD CONSTRAINT PK_B_SALE_ORDER_DISCOUNT_DATA PRIMARY KEY (ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_DISCOUNT_DATA
						(
							ID NUMBER(18) NOT NULL,
							ORDER_ID NUMBER(18) NOT NULL,
							ENTITY_TYPE NUMBER(18) NOT NULL,
							ENTITY_ID NUMBER(18) NOT NULL,
							ENTITY_VALUE VARCHAR2(255 CHAR) NULL,
							ENTITY_DATA CLOB NOT NULL,
							PRIMARY KEY (ID))", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_DSC_DATA_CMX ON B_SALE_ORDER_DISCOUNT_DATA(ORDER_ID, ENTITY_TYPE)");

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_DISCOUNT_DATA INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_ORDER_DSCDT_INSERT
										BEFORE INSERT
										ON B_SALE_ORDER_DISCOUNT_DATA
										FOR EACH ROW
											BEGIN
												IF :NEW.ID IS NULL THEN
													SELECT SQ_B_SALE_ORDER_DISCOUNT_DATA.NEXTVAL INTO :NEW.ID FROM dual;
												END IF;
											END;", true
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error)
					);
				}
			}
			
			if (!$DB->TableExists("b_sale_order_rules_descr"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("
						CREATE TABLE b_sale_order_rules_descr(
							ID int not null auto_increment,
							MODULE_ID varchar(50) not null,
							ORDER_DISCOUNT_ID int not null,
							ORDER_ID int not null,
							RULE_ID int not null,
							DESCR text not null,
							primary key (ID),
							INDEX IX_SALE_ORDER_RULES_DS_ORD (ORDER_ID)
						)
					", false)
					)
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_RULES_DESCR
						(
							ID int NOT NULL IDENTITY (1, 1),
							MODULE_ID varchar(50) NOT NULL,
							ORDER_DISCOUNT_ID int NOT NULL,
							ORDER_ID int NOT NULL,
							RULE_ID int NOT NULL,
							DESCR text NOT NULL
						)")
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_RULES_DS_ORD ON B_SALE_ORDER_RULES_DESCR(ORDER_ID)");
						$DB->Query("ALTER TABLE B_SALE_ORDER_RULES_DESCR ADD CONSTRAINT PK_B_SALE_ORDER_RULES_DESCR PRIMARY KEY (ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_RULES_DESCR
						(
							ID NUMBER(18) NOT NULL,
							MODULE_ID VARCHAR2(50 CHAR) NOT NULL,
							ORDER_DISCOUNT_ID NUMBER(18) NOT NULL,
							ORDER_ID NUMBER(18) NOT NULL,
							RULE_ID NUMBER(18) NOT NULL,
							DESCR CLOB NOT NULL,
							PRIMARY KEY (ID)
						)")
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_SALE_ORDER_RULES_DS_ORD ON B_SALE_ORDER_RULES_DESCR(ORDER_ID)");

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_RULES_DESCR INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_ORDER_RULES_DESCR_INSERT
									BEFORE INSERT
									ON B_SALE_ORDER_COUPONS
									FOR EACH ROW
										BEGIN
											IF :NEW.ID IS NULL THEN
												SELECT SQ_B_SALE_ORDER_RULES_DESCR.NEXTVAL INTO :NEW.ID FROM dual;
											END IF;
										END;", true
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error)
					);
				}
			}
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_DELIVERY');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_DISCOUNT');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 7:
			// DELIVERY
			if ($DB->TableExists("b_sale_order_delivery_old")  && !$DB->Query("select BASE_PRICE_DELIVERY from b_sale_order_delivery WHERE 1=0", true))
			{
				if ($DB->type == 'MYSQL')
				{
					$DB->Query('DROP TABLE b_sale_order_delivery_old', true);
					$DB->Query('DROP TABLE b_sale_delivery_old', true);
					$DB->Query('DROP TABLE b_sale_delivery_handler_old', true);
				}

				if ($DB->type == 'MSSQL')
				{
					$DB->Query('DROP TABLE B_SALE_ORDER_DELIVERY_OLD', true);
					$DB->Query('DROP TABLE B_SALE_DELIVERY_OLD', true);
					$DB->Query('DROP TABLE B_SALE_DELIVERY_HANDLER_OLD', true);
				}

				if ($DB->type == 'ORACLE')
				{
					$DB->Query('DROP TABLE B_SALE_ORDER_DELIVERY_OLD CASCADE CONSTRAINTS', true);
					$DB->Query('DROP TABLE B_SALE_DELIVERY_OLD CASCADE CONSTRAINTS', true);
					$DB->Query('DROP TABLE B_SALE_DELIVERY_HANDLER_OLD CASCADE CONSTRAINTS', true);
				}
			}

			if (($DB->TableExists("b_sale_order_delivery") || $DB->TableExists("B_SALE_ORDER_DELIVERY")) && !$DB->TableExists("b_sale_order_delivery_old") && !$DB->TableExists("B_SALE_ORDER_DELIVERY_OLD"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("ALTER TABLE b_sale_order_delivery RENAME b_sale_order_delivery_old", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("sp_rename B_SALE_ORDER_DELIVERY, B_SALE_ORDER_DELIVERY_OLD", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("ALTER TABLE B_SALE_ORDER_DELIVERY RENAME TO B_SALE_ORDER_DELIVERY_OLD", true))
						$error .= "<br>".$DB->GetErrorMessage();

					$DB->Query("DROP SEQUENCE SQ_B_SALE_ORDER_DELIVERY", true);
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error)
					);
				}
			}

			if ($DB->TableExists("b_sale_delivery"))
			{
				if (!$DB->Query("SELECT CONVERTED FROM b_sale_delivery WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_delivery ADD CONVERTED char(1) not null default 'N'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY ADD CONVERTED CHAR(1) NOT NULL CONSTRAINT DF_B_S_D_CONVERTED DEFAULT 'N'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY ADD CONVERTED CHAR(1) DEFAULT 'N' NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}

			if ($DB->TableExists("b_sale_delivery_handler"))
			{
				if (!$DB->Query("SELECT CONVERTED FROM b_sale_delivery_handler WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_delivery_handler ADD CONVERTED char(1) not null default 'N'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY_HANDLER ADD CONVERTED CHAR(1) NOT NULL CONSTRAINT DF_B_S_D_H_CONVERTED DEFAULT 'N'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY_HANDLER ADD CONVERTED CHAR(1) DEFAULT 'N' NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}

			if ($DB->TableExists("b_sale_delivery2paysystem"))
			{
				if (!$DB->Query("SELECT LINK_DIRECTION FROM b_sale_delivery2paysystem WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_delivery2paysystem ADD column LINK_DIRECTION CHAR(1) NOT NULL DEFAULT ''", true))
							$error .= "<br>".$DB->GetErrorMessage();
						elseif(! $DB->Query("ALTER TABLE b_sale_delivery2paysystem ADD INDEX IX_LINK_DIRECTION (LINK_DIRECTION)", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY2PAYSYSTEM ADD LINK_DIRECTION CHAR(1) NOT NULL DEFAULT ''", true))
							$error .= "<br>".$DB->GetErrorMessage();
						elseif (!$DB->Query("CREATE INDEX IX_LINK_DIRECTION ON B_SALE_DELIVERY2PAYSYSTEM(LINK_DIRECTION)", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY2PAYSYSTEM ADD LINK_DIRECTION CHAR(1) DEFAULT '' NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
						elseif (!$DB->Query("CREATE INDEX IX_LINK_DIRECTION ON B_SALE_DELIVERY2PAYSYSTEM(LINK_DIRECTION)", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}

				if ($DB->Query("SELECT DELIVERY_ID FROM b_sale_delivery2paysystem WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_delivery2paysystem MODIFY COLUMN DELIVERY_ID INT(11) NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						$DB->Query("DROP INDEX IX_DELIVERY ON B_SALE_DELIVERY2PAYSYSTEM", true);
												
						if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY2PAYSYSTEM ALTER COLUMN DELIVERY_ID INT NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
						
						$DB->Query("CREATE INDEX IX_DELIVERY ON B_SALE_DELIVERY2PAYSYSTEM(DELIVERY_ID)", true);
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY2PAYSYSTEM MODIFY DELIVERY_ID NUMBER(18)", true))
							$error .= "<br>".$DB->GetErrorMessage();

					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}

			if(!$DB->TableExists("b_sale_delivery_srv"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("create table b_sale_delivery_srv
						(
							ID int NOT NULL AUTO_INCREMENT,
							CODE varchar(50) NULL,
							PARENT_ID int NULL,
							NAME varchar(255) NOT NULL,
							ACTIVE char(1) NOT NULL,
							DESCRIPTION varchar(255) NULL,
							SORT int NOT NULL,
							LOGOTIP int NULL,
							CONFIG longtext NULL,
							CLASS_NAME varchar(255) NOT NULL,
							CURRENCY char(3) NOT NULL,
							primary key (ID),
							index IX_BSD_SRV_CODE(CODE),
							index IX_BSD_SRV_PARENT_ID(PARENT_ID)
						)", false)
					)
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_DELIVERY_SRV
						(
							ID INT NOT NULL IDENTITY(1,1),
							CODE VARCHAR(50) NULL,
							PARENT_ID INT NULL,
							NAME VARCHAR(255) NOT NULL,
							ACTIVE CHAR(1) NOT NULL,
							DESCRIPTION VARCHAR(255) NULL,
							SORT INT NOT NULL,
							LOGOTIP INT NULL,
							CONFIG TEXT NULL,
							CLASS_NAME VARCHAR(255) NOT NULL,
							CURRENCY CHAR(3) NOT NULL
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_DELIVERY_SRV ADD CONSTRAINT PK_B_SALE_DELIVERY_SRV PRIMARY KEY (ID)", false);
						$DB->Query("CREATE INDEX IX_BSD_SRV_PARENT_ID ON B_SALE_DELIVERY_SRV(PARENT_ID)", false);
						$DB->Query("CREATE INDEX IX_BSD_SRV_CODE ON B_SALE_DELIVERY_SRV(CODE)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_DELIVERY_SRV
						(
							ID NUMBER(18) NOT NULL,
							CODE VARCHAR2(50 CHAR) NULL,
							PARENT_ID NUMBER(18) NOT NULL,
							NAME VARCHAR2(255 CHAR) NULL,
							ACTIVE CHAR(1 CHAR) NOT NULL,
							DESCRIPTION VARCHAR2(255 CHAR) NULL,
							SORT NUMBER(11) DEFAULT 100 NOT NULL,
							LOGOTIP NUMBER (11) NULL,
							CONFIG CLOB NULL,
							CLASS_NAME VARCHAR2(255 CHAR) NULL,
							CURRENCY CHAR(3 CHAR) NOT NULL,
							PRIMARY KEY (ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE SEQUENCE SQ_B_SALE_DELIVERY_SRV INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_DELIVERY_SRV_INSERT
						BEFORE INSERT
						ON B_SALE_DELIVERY_SRV
						FOR EACH ROW
						BEGIN
							IF :NEW.ID IS NULL THEN
								SELECT SQ_B_SALE_DELIVERY_SRV.NEXTVAL INTO :NEW.ID FROM dual;
							END IF;
						END;", false);
						$DB->Query("CREATE INDEX IX_BSD_SRV_PARENT_ID ON B_SALE_DELIVERY_SRV (PARENT_ID)", false);
						$DB->Query("CREATE INDEX IX_BSD_SRV_CODE ON B_SALE_DELIVERY_SRV (CODE)", false);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_delivery_es"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_delivery_es(
						ID int NOT NULL AUTO_INCREMENT,
						CODE varchar(50) NULL,
						NAME varchar(255) NOT NULL,
						DESCRIPTION varchar(255) NULL,
						CLASS_NAME varchar(255) NOT NULL,
						PARAMS text NULL,
						RIGHTS char(3) NOT NULL,
						DELIVERY_ID int NOT NULL,
						INIT_VALUE varchar(255) NULL,
						ACTIVE char(1) NOT NULL,
						SORT int DEFAULT 100,
						primary key (ID),
						INDEX IX_BSD_ES_DELIVERY_ID (DELIVERY_ID))", false)
					)
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_DELIVERY_ES
						(
							ID INT NOT NULL IDENTITY(1,1),
							CODE VARCHAR(50) NULL,
							NAME VARCHAR(255) NOT NULL,
							DESCRIPTION VARCHAR(255) NULL,
							CLASS_NAME VARCHAR(255) NOT NULL,
							PARAMS TEXT NULL,
							RIGHTS CHAR(3) NOT NULL,
							DELIVERY_ID INT NOT NULL,
							INIT_VALUE VARCHAR(255) NULL,
							ACTIVE CHAR(1) NOT NULL,
							SORT INT DEFAULT 100
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_DELIVERY_ES ADD CONSTRAINT PK_B_SALE_DELIVERY_ES PRIMARY KEY (ID)", false);
						$DB->Query("CREATE INDEX IX_BSD_ES_DELIVERY_ID ON B_SALE_DELIVERY_ES(DELIVERY_ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_DELIVERY_ES
						(
							ID NUMBER(18) NOT NULL,
							CODE VARCHAR2(50 CHAR) NULL,
							NAME VARCHAR2(255 CHAR) NULL,
							DESCRIPTION VARCHAR2(255 CHAR) NULL,
							CLASS_NAME VARCHAR2(255 CHAR) NULL,
							PARAMS VARCHAR2(4000 CHAR) NULL,
							RIGHTS CHAR(3 CHAR) NOT NULL,
							DELIVERY_ID NUMBER(18) NOT NULL,
							INIT_VALUE VARCHAR2(255 CHAR) NULL,
							ACTIVE CHAR(1 CHAR) NOT NULL,
							SORT NUMBER(11) DEFAULT 100 NOT NULL,
							PRIMARY KEY (ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_BSD_ES_DELIVERY_ID ON B_SALE_DELIVERY_ES (DELIVERY_ID)", false);

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_DELIVERY_ES INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_DELIVERY_ES_INSERT
							BEFORE INSERT
							ON B_SALE_DELIVERY_ES
							FOR EACH ROW
							BEGIN
								IF :NEW.ID IS NULL THEN
									SELECT SQ_B_SALE_DELIVERY_ES.NEXTVAL INTO :NEW.ID FROM dual;
								END IF;
							END;", false);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_order_delivery"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("create table if not exists b_sale_order_delivery (
								ID INT(11) NOT NULL AUTO_INCREMENT,
								ORDER_ID INT(11) NOT NULL,
								DATE_INSERT DATETIME NOT NULL,
								DATE_REQUEST DATETIME NULL DEFAULT NULL,
								DATE_UPDATE DATETIME NULL DEFAULT NULL,
								DELIVERY_LOCATION VARCHAR(50) NULL DEFAULT NULL,
								PARAMS TEXT NULL,
								STATUS_ID CHAR(2) NOT NULL,
								PRICE_DELIVERY DECIMAL(18,4) NULL DEFAULT NULL,
								CUSTOM_PRICE_DELIVERY CHAR(1) NULL DEFAULT NULL,
								BASE_PRICE_DELIVERY DECIMAL(18,4) NULL DEFAULT NULL,
								ALLOW_DELIVERY CHAR(1) NULL DEFAULT 'N',
								DATE_ALLOW_DELIVERY DATETIME NULL DEFAULT NULL,
								EMP_ALLOW_DELIVERY_ID INT(11) NULL DEFAULT NULL,
								DEDUCTED CHAR(1) NULL DEFAULT 'N',
								DATE_DEDUCTED DATETIME NULL DEFAULT NULL,
								EMP_DEDUCTED_ID INT(11) NULL DEFAULT NULL,
								REASON_UNDO_DEDUCTED VARCHAR(255) NULL DEFAULT NULL,
								RESERVED CHAR(1) NULL DEFAULT NULL,
								DELIVERY_ID INT(11) NOT NULL,
								DELIVERY_DOC_NUM VARCHAR(20) NULL DEFAULT NULL,
								DELIVERY_DOC_DATE DATETIME NULL DEFAULT NULL,
								TRACKING_NUMBER VARCHAR(255) NULL DEFAULT NULL,
								XML_ID VARCHAR(255) NULL DEFAULT NULL,
								DELIVERY_NAME VARCHAR(128) NULL DEFAULT NULL,
								CANCELED CHAR(1) NULL DEFAULT 'N',
								DATE_CANCELED DATETIME NULL DEFAULT NULL,
								EMP_CANCELED_ID INT(11) NULL DEFAULT NULL,
								REASON_CANCELED VARCHAR(255) NULL DEFAULT '',
								MARKED CHAR(1) NULL DEFAULT NULL,
								DATE_MARKED DATETIME NULL DEFAULT NULL,
								EMP_MARKED_ID INT(11) NULL DEFAULT NULL,
								REASON_MARKED VARCHAR(255) NULL DEFAULT NULL,
								CURRENCY VARCHAR(3) NULL DEFAULT NULL,
								SYSTEM CHAR(1) NOT NULL DEFAULT 'N',
								RESPONSIBLE_ID int(11) DEFAULT NULL,
								EMP_RESPONSIBLE_ID int(11) DEFAULT NULL,
								DATE_RESPONSIBLE_ID datetime DEFAULT NULL,
								COMMENTS text,
								DISCOUNT_PRICE DECIMAL(18,4) NULL,
								COMPANY_ID int(11) DEFAULT NULL,
								PRIMARY KEY (ID),
								INDEX IX_BSOD_ORDER_ID (ORDER_ID)
							)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("create table B_SALE_ORDER_DELIVERY(
								ID INT NOT NULL IDENTITY(1, 1),
								ORDER_ID INT NOT NULL,
								DATE_INSERT DATETIME NOT NULL,
								DATE_REQUEST DATETIME NULL,
								DATE_UPDATE DATETIME NULL,
								DELIVERY_LOCATION VARCHAR(50) NULL,
								PARAMS TEXT NULL,
								STATUS_ID CHAR(2) NOT NULL,
								PRICE_DELIVERY DECIMAL(18,4) NULL,
								CUSTOM_PRICE_DELIVERY CHAR(1) NULL,
								BASE_PRICE_DELIVERY DECIMAL(18,4) NULL,
								ALLOW_DELIVERY CHAR(1) NULL CONSTRAINT DF_B_SALE_ORDER_DELIVERY_ALLOW_DELIVERY DEFAULT 'N',
								DATE_ALLOW_DELIVERY DATETIME NULL,
								EMP_ALLOW_DELIVERY_ID INT NULL,
								DEDUCTED CHAR(1) NULL CONSTRAINT DF_B_SALE_ORDER_DELIVERY_DEDUCTED DEFAULT 'N',
								DATE_DEDUCTED DATETIME NULL,
								EMP_DEDUCTED_ID INT NULL,
								REASON_UNDO_DEDUCTED VARCHAR(255) NULL ,
								RESERVED CHAR(1) NULL,
								DELIVERY_ID INT NOT NULL,
								DELIVERY_DOC_NUM VARCHAR(20) NULL,
								DELIVERY_DOC_DATE DATETIME NULL,
								TRACKING_NUMBER VARCHAR(255) NULL,
								XML_ID VARCHAR(255) NULL,
								DELIVERY_NAME VARCHAR(128) NULL,
								CANCELED CHAR(1) NULL CONSTRAINT DF_B_SALE_ORDER_DELIVERY_CANCELED DEFAULT 'N',
								DATE_CANCELED DATETIME NULL,
								EMP_CANCELED_ID INT NULL,
								REASON_CANCELED VARCHAR(255) NULL CONSTRAINT DF_B_SALE_ORDER_DELIVERY_REASON_CANCELED DEFAULT '',
								MARKED CHAR(1) NULL,
								DATE_MARKED DATETIME NULL,
								EMP_MARKED_ID INT NULL,
								REASON_MARKED VARCHAR(255) NULL,
								CURRENCY VARCHAR(3) NULL,
								SYSTEM CHAR(1) NOT NULL CONSTRAINT DF_B_SALE_ORDER_DELIVERY_SYSTEM DEFAULT 'N',
								RESPONSIBLE_ID int NULL,
								EMP_RESPONSIBLE_ID int NULL,
								DATE_RESPONSIBLE_ID datetime NULL,
								COMMENTS text,
								DISCOUNT_PRICE DECIMAL(18,4) NULL,
								COMPANY_ID int NULL
							)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_ORDER_DELIVERY_OLD DROP CONSTRAINT PK_B_SALE_ORDER_DELIVERY", false);
						$DB->Query("ALTER TABLE B_SALE_ORDER_DELIVERY ADD CONSTRAINT PK_B_SALE_ORDER_DELIVERY PRIMARY KEY (ID)", false);
						$DB->Query("CREATE INDEX IX_BSOD_ORDER_ID ON B_SALE_ORDER_DELIVERY(ORDER_ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("create table B_SALE_ORDER_DELIVERY(
								ID NUMBER(18) NOT NULL,
								ORDER_ID NUMBER(18) NOT NULL,
								DATE_INSERT DATE NOT NULL,
								DATE_REQUEST DATE NULL,
								DATE_UPDATE DATE NULL,
								DELIVERY_LOCATION VARCHAR2(50 CHAR) NULL,
								PARAMS CLOB NULL,
								STATUS_ID CHAR(2 CHAR) NOT NULL,
								PRICE_DELIVERY DECIMAL(18,4) NULL,
								CUSTOM_PRICE_DELIVERY CHAR(1 CHAR) NULL,
								BASE_PRICE_DELIVERY DECIMAL(18,4) NULL,
								ALLOW_DELIVERY CHAR(1 CHAR) DEFAULT 'N' NULL,
								DATE_ALLOW_DELIVERY DATE NULL,
								EMP_ALLOW_DELIVERY_ID NUMBER(18) NULL,
								DEDUCTED CHAR(1 CHAR) DEFAULT 'N' NULL,
								DATE_DEDUCTED DATE NULL,
								EMP_DEDUCTED_ID NUMBER(18) NULL,
								REASON_UNDO_DEDUCTED VARCHAR2(255 CHAR) NULL ,
								RESERVED CHAR(1 CHAR) NULL,
								DELIVERY_ID NUMBER(18) NOT NULL,
								DELIVERY_DOC_NUM VARCHAR2(20 CHAR) NULL,
								DELIVERY_DOC_DATE DATE NULL,
								TRACKING_NUMBER VARCHAR2(255 CHAR) NULL,
								XML_ID VARCHAR2(255 CHAR) NULL,
								DELIVERY_NAME VARCHAR2(128 CHAR) NULL,
								CANCELED CHAR(1 CHAR) DEFAULT 'N' NULL,
								DATE_CANCELED DATE NULL,
								EMP_CANCELED_ID NUMBER(18) NULL,
								REASON_CANCELED VARCHAR2(255 CHAR) DEFAULT '' NULL,
								MARKED CHAR(1 CHAR) NULL,
								DATE_MARKED DATE NULL,
								EMP_MARKED_ID NUMBER(18) NULL,
								REASON_MARKED VARCHAR2(255 CHAR) NULL,
								CURRENCY VARCHAR2(3 CHAR) NULL,
								SYSTEM CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
								RESPONSIBLE_ID NUMBER(18) NULL,
								EMP_RESPONSIBLE_ID NUMBER(18) NULL,
								DATE_RESPONSIBLE_ID DATE NULL,
								COMMENTS CLOB,
								DISCOUNT_PRICE DECIMAL(18,4) NULL,
								COMPANY_ID NUMBER(18) NULL,
								PRIMARY KEY (ID)
							)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_BSOD_ORDER_ID ON B_SALE_ORDER_DELIVERY(ORDER_ID)", false);

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_DELIVERY INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_ORDER_DELIVERY_INSERT
								BEFORE INSERT
								ON B_SALE_ORDER_DELIVERY
								FOR EACH ROW
								BEGIN
									IF :NEW.ID IS NULL THEN
								        SELECT SQ_B_SALE_ORDER_DELIVERY.NEXTVAL INTO :NEW.ID FROM dual;
									END IF;
								END;'
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_order_delivery_es"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_order_delivery_es(
							ID INT NOT NULL AUTO_INCREMENT,
							SHIPMENT_ID INT NOT NULL,
							EXTRA_SERVICE_ID INT NOT NULL,
							VALUE VARCHAR (255) NULL,
							PRIMARY KEY (ID),
							INDEX IX_BSOD_ES_SHIPMENT_ID(SHIPMENT_ID),
							INDEX IX_BSOD_ES_EXTRA_SERVICE_ID(EXTRA_SERVICE_ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_DELIVERY_ES
						(
							ID INT NOT NULL IDENTITY(1,1),
							SHIPMENT_ID INT NOT NULL,
							EXTRA_SERVICE_ID INT NOT NULL,
							VALUE VARCHAR(255) NULL
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_ORDER_DELIVERY_ES ADD CONSTRAINT PK_B_SALE_ORDER_DELIVERY_ES PRIMARY KEY(ID)", false);
						$DB->Query("CREATE UNIQUE INDEX IX_BSOD_ES_SHIPMENT_ID ON B_SALE_ORDER_DELIVERY_ES(SHIPMENT_ID)", false);
						$DB->Query("CREATE UNIQUE INDEX IX_BSOD_ES_EXTRA_SERVICE_ID ON B_SALE_ORDER_DELIVERY_ES(EXTRA_SERVICE_ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_DELIVERY_ES
									(
										ID NUMBER(18) NOT NULL,
										SHIPMENT_ID NUMBER(18) NOT NULL,
										EXTRA_SERVICE_ID NUMBER(18) NOT NULL,
										VALUE VARCHAR2(255 CHAR) NULL,
										PRIMARY KEY (ID)
									)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query('CREATE SEQUENCE SQ_B_SALE_ORDER_DELIVERY_ES INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER', false);
						$DB->Query("
								CREATE OR REPLACE TRIGGER B_SALE_ORDER_DELIVERY_ES_INS
								BEFORE INSERT
								ON B_SALE_ORDER_DELIVERY_ES
								FOR EACH ROW
								BEGIN
									IF :NEW.ID IS NULL THEN
										SELECT SQ_B_SALE_ORDER_DELIVERY_ES.NEXTVAL INTO :NEW.ID FROM dual;
									END IF;
								END;
								", false);
						$DB->Query("CREATE INDEX IX_BSOD_ES_SHIPMENT_ID ON B_SALE_ORDER_DELIVERY_ES (SHIPMENT_ID)", false);
						$DB->Query('CREATE INDEX IX_BSOD_ES_EXTRA_SERVICE_ID ON B_SALE_ORDER_DELIVERY_ES (EXTRA_SERVICE_ID)', false);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_order_dlv_basket"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_order_dlv_basket(
							ID INT(11) NOT NULL AUTO_INCREMENT,
							ORDER_DELIVERY_ID INT(11) NOT NULL,
							BASKET_ID INT(11) NOT NULL,
							DATE_INSERT DATETIME NOT NULL,
							QUANTITY DECIMAL(18,4) NOT NULL,
							RESERVED_QUANTITY DECIMAL(18,4) NOT NULL,
							PRIMARY KEY (ID),
							INDEX IX_BSODB_ORDER_DELIVERY_ID (ORDER_DELIVERY_ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_DLV_BASKET(
							ID INT NOT NULL IDENTITY(1, 1),
							ORDER_DELIVERY_ID INT NOT NULL,
							BASKET_ID INT NOT NULL,
							DATE_INSERT DATETIME NOT NULL,
							QUANTITY DECIMAL(18,4) NOT NULL,
							RESERVED_QUANTITY DECIMAL(18,4) NOT NULL,
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_ORDER_DLV_BASKET ADD CONSTRAINT PK_B_SALE_ORDER_DLV_BASKET PRIMARY KEY (ID)", false);
						$DB->Query("CREATE INDEX IX_BSODB_ORDER_DELIVERY_ID ON B_SALE_ORDER_DLV_BASKET(ORDER_DELIVERY_ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_DLV_BASKET(
							ID NUMBER(18) NOT NULL,
							ORDER_DELIVERY_ID NUMBER(18) NOT NULL,
							BASKET_ID NUMBER(18) NOT NULL,
							DATE_INSERT DATE NOT NULL,
							QUANTITY DECIMAL(18,4) NOT NULL,
							RESERVED_QUANTITY DECIMAL(18,4) NOT NULL,
							PRIMARY KEY (ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_BSODB_ORDER_DELIVERY_ID ON B_SALE_ORDER_DLV_BASKET(ORDER_DELIVERY_ID)", false);

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_DLV_BASKET INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query("CREATE OR REPLACE TRIGGER B_SALE_ORDER_DLV_BASKET_INSERT
									BEFORE INSERT
									ON B_SALE_ORDER_DLV_BASKET
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
											SELECT SQ_B_SALE_ORDER_DLV_BASKET.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;", true
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if(!$DB->TableExists("b_sale_delivery_rstr"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("create table b_sale_delivery_rstr
						(
							ID int NOT NULL AUTO_INCREMENT,
							DELIVERY_ID int NOT NULL,
							SORT int DEFAULT 100,
							CLASS_NAME varchar(255) NOT NULL,
							PARAMS  text,
							primary key (ID),
							INDEX IX_BSDR_DELIVERY_ID(DELIVERY_ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_DELIVERY_RSTR
						(
							ID INT NOT NULL IDENTITY(1,1),
							DELIVERY_ID INT NOT NULL,
							SORT INT DEFAULT 100,
							CLASS_NAME VARCHAR(255) NOT NULL,
							PARAMS TEXT NULL
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_DELIVERY_RSTR ADD CONSTRAINT PK_B_SALE_DELIVERY_RSTR PRIMARY KEY (ID)", false);
						$DB->Query("CREATE INDEX IX_BSDR_DELIVERY_ID ON B_SALE_DELIVERY_RSTR(DELIVERY_ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_DELIVERY_RSTR
						(
							ID NUMBER(18) NOT NULL,
							DELIVERY_ID NUMBER(18) NOT NULL,
							SORT NUMBER(11) DEFAULT 100 NOT NULL,
							CLASS_NAME VARCHAR2(255 CHAR) NULL,
							PARAMS VARCHAR2(4000 CHAR) NULL,
							PRIMARY KEY (ID)
						)", false)
						)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query('CREATE SEQUENCE SQ_B_SALE_DELIVERY_RSTR INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER', false);
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_DELIVERY_RSTR_INSERT
							BEFORE INSERT
							ON B_SALE_DELIVERY_RSTR
							FOR EACH ROW
							BEGIN
								IF :NEW.ID IS NULL THEN
									SELECT SQ_B_SALE_DELIVERY_RSTR.NEXTVAL INTO :NEW.ID FROM dual;
								END IF;
							END;', true
						);

						$DB->Query('CREATE INDEX IX_BSDR_DELIVERY_ID ON B_SALE_DELIVERY_RSTR (DELIVERY_ID)', false);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_OTHER_ALTERS');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_DELIVERY');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;			
		case 8:
			// OTHERS ALTERS
			if ($DB->TableExists("b_sale_store_barcode"))
			{
				if (!$DB->Query("select ORDER_DELIVERY_BASKET_ID from b_sale_store_barcode WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_store_barcode ADD COLUMN ORDER_DELIVERY_BASKET_ID INT(11) NOT NULL DEFAULT 0", true))
						{
							$error .= "<br>".$DB->GetErrorMessage();
						}
						else
						{
							$DB->Query("CREATE INDEX IX_BSSB_O_DLV_BASKET_ID ON b_sale_store_barcode (ORDER_DELIVERY_BASKET_ID)", false);
						}
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_STORE_BARCODE ADD ORDER_DELIVERY_BASKET_ID INT NOT NULL DEFAULT 0", true))
						{
							$error .= "<br>".$DB->GetErrorMessage();
						}
						else
						{
							$DB->Query("CREATE INDEX IX_BSSB_O_DLV_BASKET_ID ON b_sale_store_barcode(ORDER_DELIVERY_BASKET_ID)", false);
						}
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_STORE_BARCODE ADD ORDER_DELIVERY_BASKET_ID NUMBER(18) DEFAULT 0 NOT NULL", true))
						{
							$error .= "<br>".$DB->GetErrorMessage();
						}
						else
						{
							$DB->Query("CREATE INDEX IX_BSSB_O_DLV_BASKET_ID ON b_sale_store_barcode (ORDER_DELIVERY_BASKET_ID)", false);
						}
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}

			if ($DB->TableExists("b_sale_status"))
			{
				if ($DB->Query("SELECT ID FROM b_sale_status WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_status CHANGE ID ID char(2) NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						$DB->Query("ALTER TABLE b_sale_status DROP CONSTRAINT PK_B_SALE_STATUS", false);

						if (!$DB->Query("ALTER TABLE B_SALE_STATUS ALTER COLUMN ID CHAR(2) NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();

						$DB->Query("ALTER TABLE b_sale_status ADD CONSTRAINT PK_B_SALE_STATUS PRIMARY KEY (ID)", false);
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_STATUS MODIFY ID char(2 char)", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}

				if (!$DB->Query("SELECT TYPE FROM b_sale_status WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_status ADD TYPE char(1) not null default 'O'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_STATUS ADD TYPE char(1) not null CONSTRAINT DF_B_SALE_STATUS_TYPE DEFAULT 'O'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_STATUS ADD TYPE char(1 char) default 'O' NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}

				if (!$DB->Query("SELECT NOTIFY FROM b_sale_status WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_status ADD NOTIFY char(1) not null default 'Y'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_STATUS ADD NOTIFY char(1) not null CONSTRAINT DF_B_SALE_STATUS_NOTIFY DEFAULT 'Y'", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_STATUS ADD NOTIFY char(1 char) default 'Y' NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}

			if ($DB->TableExists("b_sale_order_history"))
			{
				if ($DB->Query("SELECT STATUS_ID FROM b_sale_order_history WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_history CHANGE STATUS_ID STATUS_ID char(2) not null", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						$DB->Query("ALTER TABLE b_sale_order_history DROP CONSTRAINT PK_B_SALE_ORDER_HISTORY", false);

						if (!$DB->Query("ALTER TABLE b_sale_order_history ALTER COLUMN STATUS_ID CHAR(2) NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();

						$DB->Query("ALTER TABLE b_sale_order_history ADD CONSTRAINT PK_B_SALE_ORDER_HISTORY PRIMARY KEY (ID)", false);
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE b_sale_order_history MODIFY STATUS_ID CHAR(2 char)", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}
			
			if ($DB->TableExists("b_sale_status_lang"))
			{
				if ($DB->Query("SELECT STATUS_ID FROM b_sale_status_lang WHERE 1=0", true))
				{
					if ($DB->type == "MYSQL")
					{
						if (!$DB->Query("ALTER TABLE b_sale_status_lang CHANGE STATUS_ID STATUS_ID char(2) not null", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}
					elseif ($DB->type == "MSSQL")
					{
						$DB->Query("ALTER TABLE B_SALE_STATUS_LANG DROP CONSTRAINT PK_B_SALE_STATUS_LANG", false);

						if (!$DB->Query("ALTER TABLE B_SALE_STATUS_LANG ALTER COLUMN STATUS_ID CHAR(2) NOT NULL", true))
							$error .= "<br>".$DB->GetErrorMessage();

						$DB->Query("ALTER TABLE B_SALE_STATUS_LANG ADD CONSTRAINT PK_B_SALE_STATUS_LANG PRIMARY KEY (STATUS_ID, LID)", false);
					}
					elseif ($DB->type == "ORACLE")
					{
						if (!$DB->Query("ALTER TABLE B_SALE_STATUS_LANG MODIFY STATUS_ID CHAR(2 CHAR)", true))
							$error .= "<br>".$DB->GetErrorMessage();
					}

					if (!empty($error))
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "-",
							"DESCRIPTION" => $error,
						));
					}
				}
			}

			if ($DB->type == "MYSQL")
			{
				if ($DB->TableExists("b_sale_loc_type"))
				{
					if (!$DB->Query("SELECT DISPLAY_SORT FROM b_sale_loc_type WHERE 1=0", true))
					{
						if (!$DB->Query("ALTER TABLE b_sale_loc_type ADD DISPLAY_SORT int default '100'", true))
						{
							$error .= "<br>".$DB->GetErrorMessage();
							
							\CEventLog::Add(array(
								"SEVERITY" => "ERROR",
								"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
								"MODULE_ID" => "sale",
								"ITEM_ID" => "-",
								"DESCRIPTION" => $error,
							));
						}
					}
				}
			}

			if ($DB->type == "MSSQL")
			{
				if ($DB->TableExists("B_SALE_LOC_TYPE"))
				{
					if (!$DB->Query("SELECT DISPLAY_SORT FROM B_SALE_LOC_TYPE WHERE 1=0", true))
					{
						if (!$DB->Query("ALTER TABLE B_SALE_LOC_TYPE ADD DISPLAY_SORT int", true))
						{
							$error .= "<br>".$DB->GetErrorMessage();
							
							\CEventLog::Add(array(
								"SEVERITY" => "ERROR",
								"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
								"MODULE_ID" => "sale",
								"ITEM_ID" => "-",
								"DESCRIPTION" => $error,
							));
						}

						if (!$DB->Query("ALTER TABLE B_SALE_LOC_TYPE ADD CONSTRAINT DF_B_SALE_LOC_TYPE_D_SORT DEFAULT '100' FOR DISPLAY_SORT", true))
						{
							$error .= "<br>".$DB->GetErrorMessage();
							
							\CEventLog::Add(array(
								"SEVERITY" => "ERROR",
								"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
								"MODULE_ID" => "sale",
								"ITEM_ID" => "-",
								"DESCRIPTION" => $error,
							));
						}
					}
				}
			}
			
			if ($DB->type == "ORACLE")
			{
				if ($DB->TableExists("B_SALE_LOC_TYPE"))
				{
					if (!$DB->Query("SELECT DISPLAY_SORT FROM B_SALE_LOC_TYPE WHERE 1=0", true))
					{
						if (!$DB->Query("ALTER TABLE B_SALE_LOC_TYPE ADD (DISPLAY_SORT NUMBER(18) DEFAULT '100')", true))
						{
							$error .= "<br>".$DB->GetErrorMessage();
							
							\CEventLog::Add(array(
								"SEVERITY" => "ERROR",
								"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
								"MODULE_ID" => "sale",
								"ITEM_ID" => "-",
								"DESCRIPTION" => $error,
							));
						}

					}
				}
			}

			if ($DB->TableExists("B_SALE_LOC_TYPE"))
			{
				if(!$DB->Query("UPDATE b_sale_loc_type SET DISPLAY_SORT = '700' WHERE CODE = 'COUNTRY'", true))
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
				if(!$DB->Query("UPDATE b_sale_loc_type SET DISPLAY_SORT = '600' WHERE CODE = 'COUNTRY_DISTRICT'", true))
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
				
				if(!$DB->Query("UPDATE b_sale_loc_type SET DISPLAY_SORT = '500' WHERE CODE = 'REGION'", true))
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}

				if(!$DB->Query("UPDATE b_sale_loc_type SET DISPLAY_SORT = '400' WHERE CODE = 'SUBREGION'", true))
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}

				if(!$DB->Query("UPDATE b_sale_loc_type SET DISPLAY_SORT = '100' WHERE CODE = 'CITY'", true))
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}

				if(!$DB->Query("UPDATE b_sale_loc_type SET DISPLAY_SORT = '200' WHERE CODE = 'VILLAGE'", true))
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}

				if(!$DB->Query("UPDATE b_sale_loc_type SET DISPLAY_SORT = '300' WHERE CODE = 'STREET'", true))
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if ($DB->TableExists("B_SALE_PAY_SYSTEM_ACTION") && $DB->type == 'ORACLE')
			{
				$dbNextVal = $DB->Query("SELECT SQ_SALE_PAY_SYSTEM_ACTION.NEXTVAL FROM DUAL", true);
				if ($dbNextVal)
				{
					$data = $dbNextVal->Fetch();
					$id = intval($data["NEXTVAL"]);

					$DB->Query('DROP SEQUENCE SQ_SALE_PAY_SYSTEM_ACTION');

					$DB->Query('CREATE SEQUENCE SQ_B_SALE_PAY_SYSTEM_ACTION START WITH '.$id.' INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER');
					$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_PS_ACTION_INSERT
								BEFORE INSERT
								ON B_SALE_PAY_SYSTEM_ACTION
								FOR EACH ROW
								BEGIN
									IF :NEW.ID IS NULL THEN
								        SELECT SQ_B_SALE_PAY_SYSTEM_ACTION.NEXTVAL INTO :NEW.ID FROM dual;
									END IF;
								END;'
					);

				}
			}

			if ($DB->TableExists("B_SALE_PAY_SYSTEM") && $DB->type == 'ORACLE')
			{
				$dbNextVal = $DB->Query("SELECT SQ_SALE_PAY_SYSTEM.NEXTVAL FROM DUAL", true);
				if ($dbNextVal)
				{
					$data = $dbNextVal->Fetch();
					$id = intval($data["NEXTVAL"]);

					$DB->Query('DROP SEQUENCE SQ_SALE_PAY_SYSTEM');

					$DB->Query('CREATE SEQUENCE SQ_B_SALE_PAY_SYSTEM START WITH '.$id.' INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER');
					$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_PAY_SYSTEM_INSERT
								BEFORE INSERT
								ON B_SALE_PAY_SYSTEM
								FOR EACH ROW
								BEGIN
									IF :NEW.ID IS NULL THEN
								        SELECT SQ_B_SALE_PAY_SYSTEM.NEXTVAL INTO :NEW.ID FROM dual;
									END IF;
								END;'
					);

				}
			}

			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_BIZVAL');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_OTHER_ALTERS');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 9:
			// BIZVAL
			
			if (!$DB->TableExists("b_sale_bizval"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_bizval(
							ID int not null auto_increment,
							CODE_ID int not null,
							PERSON_TYPE_ID int not null,
							ENTITY varchar(50) not null,
							ITEM varchar(255) not null,
							primary key(ID),
							UNIQUE INDEX IX_BSB_SECONDARY (CODE_ID, PERSON_TYPE_ID)
						)
					", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL
									(
										ID INT NOT NULL IDENTITY(1,1),
										CODE_ID INT NOT NULL,
										PERSON_TYPE_ID INT NOT NULL,
										ENTITY VARCHAR(50) NOT NULL,
										ITEM VARCHAR(255) NOT NULL
									)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_BIZVAL ADD CONSTRAINT PK_B_SALE_BIZVAL PRIMARY KEY (ID)", false);
						$DB->Query("CREATE UNIQUE INDEX IX_BSB_SECONDARY ON B_SALE_BIZVAL(CODE_ID, PERSON_TYPE_ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL
									(
										ID NUMBER(18) NOT NULL,
										CODE_ID NUMBER(18) NOT NULL,
										PERSON_TYPE_ID NUMBER(18) NOT NULL,
										ENTITY VARCHAR2(50 CHAR) NOT NULL,
										ITEM VARCHAR2(255 CHAR) NOT NULL,
										PRIMARY KEY (ID)
									)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE UNIQUE INDEX IX_BSB_SECONDARY ON B_SALE_BIZVAL(CODE_ID, PERSON_TYPE_ID)", false);

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_BIZVAL INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_BIZVAL_INSERT
									BEFORE INSERT
									ON B_SALE_BIZVAL
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
									        SELECT SQ_B_SALE_BIZVAL.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;'
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}
			if (!$DB->TableExists("b_sale_bizval_code"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_bizval_code(
							ID int not null auto_increment,
							NAME varchar(50) not null,
							DOMAIN char(1) not null,
							GROUP_ID int null,
							SORT int not null default 100,
							primary key(ID),
							UNIQUE INDEX IX_BSBC_NAME (NAME)
						)
					", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_CODE
						(
							ID INT NOT NULL IDENTITY(1,1),
							NAME VARCHAR(50) NOT NULL,
							DOMAIN CHAR(1) NOT NULL,
							GROUP_ID INT NULL,
							SORT INT NOT NULL DEFAULT 100
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_BIZVAL_CODE ADD CONSTRAINT PK_B_SALE_BIZVAL_CODE PRIMARY KEY (ID)", false);
						$DB->Query("CREATE UNIQUE INDEX IX_BSBC_NAME ON B_SALE_BIZVAL_CODE(NAME)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_CODE
						(
							ID NUMBER(18) NOT NULL,
							NAME VARCHAR2(50 CHAR) NOT NULL,
							DOMAIN CHAR(1 CHAR) NOT NULL,
							GROUP_ID NUMBER(18) NULL,
							SORT NUMBER(18) DEFAULT 100 NOT NULL,
							PRIMARY KEY (ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE UNIQUE INDEX IX_BSBC_NAME ON B_SALE_BIZVAL_CODE(NAME)", false);

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_BIZVAL_CODE INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_BIZVAL_CODE_INSERT
									BEFORE INSERT
									ON B_SALE_BIZVAL_CODE
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
									        SELECT SQ_B_SALE_BIZVAL_CODE.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;'
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_bizval_group"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_bizval_group(
								ID int not null auto_increment,
								NAME varchar(50) not null,
								SORT int not null default 100,
								primary key(ID),
								UNIQUE INDEX IX_BSBG_NAME (NAME)
							)
						", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_GROUP
							(
								ID INT NOT NULL IDENTITY(1,1),
								NAME VARCHAR(50) NOT NULL,
								SORT INT NOT NULL DEFAULT 100
							)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_BIZVAL_GROUP ADD CONSTRAINT PK_B_SALE_BIZVAL_GROUP PRIMARY KEY (ID)", false);
						$DB->Query("CREATE UNIQUE INDEX IX_BSBG_NAME ON B_SALE_BIZVAL_GROUP(NAME)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_GROUP
							(
								ID NUMBER(18) NOT NULL,
								NAME VARCHAR2(50 CHAR) NOT NULL,
								SORT NUMBER(18) DEFAULT 100 NOT NULL,
								PRIMARY KEY (ID)
							)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE UNIQUE INDEX IX_BSBG_NAME ON B_SALE_BIZVAL_GROUP(NAME)", false);

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_BIZVAL_GROUP INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_BIZVAL_GROUP_INSERT
									BEFORE INSERT
									ON B_SALE_BIZVAL_GROUP
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
									        SELECT SQ_B_SALE_BIZVAL_GROUP.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;'
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}
			if (!$DB->TableExists("b_sale_bizval_parent"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_bizval_parent(
							ID int not null auto_increment,
							NAME varchar(50) not null,
							LANG_SRC varchar(255) null,
							primary key(ID),
							UNIQUE INDEX IX_BSBP_NAME (NAME)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_PARENT
						(
							ID INT NOT NULL IDENTITY(1,1),
							NAME VARCHAR(50) NOT NULL,
							LANG_SRC VARCHAR(255) NULL
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_BIZVAL_PARENT ADD CONSTRAINT PK_B_SALE_BIZVAL_PARENT PRIMARY KEY (ID)", false);
						$DB->Query("CREATE UNIQUE INDEX IX_BSBP_NAME ON B_SALE_BIZVAL_PARENT(NAME)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_PARENT
						(
							ID NUMBER(18) NOT NULL,
							NAME VARCHAR2(50 CHAR) NOT NULL,
							LANG_SRC VARCHAR2(255 CHAR) NULL,
							PRIMARY KEY (ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE UNIQUE INDEX IX_BSBP_NAME ON B_SALE_BIZVAL_PARENT(NAME)", false);

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_BIZVAL_PARENT INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_BIZVAL_PARENT_INSERT
									BEFORE INSERT
									ON B_SALE_BIZVAL_PARENT
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
									        SELECT SQ_B_SALE_BIZVAL_PARENT.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;'
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_bizval_codeparent"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("create table if not exists b_sale_bizval_codeparent
						(
							CODE_ID int not null,
							PARENT_ID int not null,
							primary key(CODE_ID, PARENT_ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_CODEPARENT
						(
							CODE_ID INT NOT NULL,
							PARENT_ID INT NOT NULL
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_BIZVAL_CODEPARENT ADD CONSTRAINT PK_B_SALE_BIZVAL_CODEPARENT PRIMARY KEY (CODE_ID, PARENT_ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_CODEPARENT
						(
							CODE_ID NUMBER(18) NOT NULL,
							PARENT_ID NUMBER(18) NOT NULL,
							PRIMARY KEY (CODE_ID, PARENT_ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_bizval_persondomain"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("create table if not exists b_sale_bizval_persondomain
						(
							PERSON_TYPE_ID int not null,
							DOMAIN char(1) not null,
							primary key(PERSON_TYPE_ID, DOMAIN)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_PERSONDOMAIN
						(
							PERSON_TYPE_ID INT NOT NULL,
							DOMAIN CHAR(1) NOT NULL,
							PRIMARY KEY (PERSON_TYPE_ID, DOMAIN)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_BIZVAL_PERSONDOMAIN
						(
							PERSON_TYPE_ID INT NOT NULL,
							DOMAIN CHAR(1) NOT NULL
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_BIZVAL_PERSONDOMAIN ADD CONSTRAINT PK_B_SALE_BIZVAL_PERSONDOMAIN PRIMARY KEY (PERSON_TYPE_ID, DOMAIN)", false);
					}
				}
				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_OTHER_CREATE');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_BIZVAL');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 10:	
			// OTHER CREATE
			if (!$DB->TableExists("b_sale_status_group_task"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_status_group_task(
										STATUS_ID char(2) not null,
										GROUP_ID int(18) not null,
										TASK_ID int(18) not null,
										primary key (STATUS_ID, GROUP_ID, TASK_ID)
									)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_status_group_task
									(
										STATUS_ID CHAR(2) NOT NULL,
										GROUP_ID INT NOT NULL,
										TASK_ID INT NOT NULL
									)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query('ALTER TABLE b_sale_status_group_task ADD CONSTRAINT pk_b_sale_status_group_task PRIMARY KEY (STATUS_ID, GROUP_ID, TASK_ID)', false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE b_sale_status_group_task
									(
										STATUS_ID CHAR(2 CHAR) NOT NULL,
										GROUP_ID NUMBER(18) NOT NULL,
										TASK_ID NUMBER(18) NOT NULL,
										PRIMARY KEY (STATUS_ID, GROUP_ID, TASK_ID)
									)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_order_payment"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_order_payment(
							ID INT(11) NOT NULL AUTO_INCREMENT,
							ORDER_ID INT(11) NOT NULL,
							PAID CHAR(1) NOT NULL DEFAULT 'N',
							DATE_PAID DATETIME NULL DEFAULT NULL,
							EMP_PAID_ID INT(11) NULL DEFAULT NULL,
							PAY_SYSTEM_ID INT(11) NOT NULL,
							PS_STATUS CHAR(1) NULL DEFAULT NULL,
							PS_STATUS_CODE CHAR(5) NULL DEFAULT NULL,
							PS_STATUS_DESCRIPTION VARCHAR(250) NULL DEFAULT NULL,
							PS_STATUS_MESSAGE VARCHAR(250) NULL DEFAULT NULL,
							PS_SUM DECIMAL(18,4) NULL DEFAULT NULL,
							PS_CURRENCY CHAR(3) NULL DEFAULT NULL,
							PS_RESPONSE_DATE DATETIME NULL DEFAULT NULL,
							PAY_VOUCHER_NUM VARCHAR(20) NULL DEFAULT NULL,
							PAY_VOUCHER_DATE DATE NULL DEFAULT NULL,
							DATE_PAY_BEFORE DATETIME NULL DEFAULT NULL,
							DATE_BILL DATETIME NULL DEFAULT NULL,
							XML_ID VARCHAR(255) NULL DEFAULT NULL,
							SUM DECIMAL(18,4) NOT NULL,
							CURRENCY CHAR(3) NOT NULL,
							PAY_SYSTEM_NAME VARCHAR(128) NOT NULL,
							RESPONSIBLE_ID int(11) DEFAULT NULL,
							DATE_RESPONSIBLE_ID datetime DEFAULT NULL,
							EMP_RESPONSIBLE_ID int(11) DEFAULT NULL,
							COMMENTS text,
							COMPANY_ID int(11) DEFAULT NULL,
							PAY_RETURN_DATE date DEFAULT NULL,
							EMP_RETURN_ID INT(11) NULL DEFAULT NULL,
							PAY_RETURN_NUM VARCHAR(20) DEFAULT NULL,
							PAY_RETURN_COMMENT text,
							IS_RETURN CHAR(1) NOT NULL DEFAULT 'N',
							PRIMARY KEY (ID),
							INDEX IX_BSOP_ORDER_ID (ORDER_ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_PAYMENT(
							ID INT NOT NULL IDENTITY(1, 1),
							ORDER_ID INT NOT NULL,
							PAID CHAR(1) NOT NULL DEFAULT 'N',
							DATE_PAID DATETIME NULL,
							EMP_PAID_ID INT NULL,
							PAY_SYSTEM_ID INT NOT NULL,
							PS_STATUS CHAR(1) NULL,
							PS_STATUS_CODE CHAR(5) NULL,
							PS_STATUS_DESCRIPTION VARCHAR(250) NULL,
							PS_STATUS_MESSAGE VARCHAR(250) NULL,
							PS_SUM DECIMAL(18,4) NULL,
							PS_CURRENCY CHAR(3) NULL,
							PS_RESPONSE_DATE DATETIME NULL,
							PAY_VOUCHER_NUM VARCHAR(20) NULL,
							PAY_VOUCHER_DATE DATE NULL,
							DATE_PAY_BEFORE DATETIME NULL,
							DATE_BILL DATETIME NULL,
							XML_ID VARCHAR(255) NULL,
							SUM DECIMAL(18,4) NOT NULL,
							CURRENCY CHAR(3) NOT NULL,
							PAY_SYSTEM_NAME VARCHAR(128) NOT NULL,
							RESPONSIBLE_ID INT NULL,
							DATE_RESPONSIBLE_ID datetime NULL,
							EMP_RESPONSIBLE_ID int NULL,
							COMMENTS text,
							COMPANY_ID int NULL,
							PAY_RETURN_DATE date DEFAULT NULL,
							EMP_RETURN_ID INT NULL,
							PAY_RETURN_NUM VARCHAR(20) DEFAULT NULL,
							PAY_RETURN_COMMENT text,
							IS_RETURN CHAR(1) NOT NULL DEFAULT 'N'
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_BSOP_ORDER_ID ON B_SALE_ORDER_PAYMENT(ORDER_ID)", false);
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_ORDER_PAYMENT(
							ID NUMBER(18) NOT NULL,
							ORDER_ID NUMBER(18) NOT NULL,
							PAID CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
							DATE_PAID DATE NULL,
							EMP_PAID_ID NUMBER(18) NULL,
							PAY_SYSTEM_ID NUMBER(18) NOT NULL,
							PS_STATUS CHAR(1 CHAR) NULL,
							PS_STATUS_CODE CHAR(5 CHAR) NULL,
							PS_STATUS_DESCRIPTION VARCHAR2(250 CHAR) NULL,
							PS_STATUS_MESSAGE VARCHAR2(250 CHAR) NULL,
							PS_SUM DECIMAL(18,4) NULL,
							PS_CURRENCY CHAR(3 CHAR) NULL,
							PS_RESPONSE_DATE DATE NULL,
							PAY_VOUCHER_NUM VARCHAR2(20 CHAR) NULL,
							PAY_VOUCHER_DATE DATE NULL,
							DATE_PAY_BEFORE DATE NULL,
							DATE_BILL DATE NULL,
							XML_ID VARCHAR2(255 CHAR) NULL,
							SUM DECIMAL(18,4) NOT NULL,
							CURRENCY CHAR(3 CHAR) NOT NULL,
							PAY_SYSTEM_NAME VARCHAR2(128 CHAR) NOT NULL,
							RESPONSIBLE_ID NUMBER(18) NULL,
							DATE_RESPONSIBLE_ID date NULL,
							EMP_RESPONSIBLE_ID NUMBER(18) NULL,
							COMMENTS clob,
							COMPANY_ID NUMBER(18) NULL,
							PAY_RETURN_DATE date NULL,
							EMP_RETURN_ID NUMBER(18) NULL,
							PAY_RETURN_NUM VARCHAR2(20 CHAR) NULL,
							PAY_RETURN_COMMENT clob,
							IS_RETURN CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
							PRIMARY KEY (ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE INDEX IX_BSOP_ORDER_ID ON B_SALE_ORDER_PAYMENT(ORDER_ID)", false);

						$DB->Query("CREATE SEQUENCE SQ_B_SALE_ORDER_PAYMENT INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_ORDER_PAYMENT_INSERT
									BEFORE INSERT
									ON B_SALE_ORDER_PAYMENT
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
									        SELECT SQ_B_SALE_ORDER_PAYMENT.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;'
						);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}

			if (!$DB->TableExists("b_sale_company"))
			{
				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query("CREATE TABLE b_sale_company(
							ID int not null auto_increment,
							NAME varchar(128) not null,
							LOCATION_ID varchar(128) not null,
							CODE varchar(45) null,
							XML_ID varchar(45) null,
							ACTIVE char(1) not null default 'Y',
							DATE_CREATE datetime null,
							DATE_MODIFY datetime null,
							CREATED_BY int null,
							MODIFIED_BY int null,
							ADDRESS VARCHAR(255) NULL,
							primary key(ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_COMPANY
						(
							ID NUMBER(18) NOT NULL,
							NAME VARCHAR2(128 CHAR) NOT NULL,
							LOCATION_ID VARCHAR2(128 CHAR) NOT NULL,
							CODE VARCHAR2(45 CHAR) NULL,
							XML_ID VARCHAR2(45 CHAR) NULL,
							ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
							DATE_CREATE DATE NULL,
							DATE_MODIFY DATE NULL,
							CREATED_BY NUMBER(18) NULL,
							MODIFIED_BY NUMBER(18) NULL,
							PRIMARY KEY (ID)
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("CREATE SEQUENCE SQ_B_SALE_COMPANY INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER", false);
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_COMPANY_INSERT
									BEFORE INSERT
									ON B_SALE_COMPANY
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
									        SELECT SQ_B_SALE_COMPANY.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;'
						);
					}
				}
				elseif ($DB->type == "MSSQL")
				{
					if (!$DB->Query("CREATE TABLE B_SALE_COMPANY
						(
							ID int NOT NULL IDENTITY(1,1),
							NAME varchar(128) NOT NULL,
							LOCATION_ID varchar(128) NOT NULL,
							CODE varchar(45) NULL,
							XML_ID varchar(45) NULL,
							ACTIVE char(1) NOT NULL CONSTRAINT DF_B_SALE_COMPANY_ACTIVE DEFAULT 'Y',
							DATE_CREATE DATETIME NULL,
							DATE_MODIFY DATETIME NULL,
							CREATED_BY INT NOT NULL,
							MODIFIED_BY INT NULL
						)", false)
					)
					{
						$error .= "<br>".$DB->GetErrorMessage();
					}
					else
					{
						$DB->Query("ALTER TABLE B_SALE_COMPANY ADD CONSTRAINT PK_B_SALE_COMPANY PRIMARY KEY (ID)", false);
					}
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}
			
			ob_start();			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_COPY_FILES');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_OTHER_CREATE');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 11:
			if (!CopyDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/distr', $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale', true, true, true))
				$error .= "<br>".str_replace(array('#FROM#', '#TO#'), array($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/distr', $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale'),Loc::getMessage('SALE_CONVERTER_COPY_FILES_ERROR'));
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_DELIVERY_CONVERT');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_COPY_FILES');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
								
				\CEventLog::Add(array(
					"SEVERITY" => "ERROR",
					"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "-",
					"DESCRIPTION" => 'COPY FILES ERROR',
				));
			}
			
			$result['DATA'] = $message;
			break;

		case 12:
			try
			{				
				if($DB->TableExists("b_sale_delivery") && !$DB->TableExists("b_sale_delivery_old"))
				{
					$start = microtime(true);				
					$res = CSaleDelivery::convertToNew();

					if($res->isSuccess())
					{
						if ($DB->type == "MYSQL")
						{
							if (!$DB->Query("ALTER TABLE b_sale_delivery RENAME b_sale_delivery_old", true))
								$error .= "<br>".$DB->GetErrorMessage();

						}
						elseif ($DB->type == "MSSQL")
						{
							if (!$DB->Query("sp_rename B_SALE_DELIVERY, B_SALE_DELIVERY_OLD", true))
								$error .= "<br>".$DB->GetErrorMessage();
						}
						elseif ($DB->type == "ORACLE")
						{
							if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY RENAME TO B_SALE_DELIVERY_OLD", true))
								$error .= "<br>".$DB->GetErrorMessage();
						}

						if (!empty($error))
						{
							\CEventLog::Add(array(
								"SEVERITY" => "ERROR",
								"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
								"MODULE_ID" => "sale",
								"ITEM_ID" => "-",
								"DESCRIPTION" => $error,
							));
						}
					}
					else
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "b_sale_delivery",
							"DESCRIPTION" => implode('\n', $res->getErrorMessages()),
						));	
					}
					
					file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'CSaleDelivery::convertToNew = '.(microtime(true)-$start)."\n", FILE_APPEND);
				}

				if($DB->TableExists("b_sale_delivery_handler") && !$DB->TableExists("b_sale_delivery_handler_old"))
				{
					$start = microtime(true);
					$res = CSaleDeliveryHandler::convertToNew();

					if($res->isSuccess())
					{
						if ($DB->type == "MYSQL")
						{
							if (!$DB->Query("ALTER TABLE b_sale_delivery_handler RENAME b_sale_delivery_handler_old", true))
								$error .= "<br>".$DB->GetErrorMessage();

						}
						elseif ($DB->type == "MSSQL")
						{
							if (!$DB->Query("sp_rename B_SALE_DELIVERY_HANDLER, B_SALE_DELIVERY_HANDLER_OLD", true))
								$error .= "<br>".$DB->GetErrorMessage();
						}
						elseif ($DB->type == "ORACLE")
						{
							if (!$DB->Query("ALTER TABLE B_SALE_DELIVERY_HANDLER RENAME TO B_SALE_DELIVERY_HANDLER_OLD", true))
								$error .= "<br>".$DB->GetErrorMessage();
						}

						if (!empty($error))
						{
							\CEventLog::Add(array(
								"SEVERITY" => "ERROR",
								"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
								"MODULE_ID" => "sale",
								"ITEM_ID" => "-",
								"DESCRIPTION" => $error,
							));
						}
					}
					else
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "b_sale_delivery_handler",
							"DESCRIPTION" => implode('\n', $res->getErrorMessages()),
						));
					}

					file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'CSaleDeliveryHandler::convertToNew = '.(microtime(true)-$start)."\n", FILE_APPEND);
				}			

				if($DB->TableExists("b_sale_delivery2paysystem"))
				{
					$start = microtime(true);
					$res = CSaleDelivery::convertPSRelations();

					if(!$res->isSuccess())
					{
						\CEventLog::Add(array(
							"SEVERITY" => "ERROR",
							"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
							"MODULE_ID" => "sale",
							"ITEM_ID" => "b_sale_delivery2paysystem",
							"DESCRIPTION" => implode('\n', $res->getErrorMessages()),
						));
					}

					file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'CSaleDelivery::convertPSRelations= '.(microtime(true)-$start)."\n", FILE_APPEND);
				}							
			}
			catch(Exception $e)
			{							
				\CEventLog::Add(array(
					"SEVERITY" => "ERROR",
					"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "-",
					"DESCRIPTION" => $e->getMessage()
				));
			}
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_STATUS_CONVERT');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_DELIVERY_CONVERT');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 13:
			$start = microtime(true);
			
			try
			{
				require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/compatible/compatible.php';
				require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/internals/status_grouptask.php';
				require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/general/status.php';
				CSaleStatusAdapter::migrate();
			}
			catch (Exception $e)
			{
				\CEventLog::Add(array(
					"SEVERITY" => "ERROR",
					"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "CSaleStatusAdapter::migrate",
					"DESCRIPTION" => $e->getMessage()
				));
			}
			
			try
			{
				require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/status.php';
				require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/internals/status.php';
				require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/internals/status_lang.php';

				$deliveryInitialStatus = Bitrix\Sale\DeliveryStatus::getInitialStatus();
				$deliveryFinalStatus   = Bitrix\Sale\DeliveryStatus::getFinalStatus();

				$statusLanguages = array();

				$result = Bitrix\Main\Localization\LanguageTable::getList(array(
					'select' => array('LID'),
					'filter' => array('=ACTIVE' => 'Y'),
				));

				while ($row = $result->Fetch())
				{
					$languageId = $row['LID'];

					Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/status.php', $languageId);

					foreach (array($deliveryInitialStatus, $deliveryFinalStatus) as $statusId)
					{
						if ($statusName = Loc::getMessage("SALE_STATUS_{$statusId}"))
						{
							$statusLanguages[$statusId] []= array(
								'LID'         => $languageId,
								'NAME'        => $statusName,
								'DESCRIPTION' => Loc::getMessage("SALE_STATUS_{$statusId}_DESCR"),
							);
						}
					}
				}

				Bitrix\Sale\DeliveryStatus::install(array(
					'ID'     => $deliveryInitialStatus,
					'SORT'   => 300,
					'NOTIFY' => 'Y',
					'LANG'   => $statusLanguages[$deliveryInitialStatus],
				));

				Bitrix\Sale\DeliveryStatus::install(array(
					'ID'     => $deliveryFinalStatus,
					'SORT'   => 400,
					'NOTIFY' => 'Y',
					'LANG'   => $statusLanguages[$deliveryFinalStatus],
				));
			}
			catch (Exception $e)
			{
				\CEventLog::Add(array(
					"SEVERITY" => "ERROR",
					"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "Status::install",
					"DESCRIPTION" => $e->getMessage()
				));
			}
			
			try
			{
				if ($DB->Query('SELECT SIZE1 FROM b_sale_order_props WHERE 1=0', true))
				{
					require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/compatible/compatible.php';
					require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/internals/orderprops.php';
					require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/general/order_props.php';
					CSaleOrderPropsAdapter::migrate();
				}
			}
			catch (Exception $e)
			{
				\CEventLog::Add(array(
					"SEVERITY" => "ERROR",
					"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "CSaleOrderPropsAdapter::migrate",
					"DESCRIPTION" => $e->getMessage()
				));
			}
			
			if ($DB->Query('SELECT SIZE1 FROM b_sale_order_props WHERE 1=0', true))
			{

				if ($DB->type == "MYSQL")
				{
					if (!$DB->Query('ALTER TABLE b_sale_order_props DROP SIZE1, DROP SIZE2', true))
						$error .= "<br>".$DB->GetErrorMessage();

				}
				elseif ($DB->type == "MSSQL")
				{

					$DB->Query("ALTER TABLE B_SALE_ORDER_PROPS DROP CONSTRAINT DF_B_SALE_ORDER_PROPS_SIZE1", false);
					$DB->Query("ALTER TABLE B_SALE_ORDER_PROPS DROP CONSTRAINT DF_B_SALE_ORDER_PROPS_SIZE2", false);

					if (!$DB->Query("ALTER TABLE B_SALE_ORDER_PROPS DROP COLUMN SIZE1, SIZE2", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("ALTER TABLE B_SALE_ORDER_PROPS DROP (SIZE1, SIZE2)", true))
						$error .= "<br>".$DB->GetErrorMessage();
				}

				if (!empty($error))
				{
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error,
					));
				}
			}
			
			$end = microtime(true);
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'Migrate statuses and properties = '.($end-$start)."\n", FILE_APPEND);

			$result = array();
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_PAY_SYSTEM_INNER');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_STATUS_CONVERT');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 14:
			$res = \Bitrix\Sale\Internals\PaySystemInner::add();
			if ($res !== false)
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSTALLER');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_PAY_SYSTEM_INNER');
				$result['ERROR'] = true;
				$message .= "<br>".Loc::getMessage('SALE_CONVERTER_AJAX_STEP_PAY_SYSTEM_INNER_ERROR');
			}
			
			$result['DATA'] = $message;
			break;		
		case 15:
			if (!CopyDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/admin', $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin', true, true))
				$error .= "<br>".str_replace(array('#FROM#', '#TO#'), array($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/admin', $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin'),Loc::getMessage('SALE_CONVERTER_COPY_FILES_ERROR'));
				
			if (!CopyDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/components', $_SERVER["DOCUMENT_ROOT"].'/bitrix/components', true, true))
				$error .= "<br>".str_replace(array('#FROM#', '#TO#'), array($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/components', $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin'),Loc::getMessage('SALE_CONVERTER_COPY_FILES_ERROR'));
				
			if (!CopyDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/images', $_SERVER["DOCUMENT_ROOT"].'/bitrix/images/sale', true, true))
				$error .= "<br>".str_replace(array('#FROM#', '#TO#'), array($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/images', $_SERVER["DOCUMENT_ROOT"].'/bitrix/images/sale'),Loc::getMessage('SALE_CONVERTER_COPY_FILES_ERROR'));
			
			if (!CopyDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/js', $_SERVER["DOCUMENT_ROOT"].'/bitrix/js', true, true))
				$error .= "<br>".str_replace(array('#FROM#', '#TO#'), array($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/images', $_SERVER["DOCUMENT_ROOT"].'/bitrix/js'),Loc::getMessage('SALE_CONVERTER_COPY_FILES_ERROR'));
				
			if (!CopyDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/themes', $_SERVER["DOCUMENT_ROOT"].'/bitrix/themes', true, true))
				$error .= "<br>".str_replace(array('#FROM#', '#TO#'), array($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/themes', $_SERVER["DOCUMENT_ROOT"].'/bitrix/themes'),Loc::getMessage('SALE_CONVERTER_COPY_FILES_ERROR'));
				
			if (!CopyDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/tools', $_SERVER["DOCUMENT_ROOT"].'/bitrix/tools', true, true))
				$error .= "<br>".str_replace(array('#FROM#', '#TO#'), array($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/install/tools', $_SERVER["DOCUMENT_ROOT"].'/bitrix/tools'),Loc::getMessage('SALE_CONVERTER_COPY_FILES_ERROR'));
			
			$arToDelete = array(
				"modules/sale/install/js/sale/status_perms.js",
				"modules/sale/mssql/status.php",
				"modules/sale/mysql/status.php",
				"modules/sale/oracle/status.php",
				"modules/sale/lib/delivery/handler/first.php",
				"modules/sale/lang/ru/lib/input.php",
				"modules/sale/lib/input.php",
				"modules/sale/lib/orderpropsgroup.php",
				"modules/sale/lib/orderpropsrelation.php",
				"modules/sale/lib/orderpropsvalue.php",
				"modules/sale/lib/userpropsvalue.php",
				"modules/sale/install/components/bitrix/sale.ajax.locations/.description.php",
				"components/bitrix/sale.ajax.locations/.description.php",
				"modules/sale/lib/delivery/exception.php",
				"modules/sale/lib/delivery.php",
				"modules/sale/lib/paysystem.php",
				"modules/sale/lib/delivery/extraservicesmanager.php",
				"modules/sale/install/components/bitrix/sale.location.selector.system/templates/.default/lang/ru/result_modifier.php",
				"components/bitrix/sale.location.selector.system/templates/.default/lang/ru/result_modifier.php",
				"modules/sale/install/panel/sale/location_import.css",
				"modules/sale/mysql/order_props.php",
				"modules/sale/oracle/order_props.php",
				"modules/sale/lang/ru/lib/delivery/handler/automatic.php",
				"modules/sale/lang/ru/lib/delivery/handler/automatic_profile.php",
				"modules/sale/lang/ru/lib/delivery/handler/configurable.php",
				"modules/sale/lang/ru/lib/delivery/handler/group.php",
				"modules/sale/lib/delivery/handler/automatic.php",
				"modules/sale/lib/delivery/handler/automatic_profile.php",
				"modules/sale/lib/delivery/handler/configurable.php",
				"modules/sale/lib/delivery/handler/group.php",
				"modules/sale/lib/delivery/service.php",
				"modules/sale/lib/delivery/servicecreator.php",
				"modules/sale/lib/delivery/delivery.php",
				"modules/sale/lib/delivery/deliveryrestriction.php",
				"modules/sale/lib/delivery/deliveryservice.php",
				"modules/sale/lib/delivery/extraservices.php",
				"modules/sale/admin/delivery_service.php",
				"modules/sale/install/js/sale/admin.js",
				"modules/sale/lib/delivery/restriction.php",
				"modules/sale/lib/delivery/restrictiontable.php",
				"modules/sale/lib/delivery/servicetable.php",
				"modules/sale/lang/ru/lib/delivery/servicetable.php",
				"modules/sale/lib/delivery/location.php",
				"modules/sale/lib/internals/attributes.php",
				"modules/sale/lang/ru/lib/internals/orderprops_group.php",
				"modules/sale/lang/ru/lib/internals/orderprops_relation.php",
				"modules/sale/lib/internals/orderpropsgroup.php",
				"modules/sale/lib/internals/orderpropsrelation.php",
				"modules/sale/lib/internals/orderpropsvalue.php",
				"modules/sale/lib/helpers/datafield.php",
				"modules/sale/lib/helpers/admin/order_edit.php",
				"modules/sale/lib/statusgrouptask.php",
				"modules/sale/lib/statuslang.php",
				"modules/sale/lang/ru/lib/statuslang.php",
				"modules/sale/admin/order_info.php",
				"modules/sale/lang/ru/admin/order_info.php",
				"modules/sale/admin/order_delivery.php",
				"modules/sale/install/admin/sale_order_delivery.php",
				"admin/sale_order_delivery.php",
				"modules/sale/install/admin/sale_order_delivery_edit.php",
				"admin/sale_order_delivery_edit.php",
				"modules/sale/admin/order_props_edit (copy).php",
				"modules/sale/lib/orderprops.php",
				"modules/sale/install/js/sale/admin/event_manager.js",
				"modules/sale/install/js/sale/admin/css/order.css",
				"modules/sale/admin/business_value_type.php",
				"modules/sale/install/admin/sale_business_value_type.php",
				"admin/sale_business_value_type.php",
				"modules/sale/lib/delivery/restrictions/bystore.php",
				"modules/sale/lib/delivery/structures.php",
				"modules/sale/lib/orderproperties.php",
				"modules/sale/install/components/bitrix/sale.location.selector.system/templates/.default/lang/de/result_modifier.php",
				"components/bitrix/sale.location.selector.system/templates/.default/lang/de/result_modifier.php",
				"modules/sale/install/components/bitrix/sale.location.selector.system/templates/.default/lang/en/result_modifier.php",
				"components/bitrix/sale.location.selector.system/templates/.default/lang/en/result_modifier.php",
				"modules/sale/lib/location/import/import.php",
				"modules/sale/general/compatible.php",
				"modules/sale/lib/basketcompatibility.php",
				"modules/sale/lib/internals/compatible.php",
				"modules/sale/lib/internals/entitycompatibility.php",
				"modules/sale/lib/ordercompatibility.php",
				"modules/sale/lib/compatible/internals/compatible.php",
				"modules/sale/lib/internals/businessvalue_persontype.php",
				"modules/sale/lang/ru/lib/internals/orderprops_value.php",
				"modules/sale/lang/ru/lib/payment_system_webmoney.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/lang/de/result_modifier.php",
				"components/bitrix/sale.location.import/templates/.default/lang/de/result_modifier.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/lang/de/template.php",
				"components/bitrix/sale.location.import/templates/.default/lang/de/template.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/lang/en/result_modifier.php",
				"components/bitrix/sale.location.import/templates/.default/lang/en/result_modifier.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/lang/en/template.php",
				"components/bitrix/sale.location.import/templates/.default/lang/en/template.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/lang/ru/result_modifier.php",
				"components/bitrix/sale.location.import/templates/.default/lang/ru/result_modifier.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/lang/ru/template.php",
				"components/bitrix/sale.location.import/templates/.default/lang/ru/template.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/lang/ua/result_modifier.php",
				"components/bitrix/sale.location.import/templates/.default/lang/ua/result_modifier.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/lang/ua/template.php",
				"components/bitrix/sale.location.import/templates/.default/lang/ua/template.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/result_modifier.php",
				"components/bitrix/sale.location.import/templates/.default/result_modifier.php",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/script.js",
				"components/bitrix/sale.location.import/templates/.default/script.js",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/style.css",
				"components/bitrix/sale.location.import/templates/.default/style.css",
				"modules/sale/install/components/bitrix/sale.location.import/templates/.default/template.php",
				"components/bitrix/sale.location.import/templates/.default/template.php",
				"modules/sale/lang/de/lib/location/assert.php",
				"modules/sale/lang/de/lib/location/treeentity.php",
				"modules/sale/lang/en/lib/location/assert.php",
				"modules/sale/lang/en/lib/location/treeentity.php",
				"modules/sale/lang/ru/lib/location/assert.php",
				"modules/sale/lang/ru/lib/location/treeentity.php",
				"modules/sale/lib/location/assert.php",
				"modules/sale/lib/location/dbblockinserter.php",
				"modules/sale/lib/location/exception.php",
				"modules/sale/lib/location/import/csvreader.php",
				"modules/sale/lib/location/import/process.php",
				"modules/sale/lib/location/treeentity.php",
				"modules/sale/lib/calculator.php",
				"modules/sale/install/templates/lang/de/sale/.description.php",
				"modules/sale/install/templates/lang/de/sale/sale_affiliate/.description.php",
				"modules/sale/install/templates/lang/de/sale/sale_affiliate/affiliate.php",
				"modules/sale/install/templates/lang/de/sale/sale_affiliate/plans.php",
				"modules/sale/install/templates/lang/de/sale/sale_affiliate/register.php",
				"modules/sale/install/templates/lang/de/sale/sale_affiliate/shop.php",
				"modules/sale/install/templates/lang/de/sale/sale_affiliate/tech.php",
				"modules/sale/install/templates/lang/de/sale/sale_basket/.description.php",
				"modules/sale/install/templates/lang/de/sale/sale_basket/basket.php",
				"modules/sale/install/templates/lang/de/sale/sale_basket/basket_line.php",
				"modules/sale/install/templates/lang/de/sale/sale_basket/basket_small.php",
				"modules/sale/install/templates/lang/de/sale/sale_order/.description.php",
				"modules/sale/install/templates/lang/de/sale/sale_order/order_1.php",
				"modules/sale/install/templates/lang/de/sale/sale_order/order_2.php",
				"modules/sale/install/templates/lang/de/sale/sale_order/order_full.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/.description.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/account.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/cc_detail.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/cc_list.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/order_cancel.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/order_detail.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/order_list.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/order_list_t.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/order_table.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/profile_detail.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/profile_list.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/subscribe_cancel.php",
				"modules/sale/install/templates/lang/de/sale/sale_personal/subscribe_list.php",
				"modules/sale/install/templates/lang/en/sale/.description.php",
				"modules/sale/install/templates/lang/en/sale/sale_affiliate/.description.php",
				"modules/sale/install/templates/lang/en/sale/sale_affiliate/affiliate.php",
				"modules/sale/install/templates/lang/en/sale/sale_affiliate/plans.php",
				"modules/sale/install/templates/lang/en/sale/sale_affiliate/register.php",
				"modules/sale/install/templates/lang/en/sale/sale_affiliate/shop.php",
				"modules/sale/install/templates/lang/en/sale/sale_affiliate/tech.php",
				"modules/sale/install/templates/lang/en/sale/sale_basket/.description.php",
				"modules/sale/install/templates/lang/en/sale/sale_basket/basket.php",
				"modules/sale/install/templates/lang/en/sale/sale_basket/basket_line.php",
				"modules/sale/install/templates/lang/en/sale/sale_basket/basket_small.php",
				"modules/sale/install/templates/lang/en/sale/sale_order/.description.php",
				"modules/sale/install/templates/lang/en/sale/sale_order/order_1.php",
				"modules/sale/install/templates/lang/en/sale/sale_order/order_2.php",
				"modules/sale/install/templates/lang/en/sale/sale_order/order_full.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/.description.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/account.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/cc_detail.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/cc_list.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/order_cancel.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/order_detail.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/order_list.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/order_list_t.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/order_table.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/profile_detail.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/profile_list.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/subscribe_cancel.php",
				"modules/sale/install/templates/lang/en/sale/sale_personal/subscribe_list.php",
				"modules/sale/install/templates/lang/ru/sale/.description.php",
				"modules/sale/install/templates/lang/ru/sale/sale_affiliate/.description.php",
				"modules/sale/install/templates/lang/ru/sale/sale_affiliate/affiliate.php",
				"modules/sale/install/templates/lang/ru/sale/sale_affiliate/plans.php",
				"modules/sale/install/templates/lang/ru/sale/sale_affiliate/register.php",
				"modules/sale/install/templates/lang/ru/sale/sale_affiliate/shop.php",
				"modules/sale/install/templates/lang/ru/sale/sale_affiliate/tech.php",
				"modules/sale/install/templates/lang/ru/sale/sale_basket/.description.php",
				"modules/sale/install/templates/lang/ru/sale/sale_basket/basket.php",
				"modules/sale/install/templates/lang/ru/sale/sale_basket/basket_line.php",
				"modules/sale/install/templates/lang/ru/sale/sale_basket/basket_small.php",
				"modules/sale/install/templates/lang/ru/sale/sale_order/.description.php",
				"modules/sale/install/templates/lang/ru/sale/sale_order/order_1.php",
				"modules/sale/install/templates/lang/ru/sale/sale_order/order_2.php",
				"modules/sale/install/templates/lang/ru/sale/sale_order/order_full.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/.description.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/account.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/cc_detail.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/cc_list.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/order_cancel.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/order_detail.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/order_list.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/order_list_t.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/order_table.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/profile_detail.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/profile_list.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/subscribe_cancel.php",
				"modules/sale/install/templates/lang/ru/sale/sale_personal/subscribe_list.php",
				"modules/sale/install/templates/lang/ua/sale/.description.php",
				"modules/sale/install/templates/lang/ua/sale/sale_affiliate/.description.php",
				"modules/sale/install/templates/lang/ua/sale/sale_affiliate/affiliate.php",
				"modules/sale/install/templates/lang/ua/sale/sale_affiliate/plans.php",
				"modules/sale/install/templates/lang/ua/sale/sale_affiliate/register.php",
				"modules/sale/install/templates/lang/ua/sale/sale_affiliate/shop.php",
				"modules/sale/install/templates/lang/ua/sale/sale_affiliate/tech.php",
				"modules/sale/install/templates/lang/ua/sale/sale_basket/.description.php",
				"modules/sale/install/templates/lang/ua/sale/sale_basket/basket.php",
				"modules/sale/install/templates/lang/ua/sale/sale_basket/basket_line.php",
				"modules/sale/install/templates/lang/ua/sale/sale_basket/basket_small.php",
				"modules/sale/install/templates/lang/ua/sale/sale_order/.description.php",
				"modules/sale/install/templates/lang/ua/sale/sale_order/order_1.php",
				"modules/sale/install/templates/lang/ua/sale/sale_order/order_2.php",
				"modules/sale/install/templates/lang/ua/sale/sale_order/order_full.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/.description.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/account.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/cc_detail.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/cc_list.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/order_cancel.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/order_detail.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/order_list.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/order_list_t.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/order_table.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/profile_detail.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/profile_list.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/subscribe_cancel.php",
				"modules/sale/install/templates/lang/ua/sale/sale_personal/subscribe_list.php",
				"modules/sale/install/templates/sale/.description.php",
				"modules/sale/install/templates/sale/sale_affiliate/.description.php",
				"modules/sale/install/templates/sale/sale_affiliate/affiliate.php",
				"modules/sale/install/templates/sale/sale_affiliate/plans.php",
				"modules/sale/install/templates/sale/sale_affiliate/register.php",
				"modules/sale/install/templates/sale/sale_affiliate/shop.php",
				"modules/sale/install/templates/sale/sale_affiliate/tech.php",
				"modules/sale/install/templates/sale/sale_basket/.description.php",
				"modules/sale/install/templates/sale/sale_basket/basket.php",
				"modules/sale/install/templates/sale/sale_basket/basket_line.php",
				"modules/sale/install/templates/sale/sale_basket/basket_small.php",
				"modules/sale/install/templates/sale/sale_basket/images/icon_basket_white.gif",
				"modules/sale/install/templates/sale/sale_order/.description.php",
				"modules/sale/install/templates/sale/sale_order/order_1.php",
				"modules/sale/install/templates/sale/sale_order/order_2.php",
				"modules/sale/install/templates/sale/sale_order/order_full.php",
				"modules/sale/install/templates/sale/sale_order/payment.php",
				"modules/sale/install/templates/sale/sale_personal/.description.php",
				"modules/sale/install/templates/sale/sale_personal/account.php",
				"modules/sale/install/templates/sale/sale_personal/cc_detail.php",
				"modules/sale/install/templates/sale/sale_personal/cc_list.php",
				"modules/sale/install/templates/sale/sale_personal/order_cancel.php",
				"modules/sale/install/templates/sale/sale_personal/order_detail.php",
				"modules/sale/install/templates/sale/sale_personal/order_detail_old.php",
				"modules/sale/install/templates/sale/sale_personal/order_list.php",
				"modules/sale/install/templates/sale/sale_personal/order_list_t.php",
				"modules/sale/install/templates/sale/sale_personal/order_table.php",
				"modules/sale/install/templates/sale/sale_personal/profile_detail.php",
				"modules/sale/install/templates/sale/sale_personal/profile_list.php",
				"modules/sale/install/templates/sale/sale_personal/subscribe_cancel.php",
				"modules/sale/install/templates/sale/sale_personal/subscribe_list.php",
				"modules/sale/install/templates/sale/sale_pieces/.description.php",
				"modules/sale/install/templates/sale/sale_pieces/basket.php",
				"modules/sale/install/templates/sale/sale_pieces/basket_line.php",
				"modules/sale/install/templates/sale/sale_pieces/basket_small.php",
				"modules/sale/install/templates/sale/sale_pieces/images/icon_basket_white.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/basket.php",
				"modules/sale/install/templates/sale/sale_tmpl_1/images/1c.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/images/1g.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/images/2c.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/images/2g.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/images/3c.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/images/3g.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/images/4c.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/images/4g.gif",
				"modules/sale/install/templates/sale/sale_tmpl_1/order.php",
				"modules/sale/install/templates/sale/sale_tmpl_1/payment.php",
				"modules/sale/install/templates/sale/sale_tmpl_1/pers_order.php",
				"modules/sale/install/templates/sale/sale_tmpl_1/pers_order_cancel.php",
				"modules/sale/install/templates/sale/sale_tmpl_1/pers_order_detail.php",
				"modules/sale/install/templates/sale/sale_tmpl_1/profile_detail.php",
				"modules/sale/install/templates/sale/sale_tmpl_1/profiles.php",
				"modules/sale/install/templates/sale/sale_tmpl_2/basket.php",
				"modules/sale/install/templates/sale/sale_tmpl_2/order.php",
				"modules/sale/install/templates/sale/sale_tmpl_2/payment.php",
				"modules/sale/install/templates/sale/sale_tmpl_2/pers_order.php",
				"modules/sale/install/templates/sale/sale_tmpl_2/pers_order_cancel.php",
				"modules/sale/install/templates/sale/sale_tmpl_2/pers_order_detail.php",
				"modules/sale/install/templates/sale/sale_tmpl_2/profile_detail.php",
				"modules/sale/install/templates/sale/sale_tmpl_2/profiles.php",
				"modules/sale/lib/delivery/extra_services/test.php",
				"modules/sale/lang/ru/lib/delivery/services/manager.php",
				"modules/sale/payment/betaling/.description.php",
				"modules/sale/payment/betaling/payment.php",
				"modules/sale/payment/payflow_pro/.description.php",
				"modules/sale/payment/payflow_pro/action.php",
				"modules/sale/payment/payflow_pro/common.php",
				"modules/sale/payment/payflow_pro/payment.php",
				"modules/sale/payment/payflow_pro/pre_payment.php",
				"modules/sale/admin/order_props_edit222222.php",
				"modules/sale/lib/delivery/tests/create_order.php",
				"modules/sale/lib/delivery/tests/deliveries_list.php",
				"modules/sale/lib/delivery/tests/delivery_get_price.php",
				"modules/sale/lib/delivery/tests/delivery_get_price_extra.php",
				"modules/sale/lib/delivery/tests/index.php",
				"modules/sale/lib/company.php",
				"modules/sale/lib/deliveryhandler.php",
				"modules/sale/lib/goodssection.php",
				"modules/sale/lib/orderprocessing.php",
				"modules/sale/lib/orderprocessor.php",
				"modules/sale/lib/product.php",
				"modules/sale/lib/product2product.php",
				"modules/sale/lib/reservation.php",
				"modules/sale/lib/reservationbase.php",
				"modules/sale/lib/section.php",
				"modules/sale/lib/sitecurrency.php",
				"modules/sale/lib/storeproduct.php",
				"modules/sale/lib/tradingplatform.php",
				"modules/sale/install/components/bitrix/sale.discount.coupon.mail/lang/ua/.description.php",
				"components/bitrix/sale.discount.coupon.mail/lang/ua/.description.php",
				"modules/sale/install/components/bitrix/sale.discount.coupon.mail/lang/ua/.parameters.php",
				"components/bitrix/sale.discount.coupon.mail/lang/ua/.parameters.php",
				"modules/sale/install/components/bitrix/sale.discount.coupon.mail/lang/ua/class.php",
				"components/bitrix/sale.discount.coupon.mail/lang/ua/class.php",
				
				"modules/sale/lang/ru/lib/conversion/rate.php",
				"modules/sale/lib/conversion/cartrate.php",
				"modules/sale/lib/conversion/orderrate.php",
				"modules/sale/lib/conversion/paymentrate.php",
				"modules/sale/lib/conversion/rate.php",

				"modules/sale/lang/de/lib/conversion/handlers.php",
				"modules/sale/lang/de/lib/conversion/rate.php",
				"modules/sale/lang/en/lib/conversion/handlers.php",
				"modules/sale/lang/en/lib/conversion/rate.php",
				"modules/sale/lang/ru/lib/conversion/handlers.php",
				"modules/sale/lib/conversion/handlers.php",

				"modules/sale/lib/eventspool.php",
				"modules/sale/lib/location/util/exception.php"
				
			);
			
			foreach($arToDelete as $file)
				DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/".$file);
			
			RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "sale", "CSalePaySystemAction", 'OnEventLogGetAuditTypes');
			RegisterModuleDependences("sender", "OnConnectorList", "sale", "\\Bitrix\\Sale\\SenderEventHandler", "onConnectorListBuyer");
			RegisterModuleDependences("sender", "OnConnectorList", "sale", "\\Bitrix\\Sale\\SenderEventHandler", "onConnectorListBuyer");
			RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "sale", "CSalePaySystemAction", 'OnEventLogGetAuditTypes');
			RegisterModuleDependences("sender", "OnTriggerList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onTriggerList");
			RegisterModuleDependences("sender", "OnPresetMailingList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onPresetMailingList");
			RegisterModuleDependences("sender", "OnPresetTemplateList", "sale", "\\Bitrix\\Sale\\Sender\\EventHandler", "onPresetTemplateList");

			RegisterModuleDependences('conversion', 'OnGetRateClasses' , 'sale', '\Bitrix\Sale\Conversion\Handlers', 'OnGetRateClasses' );
			RegisterModuleDependences('sale', 'OnBeforeBasketAdd', 'sale', '\Bitrix\Sale\Conversion\Handlers', 'OnBeforeBasketAdd');
			RegisterModuleDependences('sale', 'OnBasketAdd', 'sale', '\Bitrix\Sale\Conversion\Handlers', 'OnBasketAdd');
			RegisterModuleDependences('sale', 'OnOrderAdd', 'sale', '\Bitrix\Sale\Conversion\Handlers', 'OnOrderAdd');
			RegisterModuleDependences('sale', 'OnSalePayOrder', 'sale', '\Bitrix\Sale\Conversion\Handlers', 'OnSalePayOrder');


			RegisterModuleDependences('conversion', 'OnGetCounterTypes'    , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGetCounterTypes'    );
			RegisterModuleDependences('conversion', 'OnGetRateTypes'       , 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGetRateTypes'       );
			RegisterModuleDependences('conversion', 'OnGenerateInitialData', 'sale', '\Bitrix\Sale\Internals\ConversionHandlers', 'onGenerateInitialData');

			RegisterModuleDependences("perfmon", "OnGetTableSchema", "sale", "sale", "OnGetTableSchema");
			

			$eventManager = \Bitrix\Main\EventManager::getInstance();

			$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderPaid', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSalePayOrder');
			$eventManager->unRegisterEventHandler('sale', 'OnSaleBeforeOrderDelete', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onBeforeOrderDelete');
			$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderDeleted', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderDelete');
			$eventManager->unRegisterEventHandler('sale', 'OnSaleShipmentDelivery', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleDeliveryOrder');
			
			$eventManager->unRegisterEventHandler('sale', 'OnSaleBeforeOrderCanceled', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleBeforeCancelOrder');
			$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderCanceled', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleCancelOrder');
			$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderPaidSendMail', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleOrderPaidSendMail');
			$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderCancelSendEmail', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleOrderCancelSendEmail');

			$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderSave');

			$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderNewSendEmail');

			$eventManager->unRegisterEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderAdd');
			

			$eventManager->registerEventHandler('sale', 'OnSaleOrderPaid', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSalePayOrder');
			$eventManager->registerEventHandler('sale', 'OnSaleBeforeOrderDelete', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onBeforeOrderDelete');
			$eventManager->registerEventHandler('sale', 'OnSaleOrderDeleted', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderDelete');
			$eventManager->registerEventHandler('sale', 'OnSaleShipmentDelivery', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleDeliveryOrder');
			
			$eventManager->registerEventHandler('sale', 'OnSaleBeforeOrderCanceled', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleBeforeCancelOrder');
			$eventManager->registerEventHandler('sale', 'OnSaleOrderCanceled', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleCancelOrder');
			$eventManager->registerEventHandler('sale', 'OnSaleOrderPaidSendMail', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleOrderPaidSendMail');
			$eventManager->registerEventHandler('sale', 'OnSaleOrderCancelSendEmail', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onSaleOrderCancelSendEmail');
			
			$eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderAdd');

			$eventManager->registerEventHandler('sale', 'OnSaleOrderSaved', 'sale', '\Bitrix\Sale\Compatible\EventCompatibility', 'onOrderSave');

			COption::SetOptionString("sale", "expiration_processing_events", 'Y');

			COption::SetOptionInt("sale", "format_quantity", 2);
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSERT_PAYMENT');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSTALLER');
				$result['ERROR'] = true;
				$message .= "<br>".$error;	
				
				\CEventLog::Add(array(
					"SEVERITY" => "ERROR",
					"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "-",
					"DESCRIPTION" => 'COPY FILES ERROR',
				));
			}
			
			$result['DATA'] = $message;
			break;
		case 16:
			$start = microtime(true);

			$res = null;
			if ($DB->type == "MYSQL")
				$res = $DB->Query('SELECT id FROM b_sale_order_payment LIMIT 1', false);
			elseif ($DB->type == "MSSQL")
				$res = $DB->Query('SELECT TOP(1) id FROM b_sale_order_payment', false);
			elseif ($DB->type == "ORACLE")
				$res = $DB->Query('SELECT id FROM b_sale_order_payment WHERE ROWNUM=1', false);

			if ($res && !$res->Fetch())
			{
				if (
					!$DB->Query("
						INSERT INTO b_sale_order_payment
							(ORDER_ID, PAID, DATE_PAID, PS_STATUS, PS_STATUS_CODE, PS_STATUS_DESCRIPTION, PS_STATUS_MESSAGE, PS_SUM, PS_CURRENCY, PS_RESPONSE_DATE, SUM, CURRENCY, PAY_SYSTEM_ID, DATE_BILL, PAY_VOUCHER_NUM, PAY_VOUCHER_DATE, DATE_PAY_BEFORE, PAY_SYSTEM_NAME, RESPONSIBLE_ID)
					SELECT
						o.ID, o.PAYED, o.DATE_PAYED, o.PS_STATUS, o.PS_STATUS_CODE, o.PS_STATUS_DESCRIPTION, o.PS_STATUS_MESSAGE, o.PS_SUM, o.PS_CURRENCY, o.PS_RESPONSE_DATE, o.PRICE,	o.CURRENCY, o.PAY_SYSTEM_ID, o.DATE_INSERT, o.PAY_VOUCHER_NUM, o.PAY_VOUCHER_DATE, o.DATE_PAY_BEFORE, b_sale_pay_system.NAME, o.CREATED_BY
					FROM b_sale_order o
					INNER JOIN b_sale_pay_system ON b_sale_pay_system.ID=o.PAY_SYSTEM_ID
					WHERE o.PAY_SYSTEM_ID IS NOT NULL", true
					)
				)
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error
					));
				}
			}
			
			$end = microtime(true);			
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'insert into b_sale_order_payment = '.($end-$start)."\n", FILE_APPEND);
			
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSERT_SHIPMENT');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSERT_PAYMENT');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 17:
			$id = \Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId();
			
			if ($id <= 0)
			{
				$fields["NAME"] = Loc::getMessage('SALE_CONVERTER_EMPTY_DELIVERY_SERVICE');
				$fields["CLASS_NAME"] = '\Bitrix\Sale\Delivery\Services\EmptyDeliveryService';
				$fields["PARENT_ID"] = 0;
				$fields["CURRENCY"] = 'RUB';
				$fields["ACTIVE"] = "Y";
				$fields["CONFIG"] = array(
					'MAIN' => array(
						'CURRENCY' => 'RUB',
						'PRICE' => 0,
						'PERIOD' => array(
							'FROM' => 0,
							'TO' => 0,
							'TYPE' => 'D'
						)
					)
				);
				$fields["SORT"] = 100;

				$res = \Bitrix\Sale\Delivery\Services\Table::add($fields);
				$id = $res->getId();

				$fields = array(
					'SORT' => 100,
					'DELIVERY_ID' => $id,
					'PARAMS' => array(
						'PUBLIC_SHOW' => 'N'
					)
				);
				$rstrPM = new \Bitrix\Sale\Delivery\Restrictions\ByPublicMode();
				$rstrPM->save($fields);
			}
				
			$start = microtime(true);

			$res = null;
			$orderDeliveryId = 'o.DELIVERY_ID';
			if ($DB->type == "MYSQL")
			{
				$res = $DB->Query('SELECT id FROM b_sale_order_delivery LIMIT 1', false);
			}
			elseif ($DB->type == "MSSQL")
			{
				$res = $DB->Query('SELECT TOP(1) id FROM b_sale_order_delivery', false);
			}
			elseif ($DB->type == "ORACLE")
			{
				$res = $DB->Query('SELECT id FROM b_sale_order_delivery WHERE ROWNUM=1', false);
				$orderDeliveryId = 'TO_NUMBER('.$orderDeliveryId.')';
			}

			if ($res && !$res->Fetch())
			{
				if (
					!$DB->Query("INSERT INTO b_sale_order_delivery (ORDER_ID, BASE_PRICE_DELIVERY, PRICE_DELIVERY, ALLOW_DELIVERY, DATE_ALLOW_DELIVERY, EMP_ALLOW_DELIVERY_ID, DEDUCTED, DATE_DEDUCTED, EMP_DEDUCTED_ID, REASON_UNDO_DEDUCTED, RESERVED, DELIVERY_ID, DELIVERY_DOC_NUM, DELIVERY_DOC_DATE, TRACKING_NUMBER, CANCELED, DATE_CANCELED, EMP_CANCELED_ID, REASON_CANCELED, MARKED, DATE_MARKED, EMP_MARKED_ID, DATE_INSERT, CURRENCY, SYSTEM, RESPONSIBLE_ID, STATUS_ID, DELIVERY_NAME)
						SELECT
							o.ID, o.PRICE_DELIVERY, o.PRICE_DELIVERY, o.ALLOW_DELIVERY, o.DATE_ALLOW_DELIVERY, o.EMP_ALLOW_DELIVERY_ID, o.DEDUCTED, o.DATE_DEDUCTED, o.EMP_DEDUCTED_ID, o.REASON_UNDO_DEDUCTED, o.RESERVED, CASE WHEN o.DELIVERY_ID IS NULL THEN ".$id." ELSE ".$orderDeliveryId." END, o.DELIVERY_DOC_NUM, o.DELIVERY_DOC_DATE, o.TRACKING_NUMBER, o.CANCELED, o.DATE_CANCELED, o.EMP_CANCELED_ID, o.REASON_CANCELED, o.MARKED, o.DATE_MARKED, o.EMP_MARKED_ID, o.DATE_INSERT, o.CURRENCY, 'N', o.RESPONSIBLE_ID, CASE o.DEDUCTED WHEN 'Y' THEN 'DF' ELSE 'DN' END, d.NAME
						FROM b_sale_order o
						LEFT JOIN b_sale_delivery_srv d ON d.ID = (CASE WHEN o.DELIVERY_ID IS NULL THEN ".$id." ELSE ".$orderDeliveryId." END)", true
					)
				)
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error
					));
				}
			}
			
			$end = microtime(true);			
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'insert into b_sale_order_delivery = '.($end-$start)."\n", FILE_APPEND);
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSERT_SHIPMENT_BASKET');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSERT_SHIPMENT');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 18:
			$start = microtime(true);

			$res = null;
			if ($DB->type == "MYSQL")
				$res = $DB->Query('SELECT id FROM b_sale_order_dlv_basket LIMIT 1', false);
			elseif ($DB->type == "MSSQL")
				$res = $DB->Query('SELECT TOP(1) id FROM B_SALE_ORDER_DLV_BASKET', false);
			elseif ($DB->type == "ORACLE")
				$res = $DB->Query('SELECT id FROM B_SALE_ORDER_DLV_BASKET WHERE ROWNUM=1', false);

			if ($res && !$res->Fetch())
			{
				if (
					!$DB->Query('
						INSERT INTO b_sale_order_dlv_basket (ORDER_DELIVERY_ID, BASKET_ID, QUANTITY, DATE_INSERT, RESERVED_QUANTITY)
						SELECT
							b_sale_order_delivery.ID, b.ID, b.QUANTITY, b.DATE_INSERT, CASE WHEN b.RESERVE_QUANTITY IS NULL THEN 0 ELSE b.RESERVE_QUANTITY END
						FROM b_sale_basket b
						INNER JOIN b_sale_order_delivery ON b_sale_order_delivery.ORDER_ID=b.ORDER_ID', true
					)
				)
				{
					$error .= "<br>".$DB->GetErrorMessage();
					
					\CEventLog::Add(array(
						"SEVERITY" => "ERROR",
						"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
						"MODULE_ID" => "sale",
						"ITEM_ID" => "-",
						"DESCRIPTION" => $error
					));
				}
			}
			
			$end = microtime(true);			
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'insert into b_sale_order_dlv_basket = '.($end-$start)."\n", FILE_APPEND);
			
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_UPDATE_SHIPMENT_BASKET_BARCODE');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSERT_SHIPMENT_BASKET');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 19:
			$start = microtime(true);
			if ($DB->type == 'MYSQL')
				$select = 'SELECT b_sale_order_dlv_basket.ID FROM b_sale_order_dlv_basket	WHERE b_sale_store_barcode.BASKET_ID = b_sale_order_dlv_basket.BASKET_ID LIMIT 1';
			elseif ($DB->type == 'MSSQL')
				$select = 'SELECT TOP(1) B_SALE_ORDER_DLV_BASKET.ID FROM B_SALE_ORDER_DLV_BASKET	WHERE b_sale_store_barcode.BASKET_ID = B_SALE_ORDER_DLV_BASKET.BASKET_ID';
			elseif ($DB->type == "ORACLE")
				$select = 'SELECT B_SALE_ORDER_DLV_BASKET.ID FROM B_SALE_ORDER_DLV_BASKET	WHERE b_sale_store_barcode.BASKET_ID = B_SALE_ORDER_DLV_BASKET.BASKET_ID AND ROWNUM=1';

			if (
				!$DB->Query('
					UPDATE b_sale_store_barcode SET
						b_sale_store_barcode.ORDER_DELIVERY_BASKET_ID = (
							'.$select.'
						)
					WHERE 1=1', true
				)
			)
			{
				$error .= "<br>".$DB->GetErrorMessage();
				
				\CEventLog::Add(array(
					"SEVERITY" => "ERROR",
					"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "-",
					"DESCRIPTION" => $error
				));
			}
			
			$end = microtime(true);			
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'update b_sale_store_barcode = '.($end-$start)."\n", FILE_APPEND);
						
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_UPDATE_ORDER_PAYMENT');
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_INSERT_SHIPMENT_BASKET_BARCODE');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
		case 20:
			$start = microtime(true);
			if (
				!$DB->Query("
					UPDATE 
						b_sale_order 
					SET
						b_sale_order.SUM_PAID = b_sale_order.PRICE 
					WHERE b_sale_order.PAYED = 'Y'", true
				)
			)
			{
				$error .= "<br>".$DB->GetErrorMessage();
				
				\CEventLog::Add(array(
					"SEVERITY" => "ERROR",
					"AUDIT_TYPE_ID" => "SALE_CONVERTER_ERROR",
					"MODULE_ID" => "sale",
					"ITEM_ID" => "-",
					"DESCRIPTION" => $error
				));
			}

			$end = microtime(true);			
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale_convert.txt', 'update b_sale_order = '.($end-$start)."\n", FILE_APPEND);

			if (empty($error))
			{
				$result['NEXT_STEP'] = ++$ajax_step;
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_UPDATE_REPORT');
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_UPDATE_ORDER_PAYMENT');
				$result['ERROR'] = true;
				$message .= "<br>".$error;
			}
			
			$result['DATA'] = $message;
			break;
			
		case 21:
			if($DB->TableExists('b_report'))
			{
				$updatedReports = array();
				$dbReports = $DB->Query(
					"SELECT ID, OWNER_ID, SETTINGS, MARK_DEFAULT ".
					"FROM b_report ".
					"WHERE OWNER_ID IN ('sale_SaleBasket', 'sale_SaleOrder', 'sale_SaleProduct', 'sale_User')"
				);
				$replaces = array(
					'sale_SaleOrder' => array(
						'entity' => 'Bitrix\\Sale\\Internals\\Order',
						'pattern' => array(
							'/^DATE_INS$/',
							'/^BUYER$/',
							'/^BUYER\./'
						),
						'replacement' => array(
							'DATE_INSERT_FORMAT',
							'USER',
							'USER.'
						)
					),
					'sale_User' => array(
						'entity' => 'Bitrix\\Main\\User',
						'pattern' => array(
							'/^Bitrix\\\Sale\\\Order$/',
							'/^Bitrix\\\Sale\\\Order:/',
							'/^Bitrix\\\Sale\\\Internals\\\Order:BUYER$/',
							'/^Bitrix\\\Sale\\\Internals\\\Order:BUYER\./',
							'/^Bitrix\\\Sale\\\Internals\\\Order:USER\.DATE_INS$/',
						),
						'replacement' => array(
							'Bitrix\\Sale\\Internals\\Order',
							'Bitrix\\Sale\\Internals\\Order:',
							'Bitrix\\Sale\\Internals\\Order:USER',
							'Bitrix\\Sale\\Internals\\Order:USER.',
							'Bitrix\\Sale\\Internals\\Order:USER.DATE_INSERT_FORMAT'
						)
					),
					'sale_SaleBasket' => array(
						'entity' => 'Bitrix\\Sale\\Internals\\Basket',
						'pattern' => array(
							'/^ORDER\.DATE_INS$/'
						),
						'replacement' => array(
							'ORDER.DATE_INSERT_FORMAT'
						)
					)
				);
				while ($report = $dbReports->Fetch())
				{
					$reportID = $report['ID'];
					$reportSettings = unserialize($report['SETTINGS']);
					$reportOwner = $report['OWNER_ID'];
					$reportMark = intval($report['MARK_DEFAULT']);

					if (is_array($reportSettings))
					{
						if ($reportOwner === 'sale_SaleOrder' || $reportOwner === 'sale_User' || $reportOwner === 'sale_SaleBasket')
						{
							$reportSettings['entity'] = $replaces[$reportOwner]['entity'];

							if (is_array($reportSettings['select']))
							{
								foreach ($reportSettings['select'] as $k => $v)
								{
									if (is_array($v) && isset($v['name']) && !empty($v['name']))
									{
										$reportSettings['select'][$k]['name'] = preg_replace(
											$replaces[$reportOwner]['pattern'],
											$replaces[$reportOwner]['replacement'],
											$v['name']
										);
									}
								}
							}

							if (is_array($reportSettings['filter']))
							{
								foreach ($reportSettings['filter'] as $fKey => $fField)
								{
									if (is_array($fField))
									{
										foreach ($fField as $k => $v)
										{
											if (is_array($v) && isset($v['type']) && $v['type'] === 'field'
												&& isset($v['name']) && !empty($v['name']))
											{
												$reportSettings['filter'][$fKey][$k]['name'] = preg_replace(
													$replaces[$reportOwner]['pattern'],
													$replaces[$reportOwner]['replacement'],
													$v['name']
												);
											}
										}
									}
								}
							}

							$updatedReports[$reportID] = serialize($reportSettings);
						}
					}
				}

				if(!empty($updatedReports))
				{
					foreach ($updatedReports as $reportID => &$reportSettings)
					{
						$reportID = intval($reportID);
						$expression = $DB->PrepareUpdate('b_report', array('SETTINGS' => $reportSettings), 'report');
						$sql = "UPDATE b_report SET {$expression} WHERE ID = {$reportID}";

						$DB->QueryBind(
							$sql,
							array('SETTINGS' => $reportSettings),
							false,
							"File: ".__FILE__."<br>Line: ".__LINE__
						);

						$dbRes = CUserOptions::GetList(
							array("ID" => "ASC"),
							array('CATEGORY' => 'report', 'NAME_MASK' => 'view_params_'.$reportID.'_')
						);
						if (is_object($dbRes))
						{
							while ($row = $dbRes->fetch())
							{
								if (strpos($row['NAME'], 'view_params_'.$reportID.'_') === 0)
									CUserOptions::DeleteOptionsByName('report', $row['NAME']);
							}
						}
						unset($dbRes);
					}
				}
				unset($reportSettings);
			}
			
			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_UPDATE_BASKET');
				$type = 'PROCESS';
				// SITE_START
				COption::SetOptionString("main", "site_stopped", "N");
				$result['NEXT_STEP'] = ++$ajax_step;
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_UPDATE_REPORT');
				$result['ERROR'] = true;
				$type = 'ERROR';
				$message .= "<br>".$error;
			}

			$result['DATA'] = $message;

			Bitrix\Main\Config\Option::set("main", "~sale_converted_15", 'Y');
			CAdminNotify::DeleteByTag('SALE_ORDER_MASTER_CONVERT');

			break;
		case 22:
			if ($DB->TableExists('b_sale_basket'))
			{
				if ($DB->type == 'MYSQL')
				{
					if (!$DB->Query('ALTER TABLE b_sale_basket CHANGE PRICE PRICE DECIMAL(18,4) not null', true))
						$error .= $DB->GetErrorMessage();
					if (!$DB->Query('ALTER TABLE b_sale_basket CHANGE DISCOUNT_PRICE DISCOUNT_PRICE DECIMAL(18,4) not null', true))
						$error .= $DB->GetErrorMessage();
					if (!$DB->Query('ALTER TABLE b_sale_basket CHANGE QUANTITY QUANTITY DECIMAL(18,4) not null', true))
						$error .= $DB->GetErrorMessage();

					if (!$DB->Query("SELECT BASE_PRICE FROM b_sale_basket WHERE 1=0", true))
					{
						if(!$DB->Query("ALTER TABLE b_sale_basket ADD BASE_PRICE decimal(18, 4) null"))
							$error .= $DB->GetErrorMessage();
					}
					if (!$DB->Query("SELECT VAT_INCLUDED FROM b_sale_basket WHERE 1=0", true))
					{
						if (!$DB->Query("ALTER TABLE b_sale_basket ADD VAT_INCLUDED char(1) not null default 'Y'"))
							$error .= $DB->GetErrorMessage();
					} 

				}
				elseif ($DB->type == 'MSSQL')
				{
					if (!$DB->Query("ALTER TABLE B_SALE_BASKET ALTER COLUMN PRICE DECIMAL(18,4) NOT NULL", true))
						$error .= $DB->GetErrorMessage();
					if (!$DB->Query("ALTER TABLE B_SALE_BASKET ALTER COLUMN DISCOUNT_PRICE DECIMAL(18,4) NOT NULL", true))
						$error .= $DB->GetErrorMessage();
					if (!$DB->Query("ALTER TABLE B_SALE_BASKET ALTER COLUMN QUANTITY DECIMAL(18,4) NOT NULL", true))
						$error .= $DB->GetErrorMessage();

					if (!$DB->Query("SELECT BASE_PRICE FROM b_sale_basket WHERE 1=0", true))
					{
						if(!$DB->Query("ALTER TABLE B_SALE_BASKET ADD BASE_PRICE decimal(18, 4) null"))
							$error .= $DB->GetErrorMessage();
					}

					if (!$DB->Query("SELECT VAT_INCLUDED FROM b_sale_basket WHERE 1=0", true))
					{
						if (!$DB->Query("ALTER TABLE B_SALE_BASKET ADD VAT_INCLUDED char(1) NOT NULL CONSTRAINT DF_B_SALE_BASKET_VAT_INCLUDED DEFAULT 'Y'"))
						{
							$error .= $DB->GetErrorMessage();
						}
						
					}
					
				}
				elseif ($DB->type == "ORACLE")
				{
					if (!$DB->Query("ALTER TABLE B_SALE_BASKET MODIFY PRICE NUMBER(20,4)", true))
						$error .= $DB->GetErrorMessage();
					if (!$DB->Query("ALTER TABLE B_SALE_BASKET MODIFY DISCOUNT_PRICE NUMBER(20,4)", true))
						$error .= $DB->GetErrorMessage();
					if (!$DB->Query("ALTER TABLE B_SALE_BASKET MODIFY QUANTITY NUMBER(24,4)", true))
						$error .= $DB->GetErrorMessage();

					if (!$DB->Query("SELECT BASE_PRICE FROM b_sale_basket WHERE 1=0", true))
					{
						if(!$DB->Query("ALTER TABLE B_SALE_BASKET ADD (BASE_PRICE NUMBER(18, 4) null)"))
							$error .= $DB->GetErrorMessage();
					}
					if (!$DB->Query("SELECT VAT_INCLUDED FROM b_sale_basket WHERE 1=0", true))
					{
						if (!$DB->Query("ALTER TABLE B_SALE_BASKET ADD (VAT_INCLUDED char(1 CHAR) DEFAULT 'Y' NOT NULL)"))
							$error .= $DB->GetErrorMessage();
					}
		
					$dbNextVal = $DB->Query("SELECT SQ_SALE_BASKET.NEXTVAL FROM DUAL", true);
					if ($dbNextVal)
					{
						$data = $dbNextVal->Fetch();
						$id = intval($data["NEXTVAL"]);

						$DB->Query('DROP SEQUENCE SQ_SALE_BASKET');

						$DB->Query('CREATE SEQUENCE SQ_B_SALE_BASKET START WITH '.$id.' INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER');
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_BASKET_INSERT
									BEFORE INSERT
									ON B_SALE_BASKET
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
											SELECT SQ_B_SALE_BASKET.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;'
						);

					}
								
					$dbNextVal = $DB->Query("SELECT SQ_SALE_BASKET_PROPS.NEXTVAL FROM DUAL", true);
					if ($dbNextVal)
					{
						$data = $dbNextVal->Fetch();
						$id = intval($data["NEXTVAL"]);

						$DB->Query('DROP SEQUENCE SQ_SALE_BASKET_PROPS');

						$DB->Query('CREATE SEQUENCE SQ_B_SALE_BASKET_PROPS START WITH '.$id.' INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER');
						$DB->Query('CREATE OR REPLACE TRIGGER B_SALE_BASKET_PROPS_INSERT
									BEFORE INSERT
									ON B_SALE_BASKET_PROPS
									FOR EACH ROW
									BEGIN
										IF :NEW.ID IS NULL THEN
											SELECT SQ_B_SALE_BASKET_PROPS.NEXTVAL INTO :NEW.ID FROM dual;
										END IF;
									END;'
						);

					}
				}
			}

			if (empty($error))
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_FINAL');
				$type = 'OK';

				if (\Bitrix\Main\ModuleManager::isModuleInstalled('catalog') && \Bitrix\Main\Config\Option::get('sale', 'basket_discount_converted'))
				{
					$countQuery = new \Bitrix\Main\Entity\Query(\Bitrix\Sale\Internals\OrderTable::getEntity());
					$countQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
					$countQuery->setFilter(array('ORDER_DISCOUNT_DATA.ID' => null));
					$totalCount = $countQuery->setLimit(1)->setOffset(null)->exec()->fetch();
					unset($countQuery);
					if ((int)$totalCount['CNT'] > 0)
					{
						$adminNotify = false;
						$adminNotifyIterator = CAdminNotify::GetList(
							array(),
							array('MODULE_ID' => 'sale', 'TAG' => 'BASKET_DISCOUNT_CONVERTED')
						);
						if (!$adminNotifyIterator)
							$adminNotify = $adminNotifyIterator->Fetch();
						unset($adminNotifyIterator);
						if (empty($adminNotify))
						{
							$langMess = array();
							$langList = array();
							$languageIterator = \Bitrix\Main\Localization\LanguageTable::getList(array(
								'select' => array('ID'),
								'filter' => array('=ACTIVE' => 'Y')
							));
							while ($oneLanguage = $languageIterator->fetch())
								$langList[] = $oneLanguage['ID'];
							unset($oneLanguage, $languageIterator);
							$messID = 'SALE_CONVERTER_ADMIN_NOTIFY_CONVERT_BASKET_DISCOUNT';
							foreach ($langList as &$oneLanguage)
							{
								$mess = Loc::loadLanguageFile(__FILE__, $oneLanguage);
								if (!isset($mess[$messID]) || empty($mess[$messID]))
									continue;
								$langMess[$oneLanguage] = str_replace(
									'#LINK#',
									'/bitrix/admin/settings.php?lang='.$oneLanguage.'&mid=sale',
									$mess[$messID]
								);
							}
							unset($mess, $oneLanguage);
							reset($langMess);
							$defaultMess = (isset($langMess[LANGUAGE_ID]) ? $langMess[LANGUAGE_ID] : current($langMess));
							$fields = array(
								'MESSAGE' => $defaultMess,
								'TAG' => 'BASKET_DISCOUNT_CONVERTED',
								'MODULE_ID' => 'sale',
								'ENABLE_CLOSE' => 'Y',
								'PUBLIC_SECTION' => 'N',
								'LANG' => $langMess
							);
							CAdminNotify::Add($fields);
							unset($fields, $langMess, $defaultMess, $langList);
						}
						unset($adminNotify);
					}
				}
			}
			else
			{
				$message = Loc::getMessage('SALE_CONVERTER_AJAX_STEP_UPDATE_BASKET');
				$result['ERROR'] = true;
				$type = 'ERROR';
				$message .= "<br>".$error;
			}

			ob_start();

			CAdminMessage::ShowMessage(array(
				"MESSAGE" => Loc::getMessage('SALE_CONVERTER_AJAX_STEP_FINAL_MESSAGE'),
				"DETAILS" => $message,
				"HTML" => true,
				"TYPE" => $type,
			));

			$result['DATA'] = ob_get_contents();
			ob_end_clean();

			break;
	}

	echo CUtil::PhpToJSObject($result);
	die();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div id="result">
	<div style = "width : 100px; float: left;" id="img_wrapper">
		<img src='/bitrix/images/sale/magaz.png'>
	</div>
<div class = 'block_step'>
<?
$step = 0;
$select = '';
if (isset($_GET['step']) && intval($_GET['step']))
	$step = intval($_GET['step']);

switch ($step)
{
	case 1:		
		echo Loc::getMessage('SALE_CONVERTER_STEP_1_DETAILS');
		break;
	case 2:
		$events = array(
			"OnSaleCalculateOrderShoppingCart",
			"OnSaleCalculateOrderPersonType",
			"OnSaleCalculateOrderProps",
			"OnSaleCalculateOrderDelivery",
			"OnSaleCalculateOrderPaySystem",
			"OnSaleCalculateOrderDiscount",
			"OnSaleCalculateOrderShoppingCartTax",
			"OnSaleCalculateOrderDeliveryTax",
			"OnSaleCalculateOrder",
			// "OnBeforeBasketUpdateAfterCheck",

			"OnBasketUpdate",
			"OnBeforeBasketDeductProduct",
			// "OnOrderPaySendEmail",
			"OnBeforeBasketDelete",
			"OnBasketDelete",
			"OnBeforeOrderAddHistory",
			"OnAfterOrderAddHistory",
			"OnSetCouponList",
			"OnClearCouponList",
			"OnDoBasketOrder",
			// "OnBeforeBasketUpdate",
			"OnSaleBeforePayOrder",
			"OnSaleBeforeDeliveryOrder",
			"OnOrderDeliverSendEmail",
			"OnSaleBeforeDeductOrder",
			"OnSaleBeforeReserveOrder",
			"OnSaleReserveOrder",
			// "OnOrderCancelSendEmail",
		);
		$eventList = '';
		foreach ($events as $event)
		{
			$moduleEvents = GetModuleEvents("sale", $event, true);
			if (!empty($moduleEvents))
				$eventList .= $event."<br>";
		}
		
		if (!empty($eventList))
		{
			$message = Loc::getMessage('SALE_CONVERTER_STEP_2_DETAILS').'<br><br>';
			echo $message.$eventList;
			break;
		}
		else
		{
			$step++;
		}
	case 3:
		if (!CSaleLocation::isLocationProMigrated())
		{
			echo Loc::getMessage('SALE_CONVERTER_STEP_3_DETAILS');
			break;
		}
		else
		{
			$step += 2;
		}
	case 4:
		if(!CSaleLocation::isLocationProMigrated())
		{
			$migrator = new \Bitrix\Sale\Location\Migration\CUpdaterLocationPro();

			$migrator->createTypes();
			$migrator->convertTree();
			$migrator->resetLegacyPath();

			$migrator->convertGroupLocationLinks();
			$migrator->convertDeliveryLocationLinks();
			$migrator->convertTaxRateLocationLinks();

			$migrator->copyDefaultLocations();
			$migrator->copyZipCodes();

			\CSaleLocation::locationProSetMigrated();
			\CSaleLocation::locationProEnable();
			
			echo Loc::getMessage('SALE_CONVERTER_STEP_4_DETAILS');
			break;
		}
	case 5:
		echo Loc::getMessage('SALE_CONVERTER_STEP_6_DETAILS');
		break;
	default :
		// ENTRY
		echo Loc::getMessage('SALE_CONVERTER_ENTRY');
}
?>
</div>
</div>

<style>
	#result {
		width: 800px;
		min-height: 100px;
		background: #fff;
		border-radius: 10px;
		padding : 20px;
		font-size : 14px;
	}
	#ajax_result {
		padding: 10px 10px 0 10px;
		width: 500px;
		height: 150px;
		background: #fff;
		display : none;
		font-size : 14px;
		overflow: auto;
		border-radius: 10px;
	}
	#ajax_result div {
		margin-bottom : 10px;
		
	}
	.block_step {
		padding: 5px;
		width: 690px;
		margin-left: 115px;
	}
</style>
	<div id="wrapper">
		<div id="ajax_result"></div>
	</div>
<br>
<form method="GET" action="<?echo $APPLICATION->GetCurPage()?>" name="form">
	<input type="hidden" name="step" value="<?=$step+1;?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID;?>">
	<?if ($step > $stepsBeforeAjax):?>
		<input type="hidden" id="ajax_step" name="ajax_step" value="<?=($ajax_step <= 0) ? 0 : $ajax_step;?>">
		<input type="button" id="start_button" value="<?=Loc::getMessage('SALE_CONVERTER_BUTTON_START_AJAX');?>" onclick="startConverter();" class="adm-btn-save">
	<?else:?>
		<input type="submit" value="<?=($step <= 0) ? Loc::getMessage('SALE_CONVERTER_BUTTON_START') : Loc::getMessage('SALE_CONVERTER_BUTTON_NEXT');?>" class="adm-btn-save">
	<?endif;?>
</form>
<?if ($step > $stepsBeforeAjax):?>
	<script type="text/javascript">
		function startConverter()
		{
			BX.show(BX('ajax_result'));			
			BX('start_button').disabled = true;
			BX.remove(BX('result'));
			ShowWaitWindow();
			doNext();
		}

		function doNext()
		{
			var ajaxNextStep = BX('ajax_step').value;
			var queryString = 'lang=<?=LANGUAGE_ID;?>&is_ajax=Y&ajax_step='+ajaxNextStep;
			var aR, div;

			BX.ajax.post(
				'sale_converter.php',
				queryString,
				function(result) {
					var data = BX.parseJSON(result);
					if (!data || data.hasOwnProperty('ERROR'))
					{
						div = BX.create('div', {
							text : (!data) ? result : data.DATA,
							style : {
								'color' : 'red'
							}
						});
						
						aR = BX('ajax_result');
						aR.appendChild(div);
						
						CloseWaitWindow();
						BX('start_button').disabled = false;
						BX('start_button').value = "<?=Loc::getMessage('SALE_CONVERTER_BUTTON_REPEAT');?>";
					}
					else
					{
						if (data.hasOwnProperty('NEXT_STEP'))
						{							
							div = BX.create('div', {
								text : data.DATA
							});
							
							aR = BX('ajax_result');
							aR.appendChild(div);
							
							BX('ajax_step').value = data.NEXT_STEP;
							aR.scrollTop = 10000;
							doNext();
						}
						else
						{
							aR = BX('wrapper');
							aR.innerHTML = data.DATA;
							
							CloseWaitWindow();
						}
					}
					aR.scrollTop = 10000;
				}
			);		

			return false;
		}
	</script>
<?endif;?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>