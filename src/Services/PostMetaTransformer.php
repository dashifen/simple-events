<?php

namespace Dashifen\SimpleEvents\Services;

use Dashifen\CaseChangingTrait\CaseChangingTrait;
use Dashifen\Transformer\StorageTransformer\AbstractStorageTransformer;

class PostMetaTransformer extends AbstractStorageTransformer
{
  use CaseChangingTrait;
  
  /**
   * getTransformationMethod
   *
   * Returns the name of a method in this class that is used to validate
   * information labeled by $field.  The "for storage" flag may alter the
   * name of this method based on whether we're transforming our value so
   * that it can be stored or after it's been retrieved from storage.
   *
   * @param string $field
   * @param bool   $forStorage
   *
   * @return string
   */
  protected function getTransformationMethod(string $field, bool $forStorage): string
  {
    $method = 'transform' . $this->kebabToPascalCase($field);
    $method .= $forStorage ? 'ForStorage' : 'FromStorage';
    return $method;
  }
  
  /**
   * transformDateFromStorage
   *
   * Given a date in YYYY-MM-DD format, converts it to the one that's specified
   * in the WordPress settings.
   *
   * @param string $date
   *
   * @return string
   */
  protected function transformDateFromStorage(string $date): string
  {
    return date(get_option('date_format'), strtotime($date));
  }
  
  /**
   * transformTimeFromStorage
   *
   * Given a time in HH:MM format, converts it to the one that's specified in
   * the WordPress settings.
   *
   * @param string $time
   *
   * @return string
   */
  protected function transformTimeFromStorage(string $time): string
  {
    return date(get_option('time_format'), strtotime($time));
  }
  
  /**
   * transformDatetimeFromStorage
   *
   * Given a datetime in YYYY-MM-DD HH:MM format, uses the above methods to
   * produce a more human-readable format.
   *
   * @param string $datetime
   *
   * @return false|string
   */
  protected function transformDatetimeFromStorage(string $datetime): string
  {
    return $this->transformDateFromStorage($datetime)
      . $this->transformTimeFromStorage($datetime);
  }
  
  /**
   * transformDurationFromStorage
   *
   * When displaying durations on-screen, we'll add the unit of time
   * measurement after the value passed here for clarity.
   *
   * @param string $duration
   *
   * @return string
   */
  protected function transformDurationFromStorage(string $duration): string
  {
    return $duration . ($duration === '1' ? ' hour' : ' hours');
  }
  
  /**
   * transformPrivateFromStorage
   *
   * The database stores the privacy of an event as either private or public.
   * To make it a little nicer on-screen, we'll capitalize that and add the
   * word "Event" after it.
   *
   * @param string $private
   *
   * @return string
   */
  protected function transformPrivateFromStorage(string $private): string
  {
    return ucfirst($private) . ' Event';
  }
}
