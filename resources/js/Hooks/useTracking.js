import { useEffect, useCallback, useRef } from 'react';
import axios from 'axios';

/**
 * Custom Hook for Anonymous Visitor & Interaction Tracking
 */
export const useTracking = (activeCategory, activeProduct) => {
    const heartbeatInterval = useRef(null);
    const lastCategory = useRef(null);

    // Simple Canvas Fingerprint
    const generateFingerprint = useCallback(() => {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            ctx.textBaseline = "top";
            ctx.font = "14px 'Arial'";
            ctx.fillText("QR-Menu-v1", 2, 2);
            const canvasHash = canvas.toDataURL();

            // Add other entropy for better recognition in strict browsers (Firefox RFP)
            const entropy = [
                canvasHash,
                navigator.userAgent,
                navigator.language,
                screen.width + "x" + screen.height,
                new Date().getTimezoneOffset(),
                navigator.hardwareConcurrency || 0
            ].join('|');

            // Simple hash of entropy string
            let hash = 0;
            for (let i = 0; i < entropy.length; i++) {
                hash = ((hash << 5) - hash) + entropy.charCodeAt(i);
                hash |= 0;
            }
            return Math.abs(hash).toString(16);
        } catch (e) {
            return 'unknown';
        }
    }, []);

    // Send Heartbeat / Continuous view log
    const sendHeartbeat = useCallback((category) => {
        if (!category) return;
        
        axios.post('/tracking/hit', {
            type: 'heartbeat',
            model: 'Category',
            id: category, // This should be the database ID if possible, or we resolve on backend
            duration: 30
        }).catch(() => {});
    }, []);

    // Log individual interaction
    const trackClick = useCallback((model, id) => {
        axios.post('/tracking/hit', {
            type: 'click',
            model: model,
            id: id
        }).catch(() => {});
    }, []);

    useEffect(() => {
        // 1. Initial Fingerprint Sync
        const hash = generateFingerprint();
        const visitorId = document.cookie.match(/qr_menu_visitor_id=([^;]+)/)?.[1];
        
        // Wait a bit for the middleware to set the cookie if it's the first visit
        setTimeout(() => {
            axios.post('/tracking/fingerprint', {
                hash: hash,
                tracking_uuid: visitorId // Helper for backend
            }).then(response => {
                if (response.data.status === 'recovered' && response.data.uuid) {
                    // If identity restored, we should ideally refresh or update local state
                    // but for now, the cookie update from backend is enough
                }
            }).catch(() => {});
        }, 1000);

        // 2. Setup Heartbeat
        heartbeatInterval.current = setInterval(() => {
            if (lastCategory.current) {
                sendHeartbeat(lastCategory.current);
            }
        }, 30000);

        return () => {
            if (heartbeatInterval.current) clearInterval(heartbeatInterval.current);
        };
    }, []);

    // Track Category View Changes
    useEffect(() => {
        if (activeCategory && activeCategory !== lastCategory.current) {
            lastCategory.current = activeCategory;
            
            // Log immediate view
            axios.post('/tracking/hit', {
                type: 'view',
                model: 'Category',
                id: activeCategory
            }).catch(() => {});
        }
    }, [activeCategory]);

    return { trackClick };
};
