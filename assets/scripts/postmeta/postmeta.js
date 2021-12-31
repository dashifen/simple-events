import PostmetaPlugin from "./postmeta-plugin";
import PostmetaLocks from './postmeta-locks';

const {registerPlugin} = wp.plugins;

registerPlugin('simple-events-postmeta-plugin', {
  render() {
    return (<PostmetaPlugin/>);
  }
});

window.addEventListener('DOMContentLoaded',
  PostmetaLocks.onLoad.bind(PostmetaLocks));


