import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { ClipboardList, Zap, Loader2 } from 'lucide-react';
import axios from 'axios';
import PollResults from './PollResults';

export default function PollDrawerTab({ storeSlug, visitorId }) {
    const [polls, setPolls] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchPolls = async () => {
        try {
            const response = await axios.get(`/api/${storeSlug}/polls`, {
                headers: {
                    'X-Visitor-Id': visitorId,
                    'Accept': 'application/json'
                }
            });
            setPolls(response.data);
        } catch (error) {
            console.error('Failed to fetch polls:', error);
            setPolls([]); // Fail gracefully
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (storeSlug) {
            fetchPolls();
        } else {
            setLoading(false);
        }
    }, [storeSlug, visitorId]);

    const handleVote = async (pollId, optionId) => {
        try {
            const response = await axios.post(`/api/${storeSlug}/polls/${pollId}/vote`, {
                option_id: optionId
            }, {
                headers: {
                    'X-Visitor-Id': visitorId,
                    'Accept': 'application/json'
                }
            });

            if (response.status === 200) {
                // Refresh specific poll or all polls
                fetchPolls();
            }
        } catch (error) {
            console.error('Vote failed:', error);
        }
    };

    if (loading) {
        return (
            <div className="h-full flex items-center justify-center min-h-[300px]">
                <Loader2 className="animate-spin text-pub-gold" size={48} strokeWidth={3} />
            </div>
        );
    }

    if (polls.length === 0) {
        return (
            <div className="h-full flex flex-col items-center justify-center text-center opacity-40 min-h-[300px]">
                <ClipboardList size={80} strokeWidth={1} className="mb-6" />
                <p className="font-heading text-2xl uppercase tracking-tighter">AKTİF ANKET BULUNAMADI</p>
                <p className="font-mono text-[10px] mt-2">Daha sonra tekrar kontrol edin.</p>
            </div>
        );
    }

    return (
        <div className="space-y-12 pb-20">
            <div className="bg-white border-4 border-pitch-black p-4 shadow-[6px_6px_0_0_var(--color-pub-gold)] rotate-1">
                <h3 className="font-heading text-3xl uppercase leading-none">Anketler</h3>
                <p className="font-mono text-[11px] text-pitch-black/60 font-bold mt-2">
                    Fikirlerin bizim için değerli. Mekanı birlikte yönetelim.
                </p>
            </div>

            {polls.map((poll) => (
                <div key={poll.id} className="space-y-6">
                    <div className="flex items-start gap-3">
                        <div className="w-2 h-8 bg-red-600 mt-1 shrink-0" />
                        <h4 className="font-heading text-2xl uppercase tracking-tight leading-none break-words">
                            {poll.question}
                        </h4>
                    </div>

                    {poll.voted_option_id ? (
                        <div className="bg-white border-2 border-pitch-black p-6 shadow-[4px_4px_0_0_rgba(0,0,0,0.05)]">
                             <PollResults 
                                options={poll.options} 
                                results={poll.results} 
                                votedOptionId={poll.voted_option_id} 
                            />
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-4">
                            {poll.options.map((option) => (
                                <button
                                    key={option.id}
                                    onClick={() => handleVote(poll.id, option.id)}
                                    className="w-full text-left p-4 bg-white border-4 border-pitch-black font-bold uppercase tracking-tight shadow-[6px_6px_0_0_#000] hover:shadow-none hover:translate-x-1.5 hover:translate-y-1.5 transition-all flex items-center justify-between group active:bg-pub-gold active:text-pub-gold-contrast"
                                >
                                    <span className="flex items-center gap-4 text-lg">
                                        {option.emoji && <span className="text-2xl">{option.emoji}</span>}
                                        {option.text}
                                    </span>
                                    <Zap size={20} className="text-pub-gold opacity-0 group-hover:opacity-100 group-active:text-pub-gold-contrast" fill="currentColor" />
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}
