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

export default function Index({ menuData = {}, store = null }) {
    const [mainTab, setMainTab] = useState('campaign'); // 'campaign' | 'drink' | 'food'
    const [activeMainCategory, setActiveMainCategory] = useState(null);
    const [activeSubCategory, setActiveSubCategory] = useState(null);
    const [selectedItem, setSelectedItem] = useState(null);
    const [selectedOption, setSelectedOption] = useState(null);
    const [activeFilters, setActiveFilters] = useState({ vegan: false, vegetarian: false, glutenFree: false });
    const [isDrawerOpen, setIsDrawerOpen] = useState(false);
    const [selectedCampaign, setSelectedCampaign] = useState(null);

    const categoryRefs = useRef({});
    const navRefs = useRef({ main: null, sub: null });
    const isManualScrolling = useRef(false);

    // Flatten data for easy rendering
    const currentGroups = menuData[mainTab] || [];
    const flatSections = useMemo(() => {
        const sections = [];
        currentGroups.forEach(group => {
            if (group.subcategories) {
                group.subcategories.forEach(sub => {
                    let filteredItems = sub.items || [];

                    if (activeFilters.vegan) {
                        filteredItems = filteredItems.filter(i => i.tags?.some(t => t.toLowerCase() === 'vegan'));
                    }
                    if (activeFilters.vegetarian) {
                        filteredItems = filteredItems.filter(i => i.tags?.some(t => t.toLowerCase().includes('vege')));
                    }
                    if (activeFilters.glutenFree) {
                        filteredItems = filteredItems.filter(i => i.tags?.some(t => t.toLowerCase().includes('gluten') || t.toLowerCase() === 'gf'));
                    }

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
    };

    return (
        <StreetLayout title={store ? store.name : "Menu"}>
            {/* Top Switch - Brutalist Sticker Style */}
            <div className="sticky top-0 z-[100] bg-off-white px-6 pt-4 pb-1">
                <div className="flex items-center border-4 border-pitch-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] bg-white overflow-visible relative h-14">
                    {/* İÇECEK */}
                    <button onClick={() => setMainTab('drink')}
                        className={`flex-1 h-full font-heading text-[13px] tracking-tight transition-colors ${mainTab === 'drink' ? 'bg-pub-gold text-pitch-black' : 'bg-white text-pitch-black/40'}`}>
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
                        className={`flex-1 h-full font-heading text-[13px] tracking-tight transition-colors ${mainTab === 'food' ? 'bg-pub-gold text-pitch-black' : 'bg-white text-pitch-black/40'}`}>
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
                                    className={`px-3 py-1 font-mono text-[9px] uppercase font-bold border transition-all ${activeSubCategory === uid ? 'bg-pitch-black text-white border-pitch-black shadow-[2px_2px_0px_0px_#ffb000]' : 'bg-white text-pitch-black border-pitch-black/20'}`}>
                                    {sub.name}
                                </button>
                            );
                        })}
                    </div>
                </nav>
            )}

            {/* List */}
            <div className={`px-6 pb-32 ${mainTab === 'campaign' ? 'mt-4' : 'mt-8'}`}>
                {mainTab === 'campaign' && (
                    <div className="mb-8 relative">
                        <span className="font-mono text-[10px] text-red-600 uppercase tracking-[0.2em] font-black block mb-2 px-1">AKTİF FIRSATLAR</span>
                        <div className="h-px bg-red-600/20 w-full"></div>
                    </div>
                )}

                <div className={mainTab === 'campaign' ? 'grid grid-cols-2 gap-x-3 gap-y-8' : 'space-y-16'}>
                    {flatSections.map((section) => (
                        <section key={section.uniqueId} data-unique-id={section.uniqueId} data-group-id={section.groupId} ref={el => categoryRefs.current[section.uniqueId] = el} className="scroll-mt-72">
                            {/* Section Header - Only for Products */}
                            {mainTab !== 'campaign' && (
                                <div className="mb-6">
                                    <span className="font-mono text-xs text-pitch-black/40 uppercase tracking-widest block mb-1">{section.groupName}</span>
                                    <h3 className="font-heading text-2xl uppercase tracking-tighter text-pitch-black flex items-center gap-3">
                                        <span className="w-1.5 h-6 bg-pub-gold"></span>{section.name}
                                    </h3>
                                </div>
                            )}

                            {/* CAMPAIGN BILLBOARD VS PRODUCT GRID */}
                            {mainTab === 'campaign' ? (
                                section.campaignData && (
                                    <div key={`${section.uniqueId}-${section.campaignData.id}`}
                                        onClick={() => setSelectedCampaign(section.campaignData)}
                                        className="group relative bg-white border-2 border-pitch-black shadow-[6px_6px_0_0_#000] overflow-hidden cursor-pointer active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all"
                                        style={{ transform: `rotate(${parseInt(section.campaignData.id) % 2 === 0 ? 0.8 : -0.8}deg)` }}
                                    >
                                        <div className="aspect-square bg-pitch-black relative border-b-2 border-pitch-black overflow-hidden">
                                            {section.campaignData.image ? (
                                                <>
                                                    <img src={section.campaignData.image} loading="lazy" className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt={section.campaignData.name} />
                                                    <div className="absolute inset-0 bg-grunge-texture opacity-20 pointer-events-none mix-blend-overlay"></div>
                                                    <div className="absolute inset-0 bg-gradient-to-t from-pitch-black/40 to-transparent pointer-events-none"></div>
                                                </>
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center opacity-30">
                                                    <Icon name="Megaphone" size={32} className="text-pub-gold" />
                                                </div>
                                            )}
                                            <div className="absolute top-2 left-2 z-10 bg-red-600 text-white font-heading text-[8px] px-2 py-0.5 -rotate-2 border border-pitch-black shadow-[2px_2px_0_0_#000] uppercase tracking-tighter">
                                                FIRSAT
                                            </div>
                                        </div>
                                        <div className="p-3 bg-grunge-texture relative min-h-[100px] flex flex-col justify-between">
                                            <div>
                                                <h3 className="font-heading text-xl leading-[0.9] uppercase mb-1 group-hover:text-red-600 transition-colors tracking-tighter line-clamp-2">{section.campaignData.name}</h3>
                                                <p className="font-sans italic text-pitch-black/60 text-[10px] line-clamp-2 leading-tight">{section.campaignData.description}</p>
                                            </div>

                                            <div className="mt-2 flex items-center justify-between border-t border-pitch-black/10 pt-2">
                                                <Icon name="ArrowRight" size={14} className="text-red-600" />
                                                <div className="flex -space-x-2">
                                                    {section.campaignData.subcategories?.[0]?.items?.slice(0, 2).map((item, idx) => (
                                                        <div key={item.id} className="w-6 h-6 rounded-full border border-pitch-black bg-white overflow-hidden" style={{ zIndex: 2 - idx }}>
                                                            <img src={item.image} loading="lazy" className="w-full h-full object-cover" alt="" />
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )
                            ) : (
                                <div className="grid grid-cols-2 gap-4">
                                    {section.items.map((item) => (
                                        <div key={`${section.uniqueId}-${item.id}`}
                                            onClick={() => openDetails(item)}
                                            className="group relative flex flex-col bg-white border-2 border-pitch-black shadow-[4px_4px_0_0_#000] hover:shadow-[6px_6px_0_0_#ffb000] active:shadow-none active:translate-x-1 active:translate-y-1 transition-all cursor-pointer"
                                        >
                                            {/* Badge & Image logic remains same for other tabs */}
                                            {item.campaign_name && (
                                                <div className="absolute -top-2 -left-4 z-30 bg-pitch-black text-pub-gold font-heading text-[10px] px-1.5 py-0 uppercase tracking-tighter -rotate-[15deg] border-2 border-pub-gold shadow-[3px_3px_0_0_#000] flex items-center gap-1 group-hover:rotate-0 transition-transform duration-300 pointer-events-none">
                                                    <Icon name="Zap" size={10} fill="currentColor" />
                                                    {item.campaign_name}
                                                </div>
                                            )}
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
                                                            const isDiscounted = !!opt.campaign_price;
                                                            return (
                                                                <div key={opt.name} className={`relative font-mono text-[10px] px-2 h-[24px] flex items-center min-w-[70px] justify-between border-l border-t border-pitch-black/20 ${idx === 0 ? 'bg-pub-gold text-pitch-black font-bold' : 'bg-pitch-black text-white'}`}>
                                                                    <span className="uppercase leading-none tracking-tight opacity-70">{opt.name}</span>
                                                                    {isDiscounted ? (
                                                                        <div className="absolute -right-1 bg-red-600 text-white px-2 py-0.5 border-2 border-pitch-black shadow-[3px_3px_0_0_#000] -rotate-2 transform group-hover:rotate-0 transition-transform z-10">
                                                                            <span className="leading-none font-black text-pub-gold text-[12px]">₺{parseFloat(opt.campaign_price)}</span>
                                                                        </div>
                                                                    ) : (
                                                                        <span className="leading-none font-bold ml-auto">₺{parseFloat(opt.price)}</span>
                                                                    )}
                                                                </div>
                                                            );
                                                        })
                                                    ) : (
                                                        <div className={`font-mono text-sm font-bold px-3 py-1 min-w-[60px] text-center shadow-[2px_2px_0_0_#000] border-l-2 border-t-2 border-pitch-black relative ${item.campaign_price ? 'bg-red-600 text-pub-gold' : 'bg-pub-gold text-pitch-black'}`}>
                                                            ₺{parseFloat(item.campaign_price || item.price)}
                                                        </div>
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
                            )}
                        </section>
                    ))}
                </div>
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
                            className="fixed inset-0 bg-pitch-black/80 backdrop-blur-md z-[100]"
                        />
                        <motion.div
                            initial={{ y: "100%" }} animate={{ y: 0 }} exit={{ y: "100%" }}
                            transition={{ type: "spring", damping: 25, stiffness: 200 }}
                            className="fixed bottom-0 left-0 right-0 bg-off-white border-t-8 border-pitch-black z-[101] max-w-md mx-auto rounded-t-[40px] overflow-hidden shadow-[0_-20px_60px_rgba(0,0,0,0.5)]"
                        >
                            {/* Texture Overlay */}
                            <div className="absolute inset-0 pointer-events-none grunge-texture opacity-10"></div>

                            <div className="w-16 h-2 bg-pitch-black/20 rounded-full mx-auto mt-6 mb-4" />

                            <div className="pb-14 relative z-10 flex flex-col h-full">
                                {/* Asymmetric Image & Vertical Info Section */}
                                <div className="flex mb-8 mt-2 relative">
                                    {/* Left: Image (Bleeding Left) */}
                                    <div className="w-[75%] relative z-10">
                                        {(selectedItem.detail_image || selectedItem.image) ? (
                                            <div className="aspect-[4/5] bg-pitch-black border-y-4 border-r-4 border-pitch-black shadow-[8px_8px_0_0_#ffb000] relative overflow-hidden -ml-1">
                                                <img src={selectedItem.detail_image || selectedItem.image} loading="lazy" alt={selectedItem.name} className="w-full h-full object-cover" />

                                                {/* Overlay Gradient for Text Readability? No, keeping it raw */}
                                            </div>
                                        ) : (
                                            <div className="aspect-[4/5] bg-pitch-black border-y-4 border-r-4 border-pitch-black flex items-center justify-center -ml-1">
                                                <Icon name="Zap" size={48} className="text-pub-gold" />
                                            </div>
                                        )}
                                    </div>

                                    {/* Right: Vertical Info Column */}
                                    <div className="w-[25%] flex flex-col items-center justify-center py-4 gap-4 relative">
                                        {/* Vertical Decorative Line */}
                                        <div className="absolute left-0 top-4 bottom-4 w-px bg-pitch-black/20 border-l border-dashed border-pitch-black"></div>

                                        {/* Chaotic Rotated Stack */}
                                        <div className="flex flex-col gap-6 items-center justify-center h-full w-full px-1">
                                            {selectedItem.tags?.map((tag, i) => (
                                                <div
                                                    key={tag}
                                                    className="font-permanent-marker text-[13px] text-pitch-black/90 tracking-widest"
                                                    style={{
                                                        transform: `rotate(${i % 2 === 0 ? '-15deg' : '10deg'})`,
                                                        textShadow: '0px 0px 1px rgba(0,0,0,0.5)',
                                                        zIndex: 10 - i
                                                    }}
                                                >
                                                    #{tag.toLocaleLowerCase('tr-TR')}
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="px-8">
                                    {/* Giant Title */}
                                    <h2 className="font-heading text-5xl uppercase leading-[0.85] tracking-tighter mb-6 italic text-pitch-black -ml-4 break-words">
                                        {selectedItem.name}
                                    </h2>

                                    {/* Description */}
                                    <div className="mb-8 relative pl-6 border-l-4 border-pub-gold">
                                        <p className="font-sans text-pitch-black/80 italic text-base leading-relaxed">
                                            {selectedItem.description || "Bu ürün için henüz bir açıklama girilmemiş."}
                                        </p>
                                    </div>

                                    {/* Price / Portions Grid */}
                                    <div className="mb-10 flex flex-wrap justify-around gap-4 w-full">
                                        {selectedItem.options ? (
                                            selectedItem.options.map((opt) => (
                                                <div key={opt.name} className="flex flex-col justify-center items-center bg-white border-2 border-pitch-black shadow-[4px_4px_0_0_#000] py-4 px-3 min-w-[100px] relative transition-transform active:scale-95">
                                                    {/* Industrial Corner Accents */}
                                                    <div className="absolute top-0.5 left-0.5 w-1 h-1 bg-pitch-black"></div>
                                                    <div className="absolute top-0.5 right-0.5 w-1 h-1 bg-pitch-black"></div>
                                                    <div className="absolute bottom-0.5 left-0.5 w-1 h-1 bg-pitch-black"></div>
                                                    <div className="absolute bottom-0.5 right-0.5 w-1 h-1 bg-pitch-black"></div>

                                                    <span className="font-heading text-[10px] uppercase tracking-widest leading-none mb-2 text-pitch-black/60">{opt.name}</span>
                                                    <span className="font-mono text-xl font-bold leading-none tracking-tighter">₺{opt.price}</span>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="flex flex-col justify-center items-center bg-white border-2 border-pitch-black shadow-[4px_4px_0_0_#000] py-4 px-6 min-w-[120px] relative">
                                                {/* Industrial Corner Accents */}
                                                <div className="absolute top-0.5 left-0.5 w-1 h-1 bg-pitch-black"></div>
                                                <div className="absolute top-0.5 right-0.5 w-1 h-1 bg-pitch-black"></div>
                                                <div className="absolute bottom-0.5 left-0.5 w-1 h-1 bg-pitch-black"></div>
                                                <div className="absolute bottom-0.5 right-0.5 w-1 h-1 bg-pitch-black"></div>

                                                <span className="font-heading text-[10px] uppercase tracking-widest leading-none mb-2 text-pitch-black/60">FİYAT</span>
                                                <span className="font-mono text-xl font-bold leading-none tracking-tighter">₺{selectedItem.price}</span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Massive Footer Button */}
                                    <button onClick={() => setSelectedItem(null)} className="w-full bg-pitch-black text-white py-6 font-heading text-2xl uppercase heavy-shadow hover:bg-pub-gold hover:text-pitch-black transition-all active:translate-x-1 active:translate-y-1 active:shadow-none relative overflow-hidden group">
                                        <span className="relative z-10">KAPAT</span>
                                        {/* Hover Effect */}
                                        <div className="absolute inset-0 bg-pub-gold transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                    </button>
                                </div>
                            </div>
                        </motion.div>
                    </>
                )}
            </AnimatePresence >
            {/* Drawer & Now Playing (Docked together logic handled by NowPlaying) */}
            <MenuInteractionDrawer
                onFilterChange={setActiveFilters}
                venueName={store ? store.name : "MEKAN"}
                isOpen={isDrawerOpen}
                onOpen={() => setIsDrawerOpen(true)}
                onClose={() => setIsDrawerOpen(false)}
            />

        </StreetLayout >
    );
}
