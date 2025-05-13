/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	
	const handleImageUpload = (media) => {
		setAttributes({
			portrait: {
				id: media.id,
				url: media.url,
				title: media.title
			}
		});
	};

	const diceTypes = ['d4', 'd6', 'd8', 'd10', 'd12', 'd20'];
	const dieValues = [4, 6, 8, 10, 12, 20];

	const getDieValue = (dieType) => {
		const index = diceTypes.indexOf(dieType);
		return dieValues[index];
	};

	const handleDieChange = (statKey, direction) => {
		const currentDie = attributes[`${statKey}Die`] || 'd4';
		const currentIndex = diceTypes.indexOf(currentDie);
		let newIndex = currentIndex + direction;
		
		if (newIndex < 0) newIndex = 0;
		if (newIndex >= diceTypes.length) newIndex = diceTypes.length - 1;
		
		setAttributes({ [`${statKey}Die`]: diceTypes[newIndex] });
	};

	return (
		<div {...blockProps}>
			<div className="pcg-character-sheet">
				{/* Name Field */}
				<div className="pcg-name-field">
					<TextControl
						placeholder={__('Name', 'pcg-character-creator')}
						value={attributes.name || ''}
						onChange={(name) => setAttributes({ name })}
					/>
				</div>

				{/* Portrait */}
				<div className="pcg-portrait">
					<MediaUploadCheck>
						<MediaUpload
							onSelect={handleImageUpload}
							allowedTypes={['image']}
							value={attributes.portrait?.id}
							render={({ open }) => (
								<Button
									onClick={open}
									className="pcg-upload-button"
								>
									{attributes.portrait ? __('Change photo', 'pcg-character-creator') : __('Add photo', 'pcg-character-creator')}
								</Button>
							)}
						/>
					</MediaUploadCheck>
					{attributes.portrait ? (
						<img 
							src={attributes.portrait.url} 
							alt={attributes.portrait.title}
							className="pcg-portrait-image"
						/>
					) : (
						<span className="pcg-add-photo-text">{__('Add photo', 'pcg-character-creator')}</span>
					)}
				</div>

				{/* Age Field */}
				<div className="pcg-info-field">
					<TextControl
						placeholder={__('Age', 'pcg-character-creator')}
						value={attributes.age || ''}
						onChange={(age) => setAttributes({ age })}
					/>
				</div>

				{/* Pronouns Field */}
				<div className="pcg-info-field">
					<TextControl
						placeholder={__('Pronouns', 'pcg-character-creator')}
						value={attributes.pronouns || ''}
						onChange={(pronouns) => setAttributes({ pronouns })}
					/>
				</div>

				{/* Stats Section */}
				<div className="pcg-stats-section">
					<h3>{__('STATS', 'pcg-character-creator')}</h3>
					<div className="pcg-stats-grid">
						{[
							{ key: 'brains', label: 'Brains' },
							{ key: 'brawn', label: 'Brawn' },
							{ key: 'fight', label: 'Fight' },
							{ key: 'flight', label: 'Flight' },
							{ key: 'grit', label: 'Grit' },
							{ key: 'charm', label: 'Charm' },
						].map(stat => {
							const currentDie = attributes[`${stat.key}Die`] || 'd4';
							const dieIndex = diceTypes.indexOf(currentDie);
							
							return (
								<div key={stat.key} className="pcg-stat-row">
									<div className="pcg-stat-name">{stat.label}</div>
									<div className="pcg-stat-die-select">
										<button
											type="button"
											className="pcg-arrow-btn pcg-arrow-left"
											aria-label={__('Decrease Die', 'pcg-character-creator')}
											onClick={() => handleDieChange(stat.key, -1)}
											disabled={dieIndex === 0}
										/>
										<div className="pcg-stat-image-container">
											<img
												src={window.pcgCharacterCreator.getImageUrlBase + currentDie + window.pcgCharacterCreator.imageExtension}
												alt={currentDie}
												className="pcg-die-image"
											/>
										</div>
										<button
											type="button"
											className="pcg-arrow-btn pcg-arrow-right"
											aria-label={__('Increase Die', 'pcg-character-creator')}
											onClick={() => handleDieChange(stat.key, 1)}
											disabled={dieIndex === diceTypes.length - 1}
										/>
									</div>
									<div className="pcg-stat-value">{getDieValue(currentDie)}</div>
								</div>
							);
						})}
					</div>
				</div>
			</div>
		</div>
	);
}
