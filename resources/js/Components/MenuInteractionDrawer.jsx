
import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Filter, MessageSquare, Send, X, Check, Zap, AlertTriangle, Wheat, Egg, Milk, Bean, Nut, Droplets, Fish, HelpCircle, Leaf, Flame, FlaskConical, Flower, Music, ClipboardList } from 'lucide-react';

export default function MenuInteractionDrawer({ storeSlug, onFilterChange, venueName = "MEKAN", isOpen, onOpen, onClose }) {
    const [activeTab, setActiveTab] = useState('filters'); // 'filters' | 'feedback'
    const [isAllergensOpen, setIsAllergensOpen] = useState(false);
    const [filters, setFilters] = useState({
        vegan: false,
        vegetarian: false,
        glutenFree: false,
        // Allergens (Simplified)
        gluten: false,     // Gluten
        egg: false,        // Yumurta
        seafood: false,    // Deniz Ürünleri (Balık + Kabuklu + Yumuşakça)
        peanut: false,     // Yer fıstığı
        soy: false,        // Soya
        milk: false,       // Süt
        nut: false,        // Ağaç yemişleri
        celery: false,     // Kereviz
        mustard: false,    // Hardal
        sesame: false,     // Susam
        sulphite: false,   // Sülfat
        lupin: false,      // Lupin
    });

    const [feedback, setFeedback] = useState('');
    const [feedbackStatus, setFeedbackStatus] = useState('idle'); // idle, sending, success

    const [track, setTrack] = useState(null);
    const [currentTimeMs, setCurrentTimeMs] = useState(0);

    const fetchTrack = async () => {
        if (!storeSlug) return;
        try {
            const response = await fetch(`/api/${storeSlug}/now-playing`);
            const data = await response.json();
            
            setTrack((prevTrack) => {
                if (!prevTrack || prevTrack.track !== data.track) {
                    // New track or first load
                    setCurrentTimeMs(data.progress_ms || 0);
                } else if (data.progress_ms) {
                    // Existing track: only sync if we've drifted by more than 15 seconds (e.g. user seeked)
                    setCurrentTimeMs((prevTime) => {
                        if (Math.abs(prevTime - data.progress_ms) > 15000) {
                            return data.progress_ms;
                        }
                        return prevTime; // Keep our smooth local timer
                    });
                }
                return data;
            });
        } catch (error) {
            console.error('Error fetching now playing:', error);
        }
    };

    useEffect(() => {
        if (!isOpen) return;
        fetchTrack();
        const interval = setInterval(fetchTrack, 10000);
        return () => clearInterval(interval);
    }, [isOpen]);

    // Internal 1s timer for counting seconds between server checks
    useEffect(() => {
        if (!isOpen || !track?.is_playing) return;

        const timer = setInterval(() => {
            setCurrentTimeMs((prev) => {
                const next = prev + 1000;
                // If it reached the end, fetch new one earlier (with a slight delay)
                if (track.duration_ms && next >= track.duration_ms) {
                    setTimeout(fetchTrack, 1000);
                    return track.duration_ms;
                }
                return next;
            });
        }, 1000);
        return () => clearInterval(timer);
    }, [isOpen, track?.track, track?.is_playing]);

    // Helper to format MS to MM:SS
    const formatTime = (ms) => {
        if (!ms || ms < 0) return '00:00';
        const totalSeconds = Math.floor(ms / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = Math.floor(totalSeconds % 60);
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    };

    const remainingTimeMs = track?.duration_ms ? Math.max(0, track.duration_ms - currentTimeMs) : 0;
    const progressPercent = track?.duration_ms ? Math.min(100, (currentTimeMs / track.duration_ms) * 100) : 0;

    const handleFilterToggle = (key) => {
        const newFilters = { ...filters, [key]: !filters[key] };
        setFilters(newFilters);
        onFilterChange(newFilters);
    };

    const handleFeedbackSubmit = (e) => {
        e.preventDefault();
        if (!feedback.trim()) return;

        setFeedbackStatus('sending');
        // Simulate API call
        setTimeout(() => {
            setFeedbackStatus('success');
            setFeedback('');
            setTimeout(() => setFeedbackStatus('idle'), 2000);
        }, 1000);
    };

    return (
        <>
            {/* FAB (Floating Action Button) */}
            <motion.button
                initial={{ scale: 0 }}
                animate={{ scale: 1 }}
                whileHover={{ scale: 1.1 }}
                whileTap={{ scale: 0.9 }}
                onClick={onOpen}
                className="fixed bottom-6 right-6 z-50 w-16 h-16 bg-pub-gold border-4 border-pitch-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] flex items-center justify-center rounded-none rotate-3 hover:rotate-0 transition-transform duration-300"
                aria-label="Menu Options"
            >
                <Zap className="w-8 h-8 text-pitch-black fill-current" />
            </motion.button>

            {/* Drawer Overlay */}
            <AnimatePresence>
                {isOpen && (
                    <>
                        {/* Full Screen Overlay (Backdrop) */}
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            onClick={onClose}
                            className="fixed inset-0 bg-pitch-black/60 cursor-pointer"
                            style={{ zIndex: 140 }}
                        />

                        {/* Right 85% Drawer Container */}
                        <motion.div
                            initial={{ x: '100%' }}
                            animate={{ x: 0 }}
                            exit={{ x: '100%' }}
                            transition={{ type: 'spring', damping: 25, stiffness: 200 }}
                            className="fixed top-0 right-0 h-full w-[85%] flex"
                            style={{ zIndex: 150 }}
                        >
                            <div className="relative w-full h-full filter drop-shadow-[-10px_0_20px_rgba(0,0,0,0.5)]">
                                {/* LAYER 1: The Black Ripped Border (Behind) */}
                                <div
                                    className="absolute inset-0 bg-pitch-black w-full h-full"
                                    style={{
                                        clipPath: 'polygon(2% 0, 100% 0, 100% 100%, 0% 100%, 3% 98%, 1% 95%, 4% 94%, 1% 91%, 4% 89%, 0% 86%, 4% 84%, 1% 81%, 3% 78%, 0% 77%, 4% 74%, 2% 71%, 0% 69%, 3% 66%, 1% 63%, 4% 61%, 0% 58%, 3% 55%, 1% 52%, 4% 50%, 0% 47%, 3% 44%, 1% 41%, 4% 39%, 0% 36%, 3% 33%, 1% 30%, 4% 28%, 0% 25%, 3% 23%, 1% 20%, 4% 17%, 0% 14%, 3% 11%, 1% 9%, 4% 6%, 0% 3%, 3% 0)'
                                    }}
                                />

                                {/* LAYER 2: The White Content (Front) */}
                                <div
                                    className="absolute inset-0 bg-off-white w-full h-full flex flex-col"
                                    style={{
                                        // Slightly different clip-path or inset to reveal black border
                                        clipPath: 'polygon(3% 0, 100% 0, 100% 100%, 1% 100%, 4% 98%, 2% 95%, 5% 94%, 2% 91%, 5% 89%, 1% 86%, 5% 84%, 2% 81%, 4% 78%, 1% 77%, 5% 74%, 3% 71%, 1% 69%, 4% 66%, 2% 63%, 5% 61%, 1% 58%, 4% 55%, 2% 52%, 5% 50%, 1% 47%, 4% 44%, 2% 41%, 5% 39%, 1% 36%, 4% 33%, 2% 30%, 5% 28%, 1% 25%, 4% 23%, 2% 20%, 5% 17%, 1% 14%, 4% 11%, 2% 9%, 5% 6%, 1% 3%, 4% 0)',
                                        left: '4px' // Inset to show black border
                                    }}
                                >
                                    {/* Static Noise Overlay */}
                                    <div className="absolute inset-0 pointer-events-none opacity-[0.08]"
                                        style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E")` }}
                                    />

                                    {/* Header - MTV Style Widget */}
                                    <div
                                        onClick={onClose}
                                        className="bg-pitch-black p-4 pl-5 flex items-center gap-3 relative overflow-hidden shrink-0 border-b-4 border-white rotate-1 scale-[1.02] -mx-1 -mt-1 shadow-xl z-20 cursor-pointer group hover:bg-black/95 transition-colors"
                                    >
                                        {/* Album Art Placeholder - Ultra Compact */}
                                        <div className="w-14 h-14 shrink-0 bg-pub-gold border-4 border-white flex items-center justify-center shadow-[3px_3px_0_0_rgba(255,255,255,0.5)] overflow-hidden">
                                            {track?.image ? (
                                                <img src={track.image} alt="Art" className="w-full h-full object-cover" />
                                            ) : (
                                                <Music size={24} strokeWidth={2.5} className="text-pitch-black" />
                                            )}
                                        </div>

                                        {/* Text Info */}
                                        <div className="flex-1 min-w-0 flex flex-col justify-center h-full">
                                            <h2 className="text-pub-gold font-heading text-xl uppercase tracking-tighter leading-none mb-1 drop-shadow-[2px_2px_0_rgba(255,255,255,0.2)] break-words line-clamp-2">
                                                {track?.artist || 'VENUE RADIO'}
                                            </h2>
                                            <div className="flex w-full">
                                                <div className="bg-white text-pitch-black px-1.5 py-0.5 font-mono text-[8px] font-bold uppercase tracking-widest mb-1 transform -skew-x-6 border-b-2 border-r-2 border-gray-400 truncate max-w-full">
                                                    {track?.track || 'STAY TUNED'}
                                                </div>
                                            </div>
                                            {/* MTV Style Metadata line / Updated with Progress & Countdown */}
                                            <div className="w-full flex items-center gap-3 text-white font-mono text-[8px] mt-1">
                                                <div className="flex items-center gap-1.5 shrink-0">
                                                    <span className="border border-white/30 px-1 rounded-[1px] opacity-50">HD</span>
                                                    <span className="tracking-widest flex items-center gap-1">
                                                        {track?.is_playing && <motion.span animate={{ opacity: [1, 0.4, 1] }} transition={{ repeat: Infinity, duration: 1.5 }} className="w-1.5 h-1.5 bg-red-500 rounded-full inline-block" />}
                                                        {track?.is_playing ? 'LIVE' : 'STEREO'}
                                                    </span>
                                                </div>
                                                
                                                {/* Progress Bar */}
                                                <div className="flex-1 h-0.5 bg-white/20 relative rounded-full overflow-hidden">
                                                    <motion.div 
                                                        className="absolute left-0 top-0 h-full bg-white opacity-60"
                                                        style={{ width: `${progressPercent}%` }}
                                                        transition={{ type: 'tween', duration: 1 }}
                                                    />
                                                </div>
                                                
                                                {/* Countdown Timer */}
                                                <span className="tabular-nums opacity-80 min-w-[30px] text-right">
                                                    {formatTime(remainingTimeMs)}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Close Icon (Subtle) */}
                                        <div className="absolute top-2 right-2 text-white/20 group-hover:text-white transition-colors">
                                            <X size={16} />
                                        </div>
                                    </div>

                                    {/* Tabs */}
                                    <div className="flex border-b-8 border-pitch-black shrink-0 bg-pitch-black gap-1 p-1 pl-4 sm:pl-6 overflow-hidden">
                                        <button
                                            onClick={() => setActiveTab('filters')}
                                            className={`flex-1 py-3 font-heading text-base sm:text-lg uppercase tracking-tight flex items-center justify-center gap-1.5 transition-all clip-path-slant
                                            ${activeTab === 'filters' ? 'bg-pub-gold text-pitch-black -rotate-1 translate-y-1' : 'bg-white text-pitch-black/50 hover:bg-gray-200 rotate-0'}`}
                                            style={{ clipPath: 'polygon(5% 0, 100% 0, 95% 100%, 0% 100%)' }}
                                        >
                                            <Filter size={20} strokeWidth={2.5} /> Filtre
                                        </button>
                                        <button
                                            onClick={() => setActiveTab('feedback')}
                                            className={`flex-1 py-3 font-heading text-base sm:text-lg uppercase tracking-tight flex items-center justify-center gap-1.5 transition-all clip-path-slant
                                            ${activeTab === 'feedback' ? 'bg-pub-gold text-pitch-black rotate-1 translate-y-1' : 'bg-white text-pitch-black/50 hover:bg-gray-200 rotate-0'}`}
                                            style={{ clipPath: 'polygon(5% 0, 100% 0, 95% 100%, 0% 100%)' }}
                                        >
                                            <MessageSquare size={20} strokeWidth={2.5} /> Ses Ver
                                        </button>
                                        <button
                                            onClick={() => setActiveTab('survey')}
                                            className={`flex-1 py-3 font-heading text-base sm:text-lg uppercase tracking-tight flex items-center justify-center gap-1.5 transition-all clip-path-slant
                                            ${activeTab === 'survey' ? 'bg-pub-gold text-pitch-black rotate-1 translate-y-1' : 'bg-white text-pitch-black/50 hover:bg-gray-200 rotate-0'}`}
                                            style={{ clipPath: 'polygon(5% 0, 100% 0, 95% 100%, 0% 100%)' }}
                                        >
                                            <ClipboardList size={18} strokeWidth={2.5} /> Anket
                                        </button>
                                    </div>

                                    {/* Content Area */}
                                    <div className="flex-1 overflow-y-auto p-6 pl-10 relative">
                                        <AnimatePresence mode="wait">
                                            {activeTab === 'filters' ? (
                                                <motion.div
                                                    key="filters"
                                                    initial={{ opacity: 0, x: 20 }}
                                                    animate={{ opacity: 1, x: 0 }}
                                                    exit={{ opacity: 0, x: -20 }}
                                                    className="space-y-6"
                                                >
                                                    <div className="bg-black text-white p-2 -rotate-1 inline-block mb-4 font-mono text-xs uppercase tracking-widest border-2 border-pub-gold">
                                                        Diyetini Seç
                                                    </div>

                                                    {/* Filter Toggles - Brutalist Checkboxes */}
                                                    {[
                                                        { key: 'vegan', label: 'Vegan', desc: 'Sadece bitki. %100 Doğa.' },
                                                        { key: 'vegetarian', label: 'Vejetaryen', desc: 'Et yok. Lezzet var.' },
                                                        { key: 'glutenFree', label: 'Glutensiz', desc: 'Buğdaya savaş açtık.' }
                                                    ].map((item, i) => (
                                                        <div
                                                            key={item.key}
                                                            onClick={() => handleFilterToggle(item.key)}
                                                            className={`cursor-pointer group relative border-4 border-pitch-black p-5 transition-all duration-200
                                                            ${filters[item.key]
                                                                    ? 'bg-pitch-black text-pub-gold shadow-[8px_8px_0_0_var(--color-pub-gold)] -translate-y-1'
                                                                    : 'bg-white text-pitch-black shadow-[8px_8px_0_0_#000] hover:-translate-y-1 hover:shadow-[10px_10px_0_0_#000] hover:bg-gray-50'
                                                                }`}
                                                            style={{ transform: `rotate(${i % 2 === 0 ? '1deg' : '-1deg'})` }}
                                                        >
                                                            <div className="flex items-center justify-between relative z-10 w-full">
                                                                <div className="flex-1 mr-4">
                                                                    <h3 className="font-heading text-xl sm:text-2xl md:text-3xl uppercase tracking-tight leading-none break-words">
                                                                        {item.label}
                                                                    </h3>
                                                                    <p className={`font-mono text-xs mt-1 ${filters[item.key] ? 'text-white/80' : 'text-pitch-black/60'}`}>
                                                                        {item.desc}
                                                                    </p>
                                                                </div>
                                                                <div className={`w-10 h-10 border-4 flex shrink-0 items-center justify-center transition-colors
                                                                    ${filters[item.key] ? 'border-pub-gold bg-pub-gold text-pitch-black' : 'border-pitch-black bg-white'}`}>
                                                                    {filters[item.key] && <Check size={28} strokeWidth={4} />}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    ))}

                                                    {/* HAZARD ZONE - Allergens */}
                                                    <div className="mt-8 border-t-4 border-pitch-black pt-8">
                                                        <button
                                                            onClick={() => setIsAllergensOpen(!isAllergensOpen)}
                                                            className="w-full bg-[#FFD700] border-4 border-pitch-black p-4 flex items-center justify-center shadow-[8px_8px_0_0_#000] active:translate-y-1 active:shadow-none transition-all relative overflow-hidden group"
                                                        >
                                                            {/* Hazard Stripes Pattern */}
                                                            <div className="absolute inset-0 opacity-10"
                                                                style={{ backgroundImage: 'repeating-linear-gradient(45deg, #000, #000 10px, #FFD700 10px, #FFD700 20px)' }}
                                                            />

                                                            <div className="flex items-center gap-3 relative z-10 text-pitch-black">
                                                                <AlertTriangle size={32} strokeWidth={3} />
                                                                <div className="text-left leading-none">
                                                                    <div className="font-heading text-xl uppercase">ALERJEN PROTOKOLÜ</div>
                                                                </div>
                                                            </div>
                                                            <div className={`transform transition-transform duration-300 absolute right-4 z-10 ${isAllergensOpen ? 'rotate-180' : ''}`}>
                                                                <div className="bg-black text-[#FFD700] p-1">
                                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="4" strokeLinecap="square" strokeLinejoin="miter"><path d="M6 9l6 6 6-6" /></svg>
                                                                </div>
                                                            </div>
                                                        </button>

                                                        <AnimatePresence>
                                                            {isAllergensOpen && (
                                                                <motion.div
                                                                    initial={{ height: 0, opacity: 0 }}
                                                                    animate={{ height: 'auto', opacity: 1 }}
                                                                    exit={{ height: 0, opacity: 0 }}
                                                                    className="overflow-hidden"
                                                                >
                                                                    <div className="pt-4 grid grid-cols-2 gap-3">
                                                                        {[
                                                                            { key: 'gluten', label: 'Gluten', Icon: Wheat },
                                                                            { key: 'egg', label: 'Yumurta', Icon: Egg },
                                                                            { key: 'seafood', label: 'Deniz Ürün.', Icon: Fish },
                                                                            { key: 'peanut', label: 'Yer Fıstığı', Icon: Nut },
                                                                            { key: 'soy', label: 'Soya', Icon: Bean },
                                                                            { key: 'milk', label: 'Süt', Icon: Milk },
                                                                            { key: 'nut', label: 'Kuruyemiş', Icon: Nut },
                                                                            { key: 'celery', label: 'Kereviz', Icon: Leaf },
                                                                            { key: 'mustard', label: 'Hardal', Icon: Flame },
                                                                            { key: 'sesame', label: 'Susam', Icon: Droplets },
                                                                            { key: 'sulphite', label: 'Sülfat', Icon: FlaskConical },
                                                                            { key: 'lupin', label: 'Lupin', Icon: Flower }
                                                                        ].map((allergen) => (
                                                                            <div
                                                                                key={allergen.key}
                                                                                onClick={() => handleFilterToggle(allergen.key)}
                                                                                className={`border-4 border-pitch-black p-3 cursor-pointer flex flex-col justify-center items-center transition-all aspect-square
                                                                                ${filters[allergen.key] ? 'bg-red-600 text-white' : 'bg-white text-pitch-black hover:bg-gray-100'}`}
                                                                            >
                                                                                <div className="flex flex-col items-center text-center gap-2">
                                                                                    <allergen.Icon size={36} strokeWidth={2} className="opacity-100" />
                                                                                    <span className="font-heading uppercase text-lg leading-none">{allergen.label}</span>
                                                                                </div>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                </motion.div>
                                                            )}
                                                        </AnimatePresence>
                                                    </div>

                                                </motion.div>
                                            ) : activeTab === 'survey' ? (
                                                <motion.div
                                                    key="survey"
                                                    initial={{ opacity: 0, x: 20 }}
                                                    animate={{ opacity: 1, x: 0 }}
                                                    exit={{ opacity: 0, x: -20 }}
                                                    className="h-full flex flex-col items-center justify-center text-center pb-20 relative"
                                                >
                                                    {/* Warning Icon */}
                                                    <div className="w-24 h-24 mb-6 bg-pitch-black text-pub-gold flex items-center justify-center border-4 border-pub-gold shadow-[8px_8px_0_0_#000] rotate-3 relative overflow-hidden transition-transform duration-500 hover:rotate-12 hover:scale-110">
                                                        <div className="absolute inset-0 opacity-10" style={{ backgroundImage: 'repeating-linear-gradient(45deg, #000, #000 10px, #FFD700 10px, #FFD700 20px)' }} />
                                                        <AlertTriangle size={48} strokeWidth={2} className="relative z-10" />
                                                    </div>
                                                    
                                                    {/* Text Box */}
                                                    <div className="bg-white border-4 border-pitch-black p-6 shadow-[8px_8px_0_0_var(--color-pub-gold)] -rotate-2 relative z-10 max-w-sm">
                                                        <h3 className="font-heading text-3xl sm:text-4xl uppercase mb-3 leading-none text-pitch-black block">YAPIM AŞAMASINDA</h3>
                                                        <div className="h-1 w-12 bg-pitch-black mx-auto mb-3 absolute top-0 left-0"></div>
                                                        <div className="h-1 w-12 bg-pitch-black mx-auto mb-3 absolute bottom-0 right-0"></div>
                                                        
                                                        <p className="font-mono text-sm text-pitch-black/80 font-bold border-t-2 border-pitch-black/10 pt-3">
                                                            Sizin için daha iyi hizmet verebilmek adına anket sistemimizi hazırlıyoruz. Çok yakında yayında!
                                                        </p>
                                                    </div>
                                                </motion.div>
                                            ) : (
                                                <motion.div
                                                    key="feedback"
                                                    initial={{ opacity: 0, x: 20 }}
                                                    animate={{ opacity: 1, x: 0 }}
                                                    exit={{ opacity: 0, x: -20 }}
                                                    className="h-full flex flex-col"
                                                >
                                                    <div className="bg-white border-4 border-pitch-black p-6 shadow-[8px_8px_0_0_#ff0000] mb-8 rotate-1">
                                                        <h3 className="font-heading text-3xl uppercase mb-2 leading-none">Ses Ver</h3>
                                                        <p className="font-mono text-xs text-pitch-black/70 font-bold">
                                                            Müzik, Ortam, Yemek? Yüksek sesle söyle.
                                                        </p>
                                                    </div>

                                                    <form onSubmit={handleFeedbackSubmit} className="flex flex-col gap-6 flex-1">
                                                        <div className="relative">
                                                            <div className="absolute top-0 left-0 w-full h-full bg-pitch-black translate-x-2 translate-y-2"></div>
                                                            <textarea
                                                                value={feedback}
                                                                onChange={(e) => setFeedback(e.target.value)}
                                                                placeholder="Haykır buraya..."
                                                                className="w-full h-48 bg-white border-4 border-pitch-black p-4 font-mono text-lg focus:outline-none relative z-10 placeholder:text-gray-400 focus:bg-yellow-50"
                                                                required
                                                            />
                                                        </div>

                                                        <button
                                                            type="submit"
                                                            disabled={feedbackStatus !== 'idle'}
                                                            className={`w-full py-5 font-heading text-2xl uppercase border-4 border-pitch-black shadow-[8px_8px_0_0_#000] flex items-center justify-center gap-3 transition-all active:translate-y-2 active:shadow-none hover:-translate-y-1 hover:shadow-[10px_10px_0_0_#000]
                                                            ${feedbackStatus === 'success' ? 'bg-green-500 text-white' : 'bg-pub-gold text-pitch-black'}`}
                                                        >
                                                            {feedbackStatus === 'idle' && <><Send size={24} strokeWidth={3} /> Yolla Gelsin</>}
                                                            {feedbackStatus === 'sending' && "..."}
                                                            {feedbackStatus === 'success' && <><Check size={24} /> İletildi!</>}
                                                        </button>
                                                    </form>
                                                </motion.div>
                                            )}
                                        </AnimatePresence>
                                    </div>
                                </div>
                            </div>
                        </motion.div>
                    </>
                )}
            </AnimatePresence>
        </>
    );
}
