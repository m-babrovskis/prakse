$(document).ready(function() {
    $('#settingsButton').click(function() {
        if ($('#settingsModal').is(':visible')) {
            $('#settingsModal').hide();
        } else {
            $('#settingsModal').show();
        }
    });

    $('#closeSettingsBtn').click(function() {
        $('#settingsModal').hide();
    });
});
