import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import BrandLogo from '@/components/brand-logo';

/**
 * Centered black-and-white auth card on a light canvas, with a circular avatar
 * floating above the card. Secondary links live inside the card, below the
 * submit button (passed in by each page).
 */
export default function AuthMinimalLayout({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <>
            <Head title={title} />
            <div className="min-h-svh w-full bg-[#FAFAFA] px-4 py-10 text-neutral-900">
                <div className="mx-auto flex w-full max-w-[460px] flex-col items-center">
                    <BrandLogo height={56} />

                    <div className="mt-7 w-full rounded-[28px] border border-neutral-200 bg-white px-7 py-10 shadow-[0_2px_30px_rgba(0,0,0,0.05)] sm:px-10">
                        {children}
                    </div>
                </div>
            </div>
        </>
    );
}
