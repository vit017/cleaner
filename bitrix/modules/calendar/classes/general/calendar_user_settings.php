<?
class CCalendarUserSettings
{
	private static
		$settings = array(
			'tabId' => 'month',
			'CalendarSelCont' => false,
			'SPCalendarSelCont' => false,
			'meetSection' => false,
			'blink' => true,
			'showDeclined' => false,
			'showMuted' => true,
			'denyBusyInvitation' => false
		);

	public static function Set($settings = array(), $userId = false)
	{
		if (!$userId)
			$userId = CCalendar::GetUserId();
		if (!$userId)
			return;

		if ($settings === false)
		{
			CUserOptions::SetOption("calendar", "user_settings", false, false, $userId);
		}
		elseif(is_array($settings))
		{
			$curSet = self::Get($userId);
			foreach($settings as $key => $val)
			{
				if (isset(self::$settings[$key]))
					$curSet[$key] = $val;
			}
			CUserOptions::SetOption("calendar", "user_settings", $curSet, false, $userId);
		}
	}

	public static function Get($userId = false)
	{
		if (!$userId)
			$userId = CCalendar::GetUserId();

		$resSettings = self::$settings;

		if ($userId)
		{
			$settings = CUserOptions::GetOption("calendar", "user_settings", false, $userId);
			if (is_array($settings))
			{
				if (isset($settings['tabId']) && in_array($settings['tabId'], array('month','week','day')))
					$resSettings['tabId'] = $settings['tabId'];
				if (isset($settings['blink']))
					$resSettings['blink'] = !!$settings['blink'];
				if (isset($settings['showDeclined']))
					$resSettings['showDeclined'] = !!$settings['showDeclined'];
				if (isset($settings['showMuted']))
					$resSettings['showMuted'] = !!$settings['showMuted'];
				if (isset($settings['meetSection']) && $settings['meetSection'] > 0)
					$resSettings['meetSection'] = intVal($settings['meetSection']);
				if (isset($settings['denyBusyInvitation']))
					$resSettings['denyBusyInvitation'] = !!$settings['denyBusyInvitation'];
			}
		}
		return $resSettings;
	}
}
?>