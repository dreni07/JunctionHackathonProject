import type { ImgHTMLAttributes } from 'react';

import { BRAND_LOGO_ALT, BRAND_LOGO_SRC } from '@/lib/brand';
import { cn } from '@/lib/utils';

type BrandLogoProps = ImgHTMLAttributes<HTMLImageElement> & {
    height?: number | string;
};

export function BrandLogo({
    alt = BRAND_LOGO_ALT,
    className,
    height = 40,
    style,
    ...props
}: BrandLogoProps) {
    return (
        <img
            src={BRAND_LOGO_SRC}
            alt={alt}
            className={cn('block w-auto max-w-full object-contain', className)}
            style={{
                height,
                width: 'auto',
                background: 'transparent',
                ...style,
            }}
            {...props}
        />
    );
}

export default BrandLogo;
