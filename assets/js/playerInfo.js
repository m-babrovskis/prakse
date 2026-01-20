$(function() {
    $('#logoutButton').on('click', function() {
        if (confirm('Are you sure you want to logout?')) {
            fetch('/assets/php/logout.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/auth';
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                window.location.href = '/auth';
            });
        }
    });
});