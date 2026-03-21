import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

export default function NowPlaying() {
    const [track, setTrack] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isExpanded, setIsExpanded] = useState(false);
    const [prevTrackId, setPrevTrackId] = useState(null);

    const fetchTrack = async () => {
        try {
            const response = await fetch('/api/now-playing');
            const data = await response.json();

            if (data) {
                const trackId = `${data.artist}-${data.track}`;
                if (trackId !== prevTrackId) {
                    setTrack(data);
                    setPrevTrackId(trackId);
                    triggerExpansion();
                } else {
                    setTrack(data);
                }
            } else {
                setTrack(null);
            }
        } catch (error) {
            console.error('Error fetching now playing:', error);
        } finally {
            setLoading(false);
        }
    };

    const triggerExpansion = () => {
        setIsExpanded(true);
        setTimeout(() => {
            setIsExpanded(false);
        }, 8000);
    };

    useEffect(() => {
        fetchTrack();
        const interval = setInterval(fetchTrack, 20000);
        return () => clearInterval(interval);
    }, [prevTrackId]);

    const toggleExpand = () => setIsExpanded(!isExpanded);

    if (loading && !track) return null;
    if (!track) return null;

    // Dimensions for precise alignment
    const SLEEVE_SIZE = 80;
    const CONTENT_HEIGHT = 64;
    const VINYL_SIZE = 64;
    const VERTICAL_OFFSET = (SLEEVE_SIZE - CONTENT_HEIGHT) / 2;

    return (
        <div className="fixed bottom-6 left-6 z-[100] pointer-events-none sm:bottom-8 sm:left-8">
            <div
                className="relative pointer-events-auto cursor-pointer flex items-center h-20"
                style={{ width: isExpanded ? 340 : 80, transition: 'width 0.5s ease' }}
                onClick={toggleExpand}
            >
                {/* 1. Vinyl Record - Internal / Sliding Part */}
                <motion.div
                    animate={{
                        rotate: track.is_playing ? 360 : 0,
                        x: isExpanded ? 245 : 10,
                        opacity: 1
                    }}
                    transition={{
                        rotate: { duration: 4, repeat: Infinity, ease: "linear" },
                        x: { type: "spring", stiffness: 60, damping: 15 }
                    }}
                    style={{
                        width: VINYL_SIZE,
                        height: VINYL_SIZE,
                        bottom: VERTICAL_OFFSET,
                        left: 0,
                        willChange: 'transform, opacity',
                        transformStyle: 'preserve-3d',
                        backfaceVisibility: 'hidden'
                    }}
                    className="absolute z-30 overflow-hidden rounded-full border-2 border-white/40 bg-pitch-black flex items-center justify-center shadow-lg transform-gpu"
                >
                    {/* Vinyl Details: Grooves */}
                    <div className="absolute inset-2 border border-white/5 rounded-full"></div>
                    <div className="absolute inset-4 border border-white/5 rounded-full"></div>

                    {/* Center Label (Mini Image) */}
                    <div className="w-1/3 h-1/3 rounded-full bg-pub-gold/30 border border-white/20 flex items-center justify-center overflow-hidden">
                        {track.image && <img src={track.image} className="w-full h-full object-cover opacity-60" />}
                    </div>
                    {/* Center Hole */}
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-white rounded-full"></div>
                </motion.div>

                {/* 2. Album Cover (The Fixed Housing) */}
                <motion.div
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    style={{
                        width: SLEEVE_SIZE,
                        height: SLEEVE_SIZE,
                        willChange: 'transform',
                        transformStyle: 'preserve-3d',
                        backfaceVisibility: 'hidden'
                    }}
                    className="relative z-50 bg-pitch-black border-4 border-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] overflow-hidden transform-gpu"
                >
                    {track.image ? (
                        <img src={track.image} alt="Art" className="w-full h-full object-cover" />
                    ) : (
                        <div className="w-full h-full flex items-center justify-center font-heading text-[10px] text-pub-gold p-2 text-center">
                            LP
                        </div>
                    )}
                </motion.div>

                {/* 3. Info Panel - Slides out from behind the cover */}
                <AnimatePresence>
                    {isExpanded && (
                        <motion.div
                            initial={{ width: 0, opacity: 0 }}
                            animate={{ width: 240, opacity: 1 }}
                            exit={{ width: 0, opacity: 0 }}
                            transition={{ type: "spring", stiffness: 60, damping: 15 }}
                            style={{
                                height: CONTENT_HEIGHT,
                                bottom: VERTICAL_OFFSET,
                                willChange: 'width, opacity',
                                transformStyle: 'preserve-3d',
                                backfaceVisibility: 'hidden'
                            }}
                            className="absolute left-10 bg-white border-2 border-pitch-black shadow-[4px_4px_0px_0px_#ffb000] overflow-hidden z-20 transform-gpu"
                        >
                            <div className="pl-12 pr-14 h-full flex flex-col justify-center min-w-[240px]">
                                <div className="flex items-center gap-2 mb-1">
                                    <div className="flex gap-0.5 items-end h-3">
                                        {[1, 2, 3].map(i => (
                                            <motion.span
                                                key={i}
                                                animate={{ height: track.is_playing ? [4, 12, 4] : 4 }}
                                                transition={{ repeat: Infinity, duration: 0.6, delay: i * 0.15 }}
                                                className="w-1 bg-pub-gold"
                                            ></motion.span>
                                        ))}
                                    </div>
                                    <span className="font-mono text-[8px] uppercase font-bold tracking-widest text-pitch-black/40">
                                        Playing_Now
                                    </span>
                                </div>
                                <div className="font-heading text-xs uppercase leading-none truncate text-pitch-black pr-2">
                                    {track.track}
                                </div>
                                <div className="font-mono text-[9px] text-pitch-black/60 truncate uppercase">
                                    {track.artist}
                                </div>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </div>
    );
}
