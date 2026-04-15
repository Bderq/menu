import React, { useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import * as LucideIcons from 'lucide-react';

const Icon = ({ name, ...props }) => {
    const LucideIcon = LucideIcons[name] || LucideIcons.HelpCircle;
    return <LucideIcon {...props} />;
};

export default function DefaultCampaignCard({ campaign, onClose, openDetails }) {
    const items = campaign.subcategories?.[0]?.items || [];
    const isBundle = campaign.type === 'bundle';
    const isCollective = campaign.type === 'collective';

    const totalListPrice = items.reduce((sum, i) => sum + (parseFloat(i.price) || 0), 0);
    const campaignPrice = parseFloat(campaign.value || 0);

    // Expert Body Scroll Lock (Prevents Safari/Chrome elastic scroll)
    useEffect(() => {
        const originalStyle = window.getComputedStyle(document.body).overflow;
        const scrollY = window.scrollY;
        
        // Block scrolling on body and html
        document.documentElement.style.overflow = 'hidden';
        document.documentElement.style.overscrollBehavior = 'none';
        document.body.style.overflow = 'hidden';
        document.body.style.overscrollBehavior = 'none';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollY}px`;
        document.body.style.width = '100%';

        return () => {
            document.documentElement.style.overflow = originalStyle;
            document.documentElement.style.overscrollBehavior = '';
            document.body.style.overflow = originalStyle;
            document.body.style.overscrollBehavior = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            window.scrollTo(0, scrollY);
        };
    }, []);

    return (
        <motion.div
            initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
            className="fixed inset-0 z-[200] flex flex-col bg-off-white h-[100dvh] overflow-hidden"
            style={{ touchAction: 'none', overscrollBehavior: 'contain' }} // Stop touch/scroll leakage
        >
            {/* Magazine Cover Hero */}
            <div className="relative w-full aspect-[4/3] bg-pitch-black overflow-hidden flex-shrink-0">
                {campaign.image ? (
                    <img
                        src={campaign.image.startsWith('http') ? campaign.image : `/storage/${campaign.image}`}
                        alt={campaign.name}
                        className="w-full h-full object-cover opacity-80"
                    />
                ) : (
                    <div className="w-full h-full flex flex-col items-center justify-center bg-pitch-black border-b-8 border-pub-gold p-12">
                         <Icon name="Zap" size={80} className="text-pub-gold animate-pulse mb-4" />
                         <div className="font-heading text-pub-gold text-2xl uppercase tracking-widest opacity-30">CAMP_PROTOCOLL</div>
                    </div>
                )}

                {/* Overlays */}
                <div className="absolute inset-0 bg-grunge-texture opacity-30 pointer-events-none mix-blend-overlay"></div>
                <div className="absolute inset-0 bg-gradient-to-t from-pitch-black via-transparent to-pitch-black/40 pointer-events-none"></div>

                {/* Floating Close Button - Adjusted for Notch/Safe Area */}
                <button 
                    onClick={onClose} 
                    className="absolute top-10 right-6 z-50 bg-white text-pitch-black p-2 border-2 border-pitch-black shadow-[4px_4px_0_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none hover:rotate-90 transition-transform duration-300"
                    style={{ marginTop: 'env(safe-area-inset-top, 0px)' }}
                >
                    <Icon name="X" size={24} />
                </button>

                {/* Magazine Title Overlay */}
                <div className="absolute bottom-12 left-6 right-6 z-20">
                    {/* Decorative Top Line */}
                    <div className="w-16 h-2 bg-pub-gold mb-4 shadow-[2px_2px_0_0_#000]"></div>

                    <h2 className="font-heading text-6xl uppercase italic tracking-tighter leading-[0.85] text-white drop-shadow-[4px_4px_0_rgba(0,0,0,1)] break-words">
                        {campaign.name}
                    </h2>
                    <p className="font-sans text-white/90 text-sm mt-3 font-bold max-w-[80%] drop-shadow-md leading-tight">
                        {campaign.description}
                    </p>
                </div>
            </div>

            {/* Content Overlap Wrapper */}
            <div className="flex-1 overflow-y-auto bg-off-white relative -mt-6 z-30 rounded-t-[2rem] border-t-4 border-pitch-black shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
                <div className="absolute inset-0 bg-grunge-texture opacity-10 pointer-events-none"></div>
                <div className="p-6 pt-8 relative z-10">
                    <div className="grid grid-cols-2 gap-4 pb-24">
                        {items.map(item => (
                            <motion.div
                                key={item.id}
                                initial={{ opacity: 0, scale: 0.98 }}
                                animate={{ opacity: 1, scale: 1 }}
                                onClick={() => openDetails?.(item)}
                                className="group relative flex flex-col bg-white border-2 border-pitch-black shadow-[4px_4px_0_0_#000] hover:shadow-[6px_6px_0_0_var(--color-pub-gold)] active:shadow-none active:translate-x-1 active:translate-y-1 transition-all cursor-pointer"
                            >
                                <div className="aspect-[4/3] bg-pitch-black overflow-hidden relative border-b-2 border-pitch-black">
                                    {item.image ? (
                                        <img src={item.image} alt={item.name} loading="lazy" className="w-full h-full object-cover" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center opacity-20">
                                            <Icon name="Zap" size={32} className="text-pub-gold" />
                                        </div>
                                    )}

                                    {/* Price Tag Logic - Matching Index.jsx */}
                                    <div className="absolute bottom-0 right-0 flex flex-col items-end z-20">
                                        {/* For Bundles, we just show the item price naturally as in menu 
                                            For other campaigns (fixed/percentage), we show the discount structure */}
                                        {isCollective && item.collective_tiers ? (
                                            (() => {
                                                const minPrice = Math.min(...item.collective_tiers.map(t => parseFloat(t.price)));
                                                return (
                                                    <div className="flex flex-col items-end p-1">
                                                        <div className="font-mono text-sm font-bold px-3 py-1 min-w-[60px] text-center shadow-[2px_2px_0_0_#000] border-l-2 border-t-2 border-pitch-black relative bg-pub-gold text-pitch-black mb-1">
                                                            ₺{parseFloat(item.price)}
                                                        </div>
                                                        <div className="font-mono text-[9px] font-black px-1.5 py-0.5 text-center shadow-[2px_2px_0_0_#000] border border-pitch-black relative bg-red-600 text-white flex-shrink-0 whitespace-nowrap z-20 rotate-0">
                                                            KOLEKTİF: ₺{minPrice}/AD
                                                        </div>
                                                    </div>
                                                );
                                            })()
                                        ) : !isBundle && item.campaign_price ? (
                                            <div className="flex flex-col items-end p-1">
                                                <span className="bg-pub-gold text-pitch-black text-[10px] font-bold px-1.5 py-0.5 line-through decoration-pitch-black/80 shadow-[2px_2px_0_0_#000] border border-pitch-black relative z-10 rotate-2 mb-0.5">
                                                    ₺{parseFloat(item.price)}
                                                </span>
                                                <div className={`font-mono text-sm font-bold px-3 py-1 min-w-[60px] text-center shadow-[2px_2px_0_0_#000] border-2 border-pitch-black relative bg-red-600 text-white -rotate-1`}>
                                                    ₺{parseFloat(item.campaign_price)}
                                                </div>
                                            </div>
                                        ) : (
                                            <div className={`font-mono text-sm font-bold px-3 py-1 min-w-[60px] text-center shadow-[2px_2px_0_0_#000] border-l-2 border-t-2 border-pitch-black relative bg-pub-gold text-pitch-black`}>
                                                ₺{parseFloat(item.price)}
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <div className="p-3 flex flex-col flex-1 bg-grunge-texture bg-repeat relative">
                                    <h4 className="font-heading leading-tight uppercase mb-1 line-clamp-2 min-h-[32px] group-hover:text-pub-gold transition-colors text-[13px]">
                                        {item.name}
                                    </h4>
                                    <p className="font-sans text-pitch-black/50 italic text-[10px] line-clamp-1">
                                        {item.description}
                                    </p>
                                </div>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Footer - The Receipt Style (Clean & Informative) */}
            {isBundle && (
                <div className="bg-pitch-black border-t-4 border-pub-gold p-6 pb-8 shadow-[0_-10px_30px_rgba(0,0,0,0.5)] relative z-20">
                    <div className="flex items-center justify-between font-mono text-xs text-white/40 mb-2">
                        <span>LİSTE FİYATI TOPLAMI</span>
                        <span className="line-through decoration-red-600 decoration-1 text-white/50">₺{totalListPrice}</span>
                    </div>

                    <div className="flex items-end justify-between">
                        <div className="flex flex-col">
                            <span className="font-heading text-2xl uppercase leading-none text-white tracking-wider">TOPLAM</span>
                        </div>
                        <div className="font-heading text-6xl leading-none text-pub-gold tracking-tighter drop-shadow-[0_2px_0_rgba(255,255,255,0.1)]">
                            <span className="text-2xl align-top mr-1 font-sans font-bold">₺</span>{campaignPrice}
                        </div>
                    </div>
                </div>

            )}
        </motion.div>
    );
}
