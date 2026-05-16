import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Zap, X, BarChart2 } from 'lucide-react';
import PollResults from './PollResults';

export default function PollPopup({ storeSlug, visitorId }) {
    const [poll, setPoll] = useState(null);
    const [isVisible, setIsVisible] = useState(false);
    const [results, setResults] = useState(null);
    const [votedOptionId, setVotedOptionId] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        const fetchActivePoll = async () => {
            try {
                const response = await fetch(`/api/${storeSlug}/polls/active`, {
                    headers: {
                        'X-Visitor-Id': visitorId,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data && data.id) {
                    setPoll(data);
                    // Show after 10 seconds to not overwhelm (2nd visit rule)
                    setTimeout(() => setIsVisible(true), 10000);
                }
            } catch (error) {
                console.error('Failed to fetch poll:', error);
            }
        };

        if (storeSlug && visitorId) {
            fetchActivePoll();
        }
    }, [storeSlug, visitorId]);

    const handleVote = async (optionId) => {
        setIsSubmitting(true);
        try {
            const response = await fetch(`/api/${storeSlug}/polls/${poll.id}/vote`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Visitor-Id': visitorId,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ option_id: optionId })
            });

            if (response.ok) {
                const data = await response.json();
                setVotedOptionId(optionId);
                setResults(data);
            } else if (response.status === 409) {
                setIsVisible(false);
            }
        } catch (error) {
            console.error('Vote failed:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    if (!poll) return null;

    return (
        <AnimatePresence>
            {isVisible && (
                <motion.div
                    initial={{ y: 100, opacity: 0, scale: 0.9 }}
                    animate={{ y: 0, opacity: 1, scale: 1 }}
                    exit={{ y: 100, opacity: 0, scale: 0.9 }}
                    className="fixed bottom-24 left-6 right-6 z-[200] md:max-w-md md:left-auto md:right-10"
                >
                    <div className="bg-white border-4 border-pitch-black shadow-[8px_8px_0_0_#000] p-6 relative overflow-hidden">
                        {/* Background Decoration */}
                        <div className="absolute -top-4 -right-4 opacity-[0.03] rotate-12 pointer-events-none">
                            <BarChart2 size={120} />
                        </div>

                        <button 
                            onClick={() => setIsVisible(false)}
                            className="absolute top-3 right-3 text-pitch-black/40 hover:text-pitch-black transition-colors z-10"
                        >
                            <X size={20} />
                        </button>

                        <div className="flex items-center gap-2 mb-4">
                            <div className="bg-pub-gold p-1.5 border-2 border-pitch-black shadow-[2px_2px_0_0_#000]">
                                <Zap size={14} className="text-pub-gold-contrast" fill="currentColor" />
                            </div>
                            <span className="font-heading text-[10px] uppercase tracking-[0.2em]">{poll.title}</span>
                        </div>

                        <h3 className="font-heading text-2xl uppercase tracking-tighter leading-[0.9] mb-6">
                            {poll.question}
                        </h3>

                        {!results ? (
                            <div className="space-y-3">
                                {poll.options.map((option) => (
                                    <button
                                        key={option.id}
                                        onClick={() => handleVote(option.id)}
                                        disabled={isSubmitting}
                                        className="w-full text-left p-4 bg-white border-2 border-pitch-black font-bold uppercase tracking-tight shadow-[4px_4px_0_0_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all flex items-center justify-between group active:bg-pub-gold active:text-pub-gold-contrast"
                                    >
                                        <span className="flex items-center gap-3">
                                            {option.emoji && <span className="text-xl">{option.emoji}</span>}
                                            {option.text}
                                        </span>
                                        <motion.div
                                            whileHover={{ x: 5 }}
                                            className="text-pub-gold group-active:text-pub-gold-contrast opacity-0 group-hover:opacity-100"
                                        >
                                            <Zap size={16} fill="currentColor" />
                                        </motion.div>
                                    </button>
                                ))}
                            </div>
                        ) : (
                            <PollResults 
                                options={poll.options} 
                                results={results} 
                                votedOptionId={votedOptionId} 
                            />
                        )}
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
