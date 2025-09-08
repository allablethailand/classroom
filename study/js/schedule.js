$(document).ready(function() {
  let currentDate = new Date();
  const $currentDateSpan = $('#current-date');

  function formatDate(date) {
    const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
  }

  function updateDateDisplay() {
    $currentDateSpan.text(formatDate(currentDate));
    console.log('Load schedule for:', currentDate.toISOString().split('T')[0]);
  }

  $('#prev-day').on('click', function() {
    currentDate.setDate(currentDate.getDate() - 1);
    updateDateDisplay();
  });

  $('#next-day').on('click', function() {
    currentDate.setDate(currentDate.getDate() + 1);
    updateDateDisplay();
  });

  updateDateDisplay();
});
