import { SVG, Path } from '@wordpress/primitives';

const DICE = {
    4: (
        <SVG width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
            <title>Four-sided die (d4)</title>
            <Path d="M9 2 L16 16 H2 Z" stroke="black" strokeWidth="1.2" fill="none"/>
            <Path d="M9 2 V16" stroke="black" strokeWidth="1"/>
            <Path d="M2 16 L9 8" stroke="black" strokeWidth="1"/>
            <Path d="M16 16 L9 8" stroke="black" strokeWidth="1"/>
            <text x="9" y="14" fontSize="4" textAnchor="middle" fill="black" fontFamily="Arial" className="screen-reader-text">4</text>
        </SVG>
    ),
    6: (
        <SVG width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
            <title>Six-sided die (d6)</title>
            <Path d="M3 3 H15 V15 H3 Z" stroke="black" strokeWidth="1.2" fill="none"/>
            <text x="9" y="11" fontSize="5" textAnchor="middle" fill="black" fontFamily="Arial" className="screen-reader-text">6</text>
        </SVG>
    ),
    8: (
        <SVG width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
            <title>Eight-sided die (d8)</title>
            <Path d="M9 2 L16 9 L9 16 L2 9 Z" stroke="black" strokeWidth="1.2" fill="none"/>
            <Path d="M9 2 V16" stroke="black" strokeWidth="1"/>
            <Path d="M2 9 H16" stroke="black" strokeWidth="1"/>
            <text x="9" y="11" fontSize="5" textAnchor="middle" fill="black" fontFamily="Arial" className="screen-reader-text">8</text>
        </SVG>
    ),
    10: (
        <SVG width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
            <title>Ten-sided die (d10)</title>
            <Path d="M9 2 L16 9 L13 16 L5 16 L2 9 Z" stroke="black" strokeWidth="1.2" fill="none"/>
            <text x="9" y="12" fontSize="4" textAnchor="middle" fill="black" fontFamily="Arial" className="screen-reader-text">10</text>
        </SVG>
    ),
    12: (
        <SVG width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
            <title>Twelve-sided die (d12)</title>
            <Path d="M9 2 L14 4 L16 9 L14 14 L9 16 L4 14 L2 9 L4 4 Z" stroke="black" strokeWidth="1.2" fill="none"/>
            <text x="9" y="11" fontSize="4" textAnchor="middle" fill="black" fontFamily="Arial" className="screen-reader-text">12</text>
        </SVG>
    ),
    20: (
        <SVG width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
            <title>Twenty-sided die (d20)</title>
            <Path d="M9 2 L16 5 L16 13 L9 16 L2 13 L2 5 Z" stroke="black" strokeWidth="1.2" fill="none"/>
            <text x="9" y="11" fontSize="4" textAnchor="middle" fill="black" fontFamily="Arial" className="screen-reader-text">20</text>
        </SVG>
    )
}

export const dice = Object.keys(DICE).map(key => ({
    name: `d${key}`,
    title: `D${key}`,
    icon: DICE[key],
    attributes: {
        sides: parseInt(key),
    },

    isDefault: key === '6'
}));