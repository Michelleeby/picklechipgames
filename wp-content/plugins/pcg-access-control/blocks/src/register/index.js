import { registerBlockType } from '@wordpress/blocks';

registerBlockType('pcg/register', {
    title: 'PCG Registration Form',
    icon: 'admin-users',
    category: 'widgets',
    edit: () => 'PCG Registration Form (will render on front end)',
    save: () => null, // Dynamic block
}); 