// source: https://bdwm.be/gutenberg-how-to-prevent-post-from-being-saved/

const Locker = {

  // this array keeps track of all of our locks.  note:  all locks must be
  // released (i.e. false) before the post can be saved, and Gutenberg has
  // locks of its own, so even if our locks are released, there might be other
  // reasons that prevent saving.

  locks: [],

  // in the methods below, we access the core/editor and core/notices
  // dispatches.  rather than request these over and over again, we'll set a
  // reference to them here and then just use those below.

  editor: wp.data.dispatch('core/editor'),
  notices: wp.data.dispatch('core/notices'),

  /**
   * addCondition
   *
   * This method adds a condition for a screen lock.
   *
   * @param shouldLock  when true, then we should lock post saving
   * @param handle      the handle by which this lock is identified
   * @param message     the message displayed as an error notice when locked
   *
   * @return void
   */
  addCondition(shouldLock, handle, message) {
    if (shouldLock) {
      if (!this.locks[handle]) {
        this.lock(handle, message);
      }
    } else {
      if (this.locks[handle]) {
        this.unlock(handle);
      }
    }
  },

  /**
   * lock
   *
   * When the post is locked, this method contacts the editor to set that lock
   * and displays an error notice to let the visitor know how to unlock it.
   *
   * @param handle
   * @param message
   *
   * @return void
   */
  lock(handle, message) {
    this.locks[handle] = true;
    this.editor.lockPostSaving(handle);
    this.notices.createErrorNotice(message, {
      isDismissible: false,
      id: handle
    });
  },

  /**
   * unlock
   *
   * When a lock should be released, this is the method that does that by
   * removing a specific lock from the post as well as removing the notice
   * that corresponds to it.
   *
   * @param handle
   */
  unlock(handle) {
    this.locks[handle] = false;
    this.editor.unlockPostSaving(handle);
    this.notices.removeNotice(handle);
  }
};

export default Locker;
