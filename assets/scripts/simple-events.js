window.addEventListener('DOMContentLoaded', () => {
  const duration = document.getElementById('event-duration');
  if (duration) {
    duration.addEventListener('blur', (event) => {
      event.target.value = Math.round(event.target.value * 4)/4;
    });
  }
});

