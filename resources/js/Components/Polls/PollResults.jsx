import React from 'react';
import { motion } from 'framer-motion';

export default function PollResults({ options, results, votedOptionId }) {
    return (
        <div className="space-y-4">
            <div className="space-y-3">
                {options.map((option) => {
                    const result = results.results?.find(r => r.id === option.id);
                    const isVoted = votedOptionId === option.id;
                    
                    return (
                        <div key={option.id} className="relative">
                            <div className="flex justify-between text-[10px] font-bold mb-1 uppercase tracking-tighter">
                                <span className="flex items-center gap-2">
                                    {isVoted && <span className="text-red-600 animate-pulse">●</span>}
                                    {option.emoji && <span>{option.emoji}</span>}
                                    {option.text}
                                </span>
                                <span>{result?.percentage || 0}%</span>
                            </div>
                            <div className="h-4 bg-gray-100 border-2 border-pitch-black overflow-hidden relative shadow-[2px_2px_0_0_rgba(0,0,0,0.1)]">
                                <motion.div
                                    initial={{ width: 0 }}
                                    animate={{ width: `${result?.percentage || 0}%` }}
                                    transition={{ duration: 1, ease: "easeOut" }}
                                    className={`h-full ${isVoted ? 'bg-red-600' : 'bg-pub-gold'}`}
                                />
                            </div>
                        </div>
                    );
                })}
            </div>
            <div className="flex justify-between items-center mt-6">
                <span className="bg-pitch-black text-white px-2 py-0.5 font-mono text-[8px] uppercase tracking-widest">
                    SONUÇLAR GÜNCEL
                </span>
                <p className="text-[10px] font-mono text-pitch-black/40 text-right uppercase">
                    Toplam Katılım: {results.total_votes}
                </p>
            </div>
        </div>
    );
}
