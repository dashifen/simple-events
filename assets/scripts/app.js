import SimpleEventsEditorPlugin from "./editor-plugin";
import SimpleEventsEditorControls from './editor-controls';
const {registerPlugin} = wp.plugins;

registerPlugin('simple-events-postmeta-plugin', {
  render() {
    return(<SimpleEventsEditorPlugin />);
  }
});

window.addEventListener('DOMContentLoaded',
  SimpleEventsEditorControls.onLoad.bind(SimpleEventsEditorControls));


