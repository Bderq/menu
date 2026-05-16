import React from 'react';
import { Head } from '@inertiajs/react';

export default function StreetLayout({ children, title }) {
    return (
        <div className="min-h-screen bg-off-white text-pitch-black font-sans selection:bg-pub-gold selection:text-crash-white">
            <Head title={title}>
                <meta name="description" content={`${title} - QR Menü ve Dijital Sipariş Deneyimi`} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={`${title} - En güncel ürünler ve kampanyalar için menümüze göz atın.`} />
                <meta property="og:type" content="website" />
            </Head>

            {/* Background Texture / Subtle Polish */}
            <div className="fixed inset-0 pointer-events-none opacity-[0.4] mix-blend-multiply bg-grunge"></div>

            <main className="max-w-md mx-auto pb-24">
                {children}
            </main>

            {/* Branding is now primarily in the footer or embedded in UI elements */}
        </div>
    );
}
