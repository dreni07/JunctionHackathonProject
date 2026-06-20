import type { ImgHTMLAttributes } from 'react';

import { BRAND_LOGO_ALT, BRAND_LOGO_SRC } from '@/lib/brand';
import { cn } from '@/lib/utils';

export default function AppLogoIcon({
    className,
    alt = BRAND_LOGO_ALT,
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src={BRAND_LOGO_SRC}
            alt={alt}
            className={cn('block h-9 w-auto max-w-full object-contain', className)}
            style={{ background: 'transparent' }}
            {...props}
        />
    );
}
