<?
class CCalendarReminder
{
	public static function ReminderAgent($eventId = 0, $userId = 0, $viewPath = '', $calendarType = '', $ownerId = 0)
	{
		if ($eventId > 0 && $userId > 0 && $calendarType != '')
		{
			if (!\Bitrix\Main\Loader::includeModule("im"))
				return false;

			$event = false;
			$skipReminding = false;
			$bTmpUser = CCalendar::TempUser(false, true);

			// We have to use this to set timezone offset to local user's timezone
			CCalendar::SetOffset(false, CCalendar::GetOffset($userId));

			$events = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
						"DELETED" => "N",
						"FROM_LIMIT" => CCalendar::Date(time() - 3600, false),
						"TO_LIMIT" => CCalendar::Date(CCalendar::GetMaxTimestamp(), false)
					),
					'parseRecursion' => true,
					'maxInstanceCount' => 3,
					'preciseLimits' => true,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				)
			);

			if ($events && is_array($events[0]))
				$event = $events[0];

			if ($event && $event['IS_MEETING'])
			{
				$attendees = CCalendarEvent::GetAttendees($event['PARENT_ID']);
				$attendees = $attendees[$event['PARENT_ID']];
				foreach($attendees as $attendee)
				{
					// If current user is an attendee but his status is 'N' we don't take care about reminding
					if ($attendee['USER_ID'] == $userId && $attendee['STATUS'] == 'N')
					{
						$skipReminding = true;
						break;
					}
				}
			}

			if ($event && $event['DELETED'] != 'Y' && !$skipReminding)
			{
				// Get Calendar Info
				$section = CCalendarSect::GetById($event['SECT_ID'], false);
				if ($section)
				{
					$arNotifyFields = array(
						'FROM_USER_ID' => $userId,
						'TO_USER_ID' => $userId,
						'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
						'NOTIFY_MODULE' => "calendar",
						'NOTIFY_EVENT' => "reminder",
						'NOTIFY_TAG' => "CALENDAR|INVITE|".$eventId."|".$userId."|REMINDER",
						'NOTIFY_SUB_TAG' => "CALENDAR|INVITE|".$eventId
					);

					$fromTs = CCalendar::Timestamp($event['DATE_FROM'], false, $event['DT_SKIP_TIME'] !== 'Y');
					if ($event['DT_SKIP_TIME'] !== 'Y')
					{
						$fromTs -= $event['~USER_OFFSET_FROM'];
					}
					$arNotifyFields['MESSAGE'] = GetMessage('EC_EVENT_REMINDER', Array(
						'#EVENT_NAME#' => $event["NAME"],
						'#DATE_FROM#' => CCalendar::Date($fromTs, $event['DT_SKIP_TIME'] !== 'Y')
					));

					$sectionName = $section['NAME'];
					$ownerName = CCalendar::GetOwnerName($calendarType, $ownerId);
					if ($calendarType == 'user' && $ownerId == $userId)
						$arNotifyFields['MESSAGE'] .= ' '.GetMessage('EC_EVENT_REMINDER_IN_PERSONAL', Array('#CALENDAR_NAME#' => $sectionName));
					else if($calendarType == 'user')
						$arNotifyFields['MESSAGE'] .= ' '.GetMessage('EC_EVENT_REMINDER_IN_USER', Array('#CALENDAR_NAME#' => $sectionName, '#USER_NAME#' => $ownerName));
					else if($calendarType == 'group')
						$arNotifyFields['MESSAGE'] .= ' '.GetMessage('EC_EVENT_REMINDER_IN_GROUP', Array('#CALENDAR_NAME#' => $sectionName, '#GROUP_NAME#' => $ownerName));
					else
						$arNotifyFields['MESSAGE'] .= ' '.GetMessage('EC_EVENT_REMINDER_IN_COMMON', Array('#CALENDAR_NAME#' => $sectionName, '#IBLOCK_NAME#' => $ownerName));

					if ($viewPath != '')
					{
						$viewPath .= '&EVENT_DATE='.CCalendar::Date($fromTs, false);
						$arNotifyFields['MESSAGE'] .= "\n".GetMessage('EC_EVENT_REMINDER_DETAIL', Array('#URL_VIEW#' => $viewPath));
					}

					$arNotifyFields["PUSH_MESSAGE"] = GetMessage('EC_EVENT_REMINDER_PUSH', Array(
						'#EVENT_NAME#' => $event["NAME"],
						'#DATE_FROM#' => CCalendar::Date($fromTs, $event['DT_SKIP_TIME'] !== 'Y')
					));
					$arNotifyFields["PUSH_MESSAGE"] = substr($arNotifyFields["PUSH_MESSAGE"], 0, \CCalendarNotify::PUSH_MESSAGE_MAX_LENGTH);

					if (\Bitrix\Main\Loader::includeModule("im"))
					{
						CIMNotify::Add($arNotifyFields);
					}

					foreach(GetModuleEvents("calendar", "OnRemindEvent", true) as $arEvent)
						ExecuteModuleEventEx($arEvent, array(
							array(
								'eventId' => $eventId,
								'userId' => $userId,
								'viewPath' => $viewPath,
								'calType' => $calendarType,
								'ownerId' => $ownerId
							)
						));

					if (CCalendarEvent::CheckRecurcion($event) && ($nextEvent = $events[1]))
					{
						$remAgentParams = array(
							'eventId' => $eventId,
							'userId' => $userId,
							'viewPath' => $viewPath,
							'calendarType' => $calendarType,
							'ownerId' => $ownerId
						);

						// 1. clean reminders
						self::RemoveAgent($remAgentParams);

						$startTs = CCalendar::Timestamp($nextEvent['DATE_FROM'], false, $event["DT_SKIP_TIME"] !== 'Y');
						if ($nextEvent["DT_SKIP_TIME"] == 'N' && $nextEvent["TZ_FROM"])
						{
							$startTs = $startTs - CCalendar::GetTimezoneOffset($nextEvent["TZ_FROM"], $startTs); // UTC timestamp
						}

						// 2. Set new reminders
						$reminder = $nextEvent['REMIND'][0];
						if ($reminder)
						{
							$delta = intVal($reminder['count']) * 60; //Minute
							if ($reminder['type'] == 'hour')
								$delta = $delta * 60; //Hour
							elseif ($reminder['type'] == 'day')
								$delta =  $delta * 60 * 24; //Day

							// $startTs - UTC timestamp;  date("Z", $startTs) - offset of the server
							$agentTime = $startTs + date("Z", $startTs);
							if (($agentTime - $delta) >= (time() - 60 * 5)) // Inaccuracy - 5 min
								self::AddAgent(CCalendar::Date($agentTime - $delta), $remAgentParams);
						}
					}
				}
			}

			CCalendar::SetOffset(false, null);

			if ($bTmpUser)
				CCalendar::TempUser($bTmpUser, false);
		}
	}

	public static function RemoveAgent($params)
	{
		CAgent::RemoveAgent("CCalendar::ReminderAgent(".$params['eventId'].", ".$params['userId'].", '".$params['viewPath']."', '".$params['calendarType']."', ".$params['ownerId'].");", "calendar");
	}

	public static function AddAgent($remindTime, $params)
	{
		global $DB;
		self::RemoveAgent($params);
		if (strlen($remindTime) > 0 && $DB->IsDate($remindTime, false, LANG, "FULL"))
		{
			$tzEnabled = CTimeZone::Enabled();
			if ($tzEnabled)
				CTimeZone::Disable();
			CAgent::AddAgent(
				"CCalendar::ReminderAgent(".intVal($params['eventId']).", ".intVal($params['userId']).", '".addslashes($params['viewPath'])."', '".addslashes($params['calendarType'])."', ".intVal($params['ownerId']).");",
				"calendar",
				"N",
				86400,
				"",
				"Y",
				$remindTime
			);
			if ($tzEnabled)
				CTimeZone::Enable();
		}
	}

	public static function UpdateReminders($params = array())
	{
		$eventId = intVal($params['id']);
		$reminders = $params['reminders'];
		$arFields = $params['arFields'];
		$userId = $params['userId'];
		$bNew = $params['bNew'];

		$path = $params['path'];
		$path = CHTTP::urlDeleteParams($path, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
		$viewPath = CHTTP::urlAddParams($path, array('EVENT_ID' => $eventId));

		$remAgentParams = array(
			'eventId' => $eventId,
			'userId' => $arFields["CREATED_BY"],
			'viewPath' => $viewPath,
			'calendarType' => $arFields["CAL_TYPE"],
			'ownerId' => $arFields["OWNER_ID"]
		);

		// 1. clean reminders
		if (!$bNew) // if we edit event here can be "old" reminders
			self::RemoveAgent($remAgentParams);

		// 2. Set new reminders
		$startTs = $arFields['DATE_FROM_TS_UTC']; // Start of the event in UTC

		foreach($reminders as $reminder)
		{
			$delta = intVal($reminder['count']) * 60; //Minute
			if ($reminder['type'] == 'hour')
				$delta = $delta * 60; //Hour
			elseif ($reminder['type'] == 'day')
				$delta =  $delta * 60 * 24; //Day

			// $startTs - UTC timestamp;  date('Z', $startTs) - offset of the server
			$agentTime = $startTs + date('Z', $startTs);
			if (($agentTime - $delta) >= (time() - 60 * 5)) // Inaccuracy - 5 min
			{
				self::AddAgent(CCalendar::Date($agentTime - $delta), $remAgentParams);
			}
			elseif($arFields['RRULE'] != '')
			{
				$events = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"ID" => $eventId,
							"DELETED" => "N",
							"FROM_LIMIT" => CCalendar::Date(time() - 3600, false),
							"TO_LIMIT" => CCalendar::GetMaxDate()
						),
						'userId' => $userId,
						'parseRecursion' => true,
						'maxInstanceCount' => 2,
						'preciseLimits' => true,
						'fetchAttendees' => true,
						'checkPermissions' => false,
						'setDefaultLimit' => false
					)
				);

				if ($events && is_array($events[0]))
				{
					$nextEvent = $events[0];
					$startTs = CCalendar::Timestamp($nextEvent['DATE_FROM'], false, $events[0]["DT_SKIP_TIME"] !== 'Y');
					if ($nextEvent["DT_SKIP_TIME"] == 'N' && $nextEvent["TZ_FROM"])
					{
						$startTs = $startTs - CCalendar::GetTimezoneOffset($nextEvent["TZ_FROM"], $startTs); // UTC timestamp
					}

					if (($startTs + date("Z", $startTs)) < (time() - 60 * 5) && $events[1]) // Inaccuracy - 5 min)
					{
						$nextEvent = $events[1];
					}

					$startTs = CCalendar::Timestamp($nextEvent['DATE_FROM'], false, $events[0]["DT_SKIP_TIME"] !== 'Y');
					if ($nextEvent["DT_SKIP_TIME"] == 'N' && $nextEvent["TZ_FROM"])
					{
						$startTs = $startTs - CCalendar::GetTimezoneOffset($nextEvent["TZ_FROM"], $startTs); // UTC timestamp
					}

					$reminder = $nextEvent['REMIND'][0];
					if ($reminder)
					{
						$delta = intVal($reminder['count']) * 60; //Minute
						if ($reminder['type'] == 'hour')
							$delta = $delta * 60; //Hour
						elseif ($reminder['type'] == 'day')
							$delta =  $delta * 60 * 24; //Day

						// $startTs - UTC timestamp;  date("Z", $startTs) - offset of the server
						$agentTime = $startTs + date("Z", $startTs);
						if (($agentTime - $delta) >= (time() - 60 * 5)) // Inaccuracy - 5 min
							self::AddAgent(CCalendar::Date($agentTime - $delta), $remAgentParams);
					}
				}
			}
		}
	}
}
?>