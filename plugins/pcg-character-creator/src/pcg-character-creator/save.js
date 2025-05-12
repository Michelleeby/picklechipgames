/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	const blockProps = useBlockProps.save();
	
	// Add debug logging
	console.log('Save component: Saving block with attributes:', attributes);

	return (
		<div {...blockProps}>
			<div 
				className="pcg-character-creator-wrapper"
				data-name={attributes.name || ''}
				data-age={attributes.age || ''}
				data-pronouns={attributes.pronouns || ''}
				data-portrait={JSON.stringify(attributes.portrait || null)}
				data-backstory={attributes.backstory || ''}
				data-trait1={attributes.trait1 || ''}
				data-desc1={attributes.desc1 || ''}
				data-trait2={attributes.trait2 || ''}
				data-desc2={attributes.desc2 || ''}
				data-brains={attributes.brains || 0}
				data-brainsdie={attributes.brainsDie || 'd4'}
				data-brawn={attributes.brawn || 0}
				data-brawndie={attributes.brawnDie || 'd4'}
				data-fight={attributes.fight || 0}
				data-fightdie={attributes.fightDie || 'd4'}
				data-flight={attributes.flight || 0}
				data-flightdie={attributes.flightDie || 'd4'}
				data-grit={attributes.grit || 0}
				data-gritdie={attributes.gritDie || 'd4'}
				data-charm={attributes.charm || 0}
				data-charmdie={attributes.charmDie || 'd4'}
			>
				{/* The view script will populate this with the character sheet */}
			</div>
		</div>
	);
}
