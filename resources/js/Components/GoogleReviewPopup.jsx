import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Zap, X, Star, Heart, MessageSquare } from 'lucide-react';

export default function GoogleReviewPopup({ 
    storeSlug, 
    visitCount, 
    visitDaysCount,
    googleReviewUrl, 
    googleReviewQuestion, 
    isTestMode = false,
    onOpenSesVer 
}) {
    const [isVisible, setIsVisible] = useState(false);
    const [step, setStep] = useState(1); // 1: Question, 2: Redirect
    const [interactionId, setInteractionId] = useState(null);

    useEffect(() => {
        // Validation: Must have data
        if (!googleReviewUrl || !googleReviewQuestion) return;
        
        if (!isTestMode) {
            // Requirement: Must be 2nd distinct business day or more
            if (visitDaysCount < 2) return;
            
            const isSeen = localStorage.getItem(`google_review_seen_${storeSlug}`);
            if (isSeen) return;
        }

        const timer = setTimeout(() => {
            setIsVisible(true);
            
            // Log interaction: showed
            fetch(`/api/${storeSlug}/review-interaction`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.id) setInteractionId(data.id);
            })
            .catch(err => console.error('Failed to log interaction showed:', err));

        }, isTestMode ? 500 : 10000);

        return () => clearTimeout(timer);
    }, [storeSlug, visitCount, googleReviewUrl, googleReviewQuestion, isTestMode]);

    const handleDismiss = () => {
        localStorage.setItem(`google_review_seen_${storeSlug}`, '1');
        setIsVisible(false);

        if (interactionId) {
            fetch(`/api/${storeSlug}/review-interaction/${interactionId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ status: 'dismissed' })
            }).catch(err => console.error('Failed to log interaction dismissed:', err));
        }
    };

    const handleYes = () => {
        setStep(2);
        if (interactionId) {
            fetch(`/api/${storeSlug}/review-interaction/${interactionId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ status: 'accepted' })
            }).catch(err => console.error('Failed to log interaction accepted:', err));
        }
    };

    const handleNo = () => {
        localStorage.setItem(`google_review_seen_${storeSlug}`, '1');
        setIsVisible(false);

        if (interactionId) {
            fetch(`/api/${storeSlug}/review-interaction/${interactionId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ status: 'rejected' })
            }).catch(err => console.error('Failed to log interaction rejected:', err));
        }

        if (onOpenSesVer) onOpenSesVer(interactionId);
    };

    const handleGoogleRedirect = () => {
        handleDismiss();
        
        if (interactionId) {
            fetch(`/api/${storeSlug}/review-interaction/${interactionId}/google-clicked`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            }).catch(err => console.error('Failed to log interaction googleClicked:', err));
        }

        window.open(googleReviewUrl, '_blank');
    };

    if (!isVisible) return null;

    return (
        <AnimatePresence>
            {isVisible && (
                <motion.div
                    initial={{ y: 100, opacity: 0, scale: 0.9 }}
                    animate={{ y: 0, opacity: 1, scale: 1 }}
                    exit={{ y: 100, opacity: 0, scale: 0.9 }}
                    className="fixed bottom-24 left-6 right-6 z-[199] md:max-w-md md:left-auto md:right-10"
                >
                    <div className="bg-white border-4 border-pitch-black shadow-[8px_8px_0_0_#000] p-6 relative overflow-hidden">
                        {/* Background Decoration */}
                        <div className="absolute -top-4 -right-4 opacity-[0.03] rotate-12 pointer-events-none">
                            <Star size={120} />
                        </div>

                        <button 
                            onClick={handleDismiss}
                            className="absolute top-3 right-3 text-pitch-black/40 hover:text-pitch-black transition-colors z-10"
                        >
                            <X size={20} />
                        </button>

                        <div className="flex items-center gap-2 mb-4">
                            <div className="bg-pub-gold p-1.5 border-2 border-pitch-black shadow-[2px_2px_0_0_#000]">
                                <Heart size={14} className="text-pub-gold-contrast" fill="currentColor" />
                            </div>
                            <span className="font-heading text-[10px] uppercase tracking-[0.2em]">Memnuniyet</span>
                        </div>

                        {step === 1 ? (
                            <>
                                <h3 className="font-heading text-2xl uppercase tracking-tighter leading-[0.9] mb-6">
                                    {googleReviewQuestion}
                                </h3>

                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        onClick={handleYes}
                                        className="text-left p-4 bg-white border-2 border-pitch-black font-bold uppercase tracking-tight shadow-[4px_4px_0_0_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all flex items-center justify-between group active:bg-pub-gold active:text-pub-gold-contrast"
                                    >
                                        <span className="flex items-center gap-3">
                                            <span className="text-xl">❤️</span>
                                            Evet
                                        </span>
                                    </button>
                                    <button
                                        onClick={handleNo}
                                        className="text-left p-4 bg-white border-2 border-pitch-black font-bold uppercase tracking-tight shadow-[4px_4px_0_0_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all flex items-center justify-between group active:bg-pitch-black active:text-white"
                                    >
                                        <span className="flex items-center gap-3">
                                            <span className="text-xl">😐</span>
                                            Hayır
                                        </span>
                                    </button>
                                </div>
                            </>
                        ) : (
                            <div className="text-center py-2">
                                <h3 className="font-heading text-2xl uppercase tracking-tighter leading-[0.9] mb-6">
                                    Harika! Bize Google'da destek olur musun?
                                </h3>
                                
                                <button
                                    onClick={handleGoogleRedirect}
                                    className="w-full text-left p-4 bg-pub-gold text-pub-gold-contrast border-2 border-pitch-black font-bold uppercase tracking-tight shadow-[4px_4px_0_0_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all flex items-center justify-between group"
                                >
                                    <span className="flex items-center gap-3">
                                        <Star size={20} fill="currentColor" />
                                        Google'da Değerlendir
                                    </span>
                                    <Zap size={16} fill="currentColor" className="animate-pulse" />
                                </button>
                                
                                <p className="mt-4 font-mono text-[9px] uppercase opacity-50">
                                    Geri bildirimin bizim için çok değerli.
                                </p>
                            </div>
                        )}
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
