const Calendar = (() => {
  const now = new Date();
  const {registerBlockType} = wp.blocks;
  const {withSelect} = wp.data;

  registerBlockType('simple-events/calendar', {
    title: 'Event Calendar',
    category: 'simple-events',
    description: 'A block that shows a calendar of events.',
    icon: 'calendar-alt',
    attributes: {
      specifyMonth: {
        type: 'boolean',
        default: false
      },
      month: {
        type: 'number',
        default: now.getMonth() + 1
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

    edit: withSelect(select => {
      const terms = select('core').getEntityRecords('taxonomy', 'event-type');

      let options = [];
      for (const i in terms) {
        if (terms.hasOwnProperty(i)) {
          options.push({
            value: terms[i].id,
            label: terms[i].name
          });
        }
      }

      // unlike our events, we can sort based specifically on the label here.
      // that'll put our event types into alphabetical order.  then we add an
      // All Types option at the top of that list.

      options.sort((a, b) => a.label < b.label ? -1 : 1);
      options.push({value: 0, label: 'All Types'});
      return {options};
    })(properties => {
      const {setAttributes, options} = properties;
      const {specifyMonth, month, year, type} = properties.attributes;
      const {TextControl, SelectControl, ToggleControl} = wp.components;
      const {Fragment} = wp.element;

      return (
        <Fragment>
          <header className="simple-events-header">Event Calendar</header>
          <SelectControl
            label="Select event type"
            value={type}
            onChange={value => setAttributes({'type': value})}
            options={options}
          />
          <ToggleControl
            label="Specify Month"
            help="This block will always show the current month unless you specify a different one."
            onChange={value => setAttributes({specifyMonth: value})}
            aria-controls="month-and-year-controls"
            checked={specifyMonth}
          />
          <div id="month-and-year-controls" className={!specifyMonth ? 'hidden' : ''}>
            <SelectControl
              label="Select month"
              value={month}
              disabled={!specifyMonth}
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
              disabled={!specifyMonth}
              onChange={value => setAttributes({'year': value})}
            />
          </div>
        </Fragment>
      );
    }),

    save: () => null,
  });
});

export default Calendar;
