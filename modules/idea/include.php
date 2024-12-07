<?php

CModule::AddAutoloadClasses(
	"idea",
	array(
		"CIdeaManagment" => "classes/general/idea.php",
		"CIdeaManagmentIdea" => "classes/general/idea_idea.php",
		"CIdeaManagmentIdeaComment" => "classes/general/idea_idea_comment.php",
		"CIdeaManagmentNotify" => "classes/general/idea_notify.php",
		"CIdeaManagmentSonetNotify" => "classes/general/idea_sonet_notify.php",
		"CIdeaManagmentEmailNotify" => "classes/general/idea_email_notify.php",
	)
);
