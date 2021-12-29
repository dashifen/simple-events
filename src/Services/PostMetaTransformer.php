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
   * transformDatetimeFromStorage
   *
   * Given a datetime in YYYY-MM-DD HH:MM format, gets the date and time
   * formats defined in the site's settings and uses them to produce a more
   * human readable datetime.
   *
   * @param string $datetime
   *
   * @return false|string
   */
  protected function transformDatetimeFromStorage(string $datetime): string
  {
    $dateFormat = get_option('date_format');
    $timeFormat = get_option('time_format');
    return date($dateFormat . ' ' . $timeFormat, strtotime($datetime));
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
