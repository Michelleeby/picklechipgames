import { store, getContext } from '@wordpress/interactivity';

const { state } = store(
    'pcg-interactive-blocks/dice-roller', {
    
        actions: {
            reset() {
                state.total = 0;
            },
            roll() {
                const context = getContext();
                if (context.rolling) { return;
                }
        
                context.rolling = true;
                context.imagePressed = true;
                context.landed = false;
                context.exploded = false;
                setTimeout(() => { context.imagePressed = false; }, 150);
            
                let rollCount = 0;
                const maxRolls = 18;
        
                function easeOutInterval(progress)
                {
                    const min = 40;
                    const max = 220;
                    return min + (max - min) * Math.pow(progress, 2);
                }
        
                function doRoll()
                {
                    if (rollCount < maxRolls) {
                        context.currentValue = Math.floor(Math.random() * context.sides) + 1;
                        rollCount++;
                        const progress = rollCount / maxRolls;
                        const interval = easeOutInterval(progress);
                        setTimeout(doRoll, interval);
                    } else {
                        // Final value
                        const finalValue = Math.floor(Math.random() * context.sides) + 1;
                        context.currentValue = finalValue;
                        state.total += finalValue;
                        context.rolling = false;
                        context.landed = true;
                        context.exploded = (finalValue === context.sides);
                        setTimeout(() => { context.landed = false; }, 600);
                    }
                }
                doRoll();
            }
        },
        callbacks: {
            logCurrentValue: () => {
                const { currentValue } = getContext();
                console.log(`currentValue: ${currentValue}`);
            },
            logSides: () => {
                const { sides } = getContext();
                console.log(`sides: ${sides}`);
            },
            logTotal: () => {
                const { total } = state;
                console.log(`total: ${total}`);
            }
        }
    }
);