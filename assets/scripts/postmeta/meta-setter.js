const MetaSetter = {
  /**
   * setDate
   *
   * Given a new value for this event's date, set it in the post meta and then
   * also set the event's datetime.
   *
   * @param date
   *
   * @return void
   */
  setDate(date) {
    this.set('simple_event_date', date);
    this.setDateTime();
  },

  /**
   * set
   *
   * Given a field/value pair, send it to the editor as new metadata for the
   * current event.
   *
   * @param field
   * @param value
   *
   * @return void
   */
  set(field, value) {
    const newMeta = [];
    newMeta[field] = value;
    wp.data.dispatch('core/editor').editPost({meta: newMeta});
  },

  /**
   * setDateTime
   *
   * Gets the current metadata from the editor and then uses the date and time
   * information therein to set this event's datetime.
   *
   * @return void
   */
  setDateTime() {
    const meta = wp.data.select('core/editor').getEditedPostAttribute('meta');
    this.set('simple_event_datetime', meta['simple_event_date'] + ' ' + meta['simple_event_time']);
  },

  /**
   * setTime
   *
   * Given a new value for this event's time, sets it in the post meta and then
   * sets the datetime meta value as well.
   *
   * @param time
   *
   * @return void
   */
  setTime(time) {
    this.set('simple_event_time', time);
    this.setDateTime();
  },

  /**
   * setDuration
   *
   * Rounds the duration to the nearest quarter and then sends it to the editor
   * to be placed in the post meta.
   *
   * @param duration
   *
   * @return void
   */
  setDuration(duration) {
    duration = Math.round(duration * 4) / 4;
    this.set('simple_event_duration', duration);
  }
}

export default MetaSetter;
