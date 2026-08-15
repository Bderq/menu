import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, ArrowRight } from 'lucide-react';

export default function WelcomePoster({ poster }) {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        if (!poster) return;

        // Show after a slight delay to allow the page to render fully
        const timer = setTimeout(() => {
            setIsVisible(true);
        }, 500);

        return () => clearTimeout(timer);
    }, [poster]);

    const handleClose = () => {
        setIsVisible(false);
    };

    if (!poster) return null;

    // Theme values
    const isLight = poster.color_scheme === 'light';
    const textColor = isLight ? 'text-pitch-black' : 'text-white';
    const bgOverlay = isLight ? 'bg-white/90' : 'bg-pitch-black/90';

    return (
        <AnimatePresence>
            {isVisible && (
                <>
                    {/* Backdrop */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.3 }}
                        className="fixed inset-0 z-[200] bg-black/60 backdrop-blur-sm"
                        onClick={handleClose}
                    />

                    {/* Poster Card Container */}
                    <motion.div
                        initial={{ opacity: 0, scale: 0.92, y: 20 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.92, y: 20 }}
                        transition={{ duration: 0.3, ease: 'easeOut' }}
                        className="fixed inset-0 z-[201] flex items-center justify-center p-4 md:p-6 pointer-events-none"
                    >
                        <div 
                            className={`relative w-full max-w-lg max-h-[85vh] overflow-y-auto pointer-events-auto shadow-[12px_12px_0_0_#000] border-4 border-pitch-black flex flex-col ${isLight ? 'bg-white' : 'bg-pitch-black'}`}
                            onClick={e => e.stopPropagation()}
                        >
                            {/* Close Button */}
                            <button 
                                onClick={handleClose}
                                className={`absolute top-3 right-3 z-20 p-2 rounded-full backdrop-blur-md ${isLight ? 'bg-black/10 hover:bg-black/20 text-black' : 'bg-white/10 hover:bg-white/20 text-white'} transition-colors`}
                            >
                                <X size={20} />
                            </button>

                            {/* Image Section (Top Half) */}
                            {poster.image_path && (
                                <div className="relative w-full aspect-[4/3] sm:aspect-[16/9] border-b-4 border-pitch-black overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    <img 
                                        src={`/storage/${poster.image_path}`} 
                                        alt={poster.title}
                                        className="absolute inset-0 w-full h-full object-cover"
                                    />
                                </div>
                            )}

                            {/* Content Section (Bottom Half) */}
                            <div className="p-6 md:p-8 flex flex-col flex-1 relative overflow-hidden">
                                
                                {/* Decoration lines for magazine aesthetic */}
                                <div className={`absolute top-0 left-0 w-1 h-full ${isLight ? 'bg-pitch-black/10' : 'bg-white/10'}`}></div>
                                
                                <div className="pl-4 flex flex-col h-full">
                                    {poster.badge_text && (
                                        <div className="mb-4">
                                            <span className={`inline-block font-heading text-[10px] sm:text-xs uppercase tracking-[0.2em] font-bold px-3 py-1 border-2 border-pitch-black ${isLight ? 'bg-pub-gold text-pitch-black' : 'bg-pub-gold text-pitch-black'} shadow-[2px_2px_0_0_#000]`}>
                                                {poster.badge_text}
                                            </span>
                                        </div>
                                    )}

                                    <h2 className={`font-heading text-4xl sm:text-5xl uppercase tracking-tighter leading-[0.9] mb-3 ${textColor}`}>
                                        {poster.title}
                                    </h2>

                                    {poster.subtitle && (
                                        <h3 className={`text-lg sm:text-xl font-medium mb-4 ${isLight ? 'text-pitch-black/70' : 'text-white/80'}`}>
                                            {poster.subtitle}
                                        </h3>
                                    )}

                                    {poster.body && (
                                        <p className={`text-sm leading-relaxed mb-8 ${isLight ? 'text-pitch-black/60' : 'text-white/60'}`}>
                                            {poster.body}
                                        </p>
                                    )}

                                    {/* Spacer to push button to bottom if needed */}
                                    <div className="flex-1"></div>

                                    {poster.cta_text && (
                                        <a 
                                            href={poster.cta_url || '#'}
                                            onClick={(e) => {
                                                if (!poster.cta_url || poster.cta_url === '#') {
                                                    e.preventDefault();
                                                    handleClose();
                                                }
                                            }}
                                            className={`mt-4 w-full text-left p-4 border-2 border-pitch-black font-bold uppercase tracking-tight shadow-[4px_4px_0_0_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all flex items-center justify-between group ${isLight ? 'bg-pitch-black text-white' : 'bg-pub-gold text-pitch-black'}`}
                                        >
                                            <span className="flex items-center gap-3">
                                                {poster.cta_text}
                                            </span>
                                            <ArrowRight size={20} className="group-hover:translate-x-1 transition-transform" />
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    </motion.div>
                </>
            )}
        </AnimatePresence>
    );
}
