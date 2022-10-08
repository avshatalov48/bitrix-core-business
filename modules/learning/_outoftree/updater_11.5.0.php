<?
if($updater->CanUpdateDatabase())
{
	if ($updater->TableExists('b_learn_test'))
	{
		if (mb_strtoupper($updater->dbType) === 'MSSQL')
		{
			$DB->Query('ALTER TABLE b_learn_test DROP CONSTRAINT FK_B_LEARN_TEST1', true);
			$DB->Query('ALTER TABLE b_learn_test DROP CONSTRAINT FK_B_LEARN_TEST2', true);
		}
	}

	if ($updater->TableExists('b_learn_lesson'))
	{
		if (mb_strtoupper($updater->dbType) === 'ORACLE')
		{
			$DB->Query('ALTER TABLE b_learn_lesson DROP COLUMN KEYWORDS CASCADE CONSTRAINTS', true);
			$DB->Query('ALTER TABLE b_learn_lesson ADD KEYWORDS CLOB DEFAULT \'\'', true);
		}
	}

	if ($updater->TableExists('b_learn_chapter'))
	{
		if (mb_strtoupper($updater->dbType) === 'ORACLE')
		{
			$DB->Query('ALTER TABLE b_learn_chapter DROP CONSTRAINT fk_b_learn_chapter1', true);
			$DB->Query('ALTER TABLE b_learn_chapter DROP CONSTRAINT fk_b_learn_chapter2', true);
		}
	}
}

if(IsModuleInstalled('learning'))
{
	$updater->CopyFiles("install/admin", "admin");
	$updater->CopyFiles("install/components", "components");
	//Following copy was parsed out from module installer
	$updater->CopyFiles("install/images", "images/learning");
	//Following copy was parsed out from module installer
	$updater->CopyFiles("install/public/js", "js");
	//Following copy was parsed out from module installer
	$updater->CopyFiles("install/public/template", "templates/learning");
	//Following copy was parsed out from module installer
	$updater->CopyFiles("install/themes", "themes");
}
//There is .sql file in update. Do not forget alter DB properly.
if($updater->CanUpdateKernel())
{
	$arToDelete = array(
		"admin/learn_chapter_admin.php",
		"admin/learn_chapter_edit.php",
		"admin/learn_course_admin.php",
		"admin/learn_course_index.php",
		"admin/learn_lesson_admin.php",
		"admin/learn_lesson_edit.php",
		"modules/learning/admin/learn_chapter_admin.php",
		"modules/learning/admin/learn_chapter_edit.php",
		"modules/learning/admin/learn_course_admin.php",
		"modules/learning/admin/learn_course_index.php",
		"modules/learning/admin/learn_lesson_admin.php",
		"modules/learning/admin/learn_lesson_edit.php",
		"modules/learning/classes/general/chapter.php",
		"modules/learning/classes/general/lesson.php",
		"modules/learning/classes/mssql/chapter.php",
		"modules/learning/classes/mssql/lesson.php",
		"modules/learning/classes/mysql/chapter.php",
		"modules/learning/classes/mysql/lesson.php",
		"modules/learning/classes/oracle/chapter.php",
		"modules/learning/classes/oracle/lesson.php",
		"modules/learning/install/admin/learn_chapter_admin.php",
		"modules/learning/install/admin/learn_chapter_edit.php",
		"modules/learning/install/admin/learn_course_admin.php",
		"modules/learning/install/admin/learn_course_index.php",
		"modules/learning/install/admin/learn_lesson_admin.php",
		"modules/learning/install/admin/learn_lesson_edit.php",
		"modules/learning/lang/de/admin/learn_lesson_edit.php",
		"modules/learning/lang/en/admin/learn_lesson_edit.php",
		"modules/learning/lang/ru/admin/learn_lesson_edit.php",
		"modules/learning/install/js/rights_edit.js",
		"modules/learning/install/js/learning/rights_edit.js",
		"modules/learning/classes/mssql/answer.php",
		"modules/learning/classes/mssql/clearngraphnode.php",
		"modules/learning/classes/mssql/clearngraphrelation.php",
		"modules/learning/classes/mssql/clearnlesson.php",
		"modules/learning/classes/mssql/course.php",
		"modules/learning/classes/mssql/question.php",
		"modules/learning/classes/mssql/student.php",
		"modules/learning/classes/mssql/testmark.php",
		"modules/learning/classes/mssql/testresult.php",
		"modules/learning/classes/mysql/answer.php",
		"modules/learning/classes/mysql/clearngraphnode.php",
		"modules/learning/classes/mysql/clearngraphrelation.php",
		"modules/learning/classes/mysql/clearnlesson.php",
		"modules/learning/classes/mysql/course.php",
		"modules/learning/classes/mysql/question.php",
		"modules/learning/classes/mysql/student.php",
		"modules/learning/classes/mysql/testmark.php",
		"modules/learning/classes/mysql/testresult.php",
		"modules/learning/classes/oracle/answer.php",
		"modules/learning/classes/oracle/clearngraphnode.php",
		"modules/learning/classes/oracle/clearngraphrelation.php",
		"modules/learning/classes/oracle/clearnlesson.php",
		"modules/learning/classes/oracle/course.php",
		"modules/learning/classes/oracle/question.php",
		"modules/learning/classes/oracle/student.php",
		"modules/learning/classes/oracle/testmark.php",
		"modules/learning/classes/oracle/testresult.php",
		"modules/learning/lang/de/admin/learn_lesson_admin.php",
		"modules/learning/lang/en/admin/learn_lesson_admin.php",
		"modules/learning/lang/ru/admin/learn_lesson_admin.php",
	);
	foreach($arToDelete as $file)
		CUpdateSystem::DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"].$updater->kernelPath."/".$file);
}

if($updater->CanUpdateDatabase())
{
	// Is module data exists?
	if ( $DB->TableExists('b_learn_lesson') )
	{
		// Ensure, that data in database converted to 11.5.0 version of module
		if (COption::GetOptionString(
				'learning', 
				'~LearnInstall201203ConvertDB::_IsAlreadyConverted', 
				'-9', 
				''
			)
			!== '1'
		)
		{
			// Data for module not converted yet, generate message
			if (method_exists('CAdminNotify', 'Add'))
			{
				$langFile = __DIR__ . '/lang/' . LANGUAGE_ID . '/updater.php';

				// Load english version, if localization not available
				if ( ! (file_exists($langFile) && is_readable($langFile)) )
					$langFile = __DIR__ . '/lang/en/updater.php';

				if (file_exists($langFile) && is_readable($langFile))
				{
					$learningNotifyMessage = '';
					include($langFile);

					CAdminNotify::Add(
						array(
							'MESSAGE'      => str_replace('#LANG#', LANGUAGE_ID, $learningNotifyMessage),
							'TAG'          => 'learning_convert_11_5_0',
							'MODULE_ID'    => 'learning',
							'ENABLE_CLOSE' => 'N'
						)
					);
				}
			}
		}
	}
}
