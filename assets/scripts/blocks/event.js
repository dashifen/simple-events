const Event = (() => {
  const {registerBlockType} = wp.blocks;
  const {withSelect} = wp.data;

  registerBlockType('simple-events/event', {
    title: 'Event',
    category: 'simple-events',
    description: 'A block that shows information about a single event.',
    icon: 'tickets-alt',
    attributes: {
      postId: {type: 'number'}
    },

    edit: withSelect(select => {
      const posts = select('core').getEntityRecords('postType', 'simple-event');
      const strtotime = require('locutus/php/datetime/strtotime');
      const date = require('locutus/php/datetime/date');

      let options = [];
      for (const i in posts) {
        if (posts.hasOwnProperty(i)) {

          // here we start to build the value/label pairs that get used as
          // the options on-screen.  however, they won't be in order, so we'll
          // also add a sort property which is a sortable string that we use
          // below.

          options.push({
            value: posts[i].id,
            sort: posts[i].meta.simple_event_datetime + ' ' + posts[i].title.raw,
            label: date('n/j/Y g:ia', strtotime(posts[i].meta.simple_event_datetime))
              + ' - ' + posts[i].title.raw
          });
        }
      }

      options.sort((a, b) => a.sort <= b.sort ? -1 : 1);
      return {options};
    })(properties => {
      const {setAttributes, options} = properties;
      const {ComboboxControl} = wp.components;
      const {Fragment} = wp.element;

      return (
        <Fragment>
          <header className="simple-events-header">Event</header>
          <ComboboxControl
            label="Select an event"
            options={options}
            value={properties.attributes.postId}
            onChange={value => setAttributes({'postId': value})}
          />
        </Fragment>
      );
    }),

    save: () => null,
  });
});

export default Event;
