const Event = (() => {
  const {registerBlockType} = wp.blocks;

  registerBlockType('simple-events/event', {
    title: 'Event',
    category: 'simple-events',
    description: 'A block that shows information about a single event.',
    icon: 'tickets-alt',
    attributes: {
      postId: {type: 'number'}
    },

    edit: (properties => {
      const {setAttributes} = properties;
      const {ComboboxControl} = wp.components;
      const {Fragment} = wp.element;

      return (
        <Fragment>
          <ComboboxControl
            label="Select an event"
            options={window.simpleEvents}
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
