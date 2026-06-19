import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

/**
 * Centered black-and-white auth card on a light canvas, with the helper links
 * pinned to the top-right and a circular avatar floating above the card.
 */
export default function AuthMinimalLayout({
    title,
    topRight,
    children,
}: {
    title: string;
    topRight: ReactNode;
    children: ReactNode;
}) {
    return (
        <>
            <Head title={title} />
            <div className="relative min-h-svh w-full bg-[#FAFAFA] px-4 py-8 text-neutral-900">
                <div className="absolute top-5 right-5 text-right text-sm leading-6 text-neutral-500 sm:top-7 sm:right-8">
                    {topRight}
                </div>

                <div className="mx-auto flex w-full max-w-[460px] flex-col items-center pt-14 sm:pt-8">
                    <div className="size-14 rounded-full bg-neutral-300" />

                    <div className="mt-7 w-full rounded-[28px] border border-neutral-200 bg-white px-7 py-10 shadow-[0_2px_30px_rgba(0,0,0,0.05)] sm:px-10">
                        {children}
                    </div>
                </div>
            </div>
        </>
    );
}
