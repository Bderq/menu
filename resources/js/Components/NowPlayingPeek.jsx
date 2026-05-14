import React, { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Music } from 'lucide-react';

export default function NowPlayingPeek({ storeSlug, isDrawerOpen, onMusicStatusChange }) {
    const [track, setTrack] = useState(null);
    const [isPeeking, setIsPeeking] = useState(false);
    const [prevTrackId, setPrevTrackId] = useState(null);
    const peekTimerRef = useRef(null);

    const fetchTrack = async () => {
        if (!storeSlug) return;
        try {
            const response = await fetch(`/api/${storeSlug}/now-playing`);
            const data = await response.json();

            if (data && data.is_playing) {
                const trackId = `${data.artist}-${data.track}`;
                
                if (trackId !== prevTrackId) {
                    setTrack(data);
                    setPrevTrackId(trackId);
                    if (!isDrawerOpen) {
                        triggerPeek();
                    }
                } else {
                    setTrack(data);
                }
                
                if (onMusicStatusChange) onMusicStatusChange(true);
            } else {
                setTrack(null);
                setPrevTrackId(null);
                setIsPeeking(false);
                if (onMusicStatusChange) onMusicStatusChange(false);
            }
        } catch (error) {
            console.error('Error fetching now playing for peek:', error);
            if (onMusicStatusChange) onMusicStatusChange(false);
        }
    };

    const triggerPeek = () => {
        setIsPeeking(true);
        if (peekTimerRef.current) clearTimeout(peekTimerRef.current);
        peekTimerRef.current = setTimeout(() => {
            setIsPeeking(false);
        }, 5000);
    };

    useEffect(() => {
        fetchTrack();
        const interval = setInterval(fetchTrack, 10000);
        return () => {
            clearInterval(interval);
            if (peekTimerRef.current) clearTimeout(peekTimerRef.current);
        };
    }, [storeSlug]);

    // If drawer opens while peeking, hide peek
    useEffect(() => {
        if (isDrawerOpen && isPeeking) {
            setIsPeeking(false);
        }
    }, [isDrawerOpen]);

    if (!track || !track.is_playing) return null;

    return (
        <AnimatePresence>
            {isPeeking && !isDrawerOpen && (
                <motion.div
                    initial={{ x: 300, opacity: 0 }}
                    animate={{ x: 0, opacity: 1 }}
                    exit={{ x: 300, opacity: 0 }}
                    transition={{ type: "spring", stiffness: 100, damping: 20 }}
                    className="fixed bottom-28 right-6 z-[90] pointer-events-none"
                >
                    <div className="bg-pitch-black border-4 border-white shadow-[8px_8px_0_0_rgba(0,0,0,1)] p-3 flex items-center gap-3 max-w-[280px] sm:max-w-[320px] -rotate-1 transform-gpu">
                        {/* Album Art Thumbnail */}
                        <div className="w-12 h-12 shrink-0 bg-pub-gold border-2 border-white flex items-center justify-center shadow-[2px_2px_0_0_rgba(255,255,255,0.3)] overflow-hidden">
                            {track.image ? (
                                <img src={track.image} alt="Art" className="w-full h-full object-cover" />
                            ) : (
                                <Music size={20} strokeWidth={2.5} className="text-pitch-black" />
                            )}
                        </div>

                        {/* Info Panel */}
                        <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 mb-1">
                                {/* EQ Bars */}
                                <div className="flex gap-0.5 items-end h-2.5">
                                    {[1, 2, 3].map(i => (
                                        <motion.span
                                            key={i}
                                            animate={{ height: [3, 10, 3] }}
                                            transition={{ repeat: Infinity, duration: 0.6, delay: i * 0.15 }}
                                            className="w-0.5 bg-pub-gold"
                                        ></motion.span>
                                    ))}
                                </div>
                                <span className="font-mono text-[7px] uppercase font-bold tracking-widest text-white/40">
                                    NOW_PLAYING
                                </span>
                            </div>
                            
                            <h4 className="text-pub-gold font-heading text-sm uppercase tracking-tighter leading-none mb-1 truncate drop-shadow-[1px_1px_0_rgba(255,255,255,0.1)]">
                                {track.track}
                            </h4>
                            
                            <div className="flex">
                                <div className="bg-white text-pitch-black px-1 py-0.5 font-mono text-[7px] font-bold uppercase tracking-widest transform -skew-x-6 border-b-1 border-r-1 border-gray-400 truncate">
                                    {track.artist}
                                </div>
                            </div>
                        </div>

                        {/* Static Noise Overlay */}
                        <div className="absolute inset-0 pointer-events-none opacity-[0.05]"
                            style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E")` }}
                        />
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
