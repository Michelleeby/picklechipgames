<?php
/**
 * Server-side rendering of the dice roller block.
 */
// Default global state for the dice roller block store.
wp_interactivity_state(
    'pcg-interactive-blocks/dice-roller', 
    array(
        'total' => 0,
    )
);

$wrapper_attributes = get_block_wrapper_attributes(
    [
    'class' => 'pcg-dice-roller',
    'data-wp-interactive' => 'pcg-interactive-blocks/dice-roller',
    'data-wp-watch' => 'callbacks.logTotal',
    ]
);
?>

<div
    <?php echo $wrapper_attributes; ?>
>
    <div class="pcg-dice-container">
        <?php echo $content; ?>
    </div>

    <div 
        class="pcg-total" 
        data-wp-text="state.total"
        aria-live="polite"
        aria-atomic="true"
    ></div>

    <button
        class="pcg-roll-reset"
        data-wp-on--click="actions.reset"
    >
        <?php esc_html_e('Reset', 'pcg-interactive-blocks'); ?>
    </button>
</div> 