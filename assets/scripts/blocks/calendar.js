const Calendar = (() => {
  const now = new Date();
  const {registerBlockType} = wp.blocks;

  registerBlockType('simple-events/calendar', {
    title: 'Event Calendar',
    category: 'simple-events',
    description: 'A block that shows a calendar of events.',
    icon: 'calendar-alt',
    attributes: {
      month: {
        type: 'number',
        default: now.getMonth()+1
      },
      year: {
        type: 'number',
        default: now.getFullYear()
      },
      type: {
        type: 'number',
        default: 0
      }
    },

    edit: (properties => {
      const {setAttributes} = properties;
      const {month, year, type} = properties.attributes;
      const {TextControl, SelectControl} = wp.components;
      const {Fragment} = wp.element;

      return (
        <Fragment>
          <SelectControl
            label="Select month"
            value={month}
            onChange={value => setAttributes({'month': value})}
            options={[
              {value: 1, label: 'January'},
              {value: 2, label: 'February'},
              {value: 3, label: 'March'},
              {value: 4, label: 'April'},
              {value: 5, label: 'May'},
              {value: 6, label: 'June'},
              {value: 7, label: 'July'},
              {value: 8, label: 'August'},
              {value: 9, label: 'September'},
              {value: 10, label: 'October'},
              {value: 11, label: 'November'},
              {value: 12, label: 'December'}
            ]}
          />
          <TextControl
            type="number"
            label="Enter year"
            value={year}
            onChange={value => setAttributes({'year': value})}
          />
          <SelectControl
            label="Select event type"
            value={type}
            onChange={value => setAttributes({'type': value})}
            options={window.simpleEventsTypes}
          />
        </Fragment>
      );
    }),

    save: () => null,
  });
});

export default Calendar;
