<?php
$unique_id = wp_unique_id('die-');
$upload_dir = wp_upload_dir();
$die_image_url = esc_url($upload_dir['baseurl'] . '/d' . $attributes['sides'] . '.png');
$attributes['imageUrl'] = $die_image_url;
$sides = intval($attributes['sides']);

$attributes_data = array(
    'class' => 'pcg-die',
    'data-wp-interactive' => 'pcg-interactive-blocks/dice-roller',
    'data-die-id' => $unique_id,
);
$context_data = array(
    'sides' => $sides,
    'currentValue' => 1,
);
?>

<div
    <?php echo get_block_wrapper_attributes($attributes_data); ?>
    <?php echo wp_interactivity_data_wp_context($context_data); ?>
    data-wp-watch="callbacks.logSides"
    data-wp-watch="callbacks.logCurrentValue"
    data-wp-on--click="actions.roll"
>
    <div class="pcg-die-container">
        <img 
            src="<?php echo esc_url($die_image_url); ?>"
            alt="<?php esc_attr_e('Die', 'pcg-interactive-blocks'); ?>"
            class="pcg-die-image"
            data-wp-class--pressed="context.imagePressed"
        />
    </div>
    <div 
        class="pcg-die-value"
        data-wp-class--rolling="context.rolling"
        data-wp-class--landed="context.landed"
        data-wp-class--exploded="context.exploded"
        data-wp-text="context.currentValue"
    ></div>
</div>
