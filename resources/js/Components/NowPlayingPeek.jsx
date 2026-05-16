import React, { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Music } from 'lucide-react';

const ScrollingText = ({ text, className }) => {
    const containerRef = useRef(null);
    const textRef = useRef(null);
    const [scrollAmount, setScrollAmount] = useState(0);

    useEffect(() => {
        if (containerRef.current && textRef.current) {
            const containerWidth = containerRef.current.clientWidth;
            const textWidth = textRef.current.scrollWidth;
            if (textWidth > containerWidth) {
                setScrollAmount(textWidth - containerWidth + 24); // Add some padding so we see the end
            } else {
                setScrollAmount(0);
            }
        }
    }, [text]);

    return (
        <div ref={containerRef} className={`overflow-hidden whitespace-nowrap relative ${className}`}>
            <motion.div
                ref={textRef}
                className="inline-block min-w-full"
                animate={scrollAmount > 0 ? { x: [0, -scrollAmount] } : { x: 0 }}
                transition={{
                    delay: 1.5,
                    duration: 3,
                    ease: "linear"
                }}
            >
                {text}
            </motion.div>
            {scrollAmount > 0 && (
                <div className="absolute right-0 top-0 bottom-0 w-6 bg-gradient-to-l from-pitch-black to-transparent pointer-events-none z-10"></div>
            )}
        </div>
    );
};

export default function NowPlayingPeek({ storeSlug, isDrawerOpen, onMusicStatusChange }) {
    const [track, setTrack] = useState(null);
    const [isPeeking, setIsPeeking] = useState(false);
    
    // Use refs to avoid closure staleness in setInterval
    const lastPeekedTrackRef = useRef(null);
    const isDrawerOpenRef = useRef(isDrawerOpen);
    const peekTimerRef = useRef(null);

    useEffect(() => {
        isDrawerOpenRef.current = isDrawerOpen;
    }, [isDrawerOpen]);

    const fetchTrack = async () => {
        if (!storeSlug) return;
        try {
            const response = await fetch(`/api/${storeSlug}/now-playing`);
            const data = await response.json();

            if (data && data.is_playing) {
                // Check if the track has changed (or if it's the first load)
                const currentTrackId = data.url || data.track;
                
                if (lastPeekedTrackRef.current !== currentTrackId) {
                    setTrack(data);
                    lastPeekedTrackRef.current = currentTrackId;
                    
                    if (!isDrawerOpenRef.current) {
                        triggerPeek();
                    }
                } else {
                    setTrack(data);
                }
                
                if (onMusicStatusChange) onMusicStatusChange(true);
            } else {
                setTrack(null);
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
                    className="fixed bottom-6 right-24 z-[90] pointer-events-none"
                >
                    <div className="bg-pitch-black border-4 border-white shadow-[8px_8px_0_0_rgba(0,0,0,1)] flex items-center gap-3 min-w-[260px] max-w-[320px] rotate-1 transform-gpu h-20 overflow-hidden relative">
                        {/* Gold Accent Bar */}
                        <div className="w-3 h-full bg-pub-gold shrink-0 border-r-2 border-white z-10"></div>

                        {/* Album Art Thumbnail */}
                        <div className="w-14 h-14 shrink-0 bg-pub-gold border-2 border-white flex items-center justify-center shadow-[2px_2px_0_0_rgba(255,255,255,0.3)] overflow-hidden z-10">
                            {track.image ? (
                                <img src={track.image} alt="Art" className="w-full h-full object-cover" />
                            ) : (
                                <Music size={24} strokeWidth={2.5} className="text-pitch-black" />
                            )}
                        </div>

                        {/* Info Panel */}
                        <div className="flex-1 min-w-0 py-2 pr-3 z-10 flex flex-col justify-center">
                            <div className="flex items-center gap-2 mb-1">
                                {/* EQ Bars */}
                                <div className="flex gap-0.5 items-end h-3">
                                    {[1, 2, 3].map(i => (
                                        <motion.span
                                            key={i}
                                            animate={{ height: [4, 12, 4] }}
                                            transition={{ repeat: Infinity, duration: 0.6, delay: i * 0.15 }}
                                            className="w-1 bg-pub-gold"
                                        ></motion.span>
                                    ))}
                                </div>
                                <span className="font-mono text-[8px] uppercase font-bold tracking-widest text-white/40">
                                    NOW_PLAYING
                                </span>
                            </div>
                            
                            <ScrollingText 
                                text={track.track}
                                className="text-pub-gold font-heading text-base uppercase tracking-tighter leading-none mb-1.5 drop-shadow-[1px_1px_0_rgba(255,255,255,0.2)]"
                            />
                            
                            <div className="flex">
                                <div className="bg-white text-pitch-black px-1.5 py-0.5 font-mono text-[9px] font-bold uppercase tracking-widest transform -skew-x-6 border-b-2 border-r-2 border-gray-400 truncate">
                                    {track.artist}
                                </div>
                            </div>
                        </div>

                        {/* Static Noise Overlay */}
                        <div className="absolute inset-0 pointer-events-none opacity-[0.05] z-0"
                            style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E")` }}
                        />
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
