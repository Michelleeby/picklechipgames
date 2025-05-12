/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import metadata from './block.json';

// Add debug logging
console.log('Registering PCG Character Creator block');

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType(metadata.name, {
	...metadata,
	edit: Edit,
	save,
});

// Add debug logging for view script
document.addEventListener('DOMContentLoaded', () => {
	console.log('DOM Content Loaded');
	const wrapper = document.querySelector('.pcg-character-creator-wrapper');
	console.log('Wrapper element:', wrapper);
	if (wrapper) {
		console.log('Wrapper dataset:', wrapper.dataset);
	}
});

// Register Custom Post Type for Character Image Gallery
register_post_type('pcg_character_gallery', {
	labels: {
		name: __('Character Galleries', 'pcg-character-creator'),
		singular_name: __('Character Gallery', 'pcg-character-creator'),
	},
	public: true,
	show_in_rest: true,
	supports: ['title', 'thumbnail'],
	menu_icon: 'dashicons-format-gallery',
	rewrite: {
		slug: 'character-gallery'
	}
});

// Register meta fields for gallery-character relationship
register_post_meta('pcg_character_gallery', 'character_id', {
	type: 'string',
	single: true,
	show_in_rest: true,
	sanitize_callback: 'sanitize_text_field',
	auth_callback: function() {
		return current_user_can('edit_posts');
	}
});
