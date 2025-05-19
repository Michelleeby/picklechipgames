import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
    const { sides, imageUrl } = attributes;
    const blockProps = useBlockProps();

    // Get the upload URL from WordPress settings
    const uploadUrl = useSelect((select) => {
        const settings = select('core').getEntityRecord('root', 'site');
        return settings?.upload_url || '';
    }, []);

    // Update imageUrl and sides when sides changes or on initial mount
    useEffect(() => {
        if (uploadUrl && sides) {
            const updates = {
                imageUrl: `${uploadUrl}/d${sides}.png`,
                sides: sides,
            };
            
            setAttributes(updates);
        }
    }, [uploadUrl, sides]);

    return (
        <div {...blockProps}>
            <div className="pcg-die">
                <img 
                    src={imageUrl || `${uploadUrl}/d${sides}.png`}
                    alt={__('Die', 'pcg-interactive-blocks')}
                    className="pcg-die-image"
                />
            </div>
        </div>
    );
} 