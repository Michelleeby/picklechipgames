<?php
/**
 * Server-side render callback for the single die block.
 * 
 * @param array $attributes The array of attributes for the block.
 * @param string $content The markup of the block as stored in the database, if any.
 * @param WP_Block $block The instance of the WP_Block class that represents the
 * rendered block (metadata of the block).
 * 
 * @return string The rendered block.
 */
$unique_id = wp_unique_id('die-');
$upload_dir = wp_upload_dir();
$die_image_url = esc_url(
    sprintf( 
        '%s/d%d.png', 
        $upload_dir['baseurl'], 
        $attributes['sides']
    )
);
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
