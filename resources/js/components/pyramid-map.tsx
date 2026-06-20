import {
    type CSSProperties,
    type MouseEvent,
    useEffect,
    useId,
    useState,
} from 'react';

export type MapPin = {
    id: string;
    x: number;
    y: number;
    label?: string;
    tone?: 'default' | 'highlight' | 'muted';
};

const PIN_STYLE = `
.pmap-wrap{position:relative;width:100%;line-height:0;user-select:none}
.pmap-img{display:block;width:100%;height:auto;border-radius:12px}
.pmap-pin{position:absolute;transform:translate(-50%,-50%);pointer-events:none}
.pmap-dot{width:13px;height:13px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.35)}
.pmap-dot.default{background:#10825B}
.pmap-dot.muted{background:#9A958B;opacity:.7;width:10px;height:10px}
.pmap-dot.highlight{background:#E0483A}
.pmap-ring{position:absolute;left:50%;top:50%;width:13px;height:13px;border-radius:50%;transform:translate(-50%,-50%);border:2px solid #E0483A;animation:pmap-pulse 1.6s ease-out infinite}
@keyframes pmap-pulse{0%{width:13px;height:13px;opacity:.9}100%{width:54px;height:54px;opacity:0}}
.pmap-label{position:absolute;left:50%;top:calc(50% + 12px);transform:translateX(-50%);white-space:nowrap;font-size:11px;font-weight:700;color:#fff;background:#E0483A;padding:2px 7px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.25)}
.pmap-label.default{background:#10825B}
.pmap-clickable{cursor:crosshair}
.pmap-missing{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;width:100%;min-height:320px;border:2px dashed #E0DCD3;border-radius:14px;background:#FBFAF7;color:#6E6E6E;text-align:center;padding:24px;line-height:1.5}
.pmap-missing b{color:#1A1A1A;font-size:15px;font-weight:700}
`;

/**
 * Renders the Pyramid floor plan with venue pins positioned by normalized
 * (0–1) coordinates. Used read-only to show an organizer their suggested
 * venue, and in click-to-place mode by the calibration tool.
 */
export function PyramidMap({
    src,
    pins,
    onPick,
    className,
    style,
}: {
    src: string;
    pins: MapPin[];
    onPick?: (point: { x: number; y: number }) => void;
    className?: string;
    style?: CSSProperties;
}) {
    const scope = useId();
    const [failed, setFailed] = useState(false);

    // Reset the error state when the image source changes (e.g. new floor).
    useEffect(() => {
        setFailed(false);
    }, [src]);

    const handleClick = (event: MouseEvent<HTMLDivElement>) => {
        if (!onPick) {
            return;
        }

        const rect = event.currentTarget.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width;
        const y = (event.clientY - rect.top) / rect.height;

        onPick({
            x: Math.min(1, Math.max(0, x)),
            y: Math.min(1, Math.max(0, y)),
        });
    };

    return (
        <div
            className={`pmap-wrap ${onPick ? 'pmap-clickable' : ''} ${className ?? ''}`}
            style={style}
            onClick={handleClick}
            data-scope={scope}
        >
            <style dangerouslySetInnerHTML={{ __html: PIN_STYLE }} />

            {failed ? (
                <div className="pmap-missing">
                    <b>Floor plan not uploaded yet</b>
                    <span>
                        An operations worker can add this floor's plan image in
                        Map&nbsp;setup, then this venue will be highlighted on it.
                    </span>
                </div>
            ) : (
                <img
                    className="pmap-img"
                    src={src}
                    alt="Pyramid floor plan"
                    onError={() => setFailed(true)}
                />
            )}

            {!failed &&
                pins.map((pin) => {
                const tone = pin.tone ?? 'default';

                return (
                    <span
                        key={pin.id}
                        className="pmap-pin"
                        style={{ left: `${pin.x * 100}%`, top: `${pin.y * 100}%` }}
                    >
                        {tone === 'highlight' && <span className="pmap-ring" />}
                        <span className={`pmap-dot ${tone}`} />
                        {pin.label && tone !== 'muted' && (
                            <span className={`pmap-label ${tone}`}>
                                {pin.label}
                            </span>
                        )}
                    </span>
                );
            })}
        </div>
    );
}
