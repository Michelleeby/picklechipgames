import { registerBlockType } from '@wordpress/blocks';

registerBlockType('pcg-campaign-core/example-block', {
    apiVersion: 2,
    title: 'Example Block',
    icon: 'smiley',
    category: 'widgets',
    edit: () => 'Hello from Example Block (edit mode)',
    save: () => null, // Dynamic block
}); 