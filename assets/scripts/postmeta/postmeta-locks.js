import Locker from './locker';

const PostmetaLocks = {
  /**
   * onLoad
   *
   * This method is attached to the DOMContentLoaded event below so that we
   * can add the behaviors of this object to the editor.
   *
   * @return void
   */
  onLoad() {

    // we want to guarantee that all events have a complete record of their
    // event information as well as at least one event type.  the type
    // criterion can be identified by subscribing to data from the WP editor
    // as follows.

    wp.data.subscribe(() => {
      Locker.addCondition(!this.hasEventType(), 'event-type-lock',
        'Please select at least one event type.');

      Locker.addCondition(!this.hasEventInformation(), 'event-info-lock',
        'Please enter all event information.');
    });
  },

  /**
   * hasEventInformation
   *
   * Determines that the visitor has entered a complete set of event
   * information.
   *
   * @return boolean
   */
  hasEventInformation() {
    let hasInformation = true;
    const meta = wp.data.select('core/editor').getEditedPostAttribute('meta');
    console.log(meta);

    for (const key in meta) {
      switch (key) {
        case 'simple_event_duration':
          hasInformation = hasInformation && Number(meta[key]) >= 0.5;
          break;

        case 'simple_event_private':
          hasInformation = hasInformation && meta[key] !== false;

        default:
          hasInformation = hasInformation && meta[key].length !== 0;
          break;
      }
    }

    return hasInformation;
  },

  /**
   * hasEventType
   *
   * Confirms that this event has at least one event type.
   *
   * @return boolean
   */
  hasEventType() {

    // we can select the currently marked event types from the editor as
    // follows.  this should return a countable object, so we know we have at
    // least one type when the object returned from the editor is truthy, and
    // its length is greater than or equal to one.

    const types = wp.data.select('core/editor').getEditedPostAttribute('event-type');
    return types && types.length >= 1;
  }
};

export default PostmetaLocks;
