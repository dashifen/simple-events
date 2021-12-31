import MetaSetter from "./meta-setter";

const {compose} = wp.compose;
const {PluginDocumentSettingPanel} = wp.editPost;
const {TextControl, RadioControl, PanelRow} = wp.components;
const {withSelect} = wp.data;

const PostmetaPlugin = ({postType, postMeta}) => {

  // if our post type is not the simple-event, then just return null.
  // otherwise, return the component that will be placed into the editor
  // sidebar.  since our javascript is only added for simple-event type posts,
  // this is probably a superfluous check, but it never hurts to be cautious.

  return postType !== 'simple-event'
    ? null
    : (
      <PluginDocumentSettingPanel
        name="event-information"
        title="Event Information"
        initialOpen="true"
        icon="calendar-alt"
      >
        <PanelRow>
          <TextControl
            label="Host"
            className="simple-events-event-information"
            onChange={(value) => MetaSetter.set('simple_event_host', value)}
            value={postMeta.simple_event_host}
            aria-required="true"
            required="true"
          />
        </PanelRow>
        <PanelRow>
          <TextControl
            label="Location"
            help="If online, enter conferencing URL."
            className="simple-events-event-information"
            onChange={(value) => MetaSetter.set('simple_event_location', value)}
            value={postMeta.simple_event_location}
            aria-required="true"
            required="true"
          />
        </PanelRow>
        <PanelRow>
          <TextControl
            type="date"
            label="Date"
            className="simple-events-event-information"
            onChange={(value) => MetaSetter.setDate(value)}
            value={postMeta.simple_event_date}
            aria-required="true"
            required="true"
          />
        </PanelRow>
        <PanelRow>
          <TextControl
            type="time"
            label="Time"
            className="simple-events-event-information"
            onChange={(value) => MetaSetter.setTime(value)}
            value={postMeta.simple_event_time}
            aria-required="true"
            required="true"
          />
        </PanelRow>
        <PanelRow>
          <TextControl
            type="number"
            label="Duration"
            help="(in hours)"
            className="simple-events-event-information"
            onChange={(value) => MetaSetter.setDuration(value)}
            value={postMeta.simple_event_duration}
            aria-required="true"
            required="true"
            step=".25"
            min=".5"
          />
        </PanelRow>
        <PanelRow>
          <RadioControl
            label="Public or Private?"
            className="simple-events-event-information"
            selected={postMeta.simple_event_visibility}
            onChange={(value) => MetaSetter.set('simple_event_visibility', value)}
            options={[
              {label: 'Public', value: 'public'},
              {label: 'Private', value: 'private'}
            ]}
          />
        </PanelRow>
      </PluginDocumentSettingPanel>
    );
};

export default compose([
  withSelect((select) => {
    return {
      postMeta: select('core/editor').getEditedPostAttribute('meta'),
      postType: select('core/editor').getCurrentPostType(),
    };
  })
])(PostmetaPlugin);
