jQuery(document).ready(function($) {
    if (typeof PCGRoute99 !== 'undefined' && PCGRoute99.campaignId) {
        // Try common field names, adjust as needed
        var $campaignField = $(
            'input[name="campaign_slug"], input[name="campaign_id"], input[name="campaign_slug_id"], input[name="associated_campaign"]'
        );
        if ($campaignField.length && !$campaignField.val()) {
            $campaignField.val(PCGRoute99.campaignId);
        }
    }
}); 