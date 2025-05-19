/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';
import './editor.scss';

/**
 * Internal dependencies
 */
import metadata from './block.json';

const ALLOWED_BLOCKS = ['pcg-interactive-blocks/single-die'];
const TEMPLATE = [
    ['pcg-interactive-blocks/single-die', { sides: 4 }],
    ['pcg-interactive-blocks/single-die', { sides: 6 }],
    ['pcg-interactive-blocks/single-die', { sides: 8 }],
    ['pcg-interactive-blocks/single-die', { sides: 10 }],
    ['pcg-interactive-blocks/single-die', { sides: 12 }],
    ['pcg-interactive-blocks/single-die', { sides: 20 }]
];

function Edit()
{
    const blockProps = useBlockProps();

    return (
        <div {...blockProps}>
            <div className="pcg-dice-roller">
                <InnerBlocks
                    allowedBlocks={ALLOWED_BLOCKS}
                    template={TEMPLATE}
                    templateLock={true}
                />
            </div>
        </div>
    );
}

function Save()
{
    const blockProps = useBlockProps.save();
    
    return (
        <div {...blockProps}>
            <div className="pcg-dice-roller">
                <InnerBlocks.Content />
            </div>
        </div>
    );
}

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType(
    metadata.name, {
        ...metadata,
        edit: Edit,
        save: Save
    }
);