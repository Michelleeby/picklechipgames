/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */
// plugins/pcg-character-creator/src/pcg-character-creator/view.js

console.log('PCG Character Creator view script loaded (frontend)');

function ready(fn) {
    if (document.readyState !== 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

ready(() => {
    setTimeout(() => {
        const wrapper = document.querySelector('.pcg-character-creator-wrapper');
        if (!wrapper) {
            console.error('No .pcg-character-creator-wrapper found');
            return;
        }
        
        try {
            // Define helper functions
            function getImageUrl(filename) {
                return window.pcgCharacterCreator.getImageUrl.replace(window.pcgCharacterCreator.filenamePlaceholder, filename);
            }

            // Create the main character sheet container
            const characterSheet = document.createElement('div');
            characterSheet.className = 'pcg-character-sheet';
            
            // Create and append name field
            const nameField = document.createElement('div');
            nameField.className = 'pcg-name-field';
            nameField.textContent = wrapper.dataset.name || 'Name';
            characterSheet.appendChild(nameField);
            
            // Create and append portrait
            const portrait = document.createElement('div');
            portrait.className = 'pcg-portrait';
            
            try {
                const portraitData = wrapper.dataset.portrait && wrapper.dataset.portrait !== 'null'
                    ? JSON.parse(wrapper.dataset.portrait)
                    : null;
                
                if (portraitData && portraitData.url) {
                    const img = document.createElement('img');
                    img.src = portraitData.url;
                    img.alt = portraitData.title || '';
                    img.className = 'pcg-portrait-image';
                    portrait.appendChild(img);
                } else {
                    const addPhotoText = document.createElement('span');
                    addPhotoText.className = 'pcg-add-photo-text';
                    addPhotoText.textContent = 'Add photo';
                    portrait.appendChild(addPhotoText);
                }
            } catch (e) {
                console.error('Error parsing portrait data:', e, wrapper.dataset.portrait);
                const addPhotoText = document.createElement('span');
                addPhotoText.className = 'pcg-add-photo-text';
                addPhotoText.textContent = 'Add photo';
                portrait.appendChild(addPhotoText);
            }
            
            characterSheet.appendChild(portrait);
            
            // Create and append age field with label
            const ageField = document.createElement('div');
            ageField.className = 'pcg-info-field';
            ageField.textContent = `Age: ${wrapper.dataset.age || '<value>'}`;
            characterSheet.appendChild(ageField);
            
            // Create and append pronouns field with label
            const pronounsField = document.createElement('div');
            pronounsField.className = 'pcg-info-field';
            pronounsField.textContent = `Pronouns: ${wrapper.dataset.pronouns || '<value>'}`;
            characterSheet.appendChild(pronounsField);
            
            // Create and append stats section
            const statsSection = document.createElement('div');
            statsSection.className = 'pcg-stats-section';
            
            const statsHeading = document.createElement('h3');
            statsHeading.textContent = 'STATS';
            statsSection.appendChild(statsHeading);
            
            const statsGrid = document.createElement('div');
            statsGrid.className = 'pcg-stats-grid';
            
            // Define stats and their values
            const stats = [
                { key: 'brains', label: 'Brains' },
                { key: 'brawn', label: 'Brawn' },
                { key: 'fight', label: 'Fight' },
                { key: 'flight', label: 'Flight' },
                { key: 'grit', label: 'Grit' },
                { key: 'charm', label: 'Charm' }
            ];
            
            const diceValues = {
                'd4': 4,
                'd6': 6,
                'd8': 8,
                'd10': 10,
                'd12': 12,
                'd20': 20
            };
            
            // Create each stat card
            stats.forEach(stat => {
                const die = wrapper.dataset[`${stat.key}die`] || 'd4';
                const dieValue = diceValues[die] || 4;
                
                // Create stat card
                const statCard = document.createElement('div');
                statCard.className = 'pcg-stat-card';
                
                // Add stat name
                const statName = document.createElement('div');
                statName.className = 'pcg-stat-name';
                statName.textContent = stat.label;
                statCard.appendChild(statName);
                
                // Add die image
                const statImageContainer = document.createElement('div');
                statImageContainer.className = 'pcg-stat-image-container';
                
                const dieImage = document.createElement('img');
                dieImage.src = window.pcgCharacterCreator.getImageUrlBase + die + window.pcgCharacterCreator.imageExtension;
                dieImage.alt = die;
                dieImage.className = 'pcg-die-image';
                
                statImageContainer.appendChild(dieImage);
                statCard.appendChild(statImageContainer);
                
                // Add value
                const value = document.createElement('div');
                value.className = 'pcg-stat-value';
                value.textContent = dieValue;
                statCard.appendChild(value);
                
                // Add the stat card to the grid
                statsGrid.appendChild(statCard);
            });
            
            statsSection.appendChild(statsGrid);
            characterSheet.appendChild(statsSection);
            
            // Clear the wrapper and append the new character sheet
            wrapper.innerHTML = '';
            wrapper.appendChild(characterSheet);
            
        } catch (error) {
            console.error('Error rendering character sheet:', error);
        }
    }, 0);
});