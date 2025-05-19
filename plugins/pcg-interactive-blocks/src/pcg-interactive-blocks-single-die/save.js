import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes })
{
    const { sides, imageUrl } = attributes;
    const blockProps = useBlockProps.save();

    return (
        <div {...blockProps}>
            <div className="pcg-die">
                <div className="pcg-die-container">
                    <img src={imageUrl} alt="Die" className="pcg-die-image" />
                    <div className="pcg-die-value" data-wp-context={`{ "currentValue": 1, "sides": ${sides} }`}></div>
                </div>
            </div>
        </div>
    );
} 