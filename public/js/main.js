//http://jsperf.com/escape-html-special-chars/11
function escapeHtml(text) {
	var map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	};
	return text.replace(/[&<>"']/g, function(m) {
		return map[m];
	});
}

$(document).ready(function() {
  $('#cookie-consent-btn').click(function(e) {
    $('.cookieconsent').hide();
    Cookies.set('allow-cookies', '1');
    e.preventDefault();
  });

  if(Cookies.get('allow-cookies') !== '1') {
    $('.cookieconsent').show();
  } else {
    Cookies.set('allow-cookies', '1');
  }
});
