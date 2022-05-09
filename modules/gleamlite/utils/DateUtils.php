<?php

namespace gleamlite\utils;

use DateTime;
use Exception;

class DateUtils
{

  public static function nowDatabaseFormat(): string
  {
    return date('Y-m-d H:i:s');
  }
  
	public static function nowDatabaseShortDate(): string
	{
		return date('Y-m-d');
	}

	public static function nowMilliseconds(): int {
		return time() * 1000;
	}

	public static function todayMilliseconds(): int {
		return strtotime(self::nowDatabaseShortDate()) * 1000;
	}

	public static function millisecondsToShortDate(int $milliseconds): string
	{
		return date('Y-m-d', ($milliseconds / 1000));
	}

  public static function toDatabaseDateFormat(string $date): string
  {
    return date('Y-m-d H:i:s', strtotime($date));
  }

	public static function toDatabaseShortDate(string $value): string
	{
		return date('Y-m-d', strtotime($value));
	}

  public static function toUserDateFormat(string $inputDate): string
  {
    list($date, $time) = explode(' ', $inputDate);
    list($year, $month, $day) = explode('-', $date);

    return "{$day}-{$month}-{$year} {$time}";
  }

  public static function toNormalizedDateString(string $inputDate): string
  {
    $date = date('F j, Y', strtotime($inputDate));
    $replace = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    $search = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    return str_replace($search, $replace, $date);
  }

  public static function timeToSeconds(string $time): int
	{
		list($hourFrom, $minutesFrom) = explode(':', $time);

		$hourSecondsFrom = ((int) $hourFrom * 3600);

		$minutesSecondsFrom = 0;
		if (StringUtils::notEquals('00', $minutesFrom)) {
			$minutesSecondsFrom = ((int) $minutesFrom * 60);
		}

		return $hourSecondsFrom + $minutesSecondsFrom;
	}

  public static function secondsToTime(string $seconds): string
	{
		$hours = floor($seconds / 3600);
		$minutes = floor(($seconds - ($hours * 3600)) / 60);

		if ((int) $hours < 10) {
			$hours = "0{$hours}";
		}
		
		if (StringUtils::equals($minutes, '0')) {
			$minutes = '00';
		}
		
		return "{$hours}:{$minutes}";
	}

	public static function toShortTime(string $value = ''): string
	{
		if (is_null($value) || empty($value)) {
			return '--:--';
		}
		list($hour, $minutes, $seconds) = explode(':', $value);
		return "{$hour}:{$minutes}";
	}

	public static function minusTime(string $start, string $end): string
	{
		$init = new DateTime($start);
		$finish = new DateTime($end);
		$interval = $init->diff($finish);
		return $interval->format("%H:%I");
	}

  public static function validateTimeRangeThrowing(string $message, string $timeFrom, string $timeTo): array
	{
		$from = self::timeToSeconds($timeFrom);
		$to = self::timeToSeconds($timeTo);

		if ($from >= $to) {
      throw new Exception($message);
		}

		return [
			$from,
			$to
		];
	}
}