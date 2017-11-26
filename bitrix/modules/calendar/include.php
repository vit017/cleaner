<?
/*patchlimitationmutatormark1*/
global $DBType;
IncludeModuleLangFile(__FILE__);

global $DBType;
CModule::AddAutoloadClasses(
	"calendar",
	array(
		"CCalendar" => "classes/general/calendar.php",
		"CCalendarSceleton" => "classes/general/calendar_sceleton.php",
		"CCalendarEvent" => "classes/general/calendar_event.php",
		"CCalendarSect" => "classes/general/calendar_sect.php",
		"CCalendarType" => "classes/general/calendar_type.php",
		"CCalendarPlanner" => "classes/general/calendar_planner.php",
		"CCalendarWebService" => "classes/general/calendar_webservice.php",
		"CCalendarNotifySchema" => "classes/general/calendar_notify_schema.php",
		"CCalendarPullSchema" => "classes/general/calendar_notify_schema.php",
		"CCalendarEventHandlers" => "classes/general/calendar_event_handlers.php",
		"CCalendarRestService" => "classes/general/calendar_restservice.php",
		"CCalendarLiveFeed" => "classes/general/calendar_livefeed.php",
		"CCalendarRequest" => "classes/general/calendar_request.php",
		"CCalendarNotify" => "classes/general/calendar_notify.php",
		"CCalendarUserSettings" => "classes/general/calendar_user_settings.php",
		"CCalendarSync" => "classes/general/calendar_sync.php",
		"CCalendarReminder" => "classes/general/calendar_reminder.php"
	)
);
/*patchlimitationmutatormark2*/
?>