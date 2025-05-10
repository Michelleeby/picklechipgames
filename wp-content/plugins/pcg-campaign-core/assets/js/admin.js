jQuery(document).ready(function($) {
    $('#reset-stats').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to reset all cache statistics? This cannot be undone.')) {
            return;
        }
        
        $.ajax({
            url: campaignCore.ajaxUrl,
            type: 'POST',
            data: {
                action: 'campaign_core_reset_stats',
                nonce: campaignCore.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to reset statistics. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
}); 