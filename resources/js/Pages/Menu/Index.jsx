import React, { useState, useRef, useEffect, useMemo } from 'react';
import StreetLayout from '@/Layouts/StreetLayout';
import { motion, AnimatePresence } from 'framer-motion';
import * as LucideIcons from 'lucide-react';

import MenuInteractionDrawer from '@/Components/MenuInteractionDrawer';

import DefaultCampaignCard from '@/Components/Campaigns/DefaultCampaignCard';

const Icon = ({ name, ...props }) => {
    const LucideIcon = LucideIcons[name] || LucideIcons.HelpCircle;
    return <LucideIcon {...props} />;
};

import { useTracking } from '@/Hooks/useTracking';

const getContrastColor = (hex) => {
    if (!hex) return '#000000';
    const cleanHex = hex.replace('#', '');
    if (cleanHex.length !== 6) return '#000000';
    
    const r = parseInt(cleanHex.substring(0, 2), 16) / 255;
    const g = parseInt(cleanHex.substring(2, 4), 16) / 255;
    const b = parseInt(cleanHex.substring(4, 6), 16) / 255;
    
    const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;
    return luminance < 0.4 ? '#ffffff' : '#000000';
};

export default function Index({ menuData = {}, store = null, likedProductIds = [] }) {
    const [likedIds, setLikedIds] = useState(likedProductIds);
    const [mainTab, setMainTab] = useState('campaign'); // 'campaign' | 'drink' | 'food'
    const [activeMainCategory, setActiveMainCategory] = useState(null);
    const [activeSubCategory, setActiveSubCategory] = useState(null);
    const [selectedItem, setSelectedItem] = useState(null);
    const [selectedOption, setSelectedOption] = useState(null);
    const [activeFilters, setActiveFilters] = useState({ vegan: false, vegetarian: false, glutenFree: false });
    const [isDrawerOpen, setIsDrawerOpen] = useState(false);
    const [selectedCampaign, setSelectedCampaign] = useState(null);

    // Analytics Tracking
    const { trackClick, toggleVote } = useTracking(
        activeSubCategory?.includes('-') ? activeSubCategory.split('-').pop() : activeSubCategory
    );

    // Dynamic Theme Color Implementation
    useEffect(() => {
        if (store?.theme_color) {
            document.documentElement.style.setProperty('--color-pub-gold', store.theme_color);
        }
    }, [store?.theme_color]);

    const handleVote = (e, productId) => {
        e.stopPropagation();
        
        // Optimistic UI
        const isLiked = likedIds.includes(productId);
        if (isLiked) {
            setLikedIds(prev => prev.filter(id => id !== productId));
        } else {
            setLikedIds(prev => [...prev, productId]);
        }

        toggleVote(productId).catch(() => {
            // Revert on error
            setLikedIds(prev => isLiked ? [...prev, productId] : prev.filter(id => id !== productId));
        });
    };

    const categoryRefs = useRef({});
    const navRefs = useRef({ main: null, sub: null });
    const isManualScrolling = useRef(false);

    const currentGroups = menuData[mainTab] || [];
    
    // 1. Flatten ONLY the currently visible and filtered items for navigation
    const flatSections = useMemo(() => {
        const sections = [];
        currentGroups.forEach(group => {
            if (group.subcategories) {
                group.subcategories.forEach(sub => {
                    let filteredItems = sub.items || [];

                    // --- New Dynamic Filtering Logic ---
                    
                    // 1. Diet Inclusions (Vegan, Vegetarian, Gluten-Free)
                    // If a diet filter is active, only show products that HAVE that diet type
                    if (activeFilters.vegan) {
                        filteredItems = filteredItems.filter(i => 
                            i.diet_types?.some(dt => dt.name?.toLowerCase().includes('vegan')) ||
                            i.tags?.some(t => t.toLowerCase() === 'vegan') // Legacy support
                        );
                    }
                    if (activeFilters.vegetarian) {
                        filteredItems = filteredItems.filter(i => 
                            i.diet_types?.some(dt => dt.name?.toLowerCase().includes('vejetaryen') || dt.name?.toLowerCase().includes('vegetarian')) ||
                            i.tags?.some(t => t.toLowerCase().includes('vege')) // Legacy support
                        );
                    }
                    if (activeFilters.glutenFree) {
                        filteredItems = filteredItems.filter(i => 
                            i.diet_types?.some(dt => dt.name?.toLowerCase().includes('glutensiz') || dt.name?.toLowerCase().includes('gluten-free')) ||
                            i.tags?.some(t => t.toLowerCase().includes('gluten') || t.toLowerCase() === 'gf') // Legacy support
                        );
                    }

                    // 2. Allergen Exclusions (Milk, Peanut, Egg, etc.)
                    // If an allergen filter is active, HIDE products that CONTAIN that allergen
                    const allergenMap = {
                        gluten: 'Gluten',
                        egg: 'Yumurta',
                        seafood: 'Deniz Ürünleri',
                        peanut: 'Yer Fıstığı',
                        soy: 'Soya',
                        milk: 'Süt',
                        nut: 'Kuruyemiş',
                        celery: 'Kereviz',
                        mustard: 'Hardal',
                        sesame: 'Susam',
                        sulphite: 'Sülfat',
                        lupin: 'Lupin'
                    };

                    Object.entries(allergenMap).forEach(([key, trName]) => {
                        if (activeFilters[key]) {
                            filteredItems = filteredItems.filter(i => 
                                !i.allergens?.some(al => al.name === trName)
                            );
                        }
                    });

                    // --- End Filtering Logic ---

                    if (filteredItems.length > 0) {
                        sections.push({
                            ...sub,
                            items: filteredItems,
                            groupId: group.id,
                            groupName: group.name,
                            uniqueId: `${group.id}-${sub.id}`,
                            // For Campaigns, pass the whole metadata
                            campaignData: mainTab === 'campaign' ? group : null
                        });
                    }
                });
            }
        });
        return sections;
    }, [currentGroups, activeFilters]);

    // 2. Navigation Item List (Derived from current view)
    const navigationProducts = useMemo(() => {
        const list = [];
        flatSections.forEach(section => {
            section.items?.forEach(item => {
                // Ensure unique items in list
                if (!list.find(p => p.id === item.id)) {
                    list.push(item);
                }
            });
        });
        return list;
    }, [flatSections]);

    // Defaults on tab change
    useEffect(() => {
        if (currentGroups.length > 0) {
            setActiveMainCategory(currentGroups[0].id);
            if (currentGroups[0].subcategories?.length > 0) {
                setActiveSubCategory(`${currentGroups[0].id}-${currentGroups[0].subcategories[0].id}`);
            }
        }
    }, [mainTab, currentGroups]);

    const safeCenterScroll = (container, target) => {
        if (!container || !target) return;
        const scrollLeft = target.offsetLeft - (container.offsetWidth / 2) + (target.offsetWidth / 2);
        container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
    };

    const scrollToSection = (uniqueId) => {
        const element = categoryRefs.current[uniqueId];
        if (element) {
            isManualScrolling.current = true;
            setActiveSubCategory(uniqueId);
            const section = flatSections.find(s => s.uniqueId === uniqueId);
            if (section) setActiveMainCategory(section.groupId);

            const offset = 172;
            const bodyRect = document.body.getBoundingClientRect().top;
            const elementRect = element.getBoundingClientRect().top;
            window.scrollTo({ top: (elementRect - bodyRect) - offset, behavior: 'smooth' });
            setTimeout(() => { isManualScrolling.current = false; }, 800);
        }
    };

    useEffect(() => {
        const observer = new IntersectionObserver((entries) => {
            if (isManualScrolling.current) return;
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setActiveSubCategory(entry.target.getAttribute('data-unique-id'));
                    setActiveMainCategory(entry.target.getAttribute('data-group-id'));
                }
            });
        }, { rootMargin: '-165px 0px -60% 0px', threshold: 0 });

        const timeoutId = setTimeout(() => {
            Object.values(categoryRefs.current).forEach(el => el && observer.observe(el));
        }, 200);
        return () => { observer.disconnect(); clearTimeout(timeoutId); };
    }, [flatSections]);

    useEffect(() => {
        if (activeMainCategory && navRefs.current.main) {
            const btn = navRefs.current.main.querySelector(`[data-group-id="${activeMainCategory}"]`);
            safeCenterScroll(navRefs.current.main, btn);
        }
    }, [activeMainCategory]);

    useEffect(() => {
        if (activeSubCategory && navRefs.current.sub) {
            const btn = navRefs.current.sub.querySelector(`[data-sub-id="${activeSubCategory}"]`);
            safeCenterScroll(navRefs.current.sub, btn);
        }
    }, [activeSubCategory]);

    // Handle Item Detail Open
    const openDetails = (item) => {
        setSelectedItem(item);
        setSelectedOption(item.options ? item.options[0] : null);
        
        // Track Product View (mapped to click in this UI as it opens detail)
        trackClick('Product', item.id);
    };

    const [direction, setDirection] = useState(0); // -1 for left, 1 for right

    const navigateProduct = (newDirection) => {
        const currentIndex = navigationProducts.findIndex(p => p.id === selectedItem.id);
        if (currentIndex === -1) return;

        let nextIndex = currentIndex + newDirection;
        if (nextIndex >= navigationProducts.length) nextIndex = 0;
        if (nextIndex < 0) nextIndex = navigationProducts.length - 1;

        setDirection(newDirection);
        const nextItem = navigationProducts[nextIndex];
        setSelectedItem(nextItem);
        setSelectedOption(nextItem.options ? nextItem.options[0] : null);
    };

    const swipeVariants = {
        enter: (direction) => ({
            x: direction > 0 ? 500 : -500,
            opacity: 0,
            scale: 0.95
        }),
        center: {
            zIndex: 1,
            x: 0,
            opacity: 1,
            scale: 1
        },
        exit: (direction) => ({
            zIndex: 0,
            x: direction < 0 ? 500 : -500,
            opacity: 0,
            scale: 0.95
        })
    };

    // Expert Body Scroll Lock when Drawer is Open
    useEffect(() => {
        if (!selectedItem) return;
        
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
    }, [selectedItem]);


    return (
        <StreetLayout title={store ? store.name : "Menu"}>
            {/* Dynamic Theme Color Override */}
            <style dangerouslySetInnerHTML={{ __html: `
                :root {
                    --color-pub-gold: ${store?.theme_color || '#ffb000'} !important;
                    --color-pub-gold-contrast: ${getContrastColor(store?.theme_color)} !important;
                }
            ` }} />

            {/* Top Switch - Brutalist Sticker Style */}
            <div className="sticky top-0 z-[100] bg-off-white px-6 pt-4 pb-1">
                <div className="flex items-center border-4 border-pitch-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] bg-white overflow-visible relative h-14">
                    {/* İÇECEK */}
                    <button onClick={() => setMainTab('drink')}
                        className={`flex-1 h-full font-heading text-[13px] tracking-tight transition-colors ${mainTab === 'drink' ? 'bg-pub-gold text-pub-gold-contrast' : 'bg-white text-pitch-black/40'}`}>
                        İÇECEK
                    </button>

                    {/* KAMPANYA - The "Sticker" in the middle */}
                    <div className="relative h-full flex items-center justify-center px-1">
                        <div className="w-px h-8 bg-pitch-black/20" /> {/* Divider */}
                        <button onClick={() => setMainTab('campaign')}
                            className={`relative px-4 py-2 font-heading text-[13px] tracking-tighter whitespace-nowrap transition-all duration-300 transform
                            ${mainTab === 'campaign'
                                    ? 'bg-red-600 text-white scale-110 -rotate-2 shadow-[4px_4px_0px_0px_#000] z-20'
                                    : 'bg-white text-pitch-black border-2 border-pitch-black rotate-1 opacity-80 hover:opacity-100 hover:scale-105 z-10'}`}>
                            KAMPANYA
                        </button>
                        <div className="w-px h-8 bg-pitch-black/20" /> {/* Divider */}
                    </div>

                    {/* YİYECEK */}
                    <button onClick={() => setMainTab('food')}
                        className={`flex-1 h-full font-heading text-[13px] tracking-tight transition-colors ${mainTab === 'food' ? 'bg-pub-gold text-pub-gold-contrast' : 'bg-white text-pitch-black/40'}`}>
                        YİYECEK
                    </button>
                </div>
            </div>

            {/* Nav 1 */}
            <nav ref={el => navRefs.current.main = el} className="sticky top-[75px] z-[90] bg-pitch-black text-white py-3 overflow-x-auto no-scrollbar border-b border-white/10">
                <div className="flex gap-6 px-6 min-w-max">
                    {currentGroups.map(group => (
                        <button key={group.id} data-group-id={group.id} onClick={() => group.subcategories?.[0] && scrollToSection(`${group.id}-${group.subcategories[0].id}`)}
                            className={`font-heading text-base uppercase tracking-wider transition-opacity ${activeMainCategory === group.id ? 'text-pub-gold opacity-100' : 'text-white opacity-40'}`}>
                            {group.name}
                        </button>
                    ))}
                </div>
            </nav>

            {/* Nav 2 - Hidden for campaigns */}
            {mainTab !== 'campaign' && (
                <nav ref={el => navRefs.current.sub = el} className="sticky top-[125px] z-[80] bg-off-white/95 backdrop-blur-md py-2 border-b-2 border-pitch-black overflow-x-auto no-scrollbar h-12">
                    <div className="flex gap-2 px-6 min-w-max items-center h-full">
                        {activeMainCategory && currentGroups.find(g => g.id === activeMainCategory)?.subcategories.map(sub => {
                            const uid = `${activeMainCategory}-${sub.id}`;
                            return (
                                <button key={sub.id} data-sub-id={uid} onClick={() => scrollToSection(uid)}
                                    className={`px-3 py-1 font-mono text-[9px] uppercase font-bold border transition-all ${activeSubCategory === uid ? 'bg-pitch-black text-white border-pitch-black shadow-[2px_2px_0px_0px_var(--color-pub-gold)]' : 'bg-white text-pitch-black border-pitch-black/20'}`}>
                                    {sub.name}
                                </button>
                            );
                        })}
                    </div>
                </nav>
            )}

            {/* List */}
            <div className={`px-6 pb-32 ${mainTab === 'campaign' ? 'mt-4' : 'mt-8'}`}>
                {mainTab === 'campaign' ? (
                    <>
                        {/* Active Section */}
                        {flatSections.some(s => s.campaignData?.is_live) && (
                            <>
                                <div className="mb-8 relative">
                                    <span className="font-mono text-[10px] text-red-600 uppercase tracking-[0.2em] font-black block mb-2 px-1">AKTİF FIRSATLAR</span>
                                    <div className="h-px bg-red-600/20 w-full"></div>
                                </div>

                                <div className="grid grid-cols-2 gap-x-3 gap-y-8 mb-16">
                                    {flatSections.filter(s => s.campaignData?.is_live).map((section) => (
                                        <section key={section.uniqueId} data-unique-id={section.uniqueId} data-group-id={section.groupId} ref={el => categoryRefs.current[section.uniqueId] = el} className="scroll-mt-72">
                                            <div key={`${section.uniqueId}-${section.campaignData.id}`}
                                                onClick={() => setSelectedCampaign(section.campaignData)}
                                                className="group relative bg-white border-2 border-pitch-black shadow-[4px_4px_0_0_#000] overflow-hidden cursor-pointer active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all p-3"
                                                style={{ transform: `rotate(${parseInt(section.campaignData.id) % 2 === 0 ? 0.8 : -0.8}deg)` }}
                                            >
                                                {/* Compact Header */}
                                                <div className="flex justify-end items-start mb-2">
                                                    <Icon name="Zap" size={14} className="text-pub-gold animate-pulse" />
                                                </div>

                                                <div className="bg-grunge-texture relative flex flex-col justify-between min-h-[80px]">
                                                    <div>
                                                        <h3 className="font-heading text-xl leading-[0.9] uppercase mb-1 group-hover:text-red-600 transition-colors tracking-tighter line-clamp-2">{section.campaignData.name}</h3>
                                                        <p className="font-sans italic text-pitch-black/60 text-[10px] line-clamp-2 leading-tight">{section.campaignData.description}</p>
                                                    </div>

                                                    <div className="mt-4 flex items-center justify-between border-t border-pitch-black/10 pt-2">
                                                        <div className="flex -space-x-2">
                                                            {section.campaignData.subcategories?.[0]?.items?.slice(0, 3).map((item, idx) => (
                                                                <div key={item.id} className="w-5 h-5 rounded-full border border-pitch-black bg-white overflow-hidden shadow-[1px_1px_0_0_#000]" style={{ zIndex: 3 - idx }}>
                                                                    <img src={item.image} loading="lazy" className="w-full h-full object-cover" alt="" />
                                                                </div>
                                                            ))}
                                                        </div>
                                                        <Icon name="ArrowRight" size={14} className="text-red-600 group-hover:translate-x-1 transition-transform" />
                                                    </div>
                                                </div>
                                                {/* Decorative perforation line for ticket feel */}
                                                <div className="absolute -bottom-1 left-0 right-0 h-1 bg-white border-t border-dashed border-pitch-black/20"></div>
                                            </div>
                                        </section>
                                    ))}
                                </div>
                            </>
                        )}

                        {/* Upcoming Section */}
                        {flatSections.some(s => s.campaignData && !s.campaignData.is_live) && (
                            <>
                                <div className="mb-8 relative mt-12">
                                    <span className="font-mono text-[10px] text-pitch-black/40 uppercase tracking-[0.2em] font-black block mb-2 px-1">Zamani Gelmeyen Fırsatlar</span>
                                    <div className="h-px bg-pitch-black/10 w-full"></div>
                                </div>
                                <div className="grid grid-cols-2 gap-x-3 gap-y-8 grayscale opacity-70">
                                    {flatSections.filter(s => s.campaignData && !s.campaignData.is_live).map((section) => (
                                        <section key={section.uniqueId} data-unique-id={section.uniqueId} data-group-id={section.groupId} ref={el => categoryRefs.current[section.uniqueId] = el} className="scroll-mt-72">
                                            <div
                                                onClick={() => setSelectedCampaign(section.campaignData)}
                                                className="group relative bg-white border border-pitch-black/20 shadow-[2px_2px_0_0_rgba(0,0,0,0.1)] overflow-hidden cursor-pointer active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all p-3 grayscale opacity-60"
                                                style={{ transform: `rotate(${parseInt(section.campaignData.id) % 2 === 0 ? 0.4 : -0.4}deg)` }}
                                            >
                                                {/* Compact Header (Mirroring Active) */}
                                                <div className="flex justify-end items-start mb-2">
                                                    <Icon name="Zap" size={14} className="text-pitch-black/20" />
                                                </div>

                                                <div className="bg-off-white relative min-h-[80px] flex flex-col justify-between">
                                                    <div>
                                                        <h3 className="font-heading text-lg leading-[0.9] uppercase mb-1 tracking-tighter line-clamp-2 text-pitch-black/50">{section.campaignData.name}</h3>
                                                        <p className="font-sans italic text-pitch-black/30 text-[9px] line-clamp-2 leading-tight">{section.campaignData.description}</p>
                                                    </div>

                                                    {/* Product Images Preview (Restored for consistency) */}
                                                    <div className="mt-4 flex items-center justify-between border-t border-pitch-black/10 pt-2">
                                                        <div className="flex -space-x-2">
                                                            {section.campaignData.subcategories?.[0]?.items?.slice(0, 3).map((item, idx) => (
                                                                <div key={item.id} className="w-5 h-5 rounded-full border border-pitch-black/20 bg-white overflow-hidden" style={{ zIndex: 3 - idx }}>
                                                                    <img src={item.image} loading="lazy" className="w-full h-full object-cover opacity-50" alt="" />
                                                                </div>
                                                            ))}
                                                        </div>
                                                        <Icon name="ArrowRight" size={12} className="text-pitch-black/20" />
                                                    </div>
                                                </div>
                                                {/* Decorative perforation line */}
                                                <div className="absolute -bottom-1 left-0 right-0 h-1 bg-white border-t border-dashed border-pitch-black/10"></div>
                                            </div>
                                        </section>
                                    ))}
                                </div>
                            </>
                        )}
                    </>
                ) : (
                    <div className="space-y-16">
                        {flatSections.map((section) => (
                            <section key={section.uniqueId} data-unique-id={section.uniqueId} data-group-id={section.groupId} ref={el => categoryRefs.current[section.uniqueId] = el} className="scroll-mt-72">
                                <div className="mb-6">
                                    <span className="font-mono text-xs text-pitch-black/40 uppercase tracking-widest block mb-1">{section.groupName}</span>
                                    <h3 className="font-heading text-2xl uppercase tracking-tighter text-pitch-black flex items-center gap-3">
                                        <span className="w-1.5 h-6 bg-pub-gold"></span>{section.name}
                                    </h3>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    {section.items.map((item) => (
                                        <div key={`${section.uniqueId}-${item.id}`}
                                            onClick={() => openDetails(item)}
                                            className="group relative flex flex-col bg-white border-2 border-pitch-black shadow-[4px_4px_0_0_#000] hover:shadow-[6px_6px_0_0_var(--color-pub-gold)] active:shadow-none active:translate-x-1 active:translate-y-1 transition-all cursor-pointer"
                                        >
                                            {/* Badge & Image logic remains same for other tabs */}
                                            {item.campaign_name && (
                                                <div className="absolute -top-2 -left-4 z-30 bg-pitch-black text-pub-gold font-heading text-[10px] px-1.5 py-0 uppercase tracking-tighter -rotate-[15deg] border-2 border-pub-gold shadow-[3px_3px_0_0_#000] flex items-center gap-1 group-hover:rotate-0 transition-transform duration-300 pointer-events-none">
                                                    <Icon name="Zap" size={10} fill="currentColor" />
                                                    {item.campaign_name}
                                                </div>
                                            )}

                                            {/* Heart / Vote Button */}
                                            <button 
                                                onClick={(e) => handleVote(e, item.id)}
                                                className="absolute top-2 right-2 z-30 p-1.5 bg-white border-2 border-pitch-black shadow-[2px_2px_0_0_#000] active:shadow-none active:translate-x-0.5 active:translate-y-0.5 transition-all group/heart"
                                            >
                                                <Icon 
                                                    name="Heart" 
                                                    size={16} 
                                                    className={likedIds.includes(item.id) ? "text-red-600 fill-red-600" : "text-pitch-black/40 group-hover/heart:text-red-400"} 
                                                />
                                            </button>
                                            <div className="aspect-[4/3] bg-pitch-black overflow-hidden relative border-b-2 border-pitch-black">
                                                {item.image ? (
                                                    <img src={item.image} alt={item.name} loading="lazy" className="w-full h-full object-cover" />
                                                ) : (
                                                    <div className="w-full h-full flex items-center justify-center opacity-20">
                                                        <Icon name="Zap" size={32} className="text-pub-gold" />
                                                    </div>
                                                )}

                                                <div className="absolute bottom-0 right-0 flex flex-col items-end z-20">
                                                    {item.options ? (
                                                        item.options.slice(0, 3).map((opt, idx) => {
                                                            const isDiscounted = !!opt.campaign_price || !!opt.collective_tiers;
                                                            return (
                                                                <div key={opt.name} className={`relative font-mono text-[10px] px-2 h-auto py-1 flex items-center min-w-[70px] justify-between border-l border-t border-pitch-black/20 ${idx === 0 && !isDiscounted ? 'bg-pub-gold text-pub-gold-contrast font-bold' : 'bg-pitch-black text-white'}`}>
                                                                    <span className="uppercase leading-none tracking-tight opacity-70 mr-1">{opt.name}</span>
                                                                    {opt.collective_tiers ? (
                                                                        (() => {
                                                                            const minPrice = Math.min(...opt.collective_tiers.map(t => parseFloat(t.price)));
                                                                            return (
                                                                                <div className="flex flex-col items-end gap-0.5 my-0.5">
                                                                                    <span className="leading-none font-bold ml-auto mb-0.5">₺{parseFloat(opt.price)}</span>
                                                                                    <div className="bg-red-600 text-white px-1 py-0.5 border border-pitch-black shadow-[1px_1px_0_0_#000] text-[8px] font-black leading-none">
                                                                                        KOLEKTİF: ₺{minPrice}/AD
                                                                                    </div>
                                                                                </div>
                                                                            );
                                                                        })()
                                                                    ) : isDiscounted ? (
                                                                        <div className="flex flex-col items-end gap-0.5">
                                                                            <span className="bg-pub-gold text-pub-gold-contrast text-[9px] font-bold px-1 line-through decoration-pitch-black/80 shadow-[1px_1px_0_0_#000] border border-pitch-black relative z-10 rotate-1">
                                                                                ₺{parseFloat(opt.price)}
                                                                            </span>
                                                                            <div className="bg-red-600 text-white px-2 py-0.5 border border-pitch-black shadow-[2px_2px_0_0_#000] -rotate-1">
                                                                                <span className="leading-none font-black text-[11px]">₺{parseFloat(opt.campaign_price)}</span>
                                                                            </div>
                                                                        </div>
                                                                    ) : (
                                                                        <span className="leading-none font-bold ml-auto">₺{parseFloat(opt.price)}</span>
                                                                    )}
                                                                </div>
                                                            );
                                                        })
                                                    ) : (
                                                        item.collective_tiers ? (
                                                            (() => {
                                                                const minPrice = Math.min(...item.collective_tiers.map(t => parseFloat(t.price)));
                                                                return (
                                                                    <div className="flex flex-col items-end p-1">
                                                                        <div className="font-mono text-sm font-bold px-3 py-1 min-w-[60px] text-center shadow-[2px_2px_0_0_#000] border-l-2 border-t-2 border-pitch-black relative bg-pub-gold text-pub-gold-contrast mb-1">
                                                                            ₺{parseFloat(item.price)}
                                                                        </div>
                                                                        <div className="font-mono text-[9px] font-black px-1.5 py-0.5 text-center shadow-[2px_2px_0_0_#000] border border-pitch-black relative bg-red-600 text-white flex-shrink-0 whitespace-nowrap z-20">
                                                                            KOLEKTİF: ₺{minPrice}/AD
                                                                        </div>
                                                                    </div>
                                                                );
                                                            })()
                                                        ) : item.campaign_price ? (
                                                            <div className="flex flex-col items-end p-1">
                                                                <span className="bg-pub-gold text-pub-gold-contrast text-[10px] font-bold px-1.5 py-0.5 line-through decoration-pitch-black/80 shadow-[2px_2px_0_0_#000] border border-pitch-black relative z-10 rotate-2 mb-0.5">
                                                                    ₺{parseFloat(item.price)}
                                                                </span>
                                                                <div className="font-mono text-sm font-bold px-3 py-1 min-w-[60px] text-center shadow-[2px_2px_0_0_#000] border-2 border-pitch-black relative bg-red-600 text-white -rotate-1">
                                                                    ₺{parseFloat(item.campaign_price)}
                                                                </div>
                                                            </div>
                                                        ) : (
                                                            <div className="font-mono text-sm font-bold px-3 py-1 min-w-[60px] text-center shadow-[2px_2px_0_0_#000] border-l-2 border-t-2 border-pitch-black relative bg-pub-gold text-pub-gold-contrast">
                                                                ₺{parseFloat(item.price)}
                                                            </div>
                                                        )
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
                                        </div>
                                    ))}
                                </div>
                            </section>
                        ))}
                    </div>
                )}
            </div>

            {/* Campaign Overlay / Modal Gallery */}
            <AnimatePresence>
                {selectedCampaign && (
                    <DefaultCampaignCard
                        campaign={selectedCampaign}
                        onClose={() => setSelectedCampaign(null)}
                        openDetails={(item) => { setSelectedCampaign(null); openDetails(item); }}
                    />
                )}
            </AnimatePresence>

            {/* Bottom Sheet UI (Brutalist Pro Max) */}
            < AnimatePresence >
                {selectedItem && (
                    <>
                        <motion.div
                            initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                            onClick={() => setSelectedItem(null)}
                            className="fixed inset-0 bg-pitch-black/80 backdrop-blur-md"
                            style={{ zIndex: 200 }}
                        />
                        <motion.div
                            initial={{ y: "100%" }} animate={{ y: 0 }} exit={{ y: "100%" }}
                            transition={{ type: "spring", damping: 25, stiffness: 200 }}
                            className="fixed inset-0 bg-off-white z-[201] flex flex-col overflow-hidden h-[100dvh]"
                            style={{ touchAction: 'none', overscrollBehavior: 'contain' }}
                        >
                            {/* Texture Overlay */}
                            <div className="absolute inset-0 pointer-events-none grunge-texture opacity-10"></div>
                            
                            {/* AnimatePresence for Swapping Content */}
                            <AnimatePresence initial={false} custom={direction}>
                                <motion.div
                                    key={selectedItem.id}
                                    custom={direction}
                                    variants={swipeVariants}
                                    initial="enter"
                                    animate="center"
                                    exit="exit"
                                    transition={{
                                        x: { type: "spring", stiffness: 300, damping: 30 },
                                        opacity: { duration: 0.2 }
                                    }}
                                    drag="x"
                                    dragConstraints={{ left: 0, right: 0 }}
                                    dragElastic={0.2}
                                    dragDirectionLock={true}
                                    onDragEnd={(e, { offset, velocity }) => {
                                        const swipeThreshold = 50;
                                        if (offset.x < -swipeThreshold) {
                                            navigateProduct(1); // Next
                                        } else if (offset.x > swipeThreshold) {
                                            navigateProduct(-1); // Prev
                                        }
                                    }}
                                    className="absolute inset-0 flex flex-col"
                                >
                                     <div className="pb-14 relative z-10 flex flex-col overflow-y-auto flex-1">
                                        {/* Top Spacing - Adjusted to prevent overlap with close button */}
                                        <div className="w-full h-12 flex-shrink-0" />

                                        {/* Asymmetric Image & Vertical Info Section */}
                                        <div className="flex mb-8 mt-0 relative">
                                            {/* Left: Image (Bleeding Left) */}
                                            <div className="w-[75%] relative z-10 px-1">
                                                {(selectedItem.detail_image || selectedItem.image) ? (
                                                    <div className="aspect-[4/5] bg-pitch-black border-y-4 border-r-4 border-pitch-black shadow-[8px_8px_0_0_var(--color-pub-gold)] relative overflow-hidden -ml-1">
                                                        <img src={selectedItem.detail_image || selectedItem.image} loading="lazy" alt={selectedItem.name} className="w-full h-full object-cover" />
                                                    </div>
                                                ) : (
                                                    <div className="aspect-[4/5] bg-pitch-black border-y-4 border-r-4 border-pitch-black flex items-center justify-center -ml-1 relative">
                                                        <Icon name="Zap" size={48} className="text-pub-gold" />
                                                    </div>
                                                )}
                                            </div>

                                            {/* Right: Vertical Info Column - Re-styled as Marker Hashtags */}
                                            <div className="w-[25%] flex flex-col items-center py-4 relative h-full">
                                                <div className="absolute left-0 top-0 bottom-0 w-px bg-pitch-black/20 border-l border-dashed border-pitch-black"></div>
                                                <div className="flex flex-col gap-6 items-center justify-start h-auto min-h-full w-full px-1 overflow-y-visible pt-12 pb-4 last:pb-20">
                                                    
                                                    {/* 1. Diet Types - Highlighted Marker Style (No Icons) */}
                                                    {selectedItem.diet_types?.map((dt, i) => (
                                                        <div key={`dt-${dt.name}`} 
                                                            className="font-permanent-marker text-[15px] flex-shrink-0 flex items-center relative group"
                                                            style={{ 
                                                                color: dt.color, 
                                                                transform: `rotate(${i % 2 === 0 ? '-10deg' : '8deg'})`,
                                                                textShadow: '0px 0px 1px rgba(0,0,0,0.3)'
                                                            }}
                                                        >
                                                            <span>#{dt.name}</span>
                                                            {/* Stylized Blur for "Marker Glow" */}
                                                            <div className="absolute -inset-1 blur-[1px] opacity-20 bg-current -z-10 group-hover:opacity-40 transition-opacity"></div>
                                                        </div>
                                                    ))}

                                                    {/* 2. Regular Tags - Standard Marker Style */}
                                                    {selectedItem.tags?.map((tag, i) => (
                                                        <div key={tag} className="font-permanent-marker text-[13px] text-pitch-black/80 tracking-widest flex-shrink-0" style={{ transform: `rotate(${i % 2 === 0 ? '-15deg' : '10deg'})`, textShadow: '0px 0px 1px rgba(0,0,0,0.2)', zIndex: 10 - i }}>
                                                            #{tag.toLocaleLowerCase('tr-TR')}
                                                        </div>
                                                    ))}

                                                    {/* 3. Allergens - Warning Marker Style (No Icons) */}
                                                    {selectedItem.allergens?.map((al, i) => (
                                                        <div key={`al-${al.name}`} 
                                                            className="font-permanent-marker text-[13px] flex-shrink-0 flex items-center relative group"
                                                            style={{ 
                                                                color: al.color, 
                                                                transform: `rotate(${i % 2 === 0 ? '12deg' : '-10deg'})`,
                                                                textShadow: '0px 0px 1px rgba(0,0,0,0.3)'
                                                            }}
                                                        >
                                                            <span>#{al.name}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="px-8 flex-1">
                                            {selectedItem.campaign_name && (
                                                <div className="mb-2 bg-red-600 text-white font-heading text-xs px-2 py-0.5 uppercase tracking-widest inline-flex items-center gap-1 -rotate-1 shadow-[2px_2px_0_0_#000]">
                                                     <Icon name="Zap" size={12} fill="currentColor" />
                                                     {selectedItem.campaign_name} AKTİF
                                                </div>
                                            )}
                                            <h2 className="font-heading text-5xl uppercase leading-[0.85] tracking-tighter mb-6 text-pitch-black -ml-4 break-words">
                                                {selectedItem.name}
                                            </h2>

                                            <div className="mb-8 relative pl-6 border-l-4 border-pub-gold">
                                                <p className="font-sans text-pitch-black/80 italic text-base leading-relaxed">
                                                    {selectedItem.description || "Bu ürün için henüz bir açıklama girilmemiş."}
                                                </p>
                                            </div>

                                            <div className="mb-10 flex flex-wrap justify-around gap-4 w-full">
                                                {selectedItem.options ? (
                                                    selectedItem.options.map((opt) => (
                                                        <div key={opt.name} className="flex flex-col justify-center items-center bg-white border-2 border-pitch-black shadow-[4px_4px_0_0_#000] py-4 px-3 min-w-[100px] relative">
                                                            <span className="font-heading text-[10px] uppercase tracking-widest leading-none mb-2 text-pitch-black/60">{opt.name}</span>
                                                            {opt.collective_tiers ? (
                                                                <div className="flex flex-col items-center space-y-1">
                                                                    <span className="font-mono text-lg font-bold leading-none tracking-tighter text-pitch-black mb-1 mt-1">Tekli: ₺{parseFloat(opt.price)}</span>
                                                                    {opt.collective_tiers.map((tier, idx) => (
                                                                        <div key={idx} className="font-mono text-center shadow-[2px_2px_0_0_#000] border border-pitch-black whitespace-nowrap px-3 py-1 bg-pub-gold text-pub-gold-contrast -rotate-1 flex flex-col items-center justify-center leading-tight">
                                                                            <div className="flex items-baseline gap-0.5">
                                                                                <span className="text-sm font-black">₺{parseFloat(tier.price)}</span>
                                                                                <span className="text-[8px] opacity-80 font-bold uppercase">/ Adet</span>
                                                                            </div>
                                                                            <span className="text-[8px] font-bold opacity-60 uppercase tracking-tighter">
                                                                                {tier.quantity}'li Toplam: ₺{tier.quantity * parseFloat(tier.price)}
                                                                            </span>
                                                                        </div>
                                                                    ))}
                                                                </div>
                                                            ) : (
                                                                <span className="font-mono text-xl font-bold">₺{opt.price}</span>
                                                            )}
                                                        </div>
                                                    ))
                                                ) : (
                                                    <div className="flex flex-col justify-center items-center bg-white border-2 border-pitch-black shadow-[4px_4px_0_0_#000] py-4 px-6 min-w-[120px] relative">
                                                        <span className="font-heading text-[10px] uppercase tracking-widest leading-none mb-2 text-pitch-black/60">FİYAT</span>
                                                        {selectedItem.collective_tiers ? (
                                                            <div className="flex flex-col items-center space-y-1 w-full mt-2">
                                                                <span className="font-mono text-2xl font-black leading-none tracking-tighter mb-2 text-pitch-black text-center">TEKLİ: ₺{parseFloat(selectedItem.price)}</span>
                                                                <div className="w-full h-px bg-pitch-black/20 my-1"></div>
                                                                {selectedItem.collective_tiers.map((tier, idx) => (
                                                                    <div key={idx} className={`flex items-center justify-between w-full font-mono shadow-[3px_3px_0_0_#000] border-2 border-pitch-black whitespace-nowrap px-4 py-2 bg-pub-gold text-pub-gold-contrast ${idx % 2 === 0 ? '-rotate-1' : 'rotate-1'}`}>
                                                                        <div className="flex flex-col items-start leading-none gap-0.5">
                                                                            <span className="text-xs uppercase font-black tracking-tight">{tier.quantity}'LÜ KOLEKTİF</span>
                                                                            <span className="text-[10px] font-bold opacity-60">TOPLAM: ₺{tier.quantity * parseFloat(tier.price)}</span>
                                                                        </div>
                                                                        <div className="flex flex-col items-end leading-none">
                                                                            <div className="flex items-baseline gap-0.5">
                                                                                <span className="text-xl font-black">₺{parseFloat(tier.price)}</span>
                                                                                <span className="text-[10px] opacity-80 font-bold uppercase">/ Adet</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        ) : (
                                                            <span className="font-mono text-xl font-bold">₺{selectedItem.price}</span>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </motion.div>
                            </AnimatePresence>

                            {/* Floating UI Elements above swipeable content */}
                             <button 
                                onClick={() => setSelectedItem(null)} 
                                className="absolute top-10 right-6 z-[210] bg-white text-pitch-black p-2 border-2 border-pitch-black shadow-[4px_4px_0_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none"
                                style={{ marginTop: 'env(safe-area-inset-top, 0px)' }}
                             >
                                <Icon name="X" size={24} />
                             </button>

                        </motion.div>
                    </>
                )}
            </AnimatePresence >
            {/* Drawer & Now Playing (Docked together logic handled by NowPlaying) */}
            <MenuInteractionDrawer
                storeSlug={store?.slug}
                onFilterChange={setActiveFilters}
                venueName={store ? store.name : "MEKAN"}
                isOpen={isDrawerOpen}
                onOpen={() => setIsDrawerOpen(true)}
                onClose={() => setIsDrawerOpen(false)}
            />

        </StreetLayout >
    );
}
